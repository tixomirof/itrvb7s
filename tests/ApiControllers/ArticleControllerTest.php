<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\Article;
use ITRvB\Models\UUID;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Http\Controllers\ArticleController;
use ITRvB\Repositories\Connection\MySQL;

class ArticleControllerTest extends TestCase
{
    private static ArticleController $controller;
    private static MySQL $mysql;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$controller = new ArticleController();
        self::$controller->init(self::$mysql);
    }

    public static function tearDownAfterClass() : void
    {
        if (!self::$mysql->isDisposed())
            self::$mysql->dispose();
    }

    private function getArticleJSON(array $response) : string
    {
        return json_encode(((array)json_decode($response['body']))['article']);
    }

    public function testGetAll() : void
    {
        $request = new FakeRequest();
        $response = self::$controller->processRequest($request);
        $this->assertSame($response['status_code_header'], 'HTTP/1.1 200 OK');
    }

    #[TestWith(["58bac195-951e-ffff-61bb-a517b2e84100", "ffffffff-eeee-dddd-cccc-aabababababb", "header", "text"])] // unexistent user
    #[TestWith(["zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz", "1e54e6a0-6844-45a9-934d-9ec512ec7fc8", "header", "text"])] // invalid uuid
    #[TestWith([null, null, "header", "text"])] // unset author uuid
    #[TestWith([null, "1e54e6a0-6844-45a9-934d-9ec512ec7fc8", null, "text"])] // unset header
    #[TestWith([null, "1e54e6a0-6844-45a9-934d-9ec512ec7fc8", "header", null])] // unset text
    #[TestWith([null, "uuid", "header", "text"])] // invalid author uuid
    public function testPostFailure(?string $uuid, ?string $author_uuid, ?string $header, ?string $text) : void
    {
        $request = new FakeRequest('POST');

        $requestBody = [];
        if (!is_null($uuid)) $requestBody['uuid'] = $uuid;
        if (!is_null($author_uuid)) $requestBody['author_id'] = $author_uuid;
        if (!is_null($header)) $requestBody['header'] = $header;
        if (!is_null($text)) $requestBody['text'] = $text;

        $request->body = $requestBody;

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 422 Unprocessable Entity', $response['status_code_header']);
    }

    #[Depends('testPostFailure')]
    public function testPost() : Article
    {
        $article = new Article(
            UUID::random(),
            // Must create user with UUID 1e54e6a0-6844-45a9-934d-9ec512ec7fc8 before testing.
            self::$mysql->getUser(new UUID('1e54e6a0-6844-45a9-934d-9ec512ec7fc8')),
            'sample header',
            'sample text'
        );

        $request = new FakeRequest('POST');
        $request->body = [
            'uuid' => $article->id,
            'author_id' => $article->author->id,
            'header' => $article->header,
            'text' => $article->text
        ];

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 201 Created', $response['status_code_header']);
        $this->assertSame(json_encode($article), $this->getArticleJSON($response));

        return $article;
    }

    #[Depends('testPost')]
    public function testGet(Article $article) : Article
    {
        $request = new FakeRequest('GET', $article->id);
        
        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 200 OK', $response['status_code_header']);
        $this->assertSame(json_encode($article), $response['body']);

        return $article;
    }

    #[Depends('testGet')]
    public function testDelete(Article $article) : Article
    {
        $request = new FakeRequest('DELETE', $article->id);

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 200 OK', $response['status_code_header']);

        return $article;
    }

    #[Depends('testDelete')]
    public function testGetNonExistent(Article $article) : Article
    {
        $request = new FakeRequest('GET', $article->id);

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 404 Not Found', $response['status_code_header']);

        return $article;
    }

    #[Depends('testGetNonExistent')]
    public function testPostAutoUuid(Article $article) : void
    {
        $request = new FakeRequest('POST');
        $request->body = [
            'author_id' => $article->author->id,
            'header' => $article->header,
            'text' => $article->text,
        ];

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 201 Created', $response['status_code_header']);

        $articleBody = (array)json_decode($this->getArticleJSON($response));
        $this->assertTrue(isset($articleBody['id']));
        
        // must not throw an exception
        $randomlyGeneratedUuid = new UUID($articleBody['id']);

        // delete created object
        $deleteRequest = new FakeRequest('DELETE', (string)$randomlyGeneratedUuid);
        $deleteResponse = self::$controller->processRequest($deleteRequest);
        $this->assertSame('HTTP/1.1 200 OK', $deleteResponse['status_code_header']);
    }
}