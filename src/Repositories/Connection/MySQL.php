<?php

namespace ITRvB\Repositories\Connection;

use ITRvB\Exceptions\NotFoundException;
use ITRvB\Exceptions\ConnectionDisposedException;
use ITRvB\Models\User;
use ITRvB\Models\UUID;
use ITRvB\Singletons\Logger;
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
            Logger::warning("MySQL: failed to connect with error '$db_con->connect_error'");
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
            Logger::warning("MySQL: an attempt to call query methods when the connection was already disposed.");
            throw new ConnectionDisposedException("Database connection was already disposed. 
                Create another instance of MySQL connection.");
        }

        return $this->db_con->query($query);
    }

    public function queryWithException(string $query, string $errorMsg)
    {
        $result = $this->query($query);
        if ($result->num_rows == 0)
        {
            Logger::warning("MySQL: query has not found anything, throwing an exception with message '$errorMsg'");
            throw new NotFoundException($errorMsg);
        }
        return $result;
    }

    public function getUser(UUID $uuid) : User
    {
        Logger::info("MySQL: retrieving User with UUID $uuid from the database...");

        $userData = $this->queryWithException(
            "SELECT * FROM users WHERE users.uuid = '$uuid' LIMIT 1",
            "Could not find any user with UUID $uuid in the database."
        )->fetch_assoc();

        Logger::info("MySQL: User was retrieved.");

        return new User(
            $uuid,
            $userData["password"],
            $userData["name"],
            $userData["surname"]
        );
    }

    public function addUser(User $user) : void
    {
        Logger::info("MySQL: saving User with UUID $user->id and name " . $user->fullName());

        $user->password = $user->getHashedPassword();
        $result = $this->query("INSERT INTO users (uuid, name, surname, password) VALUES (
            '$user->id', '$user->name', '$user->surname', '$user->password')");

        if (!$result)
        {
            Logger::warning("MySQL: Unknown error has occured during adding of new user data row. User was not added.");
            die("Unknown error has occured during adding of new user data row.");
        }

        Logger::info("MySQL: successfully saved User with UUID $user->id");
    }

    public function deleteUser(UUID $uuid) : void
    {
        Logger::info("MySQL: attempting to delete User with UUID $uuid");
        $this->query("DELETE FROM users WHERE users.uuid = '$uuid'");
    }

    public function getAllUsers() : array
    {
        Logger::info("MySQL: retrieving all users");

        $result = $this->query("SELECT * FROM users");
        $users = array();
        while ($row = $result->fetch_assoc())
        {
            $user = new User(
                new UUID($row["uuid"]),
                $row["password"],
                $row["name"],
                $row["surname"]
            );
            array_push($users, $user);
        }

        Logger::info("MySQL: found " . count($users) . " users");

        return $users;
    }

    public function dispose() : void
    {
        $this->db_con->close();
        $this->disposed = true;
    }

    public function isDisposed() : bool
    {
        return $this->disposed;
    }
}