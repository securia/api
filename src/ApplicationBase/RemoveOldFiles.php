<?php

namespace ApplicationBase;

/**
 * For making Email Queue
 *
 * Class RemoveOldFiles
 * @package ApplicationBase
 */

class RemoveOldFiles
{
    //function is remove old file from s3
    public function fire($job, $arrOldFiles)
    {
        try {
            //Reattempt failed job at-most 1 times
            if ($job->attempts() > 1) {
                $job->delete();
                return;
            }

            // Instantiate the client.
            $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');
            $s3->registerStreamWrapper();
            if (true == valArr($arrOldFiles)) {

                foreach ($arrOldFiles as $strBucket => $strFile) {
                    if (false != strpos($strFile, '.')) {
                        $arrFile = explode('.', $strFile);
                        $strFile = $arrFile[0];
                    }
                    $dir = "s3://" . $strBucket . "/";
                    if (is_dir($dir) && ($dh = opendir($dir))) {
                        while (($file = readdir($dh)) !== false) {

                            if (false === strpos($file, $strFile)) {
                                unlink($dir . $file);

                            }
                        }
                        closedir($dh);
                    }
                }

                // Deleting the job from Queue
                $job->delete();
            }

        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}