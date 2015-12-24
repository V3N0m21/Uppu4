<?php
namespace Uppu4\Helper;

use Uppu4\Entity\User;

class UserHelper
{
    private $em;
    private $responseCookies;
    private $requestCookies;

    public function __construct($em, $requestCookies, $responseCookies)
    {
        $this->em = $em;
        $this->responseCookies = $responseCookies;
        $this->requestCookies = $requestCookies;
    }

    public function saveAnonymousUser()
    {
        $userModel = new User;
        $salt = HashGenerator::generateSequence();
        $token = HashGenerator::generateSequence();
        $userModel->setSalt($salt);
        $userModel->setToken($token);
        $userModel->setLogin('Anonymous');
        $this->em->persist($userModel);
        $this->em->flush();
        $this->responseCookies->set('token', $token, '1 month');
        return $userModel;

    }

    public function saveUser(\Uppu4\Entity\User $userModel, $postParams) {
        $userModel->setLogin($postParams['login']);
        $userModel->setEmail($postParams['email']);
        $hash = HashGenerator::generateHash($postParams['password'], $userModel->getSalt());
        $userModel->setHash($hash);
        $userModel->setCreatedNow();
        $this->em->persist($userModel);
        $this->em->flush();
        $this->authenticateUser($postParams['login'], $postParams['password']);
        return $userModel;
    }

    public function userDelete($id) {
        $user = $this->em->getRepository('Uppu4\Entity\User')->findOneById($id);
        $this->em->remove($user);
        $this->em->flush();
    }

    public function getUser()
    {
        $token = strval($this->requestCookies['token']);
        if ($user = $this->em->getRepository('\Uppu4\Entity\User')->findOneByToken($token)){
        return $user;
        } else {
            $user = $this->saveAnonymousUser();
            return $user;
        }
    }

    public function getCurrentUserId()
    {
        $token = strval($this->requestCookies['token']);
        if ($user = $this->em->getRepository('\Uppu4\Entity\User')->findOneByToken($token)){
            return $user;
        }
        return null;
    }

    public function authenticateUser($login, $password)
    {
        if ($user = $this->em->getRepository('\Uppu4\Entity\User')->findOneByLogin($login)) {
            if($user->getHash() === HashGenerator::generateHash($password, $user->getSalt())) {
                $this->responseCookies->set('token', $user->getToken(), '1 month');
                $this->responseCookies->set('hash', $user->getHash(), '1 month');
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $user;
    }

    public function checkAuthorization()
    {
        if ($this->requestCookies['token'] == '' || $this->requestCookies['hash'] == '') {
            return null;
        } else {
            $token = strval($this->requestCookies['token']);
            $hash = strval($this->requestCookies['hash']);
            $user = $this->em->getRepository('Uppu4\Entity\User')->findOneByToken($token);
            if ($user->getHash() != $hash) return null;
            return true;
        }
    }

    public function logout()
    {
        $this->responseCookies->set('token', '');
        $this->responseCookies->set('hash', '');
    }
}