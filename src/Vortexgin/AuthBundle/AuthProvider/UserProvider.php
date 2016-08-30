<?php

namespace Vortexgin\AuthBundle\AuthProvider;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Vortexgin\UserBundle\Entity\User as VortexginUser;
use Vortexgin\UserBundle\Manager\UserManager;
use Vortexgin\AuthBundle\Manager\OAuthTokenManager;

class UserProvider implements UserProviderInterface {

  private $userManager;
  private $tokenManager;

  public function __construct(UserManager $userManager, OAuthTokenManager $tokenManager) {
    $this->userManager = $userManager;
    $this->tokenManager = $tokenManager;
  }

  public function getUsernameByApiKey($apiKey) {
    if (empty($apiKey))
      throw new BadCredentialsException(' Access Token Invalid.');

    $listUser = $this->userManager->get(array(
        array('token', $apiKey)
    ));
    if (count($listUser) > 0) {
      return $listUser[0]->getUsername();
    }

    $listToken = $this->tokenManager->get(array(
        array('token', $apiKey),
    ));

    $current = new \DateTime();
    if (count($listToken) <= 0) {
      throw new BadCredentialsException(' Access Token Invalid.');
    }
    if ($current > $listToken[0]->getExpires()) {
      throw new BadCredentialsException(' Access Token Expired.');
    }
    if (!$listToken[0]->getUser()) {
      throw new BadCredentialsException(' You\'re not authorized for this module.');
    }

    return $listToken[0]->getUser()->getUsername();
  }

  public function loadUserByUsername($username) {
    if (empty($username))
      throw new BadCredentialsException('Username Invalid.');

    $listUser = $this->userManager->get(array(
        array('username', $username)
    ));
    if (count($listUser) <= 0)
      throw new BadCredentialsException('Username Invalid.');

    return $listUser[0];
  }

  public function refreshUser(UserInterface $user) {
    return $user;
  }

  public function supportsClass($class) {
    return $this->userManager->classObject === $class;
  }

}
