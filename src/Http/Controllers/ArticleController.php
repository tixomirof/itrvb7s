<?php
namespace ITRvB\Http\Controllers;

use ITRvB\Interfaces\IController;
use ITRvB\Models\Article;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Exceptions\NotFoundException;
use ITRvB\Http\Request;
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
                $response = $this->unprocessableEntityResponse();
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
                    $response = $this->createArticleFromRequest();
                    break;
                case 'DELETE':
                    $response = $this->deleteArticle($articleUUID);
                    break;
                default:
                    $response = $this->notFoundResponse();
                    break;
            }
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
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
            return $this->notFoundResponse();
        }
    }

    private function createArticleFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $article = $this->validateArticle($input);
        if (!$article) {
            return $this->unprocessableEntityResponse();
        }

        $this->repo->save($article);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function deleteArticle(UUID $articleUUID)
    {
        if (!$articleUUID) return $this->notFoundResponse();

        $this->repo->delete($articleUUID);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validateArticle($input)
    {
        if (!isset($input['header']) || !isset($input['text']) || !isset($input['author_id'])) {
            return null;
        }

        try {
            $articleUUID = isset($input['uuid']) ? new UUID($input['uuid']) : UUID::random(); // check if UUID is valid
            $authorUUID = new UUID($input['author_id']); // check if UUID is valid

            $author = $this->repo->getConnection()->getUser($authorUUID); // check if DB has user with given UUID

            $article = new Article($articleUUID, $author, $input['header'], $input['text']); // check if object creates successfully

            return $article;
        }
        catch (Exception $ex) {
            return null;
        }
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            'error' => 'Not found with given arguments'
        ]);
        return $response;
    }
}