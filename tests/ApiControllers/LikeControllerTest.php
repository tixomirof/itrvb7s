<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use ITRvB\Models\ArticleLike;
use ITRvB\Models\Test\FakeRequest;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\LikeController;

class LikeControllerTest extends ControllerTest
{
    protected function getTestName() : string
    {
        return 'Like';
    }

    protected function instantiateController() : IController
    {
        return new LikeController();
    }

    protected function baseArguments() : array
    {
        return ['article' => (string)self::$sampleArticle->id];
    }

    public function testGetLikeCountZero() : void
    {
        $request = $this->genUnauthorizedFakeRequest('GET');
        
        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
        $this->assertSame(0, (int)$this->getBody($response)['likeCount']);
    }

    public function testLeaveLikeUnauthorized() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        
        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }

    #[Depends('testLeaveLikeUnauthorized')]
    public function testLeaveLikeAuthorized() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');

        $response = self::$controller->processRequest($request);
        $this->assertCreated($response);
        
        $body = $this->getBody($response);
        $this->assertSame(1, $body['newLikeCount']);
    }

    #[Depends('testLeaveLikeAuthorized')]
    public function testGetLikeCountOne() : void
    {
        $request = $this->genUnauthorizedFakeRequest('GET');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
        $this->assertSame(1, (int)$this->getBody($response)['likeCount']);
    }

    #[Depends('testGetLikeCountOne')]
    public function testDuplicateLike() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');

        $response = self::$controller->processRequest($request);
        $this->assertBadRequest($response);
    }

    #[Depends('testDuplicateLike')]
    public function testRemoveLikeUnauthorized() : void
    {
        $request = $this->genUnauthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }

    #[Depends('testRemoveLikeUnauthorized')]
    public function testRemoveLikeAuthorized() : void
    {
        $request = $this->genAuthorizedFakeRequest('DELETE');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);

        $this->testGetLikeCountZero();
    }
}