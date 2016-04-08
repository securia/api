<?php

namespace SEC\Models\Mongo;

class Session extends \Jenssegers\Mongodb\Model
{
    /**
     * Collection for Model
     *
     * @var String
     */
    protected $collection = 'sessions';

    /**
     * Connection for modal
     *
     * @var type
     */
    protected $connection = 'mongodb';

    /**
     * Get Session Details
     * @param array $wheres
     * @param array $return
     */
    public static function getSessions($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('sessions');

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

            return \ApplicationBase\Facades\Api::success(6010, $data, array('Session(s) fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }   

    /**
     * Get Session Details
     * @param array $wheres
     * @param array $return
     */
    public static function getSession($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('sessions');

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

            return \ApplicationBase\Facades\Api::success(6010, $data, array('Session fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public static function saveSession($data)
    {
        global $appConn;
        try {
            $data['updated_at'] = LARAVEL_START;
            if (!empty($data['session_id'])) {
                $data['_id'] = $data['session_id'];
                unset($data['session_id']);
                $appConn['mongo_push'][] = array(
                    'collection' => 'sessions',
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
                $appConn['mongo_push'][] = array('collection' => 'sessions', 'action' => 'insert', 'insert' => $data);
            }
            return \ApplicationBase\Facades\Api::success(2030, $data, ['Session']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
