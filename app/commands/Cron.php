<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This file is for Cron
 *
 * php artisan cron updateDashboard
 *
 */
class Cron extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cron will be called from this file';

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
            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/cron/' . $this->argument('action'), 'POST');
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('action', InputOption::VALUE_REQUIRED, 'Cron script name')
        );
    }
}
