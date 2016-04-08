<?php

namespace SEC\Models\Mongo;

use Aws\CloudFront\Exception\Exception;

class Common
{
    /**
     * Commit Mongo Transactions
     */
    static function commitMongoTransactions()
    {
        global $appConn, $appConfig;
        try {
            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            if (empty($appConn['mongo_push'])) {
                return \ApplicationBase\Facades\Api::success(6010, array(), array('Queries completed'));
            }

            foreach ($appConn['mongo_push'] as $key => $push) {
                $db = $appConn['mongo']->collection($push['collection']);

                $retryCounter = 1;
                switch ($push['action']) {
                    case 'insert':
                        $options = isset($push['options']) ? $push['options'] : array('new_id' => true);
                        if($options['new_id'] == true) {
                            unset($push['insert']['_id']);
                        }
                        $operationStatus = false;
                        while ($operationStatus == false) {
                            try {
                                $status = $db->insert($push['insert']);
                                $operationStatus = true;
                            } catch (\Exception $e) {
                                sleep($appConfig['mongo_errors']['wait_for']);
                                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                    die(exception($e));
                                }
                                $retryCounter++;
                            }
                        }
                        break;

                    case 'update':
                        foreach ($push['conditions'] as $where) {
                            if (count($where) >= 2 || count($where) <= 3) {
                                if (count($where) == 2) {
                                    $where[2] = $where[1];
                                    $where[1] = '=';
                                }
                                $db->where($where[0], $where[1], $where[2]);
                            }
                        }
                        unset($push['update']['data']['_id']);
                        if (!empty($push['update']['data'])) {
                            $operationStatus = false;
                            while ($operationStatus == false) {
                                try {
                                    $status = $db->update($push['update']['data'], $push['update']['options']);
                                    $operationStatus = true;
                                } catch (\Exception $e) {
                                    sleep($appConfig['mongo_errors']['wait_for']);
                                    if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                        die(exception($e));
                                    }
                                    $retryCounter++;
                                }
                            }
                        } else {
                            $status = 1;
                        }

                        $retryCounter = 0;
                        $operationStatus = false;
                        if ($status && isset($push['increment'])) {
                            while ($operationStatus == false) {
                                try {
                                    $status = $db->increment($push['increment'][0], $push['increment'][1]);
                                    $operationStatus = true;
                                } catch (\Exception $e) {
                                    sleep($appConfig['mongo_errors']['wait_for']);
                                    if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                        die(exception($e));
                                    }
                                    $retryCounter++;
                                }
                            }
                        }

                        $retryCounter = 0;
                        $operationStatus = false;
                        if ($status && isset($push['decrement'])) {
                            while ($operationStatus == false) {
                                try {
                                    $status = $db->decrement($push['decrement'][0], $push['decrement'][1]);
                                    $operationStatus = true;
                                } catch (\Exception $e) {
                                    sleep($appConfig['mongo_errors']['wait_for']);
                                    if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                        die(exception($e));
                                    }
                                    $retryCounter++;
                                }
                            }
                        }

                        break;

