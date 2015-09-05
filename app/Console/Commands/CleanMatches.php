<?php

namespace App\Console\Commands;

use App\Models\ClientVersion;
use App\Models\Game;
use Carbon\Carbon;
use DB;
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
        $versions = DB::table('client_versions')->select(['client_version'])->groupBy('release_version')->orderBy('id', 'desc')->get();
        $last2Versions = $versions[0];
        $patchNumber = LeagueHelper::getPatchFromVersion($last2Versions->client_version);

        $this->info($patchNumber);

        return;

        $count = Game::where('created_at', '<', $sevenDaysAgo)->where('created_at', '>', $lastPatchDate)->where('status', '!=', 'deleted')->count('id');
        $bar = $this->output->createProgressBar($count);
        $bar->setRedrawFrequency(100);
        $games = Game::where('created_at', '<', $sevenDaysAgo)->where('created_at', '>', $lastPatchDate)->get();

        foreach($games as $game)
        {
            /*if($game->status != 'deleted') {
                $gameEndStats = $game->end_stats;
                if (!$gameEndStats || LeagueHelper::comparePatch(config('clientversion', '0.0.0.0'), $gameEndStats['matchVersion']))
                    $game->deleteReplay();
            }*/

            $bar->advance();
        }

        $bar->finish();
    }
}
