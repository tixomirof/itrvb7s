<?php

namespace ITRvB\Models\Test;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\Article;
use ITRvB\Models\UUID;
use ITRvB\Models\User;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\LikeController;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Repositories\TokenRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;

abstract class ControllerTest extends TestCase
{
    protected static IController $controller;
    protected static MySQL $mysql;
    protected static ArticleRepositoryInterface $articleRepository;
    protected static User $sampleUser;
    protected static Article $sampleArticle;
    protected static string $token;

    public static function setUpBeforeClass() : void
    {
        $controllerTest = new static('ControllerTest');
        self::$mysql = new MySQL();
        self::$articleRepository = new ArticleRepositoryInterface(self::$mysql);
        self::$controller = $controllerTest->instantiateController();
        self::$controller->init(self::$mysql);

        self::$sampleUser = new User(
            UUID::random(),
            '123',
            $controllerTest->getTestName() . 'ControllerTest',
            'SampleUser'
        );
        self::$sampleArticle = new Article(
            UUID::random(),
            self::$sampleUser,
            $controllerTest->getTestName() . 'ControllerTest',
            'SampleArticle'
        );

        self::$mysql->addUser(self::$sampleUser);
        self::$articleRepository->save(self::$sampleArticle);

        $tokenRepository = new TokenRepositoryInterface(self::$mysql);
        self::$token = 'Bearer ' . $tokenRepository->getOrCreateToken(self::$sampleUser->id)->getToken();
    }

    public static function tearDownAfterClass() : void
    {
        self::$mysql->deleteUser(self::$sampleUser->id);

        if (!self::$mysql->isDisposed())
            self::$mysql->dispose();
    }

    protected abstract function getTestName() : string;
    protected abstract function instantiateController() : IController;

    protected abstract function baseArguments() : array;

    protected function genAuthorizedFakeRequest(string $method) : FakeRequest
    {
        $request = $this->genUnauthorizedFakeRequest($method);
        $request->headers = ['Authorization' => self::$token];
        return $request;
    }

    protected function genUnauthorizedFakeRequest(string $method) : FakeRequest
    {
        $request = new FakeRequest($method);
        $request->arguments = $this->baseArguments();
        return $request;
    }
    
    protected function getArrayByKey(array $response, string $key) : array
    {
        return (array)json_decode($response[$key]);
    }

    protected function getBody(array $response) : array
    {
        return $this->getArrayByKey($response, 'body');
    }

    protected function assertResponseStatus(array $response, string $expectedStatus) : void
    {
        $this->assertSame($expectedStatus, $response['status_code_header']);
    }

    protected function assertOK(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 200 OK');
    }

    protected function assertCreated(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 201 Created');
    }

    protected function assertBadRequest(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 400 Bad Request');
    }

    protected function assertUnauthorized(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 401 Unauthorized');
    }

    protected function assertNotFound(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 404 Not Found');
    }

    protected function assertMethodNotAllowed(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 405 Method Not Allowed');
    }

    protected function assertUnprocessableEntity(array $response) : void
    {
        $this->assertResponseStatus($response, 'HTTP/1.1 422 Unprocessable Entity');
    }
}