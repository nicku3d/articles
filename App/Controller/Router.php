<?php

namespace App\Controller;

use App\Model\Article;
use App\View\View;
use Exception;
use HTMLPurifier;
use InvalidArgumentException;

class Router {

    private \Bramus\Router\Router $router;
    private View $view;
    private ArticleHandler $articleHandler;
    private HTMLPurifier $purifier;


    public function __construct(\Bramus\Router\Router $router, View $view, ArticleHandler $articleHandler, HTMLPurifier $purifier)
    {
        $this->router = $router;
        $this->view = $view;
        $this->articleHandler = $articleHandler;
        $this->purifier = $purifier;
    }

    public function init()
    {
        $this->createMainPageRoute();
        $this->createFrontEndRoutes();
        $this->createApiRoutes();
        $this->setBasic404();
        $this->router->run();
    }

    public function createMainPageRoute()
    {
        $this->router->get('/', function () {
            $articles = $this->articleHandler->listArticles();
            $this->view->setData($articles);
            $this->view->print();
        });
    }

    public function createFrontEndRoutes()
    {
        $this->router->mount('/article', function () {
            //TODO edit i add na pewno można ujednolicić
            $this->router->get('/add', function () {
                //formularz dodający artykuł
                $this->view->setSite('articleAddForm');
                $this->view->print();
            });

            $this->router->get('/edit/{\d+}/', function ($id) {
                //formularz dodający artykuł
                try {
                    $article = $this->articleHandler->getArticle((int)$id);
                    $this->view->setData([$article]);
                    $this->view->setSite('articleEditForm');
                } catch (InvalidArgumentException|Exception $e) {
                    $this->view->setSite('errorMessage')
                        ->setErrorMessage($e->getMessage());
                }
                $this->view->print();

            });

            $this->router->get('/view/{\d+}/', function ($id) {
                //formularz dodający artykuł
                try {
                    $article = $this->articleHandler->getArticle((int)$id);
                    $this->view->setData([$article]);
                    $this->view->setSite('articleView');
                } catch (InvalidArgumentException|Exception $e) {
                    $this->view->setErrorMessage($e->getMessage())
                        ->setSite('errorMessage');
                }
                $this->view->print();
            });
        });
    }

    public function setBasic404()
    {
        $this->router->set404(function () {
            //there is no escape
            header( 'Location: /');
            die;
        });
    }

    public function createApiRoutes()
    {
        //TODO obiekt response
        //TODO obiekt api?
        $this->router->mount('/api/articles', function () {

            //GET /articles  - pobiera wszystkie (może stronicowanie)
            $this->router->get('/', function () {
                // nieużywane
                header('Content-Type: application/json');
                echo json_encode($this->articleHandler->listArticles(), JSON_PRETTY_PRINT);
                die;
            });

            //GET /articles/{id} - pobiera artykuł o id
            $this->router->get('/{\d+}/', function ($id) {
                // nieużywane
                try {
                    $article = $this->articleHandler->getArticle((int)$id);
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
                die;
            });

            //POST /articles - tworzy artykuł
            $this->router->post('/', function () {
                parse_str(file_get_contents('php://input'), $requestData);
                $title = $this->purifier->purify($requestData['title']);
                $content = $this->purifier->purify($requestData['content']);
                $article = new Article();
                $article->setTitle($title);
                $article->setContent($content);
                $id = $this->articleHandler->createArticle($article);
                if ($id > 0) {
                    $response = ['status' => true, 'message' => 'Succesfully created article with id: ' . $id,];
                } else {
                    $response = ['status' => false, 'message' => 'Problem occurred while creating article!'];
                }
                header('Content-Type: application/json');
                echo json_encode($response, JSON_PRETTY_PRINT);
                die;
            });

            // PUT /articles/{id} - edits article
            $this->router->put('/(\d+)/', function ($id) {
                parse_str(file_get_contents('php://input'), $requestData);
                $title = $this->purifier->purify($requestData['title']);
                $content = $this->purifier->purify($requestData['content']);
                $article = new Article();
                $article->setId((int)$id);
                $article->setTitle($title);
                $article->setContent($content);
                try {
                    $result = $this->articleHandler->editArticle($article);
                    header("HTTP/1.1 200 OK");
                } catch (Exception $e) {
                    header("HTTP/1.1 404 Not Found");
                }
                die;
            });

            //DELETE /articles/{id}
            $this->router->delete('/(\d+)/', function ($id) {
                try {
                    $result = $this->articleHandler->deleteArticle((int)$id);
                    $response = [
                        'status' => $result,
                        'message' => 'Succesfully deleted article with id: ' . (int) $id,
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

        $this->router->set404('/api(/.*)?', function() {
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: application/json');

            $jsonArray = array();
            $jsonArray['status'] = "404";
            $jsonArray['status_text'] = "route not defined";

            echo json_encode($jsonArray);
        });
    }
}