<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is cron job to update admin panel dashboard
 *
 *     php artisan cron:updateDashboard
 *
 */
class ComputeRetention extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:computeRetention';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'computeRetention cron will calculate daily retention of the users.';

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
            $inputs = array();

            if (false == is_null($this->option('install_date')) && 0 < $this->option('install_date')) {
                $inputs['install_date'] = $this->option('install_date');
            }

            \Illuminate\Support\Facades\Input::merge($inputs);

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/cron/computeRetention', 'POST', $inputs);
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
            array('install_date', null, InputOption::VALUE_OPTIONAL, 'For Install Date'),
        );
    }

}
