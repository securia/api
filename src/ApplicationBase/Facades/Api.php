<?php

namespace ApplicationBase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facades for API
 *
 * Class Api
 * @package ApplicationBase\Facades
 */
class Api extends Facade
{
    /**
     * Get Facade Accessor for API
     *
     * @return string|void
     */
    protected static function getFacadeAccessor()
    {
        return 'Api';
    }
}