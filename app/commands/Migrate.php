<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is cron job to Migrate data from Mysql
 *
 *     php artisan script:Migrate
 *
 */
class Migrate extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'script:Migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate MySql data to Mongo';

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
            global $appConfig;

            $this->comment('Script : Start');

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

            if (false == is_null($this->option('merge')) && 1 == $this->option('merge')) {
                $inputs['merge'] = $this->option('merge');
            }

            if (false == is_null($this->option('csv_generation')) && 0 == $this->option('csv_generation')) {
                $inputs['csv_generation'] = $this->option('csv_generation');
            }

            \Illuminate\Support\Facades\Input::merge($inputs);
            switch ($this->argument('action')) {
                case 'Players':
                    $this->comment('Migrate Players : Start');
                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/migration/migratePlayers', 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('Migrate Players : End');
                    break;

                case 'Devices':
                    $this->comment('Migrate Devices : Start');
                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/migration/migrateDevices', 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('Migrate Devices : End');
                    break;

                case 'Puzzles':
                    $this->comment('Migrate Puzzles : Start');
                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/migration/migratePuzzles', 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('Migrate Puzzles : End');
                    break;

                case 'PlayerDevices':
                    $this->comment('Migrate PlayerDevices : Start');
                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/migration/migratePlayerDevices', 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('Migrate PlayerDevices : End');
                    break;

                case 'PlayerPuzzles':
                    $this->comment('Migrate PlayerPuzzles : Start');
                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/migration/migratePlayerPuzzles', 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('Migrate PlayerPuzzles : End');
                    break;

                default:
                    $this->error('Please provide valid migration type.');
                    break;

            }

            $this->comment('Script : End');

        } catch (\Exception $e) {
            $this->error("Oops something is wrong. Please check your script before executing next time");
            $this->error("Error in file: " . $e->getFile());
            $this->error("Exception Message: " . $e->getMessage());
            $this->error("At line number: " . $e->getLine());
        }
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('action', null, InputOption::VALUE_REQUIRED, 'Script to be run')
        );
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('from', null, InputOption::VALUE_REQUIRED, 'From id'),
            array('to', null, InputOption::VALUE_REQUIRED, 'To id'),
            array('merge', null, InputOption::VALUE_REQUIRED, 'Merge Boolean'),
            array('csv_generation', null, InputOption::VALUE_REQUIRED, 'CSV Generation Boolean'),
        );
    }

}
