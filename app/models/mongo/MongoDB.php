<?php

namespace PSC\Models\Mongo;

/**
 * Class MongoDB
 * @package PSC\Models
 * @url: https://github.com/jenssegers/laravel-mongodb
 * @url: https://github.com/jenssegers/laravel-mongodb/blob/master/tests/QueryBuilderTest.php
 */
class MongoDB
{

    protected $connection = 'mongodb';

    public function __construct($connectionName = 'mongodb')
    {
        $this->connection = $connectionName;
    }

    /**
     * Function to get mongo collection data
     * @param $collectionName
     * @param array $data
     * @return array
     */
    public function get($collectionName, $data = array())
    {
        global $appConn;
        try {
            $db = $appConn['mongo']->collection($collectionName);
            if (empty($data['condition'])) {
                return array('status' => true, 'data' => $db->get());
            } else {
                if (!is_array($data['condition']))
                    $condition = json_decode($data['condition'], true);
                else
                    $condition = $data['condition'];

                foreach ($condition as $key => $value) {
                    $db = $db->where($key, $value);
                }
                return array('status' => true, 'data' => $db->get());
            }
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Function To save collection data to mongo
     * @param $collection
     * @param array $data
     * @return bool|string
     */
    public function save($collection, $data = array())
    {
        global $appConn;
        try {
            if ($appConn['mongo']->collection($collection)->insert($data)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Function to delete collection
     * @param null $collection
     * @param null $condition
     * @return bool|string
     */
    public function delete($collection = null, $condition = null)
    {
        global $appConn;
        try {
            if ($collection == null) {
                return false;
            }
            $db = $appConn['mongo']->collection($collection);
            if ($condition == null) {
                $db->delete();
            } else {

                if (!is_array($condition))
                    $condition = json_decode($condition, true);
                foreach ($condition as $key => $value) {
                    $db = $db->where($key, $value);
                }
                $db->delete();
            }
            return true;
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

}
