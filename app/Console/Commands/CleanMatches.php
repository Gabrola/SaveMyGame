<?php

namespace App\Console\Commands;

use App\Models\Chunk;
use App\Models\Keyframe;
use DB;
use File;
use LeagueHelper;
use Illuminate\Console\Command;

class CleanMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:migrate {type=chunks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate to new database format.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');

        $chunkStart = 3864744;
        $keyframeStart = 1802822;

        if($type == 'chunks') {
            $count = Chunk::where('id', '>=', $chunkStart)->count();

            $this->comment('Migrating chunk data');
            $this->output->progressStart($count);

            Chunk::where('id', '>=', $chunkStart)->with('chunkData')->chunk(10000, function ($chunks) {
                /** @var Chunk $chunk */
                foreach ($chunks as $chunk) {
                    @File::put(LeagueHelper::getChunkFilePath($chunk->platform_id, $chunk->game_id, $chunk->chunk_id),
                        $chunk->chunkData->chunk_data);

                    $this->output->progressAdvance();
                }
            });

            $this->output->progressFinish();
        } else {
            $count = Keyframe::where('id', '>=', $keyframeStart)->count();

            $this->comment('Migrating keyframe data');
            $this->output->progressStart($count);

            Keyframe::where('id', '>=', $keyframeStart)->chunk(10000, function ($keyframes) {
                /** @var Keyframe $keyframe */
                foreach ($keyframes as $keyframe) {
                    @File::put(LeagueHelper::getKeyframeFilePath($keyframe->platform_id, $keyframe->game_id, $keyframe->keyframe_id),
                        $keyframe->keyframeData->keyframe_data);

                    $this->output->progressAdvance();
                }
            });

            $this->output->progressFinish();
        }
    }
}
