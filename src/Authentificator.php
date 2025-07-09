<?php

namespace Testslim;


class Authentificator
{
    const DB_FILENAME = __DIR__.'/../data/auth.json';

    public function read(): array
    {
        $result = json_decode(file_get_contents(self::DB_FILENAME), true) ?? [];
        return $result;
    }

    public function write(array $data): void
    {
        file_put_contents(self::DB_FILENAME, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function add($name, $email) {
        $data = $this->read();
        
        do {
            $newId = uniqid();
        }
        while (isset($data[$newId]));

        $data[$newId] = [
            'id' => $newId,
            'name' => $name,
            'email' => $email
        ];
        $this->write($data);
    }


    public function edit(array $newData) 
    {
        $data = $this->read();
        if (isset($data[$newData['id']])) {
            $data[$newData['id']] = $newData;
            $this->write($data);
        } else {
            $message = "No such user {$newData['id']}";
            throw new \Exception ($message);
        }
        
    }

    public function getUserById(string $id): array
    {
        $data = $this->read();
        if (isset($data[$id])) {
            return $data[$id];
        }
        throw new \Exception ('No such user');
    }

    public function authUser(string $login, string $password): array {
        $data = $this->read();
        foreach ($data as $user) {
            if ($login === $user['login']) {
                if ($password === $user['password']) {
                    return [];
                } else {
                    return ['password' => 'wrong password'];
                }
            }
            return ['login' => 'no such user'];
        }
        return ['no users'];
    }

}