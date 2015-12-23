<?php
namespace Uppu4\Helper;

class DataValidator
{
    public $error;

    public function validateUserData($data)
    {
        $this->checkLogin($data['login']);
        $this->checkPassword($data['password'], $data['confirmation']);
        $this->checkEmail($data['email']);
    }

    public function validateComment(\Uppu4\Entity\Comment $comment)
    {
        $this->checkComment($comment->getComment());
    }

    public function hasErrors()
    {
        (!empty($this->error)) ? true : false;
    }

    private function checkComment($comment)
    {
        if ($this->notEmpty($comment)) {
            return true;
        }
        return $this->error['comment'] = "Comment should not be empty";
    }

    private function checkLogin($login)
    {
        if ($this->notEmpty($login)) {
            return true;
        }
        return $this->error['login'] = "Логин должен быть заполнен ";
    }

    private function checkEmail($email)
    {
        if ($this->notEmpty($email)) {
            if ($this->isEmail($email)) {
                return true;
            } else {
                return $this->error['email'] = 'Email введен некоректно';
            }
        }
        return $this->error['email'] = "Email должен быть заполнен.";
    }

    private function checkPassword($password, $confirmation)
    {
        if ($this->notEmpty($password) && $this->notEmpty($confirmation)) {
            if ($password !== $confirmation) {
                return $this->error['password'] = 'Пароль и подтверждение не совпадают';
            }
            return true;
        }
        return $this->error['password'] = 'Пароль должен быть введен';
    }

    private function notEmpty($field)
    {
        if (empty($field)) {
            return false;
        }
        return true;
    }

    private function isEmail($field)
    {
        $regExp = "/.+@.+\..+/i";
        if (!preg_match($regExp, $field)) {
            return false;
        }
        return true;
    }
}
