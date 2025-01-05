<?php
namespace ITRvB\Models;

use Faker\Factory as F;
use ITRvB\Models\UUID;
use ITRvB\Exceptions\ArgumentException;

class User {
    public function __construct(UUID $id, string $password, string $name, string $surname) {
        if (strlen($password) < 3) throw new ArgumentException("Password for user must not be less than 3 characters.");
        if (strlen($password) > 100) throw new ArgumentException("Password for user must not be more than 100 characters.");
        if (strlen($name) > 100) throw new ArgumentException("Name for user must not be more than 100 characters.");
        if (empty($name)) throw new ArgumentException("Name for user must not be empty.");
        if (strlen($surname) > 100) throw new ArgumentException("Surname for user must not be more than 100 characters.");
        if (empty($surname)) throw new ArgumentException("Surname for user must not be empty.");
        if (is_null($id)) throw new ArgumentException("ID for user cannot be null.");
        
        $this->id = $id;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
    }

    public static function createRandom() : User {
        $faker = F::create();
        return new User(UUID::random(), $faker->password(), $faker->firstName(), $faker->lastName());
    }

    public static function hashPassword(string $password, UUID $uuid) : string
    {
        return hash('sha256', $password . $uuid);
    }

    public function getHashedPassword()
    {
        return self::hashPassword($this->password, $this->id);
    }

    public function fullName() : string {
        return $this->name . ' ' . $this->surname;
    }

    public UUID $id;
    public string $password;
    public string $name;
    public string $surname;
}
?>