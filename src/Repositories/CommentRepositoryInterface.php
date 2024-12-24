<?php

namespace ITRvB\Repositories;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Interfaces\IRepository;
use ITRvB\Models\UUID;
use ITRvB\Models\Comment;
use ITRvB\Models\Article;
use ITRvB\Repositories\Connection\MySQL;

class CommentRepositoryInterface implements IRepository
{
    public function get(UUID $uuid, MySQL $openConnection = null) : Comment
    {
        $mysql = $openConnection;
        if (is_null($mysql)) $mysql = new MySQL();
        $commentQuery = $mysql->query("SELECT * FROM comments WHERE comments.uuid = '$uuid' LIMIT 1");
        if ($commentQuery->num_rows == 0)
        {
            throw new NotFoundException("Could not find any comment with UUID $uuid in the database.");
        }
        $commentData = $commentQuery->fetch_assoc();

        $comment = $this->dataToComment($commentData, $mysql);
        if (is_null($openConnection)) $mysql->dispose();

        return $comment;
    }

    public function getByArticle(Article $article) : array
    {
        $mysql = new MySQL();
        $commentQuery = $mysql->query("SELECT * FROM comments WHERE comments.article_id = '$article->id'");
        if ($commentQuery->num_rows == 0) return array();
        
        $comments = array();
        while ($row = $commentQuery->fetch_assoc())
        {
            $comment = $this->dataToComment($row, $mysql);
            array_push($comments, $comment);
        }
        return $comments;
    }

    private function dataToComment($commentData, MySQL $mysql) : Comment
    {
        $author = $mysql->getUser(new UUID($commentData["author_id"]));

        $articleRepository = new ArticleRepositoryInterface();
        $article = $articleRepository->get(new UUID($commentData["article_id"]), $mysql);

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
        $mysql = new MySQL();
        $result = $mysql->query("INSERT INTO comments VALUES 
            ('$model->id', '" . $model->author->id  . "', '" . $model->article->id . "', '$model->text')");
        $mysql->dispose();
        if (!$result)
        {
            die("Unknown error while adding data to the comments table");
        }
    }
}