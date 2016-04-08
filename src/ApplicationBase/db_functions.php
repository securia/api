<?php

/**
 * Unset Mongo DB's internal id while sending output
 *
 * @param $result
 * @param bool $multiArray
 * @return mixed
 */
function unsetMongoId($result = array(), $multiArray = true)
{
    if (empty($result)) {
        return array();
    }
    if (true == $multiArray) {
        foreach ($result as $key => $row) {
            if (isset($row['_id'])) {
                unset($result[$key]['_id']);
            }
        }
    } else {
        if (isset($result['_id'])) {
            unset($result['_id']);
        }
    }
    return $result;
}

function getInsertId($node)
{
    try {
        global $appConn, $appConfig;
        // Set Mongo Client
        if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
            $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
        }

        $retryCounter = 0;
        $operationStatus = false;
        while ($operationStatus == false) {
            try {
                $status = $appConn['mongo']->getCollection('counters')->findAndModify(array("node" => $node), array('$inc' => array('id' => 1)), null, array("new" => true));
                if (isset($status['id'])) {
                    $operationStatus = true;
                    return (int)$status['id'];
                } else {
                    throw new \Exception('Counter for ' . $node . ' is not available.');
                }
            } catch (\Exception $e) {
                sleep($appConfig['mongo_errors']['wait_for']);
                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                    die(exception($e));
                }
                $retryCounter++;
            }
        }
    } catch (\Exception $e) {
        die(exception($e));
    }
}