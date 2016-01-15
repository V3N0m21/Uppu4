<?php
namespace Uppu4\Helper;

class DataValidator
{
    private $error;

    public function validateUserData($data)
    {
        $this->checkLogin($data['login']);
        $this->checkPassword($data['password'], $data['confirmation']);
        $this->checkEmail($data['email']);
        return $this->error;
    }

    public function validateComment(\Uppu4\Entity\Comment $comment)
    {
        $this->checkComment($comment->getComment());
        return $this->error;
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
        $this->error['comment'] = "Комментарий не должен быть пустым";
    }

    private function checkLogin($login)
    {
        if ($this->notEmpty($login)) {
            return true;
        }
        $this->error['login'] = "Логин должен быть заполнен ";
    }

    private function checkEmail($email)
    {
        if ($this->notEmpty($email)) {
            if ($this->isEmail($email)) {
                return true;
            } else {
                $this->error['email'] = 'Email введен некоректно';
                return;
            }
        }
        $this->error['email'] = "Email должен быть заполнен.";
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
