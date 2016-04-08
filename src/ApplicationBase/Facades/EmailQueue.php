<?php

namespace ApplicationBase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facades for Email
 *
 * Class EmailQueue
 * @package ApplicationBase\Facades
 */
class EmailQueue extends Facade
{
    /**
     * Get Facade Accessor for EmailQueue
     *
     * @return string|void
     */
    protected static function getFacadeAccessor()
    {
        return 'EmailQueue';
    }
}