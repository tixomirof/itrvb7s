<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Exceptions\ArgumentException;

class UserTest extends TestCase
{
    #[TestWith(["pw", "Small", "Password"])]
    #[TestWith(["ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ", "Long", "Password"])]
    #[TestWith(["EmptyName", "", "Surname"])]
    #[TestWith(["EmptySurname", "Name", ""])]
    #[TestWith(["LongName", "ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ", "Surname"])]
    #[TestWith(["LongSurname", "Name", "ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ"])]
    public function testArgumentException(string $password, string $name, string $surname) : void
    {
        $uuid = UUID::random();

        $this->expectException(ArgumentException::class);
        $testUser = new User($uuid, $password, $name, $surname);
    }

    public function testFullName() : void
    {
        $testUser = new User(UUID::random(), '123', "Fred", "Calbuth");
        $this->assertSame("Fred Calbuth", $testUser->fullName());
    }

    public function testGetRandom() : void
    {
        $randomUser1 = User::createRandom();
        $randomUser2 = User::createRandom();
        $this->assertNotEquals($randomUser1, $randomUser2);
    }

    #[Depends('testGetRandom')]
    public function testGetRandomMany() : void
    {
        $comparisonAttempts = 10;
        for ($i=0; $i < $comparisonAttempts; $i++) { 
            $this->assertNotEquals(User::createRandom(), User::createRandom());
        }
    }
}