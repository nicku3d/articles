<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Article.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ArticleHandler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/View.php';

//Narazie podejście MVP to minimum viable product
//TODO fajnie by było zrobić chociaż KATEGORIE artykułów i sortowanie po kategorii takie rzeczy, byłoby cool
//TODO w pliku konfiguracyjnym zawrzeć informacje o tym czy np używać kategorii i użytkowników czy nie

//TODO utworzyć obiekt DatabaseConfiguration?
//TODO obiekt response
//TODO wyświeltić komunikat jak nie ma konfiguracji bazy danych
$dbConfig = require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$db = new MeekroDB($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['dbName']);
$articleHandler = new ArticleHandler($db);

//TODO na pewno przed uzyciem klasy ArticleHandler należałoby zwalidować dane czy są odpowiendich typów i w ogóle ok

$router = new \Bramus\Router\Router();
$view = new View();
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$router->get('/', function () use ($view, $articleHandler) {
    $articles = $articleHandler->listArticles();
    $view->setData($articles);
    $view->print();
    die;
});

// Custom 404 Handler
$router->set404(function () {
    //nie ma ucieczki
    header( 'Location: /');
    die;
});

$router->mount('/article', function () use ($view, $router, $articleHandler){
    //TODO edit i add na pewno można ujednolicić
    $router->get('/add', function () use ($view) {
        //formularz dodający artykuł
        $view->setSite('articleAddForm');
        $view->print();
    });

    $router->get('/edit/{\d+}/', function ($id) use ($view, $articleHandler) {
        //formularz dodający artykuł
        try {
            $article = $articleHandler->getArticle((int)$id);
            $view->setData([$article]);
            $view->setSite('articleEditForm');
        } catch (InvalidArgumentException|Exception $e) {
            $view->setSite('errorMessage')
                ->setErrorMessage($e->getMessage());
        }
        $view->print();

    });

    $router->get('/view/{\d+}/', function ($id) use ($view, $articleHandler) {
        //formularz dodający artykuł
        try {
            $article = $articleHandler->getArticle((int)$id);
            $view->setData([$article]);
            $view->setSite('articleView');
        } catch (InvalidArgumentException|Exception $e) {
            $view->setErrorMessage($e->getMessage())
                ->setSite('errorMessage');
        }
        $view->print();
    });
});

//BASIC API
$router->mount('/api/articles', function () use ($router, $articleHandler, $purifier) {

    //GET /articles  - pobiera wszystkie (może stronicowanie)
    $router->get('/', function () use ($articleHandler) {
        // nieużywane
        header('Content-Type: application/json');
        echo json_encode($articleHandler->listArticles(), JSON_PRETTY_PRINT);
    });

    //GET /articles/{id} - pobiera artykuł o id
    $router->get('/{\d+}/', function ($id) use ($articleHandler) {
        // nieużywane
        try {
            $article = $articleHandler->getArticle((int)$id);
            $response = [
              'status' => true,
              'data' => $article,
            ];
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    });

    //POST /articles - tworzy artykuł
    $router->post('/', function () use ($articleHandler, $purifier) {
        parse_str(file_get_contents('php://input'), $requestData);
        $title = $purifier->purify($requestData['title']);
        $content = $purifier->purify($requestData['content']);
        $article = new Article();
        $article->setTitle($title);
        $article->setContent($content);
        $result = $articleHandler->createArticle($article);
        if ($result > 0) {
            $response = ['status' => true];
        } else {
            $response = ['status' => false];
        }
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    });

    // PUT /articles/{id} - edytuje artykuł
    $router->put('/(\d+)/', function ($id) use ($articleHandler, $purifier) {
        parse_str(file_get_contents('php://input'), $requestData);
        $title = $purifier->purify($requestData['title']);
        $content = $purifier->purify($requestData['content']);
        $article = new Article();
        $article->setId((int)$id);
        $article->setTitle($title);
        $article->setContent($content);
        try {
            $result = $articleHandler->editArticle($article);
            $response = ['status' => $result];
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => $e->getMessage()];
        }
        header('Content-Type: application/json');
        return json_encode($response, JSON_PRETTY_PRINT);
    });

    //DELETE /articles/{id}
    $router->delete('/(\d+)/', function ($id) use ($articleHandler) {
        try {
            $result = $articleHandler->deleteArticle((int)$id);
            $response = [
                'status' => $result,
            ];
        } catch (InvalidArgumentException $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    });
});

$router->set404('/api(/.*)?', function() {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json');

    $jsonArray = array();
    $jsonArray['status'] = "404";
    $jsonArray['status_text'] = "route not defined";

    echo json_encode($jsonArray);
});

$router->run();
die;







