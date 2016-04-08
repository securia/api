<?php namespace Illuminate\Cookie;

use Illuminate\Support\ServiceProvider;

class CookieServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('cookie', function($app)
		{
			$config = $app['config']['session'];
			$cookieJar = new CookieJar;
			return $cookieJar->setDefaultPathAndDomain($config['path'], $config['domain']);
		});
	}

}
