<?php

namespace App\Console\Commands;

use App\Models\Game;
use DB;
use Illuminate\Console\Command;

class CleanMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:clean {skip=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean matches from chunks not used.';

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
        $skip = $this->argument('skip');

        $this->output->progressStart(Game::count());
        $this->output->progressAdvance($skip);

        $games = DB::table('games')->select(['id', 'status', 'end_startup_chunk_id'])->get();
        $games = array_slice($games, $skip);

        $count = 0;
        $gameIds = [];

        foreach($games as $game) {
            $this->output->progressAdvance();

            if($game->status != 'downloaded')
                continue;

            $countChunks = DB::table('chunks')->where('db_game_id', $game->id)->whereBetween('chunk_id', [1, $game->end_startup_chunk_id])->count();

            if($game->end_startup_chunk_id != $countChunks) {
                $count++;
                $gameIds[] = $game->id;

                if($count % 200 == 0){
                    DB::table('chunks')->whereIn('db_game_id', $gameIds)->delete();
                    DB::table('keyframes')->whereIn('db_game_id', $gameIds)->delete();
                    DB::table('games')->whereIn('id', $gameIds)->update(['status' => 'deleted']);

                    $gameIds = [];
                }
            }
        }

        $this->output->progressFinish();
    }
}
