<?php namespace Kregel\Radio;

use Illuminate\Support\ServiceProvider;
use Kregel\Radio\Commands\CreateUserChannels;

class RadioServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function register()
	{

		// Register the FormModel Provider.
		$this->app->register(\Kregel\AuthLogin\AuthLoginServiceProvider::class);
		// Register the alias.
		$this->app->singleton('command.radio.users', function ($app) {
			return new CreateUserChannels();
		});
		$this->commands('command.radio.users');

		$this->app->singleton('command.radio.global', function ($app) {
			return new Commands\GlobalChannel();
		});
		$this->commands('command.radio.global');

		$this->app->singleton('command.radio.broadcast', function ($app) {
			return new Commands\Broadcast();
		});
		$this->commands('command.radio.broadcast');

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function boot()
	{
		if (!$this->app->routesAreCached() && !config('kregel.radio.local-routes')) {
			$this->app->router->group(['namespace' => 'Kregel\Radio\Http\Controllers'], function ($router) {
				require __DIR__.'/Http/routes.php';
			});
		}
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'radio');
		$this->publishes([
			__DIR__.'/../resources/views' => resource_path('views/vendor/radio'),
		], 'views');
		$this->publishes([
			__DIR__.'/../config/config.php' => config_path('kregel/radio.php'),
		], 'config');

		$this->publishes([
			__DIR__.'/../database/migrations' => database_path('migrations/'),
		], 'migrations');

		$this->publishes([
			__DIR__.'/../resources/node/broadcast.js' => base_path('service.js'),
		], 'node');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
