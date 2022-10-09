<?php

use App\Config\Database;
use App\Controller\ArticleHandler;
use App\View\View;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

//TODO add article categories and sorting/filtering
//TODO add config file that specifies if you want to use categories and users

$dbConfig = Database::loadConfig();

$db = new MeekroDB($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['db']);
$articleHandler = new ArticleHandler($db);

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

$router = new \Bramus\Router\Router();
$view = new View();

$myRouter = new \App\Controller\Router($router, $view, $articleHandler, $purifier);
$myRouter->init();

die;






