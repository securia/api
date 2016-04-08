<?php

namespace ApplicationBase;
use scripts\CronController;

/**
 * For making Email Queue
 *
 * Class RefreshDashboard
 * @package ApplicationBase
 */
class RefreshDashboard
{
    public function fire($job, $input)
    {
        try {
            //Reattempt failed job at-most 1 times
            if ($job->attempts() > 1) {
                $job->delete();
                return;
            }
            //refreshDashboard' => array('controller' => 'DashboardController

            $objCron =  new CronController();
             $objCron->updateDashboard();
            // Deleting the job from Queue
            $job->delete();

        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}