<?php

namespace ITRvB\Http\Controllers;

use ITRvB\Interfaces\IController;
use ITRvB\Models\Article;
use ITRvB\Models\Comment;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Http\AuthorizationManager;
use ITRvB\Repositories\CommentRepositoryInterface;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;
use Exception;

class CommentController implements IController
{
    private ArticleRepositoryInterface $articleRepository;
    private CommentRepositoryInterface $commentRepository;
    private MySQL $mysql;

    public function init(MySQL $mysql)
    {
        $this->mysql = $mysql;
        $this->articleRepository = new ArticleRepositoryInterface($mysql);
        $this->commentRepository = new CommentRepositoryInterface($mysql);
    }

    private function getArguments(Request $request) : array
    {
        $args = $request->getArguments();
        
        try {
            $result = [];
            if (isset($args['article']))
                $result['article'] = new UUID((string)$args['article']);
            if (isset($args['comment']))
                $result['comment'] = new UUID((string)$args['comment']);
            return $result;
        } catch (Exception $ex) {
            return [];
        }
    }

    public function processRequest(Request $request)
    {
        $arguments = $this->getArguments($request);
        if (count($arguments) === 0) return ResponseManager::unprocessableEntityResponse();

        switch ($request->getRequestMethod()) {
            case 'GET':
                if (isset($arguments['comment'])) {
                    return $this->getComment($arguments['article'], $arguments['comment']);
                }
                return $this->getComments($arguments['article']);
            case 'POST':
                return $this->leaveComment($request, $arguments['article']);
            case 'DELETE':
                return $this->deleteComment($request, $arguments['article'], $arguments['comment']);
            default:
                return ResponseManager::methodNotAllowed();
        }
    }

    private function getComments(UUID $articleUUID)
    {
        $article = $this->tryGetArticle($articleUUID);
        if (!$article) return ResponseManager::notFoundResponse();

        $comments = $this->commentRepository->getByArticle($article);
        
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'articleUuid' => $articleUUID,
            'comments' => $comments 
        ]);
        return $response;
    }

    private function getComment(UUID $articleUuid, UUID $commentUuid)
    {
        $article = $this->tryGetArticle($articleUuid);
        $comment = $this->tryGetComment($commentUuid);
        if (!$article || !$comment) return ResponseManager::notFoundResponse();

        if ($article->id != $comment->article->id) {
            return ResponseManager::makeBadRequestResponse("Given article does not have comment with UUID $commentUuid");
        }

        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode([
                'articleUuid' => $articleUuid,
                'comment' => [
                    'uuid' => $commentUuid,
                    'author' => [
                        'uuid' => $comment->author->id,
                        'name' => $comment->author->fullName()
                    ],
                    'content' => $comment->text
                ]
            ])
        ];
    }

    private function leaveComment(Request $request, UUID $articleUuid)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->mysql)) {
            return ResponseManager::unauthorizedResponse();
        }

        $article = $this->tryGetArticle($articleUuid);
        if (!$article) {
            return ResponseManager::notFoundResponse();
        }

        $body = $request->getBody();
        if (!isset($body['text'])) {
            return ResponseManager::unprocessableEntityResponse();
        }

        $user = AuthorizationManager::getCurrentUser($request, $this->mysql);

        $comment = new Comment(
            UUID::random(),
            $user,
            $article,
            $body['text'],
        );

        $this->commentRepository->save($comment);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            'result' => 'Succesfully left a comment!',
            'articleUuid' => $articleUuid,
            'comment' => [
                'uuid' => $comment->id,
                'author' => [
                    'uuid' => $comment->author->id,
                    'name' => $comment->author->fullName()
                ],
                'content' => $comment->text
            ]
        ]);
        return $response;
    }

    private function deleteComment(Request $request, UUID $articleUuid, UUID $commentUuid)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->mysql)) {
            return ResponseManager::unauthorizedResponse();
        }

        $user = AuthorizationManager::getCurrentUser($request, $this->mysql);
        $article = $this->tryGetArticle($articleUuid);
        $comment = $this->tryGetComment($commentUuid);
        if (!$article || !$comment) return ResponseManager::notFoundResponse();

        if ($user->id != $comment->author->id) {
            return ResponseManager::makeBadRequestResponse('You cant delete a comment which is not yours');
        }

        if ($article->id != $comment->article->id) {
            return ResponseManager::makeBadRequestResponse("Given article does not have comment with UUID $commentUuid");
        }

        $this->commentRepository->delete($comment->id);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            'result' => "Database no more contains comment with UUID $commentUuid"
        ]);
        return $response;
    }

    private function tryGetArticle(UUID $articleUuid)
    {
        try {
            $article = $this->articleRepository->get($articleUuid);
            return $article;
        }
        catch (Exception $ex) {
            return null;
        }
    }

    private function tryGetComment(UUID $commentUuid)
    {
        try {
            $comment = $this->commentRepository->get($commentUuid);
            return $comment;
        }
        catch (Exception $ex) {
            return null;
        }
    }
}