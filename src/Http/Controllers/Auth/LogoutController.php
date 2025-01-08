<?php

namespace ITRvB\Http\Controllers\Auth;

use ITRvB\Interfaces\IController;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Models\AuthToken;
use ITRvB\Http\Request;
use ITRvB\Http\ResponseManager;
use ITRvB\Http\AuthorizationManager;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\TokenRepositoryInterface;
use Exception;

class LogoutController implements IController
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
            return $this->logout($request);
        }
        else return ResponseManager::methodNotAllowed();
    }

    private function logout(Request $request)
    {
        if (!AuthorizationManager::isAuthorized($request, $this->mysql)) {
            return ResponseManager::unauthorizedResponse();
        }

        $user = AuthorizationManager::getCurrentUser($request, $this->mysql);
        
        $tokenRepository = new TokenRepositoryInterface($this->mysql);
        $tokenRepository->delete($user->id);

        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode([
                'result' => 'Successfully logged out!'
            ])
        ];
    }
}