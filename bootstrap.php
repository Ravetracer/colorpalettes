<?php
/**
 *
 * Bootstrap file for Silex project
 *
 */

require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Knp\Provider\ConsoleServiceProvider;
use Colorpalettes\ResultToArrayService;
use Colorpalettes\SilexApp;

/**
 * @var Application
 * @uses Silex\Application\TwigTrait;
 * @uses Silex\Application\MonologTrait;
 */
$app = new SilexApp();
$app['debug'] = true;

/**
 * register monolog
 */
$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile'   => __DIR__.'/logs/devlog.log',
]);

/**
 * register twig
 */
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path'         => __DIR__.'/views',
]);

/**
 * register console
 */
$app->register(
    new ConsoleServiceProvider(),
    [
        'console.name'              => 'SilexProject - console',
        'console.version'           => '0.0.1',
        'console.project_directory' => __DIR__.'/..',
    ]
);

/**
 * register security service provider (currently not in use!)
 */
$app->register(new Silex\Provider\SecurityServiceProvider(), [
    'security.firewalls' => [
        'admin' => array(
            'pattern' => '^/admin',
            'http' => true,
            'users' => [
                // raw password is foo
                'admin' => ['ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='],
            ],
        ),
    ],
]);

/**
 * register doctrine for sqlite db
 */
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options'    => [
        'driver'    => 'pdo_sqlite',
        'path'      => __DIR__.'/etc/colpals.db',
    ],
]);

/**
 * register spot; service provider for tiny ORM functionalities
 */
$app->register(new Dijky\Silex\Provider\SpotServiceProvider(), [
    'spot.connections' => [
        'colpals' => [
            'driver'    => 'pdo_sqlite',
            'path'      => __DIR__.'/etc/colpals.db',
        ],
    ],
]);

/**
 * register configuration and URL generator service providers
 */
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__.'/config/parameters.yml'));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

/**
 * return DB result as palette array
 *
 * @return ResultToArrayService
 */
$app["result_to_array"] = function () {
    return new ResultToArrayService();
};

return $app;
