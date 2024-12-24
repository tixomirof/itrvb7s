<?php

namespace ITRvB\Repositories\Seeders;

use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Models\User;

class UserSeeder
{
    public function seed(int $amount) : array
    {
        $mysql = new MySQL();
        $result = array();
        for ($i=0; $i < $amount; $i++) { 
            $user = User::createRandom();
            $mysql->addUser($user);
            array_push($result, $user);
        }
        $mysql->dispose();
        return $result;
    }
}