<?php

namespace ITRvB\Repositories;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Interfaces\IRepository;
use ITRvB\Models\UUID;
use ITRvB\Models\Comment;
use ITRvB\Models\Article;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Singletons\Logger;

class CommentRepositoryInterface implements IRepository
{
    public function __construct(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    private readonly MySQL $mysql;

    public function get(UUID $uuid) : Comment
    {
        Logger::info("CommentRepository: retrieving Comment with UUID $uuid from the database...");

        $commentData = $this->mysql->queryWithException(
            "SELECT * FROM comments WHERE comments.uuid = '$uuid' LIMIT 1",
            "Could not find any comment with UUID $uuid in the database."
        )->fetch_assoc();

        $comment = $this->dataToComment($commentData);

        Logger::info("CommentRepository: Comment with UUID $uuid was retrieved.");

        return $comment;
    }

    public function getByArticle(Article $article) : array
    {
        Logger::info("CommentRepository: retrieving all comments for Article with UUID $article->id");

        $commentQuery = $this->mysql->query("SELECT * FROM comments WHERE comments.article_id = '$article->id'");
        if ($commentQuery->num_rows == 0) return array();
        
        $comments = array();
        while ($row = $commentQuery->fetch_assoc())
        {
            $comment = $this->dataToComment($row);
            array_push($comments, $comment);
        }

        Logger::info("CommentRepository: found " . count($comments) . " comments for Article with UUID $article->id");

        return $comments;
    }

    private function dataToComment($commentData) : Comment
    {
        $author = $this->mysql->getUser(new UUID($commentData["author_id"]));

        $articleRepository = new ArticleRepositoryInterface($this->mysql);
        $article = $articleRepository->get(new UUID($commentData["article_id"]));

        $comment = new Comment(
            new UUID($commentData["uuid"]),
            $author,
            $article,
            $commentData["text"]
        );

        return $comment;
    }

    public function save($model) : void
    {
        Logger::info("CommentRepository: saving Comment with UUID $model->id, " .
            "Author UUID " . $model->author->id . ", Article UUID " . $model->article->id);

        $text = str_replace('\'', '\\\'', $model->text);
        $this->mysql->query("INSERT INTO comments VALUES 
            ('$model->id', '" . $model->author->id  . "', '" . $model->article->id . "', '$text')");
        
        Logger::info("CommentRepository: successfully saved Comment with UUID $model->id");
    }

    public function delete(UUID $uuid) : void
    {
        Logger::info("CommentRepository: attempting to delete Comment with UUID $uuid");
        $this->mysql->query("DELETE FROM comments WHERE comments.uuid = '$uuid'");
    }
}