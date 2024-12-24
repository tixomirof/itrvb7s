<?php

namespace ITRvB\Repositories\Seeders;

use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Models\User;

class UserSeeder
{
    public function __construct(
        private readonly MySQL $mysql,
    )
    {
    }

    public function seed(int $amount) : array
    {
        $result = array();
        for ($i=0; $i < $amount; $i++) { 
            $user = User::createRandom();
            $this->mysql->addUser($user);
            array_push($result, $user);
        }
        return $result;
    }
}