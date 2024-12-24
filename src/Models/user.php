<?php
namespace ITRvB\Models;

use Faker\Factory as F;
use ITRvB\Models\UUID;

class User {
    public function __construct(UUID $id, string $name, string $surname) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
    }

    public static function createRandom() : User {
        $faker = F::create();
        return new User(UUID::random(), $faker->firstName(), $faker->lastName());
    }

    public function fullName() : string {
        return $this->name . ' ' . $this->surname;
    }

    public UUID $id;
    public string $name;
    public string $surname;
}
?>