<?php

namespace ITRvB\Repositories;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Interfaces\IRepository;
use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Repositories\Connection\MySQL;

class ArticleRepositoryInterface implements IRepository
{
    public function __construct(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    private readonly MySQL $mysql;

    public function get(UUID $uuid) : Article
    {
        $articleData = $this->mysql->queryWithException(
            "SELECT * FROM articles WHERE articles.uuid = '$uuid' LIMIT 1",
            "Could not find any article with UUID $uuid in the database."
        )->fetch_assoc();

        $article = $this->dataToArticle($articleData);

        return $article;
    }

    public function getRandom() : Article
    {
        $articleData = $this->mysql->queryWithException(
            "SELECT * FROM articles ORDER BY RAND() LIMIT 1",
            "Article table is empty."
        )->fetch_assoc();

        $article = $this->dataToArticle($articleData);

        return $article;
    }

    private function dataToArticle($articleData) : Article
    {
        $author = $this->mysql->getUser(new UUID($articleData["author_id"]));
        $article = new Article(
            new UUID($articleData["uuid"]),
            $author,
            $articleData["header"],
            $articleData["text"]
        );

        return $article;
    }

    public function save($model) : void
    {
        $this->mysql->query("INSERT INTO articles VALUES 
            ('$model->id', '" . $model->author->id  . "', '$model->header', '$model->text')");
    }

    public function delete(UUID $uuid) : void
    {
        $this->mysql->query("DELETE FROM articles WHERE articles.uuid = '$uuid'");
    }
}