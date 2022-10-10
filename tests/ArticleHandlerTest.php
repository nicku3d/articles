<?php

use App\Config\Database;
use App\Controller\ArticleHandler;
use App\Model\Article;
use PHPUnit\Framework\TestCase;

final class ArticleHandlerTest extends TestCase
{

    protected ArticleHandler $articleHandler;

    public function setUp(): void
    {
        $dbConfig = Database::loadConfig();
        $db = new MeekroDB($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['db']);
        $this->articleHandler = new ArticleHandler($db);
    }

    public function testCanBeCreatedWithMeekroDbInstance()
    {
        $dbConfig = Database::loadConfig();

        $db = new MeekroDB($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['db']);
        $db->queryOneRow('SELECT 1 FROM articles');
        $articleHandler = new ArticleHandler($db);

        $this->assertInstanceOf(
            ArticleHandler::class,
            $articleHandler
        );
    }

    public function testCannotBeCreatedWithWrongDbObject()
    {
        $this->expectError();
        $db = new stdClass();
        new ArticleHandler($db);
    }

    public function testCannotGetArticleWithNegativeId()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Article id must be positive!');
        $this->expectExceptionCode(1);
        $this->articleHandler->getArticle(-1);
    }

    public function testCanCreateEditAndDeleteArticle()
    {
        $article = new Article();
        $article->setTitle('test article')
            ->setContent('test article content');
        $newArticleId = $this->articleHandler->createArticle($article);

        $this->assertIsInt($newArticleId);

        $article->setId($newArticleId)
        ->setTitle('title changed');

        $this->articleHandler->editArticle($article);

        $article = $this->articleHandler->getArticle($newArticleId);

        $this->assertEquals('title changed', $article->getTitle());

        $result = $this->articleHandler->deleteArticle($newArticleId);
        $this->assertEquals(true, $result);

        $this->expectExceptionMessage("Article with id={$newArticleId} doesn't exists");
        $this->articleHandler->getArticle($newArticleId);

    }
}