<?php

namespace App\Controller;

use App\Model\Article;
use Exception;
use InvalidArgumentException;
use MeekroDB;

class ArticleHandler
{

    /**
     * @var MeekroDB
     */
    protected MeekroDB $db;

    /**
     * @param MeekroDB $db
     */
    public function __construct(MeekroDB $db)
    {
        $this->db = $db;
        $this->db->encoding = 'utf8mb4';
    }

    /**
     * Get article data
     *
     * @param int $articleId
     * @return Article
     * @throws Exception if article does not exist
     */
    public function getArticle(int $articleId): Article
    {
        $this->checkArticleId($articleId);

        $query = 'SELECT id, title, content FROM articles WHERE id = %i';
        $result = $this->db->query($query, $articleId);

        if (!empty($result[0])) {
            return (new Article())->setId((int)$result[0]['id'])
                ->setTitle($result[0]['title'])
                ->setContent($result[0]['content']);
        }
        throw new Exception("Article with id={$articleId} doesn't exists", 1);
    }

    /**
     * Create article
     *
     * @param Article $article
     * @return int added article id
     */
    public function createArticle(Article $article): int
    {
        $params = [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ];
        $this->db->insert('articles', $params);
        return $this->db->insertId();
    }

    /**
     * Edit article
     *
     * @param Article $article
     * @return bool
     *
     * @throws Exception if article does not exist
     */
    public function editArticle(Article $article): bool
    {
        $this->checkArticleId($article->getId());
        $this->getArticle($article->getId());

        $params = [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ];
        $result = $this->db->update('articles', $params, "id=%i", $article->getId());
        return (bool)$this->db->affectedRows();
    }

    /**
     * Delete article
     *
     * @param int $articleId
     * @return bool
     *
     * @throws InvalidArgumentException if article id isn't viable
     */
    public function deleteArticle(int $articleId): bool
    {
        $this->checkArticleId($articleId);
        $this->db->delete('articles', 'id=%i', $articleId);
        return (bool)$this->db->affectedRows();
    }

    /**
     * Lists articles
     *
     * @param int $limit
     * @return Article[]
     */
    public function listArticles(int $limit = 0) : array
    {
        if ($limit <= 0) {
            $limit = 100;
        }
        //TODO jeśli nie ma limitu to wszystko, też by się przydało zrobić stronnicowanie? - ale to później
        //TODO może jakieś sortowanie różne też
        $articles = [];
        $walk = $this->db->queryWalk("SELECT id, title, content FROM articles");
        for ($i = 0; $i < $limit; ++$i) {
            if ($row = $walk->next()) {
                //TODO czasami null na ostatnim i tak nie może być
                $article = new Article();
                $article->setId($row['id'])
                    ->setTitle($row['title'])
                    ->setContent($row['content']);
                $articles[] = $article;
            }
        }
        $walk->free();

        return $articles;
    }

    /**
     * Checks is article id is viable
     *
     * @param int $id
     * @return void
     */
    protected function checkArticleId(int $id) : void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Article id must be positive!', 1);
        }
    }

    /**
     * Checks if article exists
     *
     * @param $id
     * @return bool
     */
    public function existsArticle($id) : bool
    {
        try {
            return (bool) $this->getArticle($id);
        } catch (Exception $e) {
            return false;
        }
    }
}
