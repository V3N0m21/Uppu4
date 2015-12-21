<?php
namespace Uppu4\Helper;

use Uppu4\Entity\User;

class UserHelper
{
    private $em;
    private $responseCookies;

    public function __construct($em, $responseCookies) {
        $this->em = $em;
        $this->responseCookies = $responseCookies;
    }

    public function saveAnonymousUser() {
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

}