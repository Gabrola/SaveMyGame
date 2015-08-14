<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class CleanMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:migrate';

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
        DB::raw('SET unique_checks=0');
        DB::raw('SET foreign_key_checks=0');

        $this->comment('Migrating chunks');

        $this->output->progressStart(DB::table('chunks')->count());

        DB::table('chunks')->chunk(200, function($chunks) {
            DB::beginTransaction();

            foreach($chunks as $chunk) {
                $chunkInsertionId = DB::table('chunks_tmp')->insertGetId([
                    'db_game_id'    =>  $chunk->db_game_id,
                    'platform_id'   =>  $chunk->platform_id,
                    'game_id'       =>  $chunk->game_id,
                    'chunk_id'      =>  $chunk->chunk_id,
                    'keyframe_id'   =>  $chunk->keyframe_id,
                    'next_chunk_id' =>  $chunk->next_chunk_id,
                    'duration'      =>  $chunk->duration
                ]);

                $id = DB::table('chunk_data')->insertGetId([
                    'chunk_id'      => $chunkInsertionId,
                    'chunk_data'    => $chunk->chunk_data
                ]);

                DB::table('chunks_tmp')->where('id', $chunkInsertionId)->update([
                    'chunk_data_id' => $id
                ]);
            }

            DB::commit();

            $this->output->progressAdvance(200);
        });

        $this->output->progressFinish();

        $this->comment('Migrating keyframes');

        $this->output->progressStart(DB::table('keyframes')->count());

        DB::table('keyframes')->select(['id', 'keyframe_data'])->chunk(200, function($keyframes) {
            DB::beginTransaction();

            foreach($keyframes as $keyframe) {
                $keyframeInsertionId = DB::table('keyframes_tmp')->insertGetId([
                    'db_game_id'    =>  $keyframe->db_game_id,
                    'platform_id'   =>  $keyframe->platform_id,
                    'game_id'       =>  $keyframe->game_id,
                    'keyframe_id'   =>  $keyframe->keyframe_id,
                ]);

                $id = DB::table('keyframe_data')->insertGetId([
                    'keyframe_id'      => $keyframeInsertionId,
                    'keyframe_data'    => $keyframe->keyframe_data
                ]);

                DB::table('keyframes_tmp')->where('id', $keyframeInsertionId)->update([
                    'keyframe_data_id' => $id
                ]);
            }

            DB::commit();

            $this->output->progressAdvance(200);
        });

        $this->output->progressFinish();

        DB::raw('SET unique_checks=1');
        DB::raw('SET foreign_key_checks=1');
    }
}
