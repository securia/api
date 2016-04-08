<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is cron job to reset Weekly Tournament Results
 *
 *     php artisan cron:resetWeeklyTournament
 *
 */
class AddPlayerPuzzlesCollections extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:addPlayerPuzzlesCollections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'addPlayerPuzzlesCollections cron will create appropriate collections to distribute load of puzzles across player_daily_puzzles collections logically by player_id';

    /**
     * Create a new command instance.
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
    public function fire()
    {
        try {

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/cron/addPlayerPuzzlesCollections', 'POST');
            $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();

            $this->info($response);

        } catch (\Exception $e) {
            $this->error("Oops something is wrong. Please check your script before executing next time");
            $this->error("Error in file: " . $e->getFile());
            $this->error("Exception Message: " . $e->getMessage());
            $this->error("At line number: " . $e->getLine());
        }
    }

}
