<?php

namespace ApplicationBase;

/**
 * For making Email Queue
 *
 * Class ResizeImage
 * @package ApplicationBase
 */
class ResizeImage
{
    public function fire($job, $input)
    {
        try {
            //Reattempt failed job at-most 1 times
            if ($job->attempts() > 1) {
                $job->delete();
                return;
            }

            //Reattempt failed job at-most 1 times
            global $appConfig, $globalConfig;
            // Instantiate the client.
            $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');
            $arrImageSizes = $appConfig['imageSizes'][$input['image_type']];

            $path = parse_url($input['image_url'], PHP_URL_PATH);
            $sourceKeyName = basename($path);
            /* $size = getimagesize($input['image_url']);
             $currentWidth = $size[0];
             $currentHeight = $size[1];*/

            if (true == valArr($arrImageSizes)) {
                foreach ($arrImageSizes as $strKey => $strImageSize) {

                    $image = \Intervention\Image\Facades\Image::make($input['image_url']);
                    $arrSize = explode('x', $strImageSize);
                    $width = $arrSize[0];
                    $height = $arrSize[1];
                    $image->fit($width, $height);
                    $destinationPath = $globalConfig['filesTempLocation'];
                    $targetKeyName = str_replace('.', '_' . $strKey . '.', $sourceKeyName);
                    $savedFile = $destinationPath . $targetKeyName;
                    $image->save($savedFile);
                    if (isset($image->dirname) && isset($image->basename)) {
                        $imgRealPath = $image->dirname . '/' . $image->basename;
                        //upload image to s3
                        $realPath = s3upload($image, $targetKeyName, true, $imgRealPath, $input['target_bucket']);
                        if ($realPath) {
                            $copiedImage = $realPath;
                            if (file_exists($savedFile)) {
                                unlink($savedFile);
                            }
                        }
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