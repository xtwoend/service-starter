<?php

namespace App;

use Hyperf\Nano\App;
use Psr\Log\LogLevel;
use Hyperf\Di\Container;
use Hyperf\Config\Config;
use Hyperf\Nano\BoundInterface;
use Hyperf\Nano\ContainerProxy;
use Hyperf\Config\ProviderConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\HttpServer\Router\DispatcherFactory;

class Application
{
    public App $app;
    protected string $basePath;
    protected Container $container;
    protected array $dependencies = [];
    protected array $providerConfigs=[];
    protected static array $loadedProviders = [
        \Hyperf\Config\ConfigProvider::class,
        \Hyperf\Di\ConfigProvider::class,
        \Hyperf\Server\ConfigProvider::class,
        \Hyperf\HttpServer\ConfigProvider::class,
        \Hyperf\Framework\ConfigProvider::class,
        \Hyperf\Utils\ConfigProvider::class,
        \Hyperf\HttpMessage\ConfigProvider::class,
        \Hyperf\Event\ConfigProvider::class,
        \Hyperf\ExceptionHandler\ConfigProvider::class,
        \Hyperf\Dispatcher\ConfigProvider::class,
        \Hyperf\Nano\ConfigProvider::class
    ];
    public $router;
    protected string $serverName = 'http';
    protected bool $autoload;

    public function __construct($dependencies, $autoload = false) 
    {
        $this->basePath = BASE_PATH;
        $this->dependencies = $dependencies;
        $this->autoload = $autoload;
        
        $this->bootstrapContainer();
        $this->bootstrapProviders();
        $this->bootstrapRouter();

        $this->app = $this->createApp();
    }

    protected function bootstrapContainer(): void
    {
        $providers = $this->autoload? ProviderConfig::load() : $this->bootstrapProviders();

        $baseConfig = require $this->basePath . '/config/config.php';
        $config = new Config(array_merge_recursive($providers, $baseConfig));
        
        $dependencies = array_merge($config->get('dependencies', []), $this->dependencies);
        $container = new Container(new DefinitionSource($dependencies));
        $container->set(ConfigInterface::class, $config);
        $container->define(DispatcherFactory::class, DispatcherFactory::class);
        $container->define(BoundInterface::class, ContainerProxy::class);

        ApplicationContext::setContainer($container);
        
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function register($provider): void
    {
        if (! is_string($provider) 
            || ! class_exists($provider) 
            || ! method_exists($provider, '__invoke')
        ){
            throw new \Exception("Provider {$provider} not installed.");
        }

        $providerConfig = (new $provider())();
        
        $this->app->config($providerConfig);

        if(array_key_exists('dependencies', $providerConfig))
        {
            foreach($providerConfig['dependencies'] as $name => $definition){
                // register dependencies provider to container
                $this->container->define($name, $definition);
            }
        }
    }

    protected function bootstrapProviders(): array
    {
        foreach (self::$loadedProviders as $provider) {
            if (! is_string($provider) 
                || ! class_exists($provider) 
                || ! method_exists($provider, '__invoke')
            ){
                throw new \Exception("Provider {$provider} not installed.");
            }

            $this->providerConfigs[] = (new $provider())();
        }

        return $this->merge(...$this->providerConfigs);
    }

    protected function merge(...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }
        $result = array_merge_recursive(...$arrays);
        if (isset($result['dependencies'])) {
            $dependencies = array_column($arrays, 'dependencies');
            $result['dependencies'] = array_merge(...$dependencies);
        }

        return $result;
    }

    protected function bootstrapRouter(): void
    {
        $this->router = $this->container->get(DispatcherFactory::class)->getRouter($this->serverName);
    }

    public function configure($name): void
    {
        $configPath = $this->basePath . '/config';
        if(\file_exists($configPath . DIRECTORY_SEPARATOR . $name . '.php')){
            $config = require $configPath . DIRECTORY_SEPARATOR . $name . '.php';
            $this->app->config([$name => $config]);
        }
    }
    
    public function registerExceptionHandler($handler): void
    {
        $this->app->addExceptionHandler($handler);
    }

    public function run(): ApplicationInterface
    {
        return $this->app->run();
    }
    
    protected function createApp(): App
    {
        return new App($this->container);
    }
}