<?php declare(strict_types=1);

use Hyperf\Contract\ConfigInterface;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require_once BASE_PATH . '/vendor/autoload.php';

$dependencies = require BASE_PATH . '/config/dependencies.php';

// dotenv
(new App\LoadEnvironmentVariables(
    BASE_PATH
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// create instance app
$app = new \App\Application($dependencies);
$app->registerExceptionHandler(\App\Exceptions\Handler::class);

$app->configure('server');
$app->configure('config');
$app->configure('commands');
$app->configure('listeners');
$app->configure('middlewares');

// redis
$app->configure('redis');
$app->register(\Hyperf\Redis\ConfigProvider::class);

// cache
$app->configure('cache');
$app->register(\Hyperf\Cache\ConfigProvider::class);

// database 
$app->configure('databases');
$app->register(\Hyperf\DbConnection\ConfigProvider::class);

// tracer (zipkin opentrace)
$app->configure('opentracing');
$app->register(\Hyperf\Tracer\ConfigProvider::class);

// register route
$app->router->addGroup('', function($router) use ($app) {
    require BASE_PATH . '/config/router.php';
});

return $app;
