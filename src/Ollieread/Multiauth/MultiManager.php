<?php namespace Ollieread\Multiauth;

use Doctrine\ORM\EntityManager;
use Illuminate\Foundation\Application;
use Mitch\LaravelDoctrine\DoctrineUserProvider;

class MultiManager {
	
	
	/**
	 * @var Illuminate\Foundation\Application $app
	 */
	 
	protected $app;
	
	protected $config;
	
	protected $providers = array();
	
	public function __construct(Application $app) {
		$this->app = $app;
		$this->config = $this->app['config']['auth.multi'];
		
		foreach($this->config as $key => $config) {
			$this->providers[$key] = new AuthManager($this->app, $key, $config);
		}
	}
	
	public function __call($name, $arguments = array()) {
		if(array_key_exists($name, $this->providers)) {
			return $this->providers[$name];
		}
	}

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string    $driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function extend($driver, \Closure $callback)
	{
		if ($driver === 'doctrine') {
			foreach($this->providers as $name => $authManager) {
				$config = $this->config[$name];
				$authManager->extend($driver, function ($app) use ($config) {
					return new DoctrineUserProvider(
						$app['Illuminate\Hashing\HasherInterface'],
						$app[EntityManager::class],
						$config['model']
					);
				});
			}
		} else {
			foreach($this->providers as $name => $authManager) {
				$authManager->extend($driver, $callback);
			}
		}
	}
}
