<?php

namespace SEC\Models\Mongo;

class Call extends \Jenssegers\Mongodb\Model
{
    /**
     * Collection for Model
     *
     * @var String
     */
    protected $collection = 'calls';

    /**
     * Connection for modal
     *
     * @var type
     */
    protected $connection = 'mongodb';

    /**
     * Get Call Details
     * @param array $wheres
     * @param array $return
     */
    public static function getCalls($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('calls');
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
            return \ApplicationBase\Facades\Api::success(6010, $data, array('Call(s) fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }   

    /**
     * Get Call Details
     * @param array $wheres
     * @param array $return
     */
    public static function getCall($wheres = array(array()), $return = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection('calls');

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

            return \ApplicationBase\Facades\Api::success(6010, $data, array('Call fetched'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public static function saveCall($data)
    {
        global $appConn;
        try {
            $data['updated_at'] = LARAVEL_START;
            if (!empty($data['call_id'])) {
                $data['_id'] = $data['call_id'];
                unset($data['call_id']);
                $appConn['mongo_push'][] = array(
                    'collection' => 'calls',
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
                $appConn['mongo_push'][] = array('collection' => 'calls', 'action' => 'insert', 'insert' => $data);
            }
            return \ApplicationBase\Facades\Api::success(2030, $data, ['Call']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
