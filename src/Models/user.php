<?php
namespace Lab03\Models;

use Faker\Factory as F;

class User {
    public function __construct(int $id, string $name, string $surname) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
    }

    public static function createRandom() : User {
        $faker = F::create();
        return new User($faker->randomNumber(5, true), $faker->firstName(), $faker->lastName());
    }

    public function fullName() : string {
        return $this->name . ' ' . $this->surname;
    }

    public int $id;
    public string $name;
    public string $surname;
}
?>