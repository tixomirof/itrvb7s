<?php

namespace ITRvB\Repositories;

use ITRvB\Models\UUID;
use ITRvB\Models\User;
use ITRvB\Models\Article;
use ITRvB\Models\ArticleLike;
use ITRvB\Exceptions\NotFoundException;
use ITRvB\Exceptions\RepetitiveLikeException;
use ITRvB\Interfaces\IRepository;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\ArticleRepositoryInterface;

class LikeRepositoryInterface implements IRepository
{
    public function __construct(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    private readonly MySQL $mysql;

    public function get(UUID $uuid) : ArticleLike
    {
        $likeData = $this->mysql->queryWithException(
            "SELECT * FROM articleLikes WHERE articleLikes.uuid = '$uuid' LIMIT 1",
            "Could not find any article's Like with UUID $uuid in the database."
        )->fetch_assoc();

        $like = $this->dataToArticleLike($likeData);
        return $like;
    }

    public function getCountByArticleUUID(UUID $articleUuid) : int
    {
        $likeCount = $this->mysql->query(
            "SELECT COUNT(*) as count FROM articleLikes WHERE articleLikes.article_id = '$articleUuid'"
        )->fetch_assoc();

        return (int)$likeCount['count'];
    }

    public function getLikeByArticleAndUser(UUID $articleUuid, UUID $userUuid) : ArticleLike
    {
        $likeData = $this->mysql->queryWithException(
            "SELECT * FROM articleLikes WHERE articleLikes.article_id = '$articleUuid'
            AND articleLikes.user_id = '$userUuid' LIMIT 1",
            "Could not find any like for article with UUID $articleUuid and user with UUID $userUuid"
        )->fetch_assoc();
        
        $like = $this->dataToArticleLike($likeData);
        return $like;
    }

    public function hasUserLiked(UUID $articleUuid, UUID $userUuid) : bool
    {
        $likeCount = $this->mysql->query(
            "SELECT COUNT(*) as count FROM articleLikes 
            WHERE articleLikes.article_id = '$articleUuid' AND articleLikes.user_id = '$userUuid'"
        )->fetch_assoc();

        return (int)$likeCount['count'] > 0;
    }

    private function dataToArticleLike($likeData) : ArticleLike
    {
        $articleRepository = new ArticleRepositoryInterface($this->mysql);
        $article = $articleRepository->get(new UUID($likeData['article_id']));

        $user = $this->mysql->getUser(new UUID($likeData['user_id']));

        return new ArticleLike(
            new UUID($likeData['uuid']),
            $article,
            $user
        );
    }

    public function save($model) : void
    {
        if ($this->hasUserLiked($model->article->id, $model->user->id)) {
            throw new RepetitiveLikeException("Cannot leave a like for an article that has already been liked by this user.");
        }
        $this->mysql->query("INSERT INTO articleLikes VALUES
            ('$model->id', '" . $model->article->id . "', '" . $model->user->id . "')");
    }

    public function delete(UUID $uuid) : void
    {
        $this->mysql->query("DELETE FROM articleLikes WHERE articleLikes.uuid = '$uuid'");
    }

    public function getConnection() : MySQL
    {
        return $this->mysql;
    }
}