<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use ITRvB\Models\UUID;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\Auth\LogoutController;
use DateTimeImmutable;

class LogoutControllerTest extends ControllerTest
{
    protected function getTestName() : string {
        return 'Login';
    }

    protected function instantiateController() : IController {
        return new LogoutController();
    }

    protected function baseArguments() : array {
        return [];
    }

    public function testLogoutUnauthorized() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        
        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }

    #[Depends('testLogoutUnauthorized')]
    public function testLogout() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);
    }

    #[Depends('testLogout')]
    public function testTokenInvalidation() : void
    {
        $request = $this->genAuthorizedFakeRequest('POST');

        $response = self::$controller->processRequest($request);
        $this->assertUnauthorized($response);
    }
}