<?php
namespace App\Http\Controllers;

use App\Models\Chunk;
use App\Models\Game;
use Cache;
use File;
use LeagueHelper;
use Request;

class SpectatorController extends Controller
{
    public function version()
    {
        return '1.82.86';
    }

    public function getGameMetaData($region, $gameId, $num)
    {
        /** @var Game $game */
        $game = Game::byGame($region, $gameId)->firstOrFail();
        $lastChunk = Chunk::byGame($region, $gameId)->lastChunk()->firstOrFail();

        return response()->json(
            [
                'gameKey' => [
                    'gameId' => $game->game_id,
                    'platformId' => $game->platform_id
                ],
                'gameServerAddress' => '',
                'port' => 0,
                'encryptionKey' => '',
                'chunkTimeInterval' => 30000,
                'startTime' => 'Jan 1, 1970 12:00:00 AM',
                'gameEnded' => true,
                'lastChunkId' => $lastChunk->chunk_id,
                'lastKeyFrameId' => $lastChunk->keyframe_id,
                'endStartupChunkId' => $game->end_startup_chunk_id,
                'delayTime' => 150000,
                'pendingAvailableChunkInfo' => [],
                'pendingAvailableKeyFrameInfo' => [],
                'keyFrameTimeInterval' => 60000,
                'decodedEncryptionKey' => '',
                'startGameChunkId' => $game->start_game_chunk_id,
                'gameLength' => 0,
                'clientAddedLag' => 30000,
                'clientBackFetchingEnabled' => false,
                'clientBackFetchingFreq' => 1000,
                'interestScore' => $game->interest_score,
                'featuredGame' => false,
                'createTime' => date('M j, Y g:i:s A', strtotime($game->created_at)),
                'endGameChunkId' => $lastChunk->chunk_Id,
                'endGameKeyFrameId' => $lastChunk->keyframe_id
            ]
        );
    }

    public function getLastChunkInfo($region, $gameId, $num)
    {
        /** @var Game $game */
        $game = Game::byGame($region, $gameId)->firstOrFail();

        $requestHost = Request::getHost();

        if(str_contains($requestHost, '.alt.')) {
            $domainParts = explode('.', $requestHost);
            $randomPart = $domainParts[0];

            $cacheKey = $randomPart . '_' . Request::getClientIp() . '_' . $game->platform_id . $game->game_id;

            if(Cache::get($cacheKey, 1) < $game->end_startup_chunk_id) {
                /** @var Chunk $firstChunk */
                $firstChunk = Chunk::byGame($region, $gameId)->startGame($game->start_game_chunk_id)->firstOrFail();

                if($firstChunk->next_chunk_id != $firstChunk->chunk_id)
                    $firstChunk = Chunk::byGame($region, $gameId)->whereChunkId($firstChunk->chunk_id + 1)->first();

                if($firstChunk) {
                    if (Cache::has($cacheKey))
                        Cache::increment($cacheKey);
                    else
                        Cache::add($cacheKey, 1, 60);

                    return response()->json(
                        [
                            'chunkId' => $firstChunk->chunk_id,
                            'availableSince' => 30000,
                            'nextAvailableChunk' => 0,
                            'keyFrameId' => $firstChunk->keyframe_id,
                            'nextChunkId' => $firstChunk->next_chunk_id,
                            'endStartupChunkId' => $game->end_startup_chunk_id,
                            'startGameChunkId' => $game->start_game_chunk_id,
                            'endGameChunkId' => $firstChunk->chunk_id,
                            'duration' => $firstChunk->duration
                        ]
                    );
                }
            }
        } else if(str_contains($requestHost, '.partial.') && substr_count($requestHost, '.') == 4) {
            $domainParts = explode('.', $requestHost);
            $randomPart = $domainParts[0];
            $chunks = LeagueHelper::chunksFromPartialInt($domainParts[1]);

            if(count($chunks) != 2)
                abort(400);

            /** @var Chunk $firstChunk */
            /** @var Chunk $lastChunk */
            $firstChunk = Chunk::byGame($region, $gameId)->whereChunkId($chunks[0] + $game->start_game_chunk_id)->firstOrFail();
            $lastChunk = Chunk::byGame($region, $gameId)->whereChunkId($chunks[1] + $game->start_game_chunk_id)->firstOrFail();

            $cacheKey = $randomPart . '_' . Request::getClientIp() . '_' . $game->platform_id . $game->game_id;

            if(Cache::get($cacheKey, 1) < $game->end_startup_chunk_id + 2) {
                if (Cache::has($cacheKey))
                    Cache::increment($cacheKey);
                else
                    Cache::add($cacheKey, 1, 60);

                return response()->json(
                    [
                        'chunkId' => $firstChunk->chunk_id,
                        'availableSince' => 30000,
                        'nextAvailableChunk' => 0,
                        'keyFrameId' => $firstChunk->keyframe_id,
                        'nextChunkId' => $firstChunk->next_chunk_id,
                        'endStartupChunkId' => $game->end_startup_chunk_id,
                        'startGameChunkId' => $game->start_game_chunk_id,
                        'endGameChunkId' => $lastChunk->chunk_id,
                        'duration' => $firstChunk->duration
                    ]
                );
            }

            return response()->json(
                [
                    'chunkId' => $lastChunk->chunk_id,
                    'availableSince' => 30000,
                    'nextAvailableChunk' => 0,
                    'keyFrameId' => $lastChunk->keyframe_id,
                    'nextChunkId' => $lastChunk->chunk_id,
                    'endStartupChunkId' => $game->end_startup_chunk_id,
                    'startGameChunkId' => $game->start_game_chunk_id,
                    'endGameChunkId' => $lastChunk->chunk_id,
                    'duration' => $lastChunk->duration
                ]
            );
        }

        $lastChunk = Chunk::byGame($region, $gameId)->lastChunk()->firstOrFail();

        return response()->json(
            [
                'chunkId' => $lastChunk->chunk_id,
                'availableSince' => 30000,
                'nextAvailableChunk' => 0,
                'keyFrameId' => $lastChunk->keyframe_id,
                'nextChunkId' => $lastChunk->chunk_id,
                'endStartupChunkId' => $game->end_startup_chunk_id,
                'startGameChunkId' => $game->start_game_chunk_id,
                'endGameChunkId' => $lastChunk->chunk_id,
                'duration' => $lastChunk->duration
            ]
        );
    }

    public function getGameDataChunk($region, $gameId, $num)
    {
        return response()->make(File::get(LeagueHelper::getChunkFilePath($region, $gameId, $num)), '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }

    public function getKeyFrame($region, $gameId, $num)
    {
        return response()->make(File::get(LeagueHelper::getKeyframeFilePath($region, $gameId, $num)), '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }

    public function endOfGameStats($region, $gameId, $num)
    {
        /** @var Game $game */
        $game = Game::byGame($region, $gameId)->firstOrFail();

        if($game->end_stats == null)
            abort(404);

        return response()->make(gzdecode($game->end_stats), '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }
}