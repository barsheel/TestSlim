<?php

namespace App;

class UserDAO
{

    private $conn;

    public function __construct(\PDO $conn)
    {   
        $this->conn = $conn;
        // Создаем таблицу users
        $sql = "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)";
        $this->conn->exec($sql);
    }
    
    public function readAll(): array
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->conn->query($sql);
        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = User::createFromArray($row);
        }
        return $users;
    }

    public function getUserById(int $id): User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $user = $stmt->execute();
        $user = $stmt->fetch();
        return User::createFromArray($user);        
    }

    public function insert($name, $email) {
        $sql = "INSERT INTO users (name, email) VALUES (:name, :email)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
    }


    public function update(User $user) 
    {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->execute();
    }

    public function delete(int $id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}