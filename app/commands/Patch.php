<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is script for Patch
 *
 *     php artisan script:Patch
 *
 */
class Patch extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'script:Patch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Patch on Mongo data';

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

            if (false == is_null($this->option('type')) && '' != $this->option('type')) {
                $inputs['type'] = $this->option('type');
            }

            \Illuminate\Support\Facades\Input::merge($inputs);

            $this->comment('Script : Start');

            $this->comment('Patch : Start');

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/patch/' . $this->argument('action'), 'POST');
            $response = \Illuminate\Support\Facades\Route::dispatch($request)->getContent();
            $this->info($response);

            $this->comment('Patch : End');

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
        return array(
            array('type', null, InputOption::VALUE_OPTIONAL, 'Merge Boolean'),
            array('from', null, InputOption::VALUE_OPTIONAL, 'From id'),
            array('to', null, InputOption::VALUE_OPTIONAL, 'To id'),
        );
    }

}
