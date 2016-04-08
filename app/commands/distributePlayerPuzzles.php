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
class DistributePlayerPuzzles extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'patch:distributePlayerPuzzles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'distributePlayerPuzzles will distribute players puzzles in appropriate player_daily_puzzles collection based on the player_id';

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

            /**
             * Process Options
             */
            $inputs = array();

            if (false == is_null($this->option('from')) && 0 < $this->option('from')) {
                $inputs['from'] = $this->option('from');
            }

            if (false == is_null($this->option('to')) && 0 < $this->option('to')) {
                $inputs['to'] = $this->option('to');
            }

            if (false == is_null($this->option('together')) && 0 < $this->option('together')) {
                $inputs['together'] = $this->option('together');
            }


            \Illuminate\Support\Facades\Input::merge($inputs);

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/patch/distributePlayerPuzzles', 'POST', $inputs);
            $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();

            $this->info($response);

        } catch (\Exception $e) {
            $this->error("Oops something is wrong. Please check your script before executing next time");
            $this->error("Error in file: " . $e->getFile());
            $this->error("Exception Message: " . $e->getMessage());
            $this->error("At line number: " . $e->getLine());
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('from', null, InputOption::VALUE_REQUIRED, 'From player id'),
            array('to', null, InputOption::VALUE_REQUIRED, 'To player id'),
            array('together', null, InputOption::VALUE_REQUIRED, 'Total records process together'),
        );
    }

}
