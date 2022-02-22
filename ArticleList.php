<?php

//TODO to może być myśl ale narazie nie bo za dużo roboty :/
class ArticleList implements JsonSerializable
{
    /**
     * @var Article[]
     */
    private array $articles = [];

    public function __construct(array $articles)
    {

    }

    public function getArticles() {

    }

    public function addArticle() {

    }


    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}