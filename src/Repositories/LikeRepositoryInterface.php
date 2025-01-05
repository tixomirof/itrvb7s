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
use ITRvB\Singletons\Logger;

class LikeRepositoryInterface implements IRepository
{
    public function __construct(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    private readonly MySQL $mysql;

    public function get(UUID $uuid) : ArticleLike
    {
        Logger::info("LikeRepository: retrieving ArticleLike with UUID $uuid from the database...");

        $likeData = $this->mysql->queryWithException(
            "SELECT * FROM articleLikes WHERE articleLikes.uuid = '$uuid' LIMIT 1",
            "Could not find any article's Like with UUID $uuid in the database."
        )->fetch_assoc();

        $like = $this->dataToArticleLike($likeData);

        Logger::info("LikeRepository: ArticleLike with UUID $uuid was retrieved.");

        return $like;
    }

    public function getCountByArticleUUID(UUID $articleUuid) : int
    {
        Logger::info("LikeRepository: retrieving like count for Article with UUID $articleUuid");

        $likeCountData = $this->mysql->query(
            "SELECT COUNT(*) as count FROM articleLikes WHERE articleLikes.article_id = '$articleUuid'"
        )->fetch_assoc();

        $likeCount = (int)$likeCountData['count'];
        Logger::info("LikeRepository: Article with UUID $articleUuid has $likeCount likes.");

        return $likeCount;
    }

    public function getLikeByArticleAndUser(UUID $articleUuid, UUID $userUuid) : ArticleLike
    {
        Logger::info("LikeRepository: retrieving like by user $userUuid and article $articleUuid");

        $likeData = $this->mysql->queryWithException(
            "SELECT * FROM articleLikes WHERE articleLikes.article_id = '$articleUuid'
            AND articleLikes.user_id = '$userUuid' LIMIT 1",
            "Could not find any like for article with UUID $articleUuid and user with UUID $userUuid"
        )->fetch_assoc();
        
        $like = $this->dataToArticleLike($likeData);

        Logger::info("LikeRepository: like was successfully found");

        return $like;
    }

    public function hasUserLiked(UUID $articleUuid, UUID $userUuid) : bool
    {
        Logger::info("LikeRepository: checking if user $userUuid has liked article $articleUuid");

        $likeCount = $this->mysql->query(
            "SELECT COUNT(*) as count FROM articleLikes 
            WHERE articleLikes.article_id = '$articleUuid' AND articleLikes.user_id = '$userUuid'"
        )->fetch_assoc();

        $hasLiked = (int)$likeCount['count'] > 0;

        Logger::info("LikeRepository: has user $userUuid liked article $articleUuid: $hasLiked");

        return $hasLiked;
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
        Logger::info("LikeRepository: attempting to save ArticleLike from " .
            $model->user->id . " to " . $model->article->id . " with itself's UUID $model->id");

        if ($this->hasUserLiked($model->article->id, $model->user->id)) {
            Logger::warning("LikeRepository: an attempt to save a duplicate ArticleLike.");
            throw new RepetitiveLikeException("Cannot leave a like for an article that has already been liked by this user.");
        }
        $this->mysql->query("INSERT INTO articleLikes VALUES
            ('$model->id', '" . $model->article->id . "', '" . $model->user->id . "')");

        Logger::info("LikeRepository: successfully saved ArticleLike with UUID $model->id");
    }

    public function delete(UUID $uuid) : void
    {
        Logger::info("LikeRepository: attempting to delete ArticleLike with UUID $uuid");
        $this->mysql->query("DELETE FROM articleLikes WHERE articleLikes.uuid = '$uuid'");
    }

    public function getConnection() : MySQL
    {
        return $this->mysql;
    }
}