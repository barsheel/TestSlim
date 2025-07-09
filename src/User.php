<?php

namespace App;

class User
{
    private ?int $id;
    private ?string $name;
    private ?string $email;

    public static function createFromArray($data): User
    {   
        $user = new User();
        $user->id = isset($data['id']) ? $data['id'] : null;
        $user->name = isset($data['name']) ? $data['name'] : null;
        $user->email = isset($data['email']) ? $data['email'] : null;
        return $user;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function print()
    {
        print_r($this->id);
        print_r($this->name);
        print_r($this->email);
    }
}