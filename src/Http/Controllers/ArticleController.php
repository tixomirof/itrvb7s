<?php
namespace ITRvB\Http\Controllers;

use ITRvB\Interfaces\IController;
use ITRvB\Models\Article;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Exceptions\NotFoundException;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Http\AuthorizationManager;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;
use Exception;

class ArticleController implements IController
{
    private ArticleRepositoryInterface $repo;

    public function init(MySQL $mysql)
    {
        $this->repo = new ArticleRepositoryInterface($mysql);
    }

    public function processRequest(Request $request)
    {
        $articleUUID = null;
        $response = null;

        $arg = $request->getArguments();
        if (isset($arg['uuid'])) {
            try {
                $articleUUID = new UUID((string)$arg['uuid']);
            } catch (Exception $ex) {
                $response = ResponseManager::unprocessableEntityResponse();
            }
        }

        if (!$response)
        {
            switch ($request->getRequestMethod()) {
                case 'GET':
                    if ($articleUUID) {
                        $response = $this->getArticle($articleUUID);
                    } else {
                        $response = $this->getAllArticles();
                    };
                    break;
                case 'POST':
                    $response = $this->createArticleFromRequest($request);
                    break;
                case 'DELETE':
                    $response = $this->deleteArticle($request, $articleUUID);
                    break;
                default:
                    $response = ResponseManager::notFoundResponse();
                    break;
            }
        }

        return $response;
    }

    private function getAllArticles()
    {
        $articles = $this->repo->getAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($articles);
        return $response;
    }

    private function getArticle(UUID $articleUUID)
    {
        try {
            $article = $this->repo->get($articleUUID);
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($article);
            return $response;
        } catch(NotFoundException $e) {
            return ResponseManager::notFoundResponse();
        }
    }

    private function createArticleFromRequest(Request $request)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->repo->getConnection())) {
            return ResponseManager::unauthorizedResponse();
        }

        $input = $request->getBody();
        $article = $this->validateArticle($request, $input);
        if (!$article) {
            return ResponseManager::unprocessableEntityResponse();
        }

        $this->repo->save($article);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'result' => 'Succesfully created the Article!',
            'article' => $article
        ]);
        return $response;
    }

    private function deleteArticle(Request $request, UUID $articleUUID)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->repo->getConnection())) {
            return ResponseManager::unauthorizedResponse();
        }

        if (!$articleUUID) return ResponseManager::notFoundResponse();

        $user = AuthorizationManager::getCurrentUser($request, $this->repo->getConnection());
        try {
            $article = $this->repo->get($articleUUID);
        } catch (Exception $e) {
            return ResponseManager::notFoundResponse();
        }

        if ($user->id != $article->author->id) {
            return ResponseManager::makeBadRequestResponse('Cannot delete article which author is not you');
        }

        $this->repo->delete($articleUUID);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'result' => "Database no more contains article with UUID $articleUUID"
        ]);
        return $response;
    }

    private function validateArticle(Request $request, array $input) : ?Article
    {
        if (!isset($input['header']) || !isset($input['text'])) {
            return null;
        }

        try {
            $articleUUID = isset($input['uuid']) ? new UUID($input['uuid']) : UUID::random(); // check if UUID is valid

            $author = AuthorizationManager::getCurrentUser($request, $this->repo->getConnection());

            $article = new Article($articleUUID, $author, $input['header'], $input['text']); // check if object creates successfully

            return $article;
        }
        catch (Exception $ex) {
            return null;
        }
    }
}