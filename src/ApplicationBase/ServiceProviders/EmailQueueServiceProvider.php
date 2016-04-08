<?php
namespace ApplicationBase\ServiceProviders;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Email service provider
 *
 * Class EmailQueueServiceProvider
 * @package ApplicationBase\ServiceProviders
 */
class EmailQueueServiceProvider extends ServiceProvider
{
    /**
     * Register Service
     */
    public function register()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->app['EmailQueue'] = $this->app->share(function ($app) {
            return new \ApplicationBase\EmailQueue();
        });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('EmailQueue', 'ApplicationBase\Facades\EmailQueue');
        });
    }
}
