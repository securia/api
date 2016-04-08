<?php
namespace ApplicationBase\ServiceProviders;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Api service Provider
 *
 * Class ApiServiceProvider
 * @package ApplicationBase\ServiceProviders
 */
class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register Service
     */
    public function register()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->app['Api'] = $this->app->share(function ($app) {
            return new \ApplicationBase\Api();
        });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Api', 'ApplicationBase\Facades\Api');
        });
    }
}