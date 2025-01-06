<?php

namespace ITRvB\Http\Controllers\Auth;

use ITRvB\Interfaces\IController;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Models\AuthToken;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\TokenRepositoryInterface;
use ITRvB\Exceptions\ArgumentException;
use Exception;

class RegisterController implements IController
{
    private MySQL $mysql;

    public function init(MySQL $mysql)
    {
        $this->mysql = $mysql;
    }

    public function processRequest(Request $request)
    {
        if ($request->getRequestMethod() === 'POST')
        {
            return $this->register($request);
        }
        else return ResponseManager::methodNotAllowed();
    }

    private function register(Request $request)
    {
        $body = $request->getBody();
        if (!$this->validateUser($body)) return ResponseManager::unprocessableEntityResponse();
        
        try {
            $user = new User(
                UUID::random(),
                $body['password'],
                $body['name'],
                $body['surname']
            );
        } catch (ArgumentException $argEx) {
            return ResponseManager::makeBadRequestResponse($argEx->getMessage());
        }

        $this->mysql->addUser($user);
        
        $tokenRepository = new TokenRepositoryInterface($this->mysql);
        $token = $tokenRepository->getOrCreateToken($user->id);

        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode([
                'result' => 'Successfully registered',
                'uuid' => $user->id,
                'token' => $token->getToken(),
                'token_expiration_date' => $token->getAtomExpirationDate()
            ]),
        ];
    }

    private function validateUser($body)
    {
        return isset($body['password'])
            && isset($body['name'])
            && isset($body['surname']);
    }
}