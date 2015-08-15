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
        DB::statement('DELETE FROM chunks_tmp');
        DB::statement('ALTER TABLE chunks_tmp AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE chunk_data AUTO_INCREMENT = 1;');

        DB::statement('DELETE FROM keyframes_tmp');
        DB::statement('ALTER TABLE keyframes_tmp AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE keyframes_data AUTO_INCREMENT = 1;');

        DB::statement('SET unique_checks=0');
        DB::statement('SET foreign_key_checks=0');

        $this->comment('Migrating chunks');

        $chunksNumTotal = DB::table('chunks')->count();
        $this->output->progressStart();

        $chunkCount = 100;
        $lastId = $chunkCount;

        $totalDone = 0;

        while($totalDone < $chunksNumTotal) {
            $chunks = DB::table('chunks')->whereBetween('id', [$lastId - $chunkCount + 1, $lastId])->get();

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

                $this->output->progressAdvance();
                $totalDone++;
            }

            DB::commit();

            $lastId += $chunkCount;
        }

        $this->output->progressFinish();

        $this->comment('Migrating keyframes');

        $keyframesNumTotal = DB::table('keyframes')->count();
        $this->output->progressStart();

        $totalDone = 0;
        $lastId = $chunkCount;

        while($totalDone < $keyframesNumTotal) {
            $keyframes = DB::table('keyframes')->whereBetween('id', [$lastId - $chunkCount + 1, $lastId])->get();

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

                $this->output->progressAdvance();
                $totalDone++;
            }

            DB::commit();

            $lastId += $chunkCount;
        }

        $this->output->progressFinish();

        DB::statement('SET unique_checks=1');
        DB::statement('SET foreign_key_checks=1');
    }
}
