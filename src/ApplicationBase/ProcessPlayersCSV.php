<?php

namespace ApplicationBase;

/**
 * For making Email Queue
 *
 * Class EmailQueue
 * @package ApplicationBase
 */
class ProcessPlayersCSV
{
    public function fire($job, $input)
    {
        try {
            //Reattempt failed job at-most 1 times
            if ($job->attempts() > 1) {
                $job->delete();
                return;
            }

            global $globalConfig, $appConfig;
            $strQuery = \PSC\Models\Mongo\Player::processGrid($input['inputs'], $input['columns'], true);
            $intTotalRecords = $strQuery->count();
            if (ob_get_length()) ob_end_clean();

            $strFileLocation = $globalConfig['filesTempLocation'] . 'player.csv';
            if (file_exists($strFileLocation)) {
                unlink($strFileLocation);
            }

            $fp = fopen($strFileLocation, 'w');
            fputcsv($fp, array('Player ID', 'Name', 'Email', 'Install Date', 'Inactivity(Days)', 'Facebook Id', 'Puzzle Solved', 'Revenue', 'Coins'));
            // $params = array();
            if (0 < $intTotalRecords) {
                $intFixedLimit = 10000;
                $fltCurruntTime = microtime(true);
                $arrReturnValues = array_keys($input['columns']);

                for ($intLimit = 0; $intLimit <= $intTotalRecords; $intLimit = $intLimit + $intFixedLimit) {
                    $arrPlayers = array();
                    $arrPlayers = $strQuery->skip((int)$intLimit)->take($intFixedLimit)->get($arrReturnValues);
                    if (true == valArr($arrPlayers)) {
                        foreach ($arrPlayers as $arrPlayer) {
                            $arrCsvData['player_id'] = (isset($arrPlayer['player_id'])) ? $arrPlayer['player_id'] : null;
                            $arrCsvData['name'] = (isset($arrPlayer['name'])) ? $arrPlayer['name'] : null;
                            $arrCsvData['email'] = (isset($arrPlayer['email'])) ? $arrPlayer['email'] : null;
                            $arrCsvData['joining_date'] = (isset($arrPlayer['created_at'])) ? date("m/d/Y h:i A", $arrPlayer['created_at']) : null;
                            $arrCsvData['inactivity'] = (isset($arrPlayer['last_login_at'])) ? round(($fltCurruntTime - $arrPlayer['last_login_at']) / 60 / 60 / 24) : null;
                            $arrCsvData['fb_id'] = (isset($arrPlayer['fb_id'])) ? (string)$arrPlayer['fb_id'] : null;
                            $arrCsvData['puzzles_completed'] = (isset($arrPlayer['puzzles_completed'])) ? $arrPlayer['puzzles_completed'] : null;
                            $arrCsvData['amount_spent'] = (isset($arrPlayer['amount_spent'])) ? $arrPlayer['amount_spent'] : null;
                            $arrCsvData['coin_balance'] = (isset($arrPlayer['coin_balance'])) ? $arrPlayer['coin_balance'] : null;
                            fputcsv($fp, $arrCsvData);
                        }
                    }
                }
            }

            fclose($fp);

            //Upload image to s3 temp
            $newFileName = 'PlayerCsv/' . time() . '_player.csv';

            $realPath = s3uploadFile($strFileLocation, $newFileName);
            if (true == valStr($realPath)) {
                $dataPoints = array('csv_url' => $realPath);
                $template = \Illuminate\Support\Facades\View::make('EmailTemplate-PLAYER_CSV', $dataPoints)->render();
                $subject = $appConfig['emailNotifications']['PLAYER_CSV']['subject'];

                $sendEmailData = array(
                    'to_email' => $input['email'],
                    'subject' => $subject,
                    'content' => $template,
                    'type' => 'PLAYER_CSV',
                    'from_email' => $appConfig['emails']['defaultFromEmail'],
                    'reply_to_email' => $appConfig['emails']['defaultFromEmail']
                );
                unlink($strFileLocation);
                addToEmailQueue($sendEmailData);
            }


            // Deleting the job from Queue
            $job->delete();

        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}