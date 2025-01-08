<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use ITRvB\Models\UUID;
use ITRvB\Models\Comment;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Http\Controllers\CommentController;
use ITRvB\Interfaces\IController;

class CommentControllerTest extends ControllerTest
{
    protected static Comment $comment;

    protected function getTestName() : string
    {
        return 'Comment';
    }

    protected function instantiateController() : IController
    {
        return new CommentController();
    }

    protected function fillFields() : void
    {
        self::$comment = new Comment(
            UUID::random(),
            self::$sampleUser,
            self::$sampleArticle,
            'CommentControllerTest'
        );
    }

    protected function baseArguments() : array
    {
        return [
            'article' => self::$sampleArticle->id,
            'comment' => self::$comment->id
        ];
    }

    public function testGetNoArguments() : void
    {
        $request = new FakeRequest();

        $response = self::$controller->processRequest($request);
        $this->assertUnprocessableEntity($response);
    }

    public function testGetAllComments() : void
    {
        $request = new FakeRequest();
        $request->arguments = ['article' => self::$sampleArticle->id];
        
        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
    }

    public function testGetUnexistentComment() : void
    {
        $request = $this->genUnauthorizedFakeRequest('GET');

        $response = self::$controller->processRequest($request);
        $this->assertNotFound($response);
    }

    #[Depends('testGetUnexistentComment')]
    public function testPostUnauthorized() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');

        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }

    private function assertCommentEquality(array $response)
    {
        $body = $this->getBody($response);
        $comment = $body['comment'];
        $author = $comment['author'];
        
        $this->assertEquals(self::$comment->id, new UUID($comment['uuid']));
        $this->assertEquals(self::$comment->author->id, $author['uuid']);
        $this->assertEquals(self::$comment->article->id, $body['articleUuid']);
        $this->assertSame(self::$comment->text, $comment['content']);
    }

    #[Depends('testPostUnauthorized')]
    public function testPost() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');
        $request->body = [
            'text' => self::$comment->text
        ];

        $response = self::$controller->processRequest($request);
        self::$comment->id = new UUID($this->getBody($response)['comment']['uuid']);
        $this->assertCreated($response);
        $this->assertCommentEquality($response);
    }

    #[Depends('testPost')]
    public function testGet() : void
    {
        $request = $this->genUnauthorizedFakeRequest('GET');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
        $this->assertCommentEquality($response);
    }

    #[Depends('testGet')]
    public function testDeleteUnauthorized() : void
    {
        $request = $this->genUnauthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }

    #[Depends('testDeleteUnauthorized')]
    public function testDelete() : void
    {
        $request = $this->genAuthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
        $this->testGetUnexistentComment();
    }

    #[Depends('testDelete')]
    public function testDeleteUnexistent() : void
    {
        $request = $this->genAuthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertNotFound($response);
    }
}