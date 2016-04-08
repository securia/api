<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is script for Aws
 *
 *     php artisan script:Patch
 *
 */
class Aws extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'script:Aws';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aws scripts';

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
            \Illuminate\Support\Facades\Input::merge($inputs);

            $this->comment('Script : Start');

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/aws/' . $this->argument('action'), 'POST');
            $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();

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
            array('action', InputOption::VALUE_REQUIRED, 'Bootstrap setup script')
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
