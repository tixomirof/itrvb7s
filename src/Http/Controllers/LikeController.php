<?php

namespace ITRvB\Http\Controllers;

use ITRvB\Interfaces\IController;
use ITRvB\Models\Article;
use ITRvB\Models\ArticleLike;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Repositories\LikeRepositoryInterface;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;
use Exception;

class LikeController implements IController
{
    private LikeRepositoryInterface $repo;

    public function init(MySQL $mysql)
    {
        $this->repo = new LikeRepositoryInterface($mysql);
    }

    private function getArguments(Request $request) : array
    {
        $args = $request->getArguments();
        
        try {
            $result = [
                'article' => new UUID((string)$args['article'])
            ];
            if (isset($args['user'])) {
                $result['user'] = new UUID((string)$args['user']);
            }
            return $result;
        } catch (Exception $ex) {
            return [];
        }
    }

    public function processRequest(Request $request)
    {
        $args = $this->getArguments($request);
        if (count($args) === 0) return ResponseManager::unprocessableEntityResponse();

        switch ($request->getRequestMethod()) {
            case 'GET':
                $response = $this->getLikeCount($args['article']);
                break;
            case 'POST':
                $response = $this->leaveLike($args);
                break;
            case 'DELETE':
                $response = $this->removeLike($args);
                break;
            default:
                $response = ResponseManager::methodNotAllowed();
                break;
        }

        return $response;
    }

    private function getLikeCount(UUID $articleUUID)
    {
        $count = $this->repo->getCountByArticleUUID($articleUUID);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'articleUuid' => $articleUUID,
            'likeCount' => $count 
        ]);
        return $response;
    }

    private function leaveLike(array $args)
    {
        $article = $this->validateArticle($args);
        $user = $this->validateUser($args);
        if (!$article || !$user) {
            return ResponseManager::unprocessableEntityResponse();
        }

        if ($this->repo->hasUserLiked($article->id, $user->id)) {
            return ResponseManager::makeBadRequestResponse('This user had already left a like for this article');
        }

        $like = new ArticleLike(
            UUID::random(),
            $article,
            $user
        );
        $this->repo->save($like);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'result' => 'Succesfully left a like!',
            'newLikeCount' => $this->repo->getCountByArticleUUID($article->id)
        ]);
        return $response;
    }

    private function removeLike(array $args)
    {
        if (!isset($args['user']) || !$this->repo->hasUserLiked($args['article'], $args['user'])) {
            return ResponseManager::unprocessableEntityResponse();
        }

        $like = $this->repo->getLikeByArticleAndUser($args['article'], $args['user']);

        $this->repo->delete($like->id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'result' => "Database no more contains like with UUID $like->id " .
                "(like for article " . $like->article->id . " from user " . $like->user->id . ")"
        ]);
        return $response;
    }

    private function validateArticle(array $args)
    {
        if (!isset($args['article'])) {
            return null;
        }

        try {
            $articleUuid = $args['article'];
            $articleRepository = new ArticleRepositoryInterface($this->repo->getConnection());
            $article = $articleRepository->get($articleUuid);
            return $article;
        }
        catch (Exception $ex) {
            return null;
        }
    }

    private function validateUser(array $args)
    {
        if (!isset($args['user'])) {
            return null;
        }

        try {
            $userUuid = $args['user'];
            $user = $this->repo->getConnection()->getUser($userUuid);
            return $user;
        }
        catch (Exception $ex) {
            return null;
        }
    }
}