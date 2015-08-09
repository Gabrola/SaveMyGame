<?php
namespace App\Http\Controllers;

class SpectatorController extends Controller
{
    public function version()
    {
        return '1.82.86';
    }

    public function getGameMetaData(/*$randomID,*/ $region, $gameId, $num)
    {
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::byGame($region, $gameId)->firstOrFail();
        $firstChunk = \App\Models\Chunk::whereDbGameId($game->id)->startGame($game->start_game_chunk_id)->firstOrFail();
        $lastChunk = \App\Models\Chunk::byGame($region, $gameId)->lastChunk()->firstOrFail();

        return \Response::json(
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
                'startGameChunkId' => $firstChunk->chunk_id,
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

    public function getLastChunkInfo(/*$randomID,*/ $region, $gameId, $num)
    {
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::byGame($region, $gameId)->firstOrFail();
        $firstChunk = \App\Models\Chunk::whereDbGameId($game->id)->startGame($game->start_game_chunk_id)->firstOrFail();
        $lastChunk = \App\Models\Chunk::byGame($region, $gameId)->lastChunk()->firstOrFail();

        return \Response::json(
            [
                'chunkId' => $lastChunk->chunk_id,
                'availableSince' => 30000,
                'nextAvailableChunk' => 0,
                'keyFrameId' => $lastChunk->keyframe_id,
                'nextChunkId' => $lastChunk->chunk_id,
                'endStartupChunkId' => $game->end_startup_chunk_id,
                'startGameChunkId' => $firstChunk->chunk_id,
                'endGameChunkId' => $lastChunk->chunk_id,
                'duration' => $lastChunk->duration
            ]
        );
    }

    public function getGameDataChunk(/*$randomID,*/ $region, $gameId, $num)
    {
        /** @var \App\Models\Chunk $chunk */
        $chunk = \App\Models\Chunk::byGame($region, $gameId)->whereChunkId($num)->firstOrFail();

        return \Response::make($chunk->chunk_data, '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }

    public function getKeyFrame(/*$randomID,*/ $region, $gameId, $num)
    {
        /** @var \App\Models\Keyframe $keyframe */
        $keyframe = \App\Models\Keyframe::byGame($region, $gameId)->whereKeyframeId($num)->firstOrFail();

        return \Response::make($keyframe->keyframe_data, '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }

    public function endOfGameStats(/*$randomID,*/ $region, $gameId, $num)
    {
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::byGame($region, $gameId)->firstOrFail();

        if($game->end_stats == null)
            abort(404);

        return \Response::make(gzdecode($game->end_stats), '200', array(
            'Content-Type' => 'application/octet-stream'
        ));
    }
}