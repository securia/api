<?php

namespace SEC\Models\Mongo;

class User extends \Jenssegers\Mongodb\Model
{
    /**
     * Collection for Model
     *
     * @var String
     */
    protected $collection = 'users';

    /**
     * Connection for modal
     *
     * @var type
     */
    protected $connection = 'mongodb';

    /**
     * Get Users Details
     * @param array $wheres
     * @param array $return
     */
    public static function getUsers($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('users');

            foreach ($wheres as $where) {
                if (count($where) <= 1 || count($where) >= 4) {
                    return \ApplicationBase\Facades\Api::error(1020, array(), array('where array passed'));
                }
                if (count($where) == 2) {
                    $where[2] = $where[1];
                    $where[1] = '=';
                }
                $db->where($where[0], $where[1], $where[2]);
            }

            $data = (array)$db->get($return);

            return \ApplicationBase\Facades\Api::success(6010, $data, array('User(s) fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Get User Details
     * @param array $wheres
     * @param array $return
     */
    public static function getUser($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('users');

            foreach ($wheres as $where) {
                if (count($where) <= 1 || count($where) >= 4) {
                    return \ApplicationBase\Facades\Api::error(1020, array(), array('where array passed'));
                }
                if (count($where) == 2) {
                    $where[2] = $where[1];
                    $where[1] = '=';
                }
                $db->where($where[0], $where[1], $where[2]);
            }

            $data = (array)$db->first($return);

            return \ApplicationBase\Facades\Api::success(6010, $data, array('User fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public static function saveUser($data)
    {
        global $appConn;
        try {
            $data['updated_at'] = LARAVEL_START;
            if (!empty($data['user_id'])) {
                $data['_id'] = $data['user_id'];
                unset($data['user_id']);
                $appConn['mongo_push'][] = array(
                    'collection' => 'users',
                    'action' => 'update',
                    'conditions' => array(array('_id', '=', $data['_id'])),
                    'update' => array(
                        'data' => $data,
                        'options' => array()
                    )
                );
            } else {
                $data['created_at'] = LARAVEL_START;
                $data['_id'] = new \MongoId();
                $appConn['mongo_push'][] = array('collection' => 'users', 'action' => 'insert', 'insert' => $data);
            }
            return \ApplicationBase\Facades\Api::success(2030, $data, ['Device']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public static function saveSession($data)
    {
        global $appConn;
        try {
            $data['updated_at'] = LARAVEL_START;
            if (!empty($data['user_id'])) {
                $appConn['mongo_push'][] = array(
                    'collection' => 'users',
                    'action' => 'update',
                    'conditions' => array(array('_id', '=', $data['user_id'])),
                    'update' => array(
                        'data' => $data,
                        'options' => array()
                    )
                );
            } else {
                $data['created_at'] = LARAVEL_START;
                $data['user_id'] = new \MongoId();
                $appConn['mongo_push'][] = array('collection' => 'users', 'action' => 'insert', 'insert' => $data);
            }
            return \ApplicationBase\Facades\Api::success(2030, $data, ['User']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
