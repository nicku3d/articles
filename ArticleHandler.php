<?php


class ArticleHandler
{

    protected MeekroDB $db;

    public function __construct(MeekroDB $db)
    {
        $this->db = $db;
        $this->db->encoding = 'utf8mb4_0900_ai_ci';
    }

    /**
     * @param int $articleId
     * @return Article
     * @throws Exception
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
     * @param Article $article
     * @return int
     */
    public function createArticle(Article $article): int
    {
        $params = [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ];
        $this->db->insert('articles', $params);
//        var_dump('po dodaniu taka zwrotka: ', $result, $this->db->affectedRows());
//        echo '</br>';
        return $this->db->insertId();
    }

    /**
     * @param Article $article
     * @return bool
     */
    public function editArticle(Article $article): bool
    {
        $this->checkArticleId($article->getId());
        $this->getArticle($article->getId());

        // TODO można też sprawdzić wczesniej czy taki artykuł w ogóle istnieje za pomocą getArticle :O
        // albo checkArticle, który bierze getArticle i robi z niego boola xD
        $params = [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ];
        $result = $this->db->update('articles', $params, "id=%i", $article->getId());
//        var_dump('po edycji taka zwrotka: ', $result, $this->db->affectedRows());
//        echo '</br>';
        return (bool)$this->db->affectedRows();
    }

    /**
     * @param int $articleId
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function deleteArticle(int $articleId): bool
    {
        $this->checkArticleId($articleId);
        $this->db->delete('articles', 'id=%i', $articleId);
        return (bool)$this->db->affectedRows();
    }

    public function listArticles(int $limit = 0)
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
     * @param int $id
     * @return void
     */
    protected function checkArticleId(int $id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Article id must be positive!', 1);
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function existsArticle($id)
    {
        try {
            return (bool) $this->getArticle($id);
        } catch (Exception $e) {
            return false;
        }
    }
}
