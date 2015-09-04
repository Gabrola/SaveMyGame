<?php

namespace App\Console\Commands;

use App\Models\Game;
use Carbon\Carbon;
use LeagueHelper;
use Illuminate\Console\Command;

class CleanMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old matches.';

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
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $count = Game::where('created_at', '<', $sevenDaysAgo)->count('id');
        $bar = $this->output->createProgressBar($count);
        $bar->setRedrawFrequency(100);
        Game::where('created_at', '<', $sevenDaysAgo)->chunk(1000, function($games) use (&$bar){
            /** @var Game $game */
            foreach($games as $game)
            {
                $bar->advance();
                /*$gameEndStats = $game->end_stats;
                if (!$gameEndStats || LeagueHelper::comparePatch(config('clientversion', '0.0.0.0'), $gameEndStats['matchVersion']))
                    $game->deleteReplay();*/
            }
        });

        $bar->finish();
    }
}
