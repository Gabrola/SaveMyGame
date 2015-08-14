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
    protected $signature = 'replay:clean';

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
        $this->output->progressStart(Game::count());

        DB::table('games')->chunk(100, function($games){
            foreach($games as $game) {
                $this->output->progressAdvance();

                if($game->status != 'downloaded')
                    continue;

                $countChunks = DB::table('chunks')->where('db_game_id', $game->id)->whereBetween('chunk_id', [1, $game->end_startup_chunk_id])->count();

                if($game->end_startup_chunk_id != $countChunks) {
                    DB::table('chunks')->where('db_game_id', $game->id)->delete();
                    DB::table('keyframes')->where('db_game_id', $game->id)->delete();
                    DB::table('games')->where('id', $game->id)->update(['status' => 'deleted']);
                }
            }
        });

        $this->output->progressFinish();
    }
}
