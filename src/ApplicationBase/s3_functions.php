<?php

/**
 * Upload image to s3
 * @param $imageObject
 * @param $newImageName
 * @param bool $uploadToTemp
 * @param null $imgRealPath
 * @param null $targetBucket
 */
function s3upload($imageObject, $newImageName, $uploadToTemp = false, $imgRealPath = null, $targetBucket = null)
{
    try {
        global $globalConfig;
        if (true == is_null($targetBucket)) {

            $bucketName = $globalConfig['s3']['bucketName'];

            //check if we need to upload files to s3 temp directory
            if ($uploadToTemp) {
                $bucketName = $globalConfig['s3']['locations']['temp'];
            }
        } else {
            $bucketName = trim($targetBucket, '/ ');
        }

        if ($imgRealPath == null) {

            $imgRealPath = $imageObject->getRealPath();
        }

        $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');
        $strSearch = array('(', ')', '[', ']', '{', '}');
        $newImageName = str_replace($strSearch, '', $newImageName);

        // Check bucket name is valid
        $isUploaded = $s3->putObject(array(
            'Bucket' => $bucketName,
            'Key' => $newImageName,
            'SourceFile' => $imgRealPath,
            'ACL' => 'public-read-write',
            'ContentType' => mime_content_type($imgRealPath)
        ));

        if ($isUploaded) {
            return 'https://s3.amazonaws.com/' . $bucketName . '/' . $newImageName;
        } else {
            return false;
        }
    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Function to copy files on s3 one bucket to other working bucket
 * @param null $sourceBucket
 * @param null $targetBucket
 * @param $sourceKeyName
 * @param $targetKeyName
 */
function copyFileFromS3Bucket($sourceBucket = null, $targetBucket = null, $sourceKeyName, $targetKeyName)
{
    try {
        global $appConfig;

        // Instantiate the client.
        $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');

        // Copy an object.
        $status = $s3->copyObject(array(
            'Bucket' => $targetBucket,
            'Key' => "{$targetKeyName}",
            'CopySource' => "{$sourceBucket}/{$sourceKeyName}",
            'ACL' => 'public-read',
        ));

        //check file is copied
        if ($status) {
            return $appConfig['common']['siteProtocol'] . 's3.amazonaws.com/' . $targetBucket . '/' . $targetKeyName;
        }
        return false;
    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Upload file to s3
 * @param File path
 * @param $newFileName
 * @return bool|string
 */
function s3uploadFile($file, $newFileName)
{
    global $globalConfig;
    try {
        $bucketName = $globalConfig['s3']['bucketName'];
        $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');
        // Check bucket name is valid
        $strSearch = array('(', ')', '[', ']', '{', '}');
        $newFileName = str_replace($strSearch, '', $newFileName);

        if ($s3->isValidBucketName($bucketName)) {
            $isUploaded = $s3->putObject(array(
                'Bucket' => $bucketName,
                'Key' => $newFileName,
                'SourceFile' => $file,
                'ACL' => 'public-read',
                'CacheControl' => 'max-age=120'
            ));

            if ($isUploaded) {
                return 'https://s3.amazonaws.com/' . $bucketName . '/' . $newFileName;
            } else {
                return false;
            }
        }
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Delete File from S3 bucket
 * @param $filename
 * @param null $bucketName
 * @return bool
 */
function s3DeleteFile($filename, $bucketName = null)
{
    try {
        //get Bucket name
        global $appConfig;

        if ($bucketName == null) {
            $bucketName = $appConfig['s3']['bucketName'];
        }

        $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');
        if ($s3->deleteMatchingObjects($bucketName, $filename)) {
            return true;
        } else {
            return false;
        }
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Function to get bucket name from URL
 * @param $url
 * @return mixed|string
 */
function getSourceBucketFromUrl($url)
{
    try {
        global $appConfig;

        //replace site protocol and s3.amazonaws.com/ from Url
        $sourceBucket = str_replace($appConfig["common"]["siteProtocol"] . 's3.amazonaws.com/', '', $url);

        //Remove file name from Url to get complete bucket name
        $path = parse_url($url, PHP_URL_PATH);
        $fileName = basename($path);
        $sourceBucket = str_replace('/' . $fileName, '', $sourceBucket);

        //return bucket
        return $sourceBucket;
    } catch (\Exception $e) {
        die(exception($e));
    }
}

/**
 * Function to move files from s3 temp bucket to other working bucket
 * @param null $sourceBucket
 * @param null $targetBucket
 * @param $sourceKeyName
 * @return bool|string
 */
function moveFileFromS3Bucket($sourceBucket = null, $targetBucket = null, $sourceKeyName)
{
    $imagePath = false;

    try {
        global $appConfig;

        // Instantiate the client.
        $s3 = \Illuminate\Support\Facades\App::make('aws')->get('s3');

        $targetBucket = trim($targetBucket, '/ ');

        // Copy an object.
        $status = $s3->copyObject(array(
            'Bucket' => $targetBucket,
            'Key' => "{$sourceKeyName}",
            'CopySource' => "{$sourceBucket}/{$sourceKeyName}",
            'ACL' => 'public-read',
        ));

        // Check file is copied
        if ($status) {
            $imagePath = 'https://' . $targetBucket . '/' . $sourceKeyName;
        }

        return $imagePath;

    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Get Temporary S3 Token
 * @return mixed|null
 */
function getS3SessionToken()
{
    try {
        global $appConfig;

        //Create Instance of STS Client
        $sts = \Aws\Sts\StsClient::factory(array(
            'key' => $appConfig['s3']['accessKey'],
            'secret' => $appConfig['s3']['accessToken'],
        ));

        //Fetch session token and Temporary Access Key
        $credentials = $sts->getSessionToken()->get('Credentials');

        //return Access Key information
        return $credentials;
    } catch (\Exception $e) {
        die(exception($e));
    }
}

