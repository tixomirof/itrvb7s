<?php

namespace ITRvB\UnitTests;

use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Models\Test\ControllerTest;
use ITRvB\Interfaces\IController;
use ITRvB\Http\Controllers\Auth\RegisterController;
use ITRvB\Repositories\TokenRepositoryInterface;

class RegisterControllerTest extends ControllerTest
{
    protected static User $user;

    protected function getTestName() : string {
        return 'Register';
    }

    protected function instantiateController() : IController {
        return new RegisterController();
    }

    protected function baseArguments() : array {
        return [];
    }

    protected function fillFields() : void {
        self::$user = new User(
            UUID::random(),
            'password',
            'RegisterControllerTest',
            'UserToRegister'
        );
    }

    protected function unfillFields() : void {
        self::$mysql->deleteUser(self::$user->id);
    }

    public function testRegisterNoBody() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        
        $response = self::$controller->processRequest($request);
        $this->assertUnprocessableEntity($response);
    }

    public function testRegister() : void
    {
        $request = $this->genUnauthorizedFakeRequest('POST');
        $request->body = [
            'password' => self::$user->password,
            'name' => self::$user->name,
            'surname' => self::$user->surname
        ];

        $response = self::$controller->processRequest($request);
        $this->assertOK($response);

        $body = $this->getBody($response);

        self::$user->id = new UUID($body['uuid']);

        $tokenRepository = new TokenRepositoryInterface(self::$mysql);
        $authToken = $tokenRepository->get(self::$user->id);

        $this->assertEquals($authToken->getToken(), $body['token']);
        $this->assertEquals((string)$authToken->getAtomExpirationDate(), $body['token_expiration_date']);
        $this->assertNotEquals(self::$user->password, self::$mysql->getUser(self::$user->id)->password);
    }
}