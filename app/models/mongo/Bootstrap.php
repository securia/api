<?php

namespace PSC\Models\Mongo;

use Aws\CloudFront\Exception\Exception;

/**
 * Class Bootstrap
 * @package PSC\Models
 * @url: https://github.com/jenssegers/laravel-mongodb
 * @url: https://github.com/jenssegers/laravel-mongodb/blob/master/tests/QueryBuilderTest.php
 */
class Bootstrap
{

    protected $connection = 'mongodb';

    public function __construct($connectionName = 'mongodb')
    {
        $this->connection = $connectionName;
    }

    /**
     * Create Indexes on Collection properties
     * @param $collectionName
     * @param $properties
     */
    public function createIndexes($collectionName, $properties)
    {
        try {
            \Illuminate\Support\Facades\Schema::create($collectionName, function ($collection) use ($collectionName, $properties) {
                foreach ($properties as $columnName => $property) {
                    $property['name'] = $columnName.'_'.$property['index_type'];
                    $collection->index(array($columnName => $property['index_type']), $property['options']);
                }
            });

            return \ApplicationBase\Facades\Api::success(2030, array(), 'Indexes');
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Delete Indexes on Collection properties
     * @param $collectionName
     * @param $properties
     */
    public function deleteIndexes($collectionName, $properties)
    {
        try {
            \Illuminate\Support\Facades\Schema::create($collectionName, function ($collection) use ($collectionName, $properties) {
                foreach ($properties as $columnName => $property) {
                    $collection->dropIndex(array($columnName => $property['index_type']));
                }
            });

            return \ApplicationBase\Facades\Api::success(2030, array(), 'Indexes');
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Function to get mongo collection data
     * @param $connection
     * @return array
     */
    public function deleteAllCollections($connection)
    {
        try {
            $collections = $connection->getCollectionNames();
            foreach ($collections as $collection) {
                $connection->selectCollection($collection)->drop();
            }

            return \ApplicationBase\Facades\Api::success(2060, array(), 'Collections');
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
