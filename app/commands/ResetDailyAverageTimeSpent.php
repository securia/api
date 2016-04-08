<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is cron job to reset Daily Average Time Spent for Today's Puzzle
 *
 *     php artisan cron:resetDailyAverageTimeSpent
 *
 */
class ResetDailyAverageTimeSpent extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:resetDailyAverageTimeSpent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'resetDailyAverageTimeSpent cron will reset daily_average_score collection.';

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

            $request = \Illuminate\Support\Facades\Request::create('scripts/4.0/cron/resetDailyAverageTimeSpent', 'POST');
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
