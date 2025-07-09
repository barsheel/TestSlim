<?php

namespace Testslim;

class Validator
{


    public function validate(array $user) {
        $errors = [];
        if (!$this->isValidName($user['name'])) {
            $errors['name'] = 'Введите правильное имя';
        }    
        if (!$this->isValidEmail($user['email'])) {
            $errors['email'] = 'Введите правильный email';
        }
        return $errors;
    }

    public function isValidName(string $name): bool
    {
        if (preg_match('/^[a-zA-Zа-яА-ЯёЁ]{3,}$/u', $name)) {
            return true;
        }
        return false;
    }

    public function isValidEmail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }
}