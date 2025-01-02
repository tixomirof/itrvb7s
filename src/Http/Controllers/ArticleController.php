<?php
namespace ITRvB\Http\Controllers;

use ITRvB\Models\Article;
use ITRvB\Models\User;
use ITRvB\Exceptions\NotFoundException;
use Exception;

class ArticleController {

    private readonly ArticleRepositoryInterface $repo;
    private readonly string $requestMethod;
    private readonly ?string $articleUUID;

    public function __construct(ArticleRepositoryInterface $repo, string $requestMethod, ?string $articleUUID = null)
    {
        $this->repo = $repo;
        $this->requestMethod = $requestMethod;
        $this->articleUUID = $articleUUID;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->articleUUID) {
                    $response = $this->getArticle($this->articleUUID);
                } else {
                    $response = $this->getAllArticles();
                };
                break;
            case 'POST':
                $response = $this->createArticleFromRequest();
                break;
            case 'DELETE':
                $response = $this->deleteArticle($this->articleUUID);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
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

    private function getArticle(string $articleUUID)
    {
        try {
            $article = $this->repo->get(new UUID($articleUUID));
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
        if (!$this->validateArticle($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->repo->save(new Article(
            new UUID($input['uuid']),
            $this->repo->getConnection()->getUser(new UUID($input['author_id'])),
            $input['header'],
            $input['text']
        ));

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function deleteArticle(string $articleUUID)
    {
        if (!$articleUUID) return $this->notFoundResponse();
        $uuid = new UUID($articleUUID);

        $this->repo->delete($uuid);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validateArticle($input)
    {
        if (!isset($input['header']) || !isset($input['text'])
            || !isset($input['uuid']) || !isset($input['author_id'])) {
            return false;
        }

        try {
            $articleUUID = new UUID($input['uuid']); // check if UUID is valid
            $authorUUID = new UUID($input['author_id']); // check if UUID is valid

            $author = $this->repo->getConnection()->getUser($authorUUID); // check if DB has user with given UUID
        }
        catch (Exception $ex) {
            return false;
        }

        return true;
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
        $response['body'] = null;
        return $response;
    }
}