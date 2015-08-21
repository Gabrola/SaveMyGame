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
        $count = DB::table('games')->where('id', '>', 59000)->count();

        $this->output->progressStart($count);

        DB::table('games')->where('id', '>', 59000)->chunk(10000, function($games){
            foreach($games as $game) {
                $replayDirectory = LeagueHelper::getReplayDirectory($game->platform_id, $game->game_id);

                File::makeDirectory($replayDirectory, 0755, true, true);

                File::put($replayDirectory . DIRECTORY_SEPARATOR . 'endStats',
                    gzencode($game->end_stats));

                File::put($replayDirectory . DIRECTORY_SEPARATOR . 'events',
                    gzencode($game->events));

                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
    }
}
