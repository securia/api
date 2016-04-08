<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is script to Index processing
 *
 *     php artisan script:Indexes
 *
 */
class Indexes extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'script:Indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap data to Mongo';

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

            switch ($this->argument('action')) {
                case 'add':
                    $this->comment('add : Start');

                    $script = '/bootstrap/createIndexes';
                    if ('mongo' == strtolower($this->argument('type'))) {
                        $script = '/bootstrap/createMongoIndexes';
                    }

                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0' . $script, 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);

                    $this->comment('add : End');
                    break;

                case 'remove':
                    $this->comment('remove : Start');

                    $script = '/bootstrap/deleteIndexes';
                    if ('mongo' == strtolower($this->argument('type'))) {
                        $script = '/bootstrap/deleteMongoIndexes';
                    }

                    $request = \Illuminate\Support\Facades\Request::create('scripts/4.0' . $script, 'POST');
                    $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
                    $this->info($response);
                    $this->comment('remove : End');
                    break;

                default:
                    $this->error('Please provide valid action: add | remove');
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
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('action', InputOption::VALUE_REQUIRED, 'Bootstrap setup script'),
            array('type', InputOption::VALUE_REQUIRED, 'Type of script: Mongo')
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}