<?php

use App\Config\Database;
use App\Controller\ArticleHandler;
use App\View\View;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

//Narazie podejście MVP to minimum viable product
//TODO fajnie by było zrobić chociaż KATEGORIE artykułów i sortowanie po kategorii takie rzeczy, byłoby cool
//TODO w pliku konfiguracyjnym zawrzeć informacje o tym czy np używać kategorii i użytkowników czy nie

//TODO wyświeltić komunikat jak nie ma konfiguracji bazy danych
$db = new MeekroDB(Database::HOST, Database::USER, Database::PASSWORD, Database::DB_NAME);
$articleHandler = new ArticleHandler($db);

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

$router = new \Bramus\Router\Router();
$view = new View();

$myRouter = new \App\Controller\Router($router, $view, $articleHandler, $purifier);
$myRouter->init();

die;






