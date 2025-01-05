<?php

namespace ITRvB\Http\Controllers\Auth;

use ITRvB\Interfaces\IController;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Repositories\Connection\MySQL;
use Exception;

class LoginController implements IController
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
            return $this->login($request);
        }
        else return ResponseManager::methodNotAllowed();
    }

    private function login(Request $request)
    {
        $body = $request->getBody();
        $user = $this->validateUser($body);
        if (!$user) return ResponseManager::unprocessableEntityResponse();
        
        if ($user->password !== User::hashPassword($body['password'], $user->id)) {
            return ResponseManager::makeBadRequestResponse('Wrong password');
        }

        // gen token

        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode([
                'result' => 'Successfully logged in',
                // token
            ]),
        ];
    }

    private function validateUser($body)
    {
        if (!isset($body['uuid']) || !isset($body['password'])) {
            return null;
        }

        try {
            $uuid = new UUID($body['uuid']);
            $user = $this->mysql->getUser($uuid);
            return $user;
        } catch (Exception $e) { return null; }
    }
}