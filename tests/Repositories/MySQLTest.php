<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\UUID;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Exceptions\NotFoundException;
use ITRvB\Exceptions\ConnectionDisposedException;

class MySQLTest extends TestCase
{
    private static MySQL $mysql;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
    }

    public static function tearDownAfterClass() : void
    {
        if (!self::$mysql->isDisposed())
            self::$mysql->dispose();
    }

    #[DoesNotPerformAssertions]
    public function testAddUser() : User
    {
        $user = new User(UUID::random(), "Alice", "Rough");
        self::$mysql->addUser($user); // must not throw an exception
        return $user;
    }

    #[Depends('testAddUser')]
    public function testGetUser(User $user) : User
    {
        $dbUser = self::$mysql->getUser($user->id);
        $this->assertEquals($user, $dbUser);
        return $user;
    }

    #[Depends('testGetUser')]
    public function testGetAllUsers(User $user) : User
    {
        $allUsers = self::$mysql->getAllUsers();
        $this->assertContainsEquals($user, $allUsers);
        return $user;
    }

    #[Depends('testGetAllUsers')]
    #[DoesNotPerformAssertions]
    public function testDeleteUser(User $user) : UUID
    {
        self::$mysql->deleteUser($user->id);
        return $user->id;
    }

    #[Depends('testDeleteUser')]
    public function testGetUnexistingUser(UUID $uuid) : void
    {
        $this->expectException(NotFoundException::class);
        self::$mysql->getUser($uuid);
    }

    #[Depends('testGetUnexistingUser')]
    public function testDisposedConnectionException() : void
    {
        self::$mysql->dispose();
        $this->expectException(ConnectionDisposedException::class);
        self::$mysql->getAllUsers();
    }
}