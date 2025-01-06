<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\Article;
use ITRvB\Models\UUID;
use ITRvB\Models\User;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Http\Controllers\ArticleController;
use ITRvB\Repositories\TokenRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;

class ArticleControllerTest extends TestCase
{
    private static ArticleController $controller;
    private static MySQL $mysql;
    private static User $authorizedUser;
    private static string $token;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$controller = new ArticleController();
        self::$controller->init(self::$mysql);

        self::$authorizedUser = new User(
            UUID::random(),
            '123',
            'ArticleControllerTest',
            'AuthorizedUser'
        );

        self::$mysql->addUser(self::$authorizedUser);

        $tokenRepository = new TokenRepositoryInterface(self::$mysql);
        self::$token = 'Bearer ' . $tokenRepository->getOrCreateToken(self::$authorizedUser->id)->getToken();
    }

    public static function tearDownAfterClass() : void
    {
        self::$mysql->deleteUser(self::$authorizedUser->id);

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
    public function testPost() : Article
    {
        $article = new Article(
            UUID::random(),
            self::$authorizedUser,
            'sample header',
            'sample text'
        );

        $request = new FakeRequest('POST');
        $request->headers = [
            'Authorization' => self::$token
        ];
        $request->body = [
            'uuid' => $article->id,
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
    public function testUnauthorizedDelete(Article $article) : Article
    {
        $request = new FakeRequest('DELETE', $article->id);

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 401 Unauthorized', $response['status_code_header']);

        return $article;
    } 

    #[Depends('testUnauthorizedDelete')]
    public function testDelete(Article $article) : Article
    {
        $request = new FakeRequest('DELETE', $article->id);

        $request->headers = [
            'Authorization' => self::$token
        ];

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
        $headers = ['Authorization' => self::$token];
        $request = new FakeRequest('POST');
        $request->body = [
            'author_id' => $article->author->id,
            'header' => $article->header,
            'text' => $article->text,
        ];
        $request->headers = $headers;

        $response = self::$controller->processRequest($request);
        $this->assertSame('HTTP/1.1 201 Created', $response['status_code_header']);

        $articleBody = (array)json_decode($this->getArticleJSON($response));
        $this->assertTrue(isset($articleBody['id']));
        
        // must not throw an exception
        $randomlyGeneratedUuid = new UUID($articleBody['id']);

        // delete created object
        $deleteRequest = new FakeRequest('DELETE', (string)$randomlyGeneratedUuid);
        $deleteRequest->headers = $headers;
        $deleteResponse = self::$controller->processRequest($deleteRequest);
        $this->assertSame('HTTP/1.1 200 OK', $deleteResponse['status_code_header']);
    }
}