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
        $this->comment('Migrating chunks');

        $this->output->progressStart(DB::table('chunks')->count());

        DB::table('chunks')->select(['id', 'chunk_data'])->chunk(100, function($chunks) {
            foreach($chunks as $chunk) {
                $id = DB::table('chunk_data')->insertGetId([
                    'chunk_id'      => $chunk->id,
                    'chunk_data'    => $chunk->chunk_data
                ]);

                DB::table('chunks')->where('id', $chunk->id)->update([
                    'chunk_data_id' => $id
                ]);
            }

            $this->output->progressAdvance(100);
        });

        $this->output->progressFinish();

        $this->comment('Migrating keyframes');

        $this->output->progressStart(DB::table('keyframes')->count());

        DB::table('keyframes')->select(['id', 'keyframe_data'])->chunk(100, function($keyframes) {
            foreach($keyframes as $keyframe) {
                $id = DB::table('keyframe_data')->insertGetId([
                    'keyframe_id'      => $keyframe->id,
                    'keyframe_data'    => $keyframe->keyframe_data
                ]);

                DB::table('keyframes')->where('id', $keyframe->id)->update([
                    'keyframe_data_id' => $id
                ]);
            }

            $this->output->progressAdvance(100);
        });

        $this->output->progressFinish();
    }
}
