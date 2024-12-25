<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\User;
use ITRvB\Models\UUID;

class UserTest extends TestCase
{
    public function testFullName() : void
    {
        $testUser = new User(UUID::random(), "Fred", "Calbuth");
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