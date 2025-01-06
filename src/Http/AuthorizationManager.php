<?php

namespace ITRvB\Http;

use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\TokenRepositoryInterface;
use ITRvB\Models\User;
use ITRvB\Http\Request;
use ITRvB\Exceptions\UnauthorizedException;

class AuthorizationManager
{
    public static function getCurrentUser(Request $request, MySQL $mysql) : User
    {
        $bearerToken = $request->getHeader("Authorization");
        if ($bearerToken == "EMPTY")
        {
            throw new UnauthorizedException("Cannot get current user, Authorization header is not filled.");
        }

        $token = substr($bearerToken, strlen("Bearer "));

        $tokenRepository = new TokenRepositoryInterface($mysql);
        $user = $tokenRepository->tryGetUserByToken($token);

        if (!$user)
        {
            throw new UnauthorizedException("Cannot get current user, given token is invalid.");
        }

        return $user;
    }

    public static function isAuthorized(Request $request, MySQL $mysql) : bool
    {
        try {
            self::getCurrentUser($request, $mysql);
        } catch (UnauthorizedException $e) {
            return false;
        }
        return true;
    }
}