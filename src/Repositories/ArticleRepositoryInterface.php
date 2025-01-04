<?php

namespace ITRvB\Repositories;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Interfaces\IRepository;
use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Singletons\Logger;

class ArticleRepositoryInterface implements IRepository
{
    public function __construct(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    // its better to make a dependency injection container for both MySQL and Logger,
    // but since it's only an university project, I dont want to stuck my head with all that :)
    private readonly MySQL $mysql;

    public function get(UUID $uuid) : Article
    {
        Logger::info("ArticleRepository: retrieving Article with UUID $uuid from the database...");

        $articleData = $this->mysql->queryWithException(
            "SELECT * FROM articles WHERE articles.uuid = '$uuid' LIMIT 1",
            "Could not find any article with UUID $uuid in the database."
        )->fetch_assoc();

        $article = $this->dataToArticle($articleData);

        Logger::info("ArticleRepository: Article with UUID $uuid was retrieved.");

        return $article;
    }

    public function getRandom() : Article
    {
        Logger::info("ArticleRepository: retrieving random Article from the database...");

        $articleData = $this->mysql->queryWithException(
            "SELECT * FROM articles ORDER BY RAND() LIMIT 1",
            "Article table is empty."
        )->fetch_assoc();

        $article = $this->dataToArticle($articleData);

        Logger::info("ArticleRepository: retrieved article with UUID $article->id");

        return $article;
    }

    public function getAll() : array
    {
        Logger::info("ArticleRepository: retrieving all Articles from the database...");

        $articleQuery = $this->mysql->query("SELECT * FROM articles");
        if ($articleQuery->num_rows == 0) return array();
        
        $articles = array();
        while ($row = $articleQuery->fetch_assoc())
        {
            $article = $this->dataToArticle($row);
            array_push($articles, $article);
        }

        Logger::info("ArticleRepository: successfully retrieved " . count($articles) . " Articles.");

        return $articles;
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
        Logger::info("ArticleRepository: saving Article with UUID $model->id, " . 
            "Author UUID " . $model->author->id . ", header $model->header");

        $header = str_replace('\'', '\\\'', $model->header);
        $text = str_replace('\'', '\\\'', $model->text);
        $this->mysql->query("INSERT INTO articles VALUES 
            ('$model->id', '" . $model->author->id  . "', '$header', '$text')");

        Logger::info("ArticleRepository: successfully saved Article with UUID $model->id");
    }

    public function delete(UUID $uuid) : void
    {
        Logger::info("ArticleRepository: attempting to delete Article with UUID $uuid");
        $this->mysql->query("DELETE FROM articles WHERE articles.uuid = '$uuid'");
    }

    public function getConnection() : MySQL
    {
        return $this->mysql;
    }
}