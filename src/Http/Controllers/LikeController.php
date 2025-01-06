<?php

namespace ITRvB\Http\Controllers;

use ITRvB\Interfaces\IController;
use ITRvB\Models\Article;
use ITRvB\Models\ArticleLike;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Http\AuthorizationManager;
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

    private function getArticleUuid(Request $request) : ?UUID
    {
        $args = $request->getArguments();
        
        try {
            return new UUID((string)$args['article']);
        } catch (Exception $ex) {
            return null;
        }
    }

    public function processRequest(Request $request)
    {
        $articleUuid = $this->getArticleUuid($request);
        if (!$articleUuid) return ResponseManager::unprocessableEntityResponse();

        switch ($request->getRequestMethod()) {
            case 'GET':
                $response = $this->getLikeCount($articleUuid);
                break;
            case 'POST':
                $response = $this->leaveLike($request, $articleUuid);
                break;
            case 'DELETE':
                $response = $this->removeLike($request, $articleUuid);
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

    private function leaveLike(Request $request, UUID $articleUuid)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->repo->getConnection())) {
            return ResponseManager::unauthorizedResponse();
        }

        $article = $this->validateArticle($articleUuid);
        if (!$article) {
            return ResponseManager::unprocessableEntityResponse();
        }

        $user = AuthorizationManager::getCurrentUser($request, $this->repo->getConnection());
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

    private function removeLike(Request $request, UUID $articleUuid)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->repo->getConnection())) {
            return ResponseManager::unauthorizedResponse();
        }

        $user = AuthorizationManager::getCurrentUser($request, $this->repo->getConnection());
        if (!$this->repo->hasUserLiked($articleUuid, $user->id)) {
            return ResponseManager::unprocessableEntityResponse();
        }

        $like = $this->repo->getLikeByArticleAndUser($articleUuid, $user->id);

        $this->repo->delete($like->id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'result' => "Database no more contains like with UUID $like->id " .
                "(like for article " . $like->article->id . " from user " . $like->user->id . ")"
        ]);
        return $response;
    }

    private function validateArticle(UUID $articleUuid)
    {
        try {
            $articleRepository = new ArticleRepositoryInterface($this->repo->getConnection());
            $article = $articleRepository->get($articleUuid);
            return $article;
        }
        catch (Exception $ex) {
            return null;
        }
    }
}