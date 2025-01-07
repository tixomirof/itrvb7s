<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\AuthToken;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\TokenRepositoryInterface;
use ITRvB\Exceptions\NotFoundException;

class TokenRepositoryTest extends TestCase
{
    private static MySQL $mysql;
    private static TokenRepositoryInterface $repo;
    private static User $sampleUser;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$repo = new TokenRepositoryInterface(self::$mysql);
        self::$sampleUser = User::createRandom();
        self::$mysql->addUser(self::$sampleUser);
    }

    public static function tearDownAfterClass() : void
    {
        self::$mysql->deleteUser(self::$sampleUser->id);
        self::$mysql->dispose();
    }

    public function testGetUnexistent() : void
    {
        $this->expectException(NotFoundException::class);
        $authToken = self::$repo->get(self::$sampleUser->id);
    }

    public function testTryGetUserByUnexistentToken() : void
    {
        $user = self::$repo->tryGetUserByToken(AuthToken::generateTokenString());
        $this->assertNull($user);
    }

    public function testSave() : AuthToken
    {
        $authToken = self::$repo->createTokenFor(self::$sampleUser->id);
        $this->assertEquals(self::$sampleUser->id, $authToken->getUserUuid());
        return $authToken;
    }

    #[Depends('testSave')]
    public function testGet(AuthToken $authToken) : AuthToken
    {
        $dbAuthToken = self::$repo->get($authToken->getUserUuid());
        $this->assertEquals($authToken->getToken(), $dbAuthToken->getToken());
        $this->assertEquals($authToken->getUserUuid(), $dbAuthToken->getUserUuid());
        $this->assertEquals($authToken->getAtomExpirationDate(), $dbAuthToken->getAtomExpirationDate());
        return $authToken;
    }

    #[Depends('testGet')]
    public function testDelete(AuthToken $authToken) : void
    {
        self::$repo->delete($authToken->getUserUuid());

        $this->expectException(NotFoundException::class);
        self::$repo->get($authToken->getUserUuid());
    }
}