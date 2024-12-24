<?php

namespace ITRvB\Repositories\Connection;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Exceptions\ConnectionDisposedException;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use mysqli;

class MySQL
{
    public function __construct()
    {
        $this->disposed = false;
        $this->establishConnection();
    }

    private function establishConnection()
    {
        $db_con = new mysqli("localhost", "root", "", "itrvb-7-semestr");
    
        if ($db_con->connect_error) {
            die("Connection failed: " . $db_con->connect_error);
        }
    
        $this->db_con = $db_con;
    }

    private mysqli $db_con;
    private bool $disposed;

    public function query(string $query)
    {
        if ($this->disposed)
        { 
            throw new ConnectionDisposedException("Database connection was already disposed. 
                Create another instance of MySQL connection.");
        }

        return $this->db_con->query($query);
    }

    public function getUser(UUID $uuid) : User
    {
        $userQuery = $this->query("SELECT * FROM users WHERE users.uuid = '$uuid' LIMIT 1");
        if ($userQuery->num_rows == 0)
        {
            throw new NotFoundException("Could not find any user with UUID $uuid in the database.");
        }
        $userData = $userQuery->fetch_assoc();

        return new User(
            $uuid,
            $userData["name"],
            $userData["surname"]
        );
    }

    public function addUser(User $user) : void
    {
        $result = $this->query("INSERT INTO users (uuid, name, surname) VALUES (
            '$user->id', '$user->name', '$user->surname')");

        if (!$result)
        {
            die("Unknown error has occured during adding of new user data row.");
        }
    }

    public function getAllUsers() : array
    {
        $result = $this->query("SELECT * FROM users");
        $users = array();
        while ($row = $result->fetch_assoc())
        {
            $user = new User(
                new UUID($row["uuid"]),
                $row["name"],
                $row["surname"]
            );
            array_push($users, $user);
        }
        return $users;
    }

    public function dispose() : void
    {
        $this->db_con->close();
        $this->disposed = true;
    }
}