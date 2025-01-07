<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use ITRvB\Models\Article;
use ITRvB\Models\UUID;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\ArticleController;

class ArticleControllerTest extends ControllerTest
{
    protected static Article $article;

    private function getArticleJSON(array $response) : string
    {
        return json_encode(((array)json_decode($response['body']))['article']);
    }

    protected function getTestName() : string
    {
        return 'Article';
    }

    protected function instantiateController() : IController
    {
        return new ArticleController();
    }

    protected function fillFields() : void
    {
        self::$article = new Article(
            UUID::random(),
            self::$sampleUser,
            'ArticleControllerTest',
            'CreatableArticle'
        );
    }

    protected function baseArguments() : array
    {
        return ['uuid' => self::$article->id];
    }

    public function testGetAll() : void
    {
        $request = new FakeRequest();
        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
    }

    #[TestWith(["58bac195-951e-ffff-61bb-a517b2e84100", false, "header", "text", '401 Unauthorized'])] // unauthorized user
    #[TestWith(["zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz", true, "header", "text"])] // invalid uuid
    #[TestWith([null, true, null, "text"])] // unset header
    #[TestWith([null, true, "header", null])] // unset text
    public function testPostFailure(?string $uuid, bool $authorized, ?string $header,
        ?string $text, string $expectableStatus = '422 Unprocessable Entity') : void
    {
        $request = new FakeRequest('POST');

        $requestBody = [];
        if (!is_null($uuid)) $requestBody['uuid'] = $uuid;
        if (!is_null($header)) $requestBody['header'] = $header;
        if (!is_null($text)) $requestBody['text'] = $text;

        $request->body = $requestBody;
        $request->headers = [
            'Authorization' => !$authorized ? 'EMPTY' : self::$token
        ];

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 ' . $expectableStatus, $response['status_code_header']);
    }

    #[Depends('testPostFailure')]
    public function testPost() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');
        $request->body = [
            'uuid' => self::$article->id,
            'header' => self::$article->header,
            'text' => self::$article->text
        ];

        $response = self::$controller->processRequest($request);
        $this->assertCreated($response);
        $this->assertSame(json_encode(self::$article), $this->getArticleJSON($response));
    }

    #[Depends('testPost')]
    public function testGet() : void
    {
        $request = $this->genAuthorizedFakeRequest('GET');
        
        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
        $this->assertSame(json_encode(self::$article), $response['body']);
    }

    #[Depends('testGet')]
    public function testUnauthorizedDelete() : void
    {
        $request = $this->genUnauthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    } 

    #[Depends('testUnauthorizedDelete')]
    public function testDelete() : void
    {
        $request = $this->genAuthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
    }

    #[Depends('testDelete')]
    public function testGetNonExistent() : void
    {
        $request = $this->genUnauthorizedFakeRequest('GET');

        $response = self::$controller->processRequest($request);
        $this->assertNotFound($response);
    }

    #[Depends('testGetNonExistent')]
    public function testDeleteUnexistent() : void
    {
        $request = $this->genAuthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertNotFound($response);
    }

    #[Depends('testDeleteUnexistent')]
    public function testPostAutoUuid() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');
        $request->body = [
            'author_id' => self::$article->author->id,
            'header' => self::$article->header,
            'text' => self::$article->text,
        ];

        $response = self::$controller->processRequest($request);
        $this->assertCreated($response);

        $articleBody = (array)json_decode($this->getArticleJSON($response));
        $this->assertTrue(isset($articleBody['id']));
        
        // must not throw an exception
        $randomlyGeneratedUuid = new UUID($articleBody['id']);

        // delete created object
        $deleteRequest = $this->genAuthorizedFakeRequest('DELETE');
        $deleteRequest->arguments = ['uuid' => (string)$randomlyGeneratedUuid];
        $deleteResponse = self::$controller->processRequest($deleteRequest);
        $this->assertOK($deleteResponse);
    }
}