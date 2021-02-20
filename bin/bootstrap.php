<?php declare(strict_types=1);

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require_once BASE_PATH . '/vendor/autoload.php';

$dependencies = require BASE_PATH . '/config/dependencies.php';

// dotenv
(new Mix\LoadEnvironmentVariables(
    BASE_PATH
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// create instance app

// for container proxy DI
Hyperf\Di\ClassLoader::init();

$app = new Mix\Application($dependencies, true);
$app->registerExceptionHandler(App\Exception\Handler::class);
$app->configAutoload();

// $app->configure('server');
// $app->configure('commands');
// $app->configure('listeners');
// $app->configure('middlewares');

// // redis
// $app->configure('redis');
// $app->register(\Hyperf\Redis\ConfigProvider::class);

// // cache
// $app->configure('cache');
// $app->register(\Hyperf\Cache\ConfigProvider::class);

// // database 
// $app->configure('databases');
// $app->register(\Hyperf\DbConnection\ConfigProvider::class);

// // tracer (zipkin opentrace)
// $app->configure('opentracing');
// $app->register(Hyperf\Tracer\ConfigProvider::class);

// register route
$app->router->addGroup('', function($router) use ($app) {
    require BASE_PATH . '/config/router.php';
});

return $app;
