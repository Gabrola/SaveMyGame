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
        $previousVersion = DB::table('client_versions')->select(['client_version'])->groupBy('release_version')->orderBy('id', 'desc')->offset(1)->first();
        $patchNumber = LeagueHelper::getPatchFromVersion($previousVersion->client_version);

        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $query = Game::wherePatch($patchNumber)->where('status', '!=', 'deleted')->where('created_at', '<', $sevenDaysAgo);
        $count = $query->count('id');

        $bar = $this->output->createProgressBar($count);

        $games = $query->select(['id', 'platform_id', 'game_id'])->get();

        foreach($games as $game) {
            $bar->advance();
            $game->deleteReplay();
        }

        $query->update(['status' => 'deleted']);
        $bar->finish();
    }
}
