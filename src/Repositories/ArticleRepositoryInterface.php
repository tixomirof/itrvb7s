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

    public function get(UUID $uuid, MySQL $openConnection = null) : Article
    {
        $articleQuery = $this->mysql->query("SELECT * FROM articles WHERE articles.uuid = '$uuid' LIMIT 1");
        if ($articleQuery->num_rows == 0)
        {
            throw new NotFoundException("Could not find any article with UUID $uuid in the database.");
        }
        $articleData = $articleQuery->fetch_assoc();

        $article = $this->dataToArticle($articleData);

        return $article;
    }

    public function getRandom() : Article
    {
        $articleQuery = $this->mysql->query("SELECT * FROM articles ORDER BY RAND() LIMIT 1");
        if ($articleQuery->num_rows == 0)
        {
            throw new NotFoundException("Article table is empty.");
        }
        $articleData = $articleQuery->fetch_assoc();

        $article = $this->dataToArticle($articleData, $this->mysql);
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
        $result = $this->mysql->query("INSERT INTO articles VALUES 
            ('$model->id', '" . $model->author->id  . "', '$model->header', '$model->text')");
        if (!$result)
        {
            die("Unknown error while adding data to the articles table");
        }
    }
}