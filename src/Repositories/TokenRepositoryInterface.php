<?php

namespace ITRvB\Repositories;

use ITRvB\Interfaces\IRepository;
use ITRvB\Models\AuthToken;
use ITRvB\Models\UUID;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Singletons\Logger;
use DateTimeImmutable;

class TokenRepositoryInterface implements IRepository
{
    public function __construct(
        private readonly MySQL $mysql
    ) {}

    // by user UUID
    public function get(UUID $uuid)
    {
        Logger::info("TokenRepository: retrieving token for user $uuid");

        $tokenQuery = $this->mysql->query("SELECT * FROM authTokens WHERE authTokens.user_id = '$uuid' LIMIT 1");
        if ($tokenQuery->num_rows == 0)
        {
            Logger::info("TokenRepository: no token was found for user $uuid. Initializating a new token...");
            return $this->createTokenFor($uuid);
        }

        $tokenData = $tokenQuery->fetch_assoc();
        $authToken = $this->dataToAuthToken($tokenData);

        return $authToken;
    }

    public function createTokenFor(UUID $userUuid) : AuthToken
    {
        $token = bin2hex(random_bytes(40));

        $user = $this->mysql->getUser($userUuid);
        
        $currentTime = new DateTimeImmutable();
        $tokenExpirationTime = $currentTime->add(AuthToken::getDefaultExpirationTime());

        $authToken = new AuthToken(
            $token,
            $user->id,
            $tokenExpirationTime
        );

        $this->save($authToken);

        return $authToken;
    }

    public function getOrCreateToken(UUID $userUuid) : AuthToken
    {
        $token = $this->get($userUuid);
        if ($token->hasExpired()) {
            $this->delete($token->getUserUuid());
            return $this->createTokenFor($userUuid);
        }
        return $token;
    }

    public function tryGetUserByToken(string $token) : ?User
    {
        Logger::info("TokenRepository: retrieving user by token $token");

        $query = $this->mysql->query(
            "SELECT * FROM users INNER JOIN authTokens ON users.uuid = authTokens.user_id WHERE authTokens.token = '$token'");
        if ($query->num_rows === 0) {
            Logger::info("TokenRepository: invalid token sent. No such token is present in the database.");
            return null;
        }

        while ($data = $query->fetch_assoc())
        {
            $authToken = $this->dataToAuthToken($data);

            if (!$authToken->hasExpired()) {
                $user = new User(
                    new UUID($data['uuid']),
                    $data['password'],
                    $data['name'],
                    $data['surname']
                );
                return $user;
            } else {
                Logger::warning("TokenRepository: expired token detected. Deleting it.");
                $this->delete($authToken->getUserUuid());
            }
        }

        return null;
    }

    public function save($model) : void
    {
        Logger::info("TokenRepository: saving token " . $model->getToken() . 
            " for user " . $model->getUserUuid() . ". Expires " . $model->getAtomExpirationDate());
        $this->mysql->query("INSERT INTO authTokens VALUES " .
            "('" . $model->getUserUuid() . "', '" . 
            $model->getAtomExpirationDate() . "', '" . $model->getToken() . "')");
    }

    // by user UUID
    public function delete(UUID $uuid) : void
    {
        Logger::info("TokenRepository: attempting to delete AuthToken for user with UUID $uuid");
        $this->mysql->query("DELETE FROM authTokens WHERE authTokens.user_id = '$uuid'");
    }

    private function dataToAuthToken($tokenData)
    {
        return new AuthToken(
            $tokenData['token'],
            new UUID($tokenData['user_id']),
            new DateTimeImmutable($tokenData['expiresOn'])
        );
    }
}