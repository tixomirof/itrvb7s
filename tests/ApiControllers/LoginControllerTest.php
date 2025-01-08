<?php

namespace ITRvB\UnitTests;

use ITRvB\Models\UUID;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\Auth\LoginController;
use DateTimeImmutable;

class LoginControllerTest extends ControllerTest
{
    protected function getTestName() : string {
        return 'Login';
    }

    protected function instantiateController() : IController {
        return new LoginController();
    }

    protected function baseArguments() : array {
        return [];
    }

    public function testLoginNoBody() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        
        $response = self::$controller->processRequest($request);
        $this->assertUnprocessableEntity($response);
    }

    public function testLogin() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        $request->body = [
            'uuid' => self::$sampleUser->id,
            'password' => '123'
        ];

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);

        $body = $this->getBody($response);
        $token = $body['token'];
        $loggedUuid = new UUID($body['logged_user_uuid']);
        $tokenExpirationDate = new DateTimeImmutable($body['token_expiration_date']);

        $this->assertEquals(self::$token, 'Bearer ' . $token);
        $this->assertEquals(self::$sampleUser->id, $loggedUuid);
        $this->assertGreaterThan(new DateTimeImmutable(), $tokenExpirationDate);
    }
}