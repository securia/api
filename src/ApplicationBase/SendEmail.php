<?php

namespace ApplicationBase;

/**
 * For making Email Queue
 *
 * Class EmailQueue
 * @package ApplicationBase
 */
class SendEmail
{
    public function fire($job, $input)
    {
        try {
            //Reattempt failed job at-most 1 times
            if ($job->attempts() > 2) {
                $job->delete();
                return;
            }

            global $globalConfig;
            if (isset($input['to_email']) && $input['to_email'] != '') {

                // pass message, base_url to email template
                $data = array('msg' => $input['content']);

                $fileLocation = null;

                $input['from_email'] = (!empty($input['from_email']) ? $input['from_email'] : $globalConfig['smtp']['SMTP_FROM_EMAIL']);
                $input['from_username'] = (!empty($input['from_username']) ? $input['from_username'] : $globalConfig['smtp']['SMTP_FROM_USERNAME']);

                // Here data will pass to email template
                \Illuminate\Support\Facades\Mail::send(array('html' => 'EmailTemplate'), $data, function ($message) use ($input) {
                    $message->from($input['from_email'], $input['from_username']);
                    $multipleTo = explode(',', $input['to_email']);

                    $message->to($multipleTo)
                        ->replyTo($input['reply_to_email'])
                        ->subject($input['subject']);

                    if (true == isset($input['cc']) && false == is_null($input['cc']) && 0 < strlen($input['cc'])) {
                        $multipleCC = explode(',', $input['cc']);
                        $message->cc($multipleCC);
                    }

                    if (true == isset($input['bcc']) && false == is_null($input['bcc']) && 0 < strlen($input['bcc'])) {
                        $multipleBCC = explode(',', $input['bcc']);
                        $message->bcc($multipleBCC);
                    }

                    if (true == isset($input['attachment']) && true == $input['attachment']) {
                        $message->attach($input['file_location']);
                    }
                });

                // Once email was send delete file from temp directory
                if (count(\Illuminate\Support\Facades\Mail::failures()) <= 0 && true == isset($input['attachment']) && true == $input['attachment']) {
                    unlink($input['file_location']);
                }

            }

            // Deleting the job from Queue
            $job->delete();

        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}