                    case
                    'delete':
                        foreach ($push['conditions'] as $where) {
                            if (count($where) >= 2 || count($where) <= 3) {
                                if (count($where) == 2) {
                                    $where[2] = $where[1];
                                    $where[1] = '=';
                                }
                                $db->where($where[0], $where[1], $where[2]);
                            }
                        }
                        $operationStatus = false;
                        while ($operationStatus == false) {
                            try {
                                $status = $db->delete();
                                $operationStatus = true;
                            } catch (\Exception $e) {
                                sleep($appConfig['mongo_errors']['wait_for']);
                                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                    die(exception($e));
                                }
                                $retryCounter++;
                            }
                        }
                        break;

                    case 'unset':
                        foreach ($push['conditions'] as $where) {
                            if (count($where) >= 2 || count($where) <= 3) {
                                if (count($where) == 2) {
                                    $where[2] = $where[1];
                                    $where[1] = '=';
                                }
                                $db->where($where[0], $where[1], $where[2]);
                            }
                        }
                        unset($push['unset']['_id']);
                        $operationStatus = false;
                        while ($operationStatus == false) {
                            try {
                                $status = $db->unset($push['unset']);
                                $operationStatus = true;
                            } catch (\Exception $e) {
                                sleep($appConfig['mongo_errors']['wait_for']);
                                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                    die(exception($e));
                                }
                                $retryCounter++;
                            }
                        }
                        break;

                    case
                    'increment':
                        foreach ($push['conditions'] as $where) {
                            if (count($where) >= 2 || count($where) <= 3) {
                                if (count($where) == 2) {
                                    $where[2] = $where[1];
                                    $where[1] = '=';
                                }
                                $db->where($where[0], $where[1], $where[2]);
                            }
                        }
                        $operationStatus = false;
                        while ($operationStatus == false) {
                            try {
                                $status = $db->increment($push['increment'][0], $push['increment'][1]);
                                $operationStatus = true;
                            } catch (\Exception $e) {
                                sleep($appConfig['mongo_errors']['wait_for']);
                                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                    die(exception($e));
                                }
                                $retryCounter++;
                            }
                        }
                        break;

                    case 'decrement':
                        foreach ($push['conditions'] as $where) {
                            if (count($where) >= 2 || count($where) <= 3) {
                                if (count($where) == 2) {
                                    $where[2] = $where[1];
                                    $where[1] = '=';
                                }
                                $db->where($where[0], $where[1], $where[2]);
                            }
                        }
                        $operationStatus = false;
                        while ($operationStatus == false) {
                            try {
                                $status = $db->decrement($push['decrement'][0], $push['decrement'][1]);
                                $operationStatus = true;
                            } catch (\Exception $e) {
                                sleep($appConfig['mongo_errors']['wait_for']);
                                if ($retryCounter >= $appConfig['mongo_errors']['retry_for']) {
                                    die(exception($e));
                                }
                                $retryCounter++;
                            }
                        }
                        break;
                }
                $appConn['mongo_push'][$key]['status'] = $status;
            }

            $appConn['mongo_push'] = array();

            return \ApplicationBase\Facades\Api::success(6010, array(), array('Queries completed'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Update daily Average time for Solving Daily Puzzle
     * @param $inputs
     */
    static function updateDailyAverageTime($inputs)
    {
        global $appConn;
        try {
            $data = $appConn['mongo']->collection('daily_average_score')->where('id', '=', 1)->first();

            if (empty($data)) {
                $data = array('id' => 1, 'total_time_spent' => 0, 'total_number_of_players' => 0, 'average_time' => 0, 'updated_at' => LARAVEL_START);
                $appConn['mongo']->collection('daily_average_score')->insert($data);
            }

            $data['total_time_spent'] += $inputs['time_spent'];
            $data['total_number_of_players'] += 1;
            $data['average_time'] = (int)$data['total_time_spent'] / $data['total_number_of_players'];

            $appConn['mongo_push'][] = array('collection' => 'daily_average_score', 'action' => 'increment', 'increment' => array('total_time_spent', $inputs['time_spent']), 'conditions' => array(array('id', '=', 1)));
            $appConn['mongo_push'][] = array('collection' => 'daily_average_score', 'action' => 'increment', 'increment' => array('total_number_of_players', 1), 'conditions' => array(array('id', '=', 1)));
            $appConn['mongo_push'][] = array('collection' => 'daily_average_score', 'action' => 'update', 'update' => array('data' => array('average_time' => $data['average_time'], 'updated_at' => LARAVEL_START), 'options' => array()), 'conditions' => array(array('id', '=', 1)));

            return \ApplicationBase\Facades\Api::success(2050, $data, array('Daily average score'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Get Daily average score of Daily Puzzle
     */
    static function getDailyAverageTime()
    {
        global $appConn;
        try {
            $data = $appConn['mongo']->collection('daily_average_score')->where('id', '=', 1)->first();

            if (empty($data)) {
                $data = array('id' => 1, 'total_time_spent' => 0, 'total_number_of_players' => 0, 'average_time' => 0, 'updated_at' => LARAVEL_START);
                $appConn['mongo']->collection('daily_average_score')->insert($data);
            }

            return \ApplicationBase\Facades\Api::success(2040, $data, array('Daily average score'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Add transaction of player
     * @param $entry
     * $entry array should contain coins, device_code, device_id, name, player_id, platform, transaction_type
     */
    static function addTransaction($entry)
    {
        global $appConn;
        try {
            $entry['created_at'] = LARAVEL_START;
            $appConn['mongo_push'][] = array('collection' => 'transactions', 'action' => 'insert', 'insert' => $entry);

            $update = array();
            if ($entry['transaction_type'] == 'credited_coins') {
                $update['coins_to_credit'] = 0;
            }

            $operation = $entry['coins'] < 0 ? 'decrement' : 'increment';
            $field = array('coin_balance', abs($entry['coins']));

            $appConn['mongo_push'][] = array('collection' => 'players', 'action' => 'update', 'update' => array('data' => $update, 'options' => array()), $operation => $field, 'conditions' => array(array('player_id', '=', $entry['player_id'])));

            //update current device coin balance by player coin balance
            if($entry['device_id'] > 0) {
                $appConn['mongo_push'][] = array('collection' => 'devices', 'action' => 'update', 'update' => array('data' => $update, 'options' => array()), $operation => $field, 'conditions' => array(array('device_id', '=', $entry['device_id'])));
            }

            return \ApplicationBase\Facades\Api::success(2030, array(), array('Transaction'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Add Purchase History of Player
     * @param $entry
     */
    static function addPurchase($entry)
    {
        global $appConn;
        try {
            $entry['created_at'] = LARAVEL_START;
            $amount = $entry['is_verified'] == false ? 0 : $entry['amount'];
            $transactions = $entry['is_verified'] == false ? 0 : 1;

            if (empty($entry['player_id']) && !empty($entry['device_id'])) {
                $entry['amount_spent'] = $entry['device_amount_spent'];

            } else if (!empty($entry['player_id']) && !empty($entry['device_id'])) {
                $entry['amount_spent'] = $entry['player_amount_spent'];
            }

            $appConn['mongo_push'][] = array('collection' => 'purchases', 'action' => 'insert', 'insert' => $entry);

            $appConn['mongo_push'][] = array('collection' => 'devices', 'action' => 'increment', 'increment' => array('transaction_count', $transactions), 'conditions' => array(array('device_id', '=', $entry['device_id'])));
            $appConn['mongo_push'][] = array('collection' => 'devices', 'action' => 'increment', 'increment' => array('amount_spent', $amount), 'conditions' => array(array('device_id', '=', $entry['device_id'])));

            if (!empty($entry['player_id']) && !empty($entry['device_id'])) {
                $appConn['mongo_push'][] = array('collection' => 'players', 'action' => 'increment', 'increment' => array('transaction_count', $transactions), 'conditions' => array(array('player_id', '=', $entry['player_id'])));
                $appConn['mongo_push'][] = array('collection' => 'players', 'action' => 'increment', 'increment' => array('amount_spent', $amount), 'conditions' => array(array('player_id', '=', $entry['player_id'])));
            }

            return \ApplicationBase\Facades\Api::success(2030, array(), array('Purchase'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Update coin balance of Player
     * @param $entry
     */
    static function updateCoinBalance($entry)
    {
        global $appConn;
        try {
            if ($entry['coins'] < 0) {
                $action = 'decrement';
                $operator = '-';
            } else {
                $action = 'increment';
                $operator = '+';
            }

            $appConn['mongo_push'][] = array('collection' => 'players', 'action' => $action, $action => array('coin_balance', abs($entry['coins'])), 'conditions' => array(array('player_id', '=', $entry['player_id'])));
            return \ApplicationBase\Facades\Api::success(2030, array(), array('Transaction'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
