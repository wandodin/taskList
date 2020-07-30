<?php
declare(strict_types=1);

use TaskList\Core\Config;
use TaskList\Core\Router;
use TaskList\Core\Request;
use TaskList\Utils\DependencyInjector;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__) . '/vendor/autoload.php';

$config = new Config();
$tasksFile = realpath($config->get('tasks'));

$loader = new Twig\Loader\FilesystemLoader((__DIR__) . '/views');
$view = new Twig\Environment($loader);

$log = new Logger('tasklist');
$logFile = $config->get('log');
$log->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

$di = new DependencyInjector();
$di->set('Utils\Config', $config);
$di->set('Twig', $view);
$di->set('Logger', $log);
$di->set('File', $tasksFile);

$router = new Router($di);
$response = $router->route(new Request());
echo $response;