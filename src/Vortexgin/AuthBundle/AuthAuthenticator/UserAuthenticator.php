<?php

namespace Vortexgin\AuthBundle\AuthAuthenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Vortexgin\AuthBundle\AuthProvider\UserProvider;

class UserAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface {

  protected $httpUtils;

  public function __construct(HttpUtils $httpUtils) {
    $this->httpUtils = $httpUtils;
  }

  public function createToken(Request $request, $providerKey) {
    $apiKey = $request->get('access_token');
    if (!$apiKey){
      $apiKey = $request->cookies->get('access_token');      
    }
    if (!$apiKey) {
      throw new BadCredentialsException(' No API key found.');
    }

    return new PreAuthenticatedToken(
            'anon.', $apiKey, $providerKey
    );
  }

  public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
    if (!$userProvider instanceof UserProvider) {
      throw new \InvalidArgumentException(
        sprintf(' The user provider must be an instance of UserProvider (%s was given).', get_class($userProvider))
      );
    }

    $apiKey = $token->getCredentials();
    $username = $userProvider->getUsernameByApiKey($apiKey);

    if (!$username) {
      throw new AuthenticationException(
        sprintf(' API Key "%s" does not exist.', $apiKey)
      );
    }

    $user = $userProvider->loadUserByUsername($username);

    return new PreAuthenticatedToken(
            $user, $apiKey, $providerKey, $user->getRoles()
    );
  }

  public function supportsToken(TokenInterface $token, $providerKey) {
    return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
  }

  public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
    return new Response($exception->getMessage(), 403);
  }

}
