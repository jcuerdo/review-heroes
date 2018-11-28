<?php

$app = new Silex\Application();


$app->register(new DF\Silex\Provider\YamlConfigServiceProvider(__DIR__ . '/../config/' . getenv('ENV') .'.yml'));

$app['debug'] = $app['config']['app']['debug'];

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__.'/../views',
]);

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
    'assets.named_packages' => array(
        'css' => array('version' => 'css2', 'base_path' => '/app/web/css'),
        'images' => array('base_path' => '/app/web/img'),
    ),
));

$app->register(new \ReviewHeroes\Providers\RedisServiceProvider(), array(
    'redis.host' => $app['config']['redis']['host'],
    'redis.port' => $app['config']['redis']['port'],
    'redis.timeout' => $app['config']['redis']['timeout'],
    'redis.persistent' => $app['config']['redis']['persistent'],
    'redis.serializer.igbinary' => $app['config']['redis']['serializer.igbinary'],
    'redis.serializer.php' => $app['config']['redis']['serializer.php'],
    'redis.database' => $app['config']['redis']['database']
));

$app->register(new \Silex\Provider\RoutingServiceProvider());
$app->register(new \ReviewHeroes\Providers\RepositoryServiceProvider());
$app->register(new \ReviewHeroes\Providers\ControllerServiceProvider());
$app->register(new \ReviewHeroes\Providers\UseCaseServiceProvider());
$app->register(new \ReviewHeroes\Providers\NotificationServiceProvider());

$app->register(
    new Knp\Provider\ConsoleServiceProvider(),
    array(
        'console.name' => 'ReviewHeroes',
        'console.version' => '0.1.0',
        'console.project_directory' => __DIR__ . "/../.."
    )
);

$app->mount('/', new ReviewHeroes\Routes\RoutesControllerProvider());

return $app;
