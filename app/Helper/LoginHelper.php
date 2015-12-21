<?php
namespace Uppu4\Helper;


class LoginHelper
{

    protected $userRepository;
    protected $requestCookies;
    protected $responseCookies;


    public function __construct($userRepository, $requestCookies, $responseCookies)
    {
        $this->userRepository = $userRepository;
        $this->requestCookies = $requestCookies;
        $this->responseCookies = $responseCookies;
    }

    public function getUser()
    {
        $token = $this->requestCookies['token'];
        $user = $this->userRepository->findOneByToken($token);
        return $user;
//        if ($token == '') {
//            $token = HashGenerator::generateSequence();
//            $this->responseCookies->set('token', $token, '1 month');
//        }

    }

    public function checkAuthorization()
    {
        if ($this->requestCookies['token'] == '' || $this->requestCookies['hash'] == '') {
            return false;
        } else {
            $token = strval($this->requestCookies['token']);
            $hash = strval($this->requestCookies['hash']);
            $user = $this->userRepository->findOneByToken($token);
            if ($user->getHash() != $hash) return false;

        }
        return true;
    }

    public function authenticateUser(\Uppu4\Entity\User $user) {
        $this->responseCookies->set('token', $user->getToken(), time() + 3600 * 24 * 7);
        $this->responseCookies->set('hash', $user->getHash(), time() + 3600 * 24 * 7);
    }
}