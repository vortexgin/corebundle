<?php

namespace Vortexgin\AuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vortexgin\CoreBundle\Controller\BaseController;
use Vortexgin\CoreBundle\Util\HttpStatusHelper;
use Vortexgin\CoreBundle\Util\Validator;

class DefaultController extends BaseController {

  /**
   * @ApiDoc(
   *      section="Vortexgin",
   *      resource="Authorization",
   *      description="Login into system",
   *      parameters={
   *          {"name"="app_id", "dataType"="string", "required"=true, "description"="app_id"},
   *          {"name"="secret_key", "dataType"="string", "required"=true, "description"="secret key application"},
   *          {"name"="email",    "dataType"="string", "required"=false, "description"="email"},
   *          {"name"="username", "dataType"="string", "required"=false, "description"="username"},
   *          {"name"="password", "dataType"="string", "required"=true, "description"="password"},
   *      },
   *      statusCodes={
   *          200="Returned when successful",
   *          400="Bad request",
   *          500="System error",
   *      }
   * )
   */
  public function loginAction(Request $request) {
    try {
      $this->init();
      $post = $request->request->all();

      /** @var $fosUserManager \FOS\UserBundle\Model\UserManagerInterface */
      $fosUserManager = $this->container->get('fos_user.user_manager');
      /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
      $userManager = $this->container->get('vortexgin.user.manager.user');
      /** @var $clientManager \Vortexgin\UserBundle\Manager\OAuthTokenManager */
      $tokenManager = $this->container->get('vortexgin.oauth.manager.token');
      $securityFactory = $this->container->get('security.encoder_factory');


      // request validation
      if (!Validator::validate($post, 'app_id', null, 'empty'))
        return $this->errorResponse('Please insert app id', HttpStatusHelper::HTTP_BAD_REQUEST);
      if (!Validator::validate($post, 'secret_key', null, 'empty'))
        return $this->errorResponse('Please insert secret key', HttpStatusHelper::HTTP_BAD_REQUEST);
      if (!Validator::validate($post, 'username', null, 'empty') && !Validator::validate($post, 'email', null, 'empty'))
        return $this->errorResponse('Please insert username or email', HttpStatusHelper::HTTP_BAD_REQUEST);
      if (!Validator::validate($post, 'password', null, 'empty'))
        return $this->errorResponse('Please insert password', HttpStatusHelper::HTTP_BAD_REQUEST);
      $clientApp = $this->validateClient($post['app_id'], $post['secret_key']);
      if (!$clientApp)
        return $this->errorResponse('Client Apps not found', HttpStatusHelper::HTTP_NOT_FOUND);

      $filter = array(
          array('username', Validator::validate($post, 'username', null, 'empty') ? $post['username'] : null),
          array('email', Validator::validate($post, 'email', null, 'empty') ? $post['email'] : null),
      );
      $listUser = $userManager->get($filter);
      if (count($listUser) <= 0)
        return $this->errorResponse('User not found', HttpStatusHelper::HTTP_NOT_FOUND);
      $user = $listUser[0];

      $encoder = $securityFactory->getEncoder($user);
      if (!$encoder->isPasswordValid($user->getPassword(), $post['password'], $user->getSalt()))
        return $this->errorResponse('Password doesn\'t match', HttpStatusHelper::HTTP_FORBIDDEN);

      $param = array(
          'token' => SHA1($user->getId() . $this->timeInit->format('YmdHis')),
          'oauth_client' => $clientApp,
          'user' => $listUser[0],
          'expires' => $this->timeInit->add(new \DateInterval('PT' . $clientApp->getTokenExpires() . 'S')),
          'user_log' => 'SYSTEM',
      );
      $token = $tokenManager->insert($param);
      if (!$token)
        return $this->errorResponse('Generate token failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

      return $this->successResponse(array(
                  'token' => $token->getToken(),
                      ), HttpStatusHelper::HTTP_CREATED);
    } catch (\Exception $e) {
      var_dump($e->getMessage());
      $this->container->get('logger')->error(sprintf($e->getMessage()));
      return $this->errorResponse('Login failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
    }
  }

  /**
   * @ApiDoc(
   *      section="Vortexgin",
   *      resource="Authorization",
   *      description="Logout from system",
   *      parameters={
   *          {"name"="access_token", "dataType"="string", "required"=true, "description"="access token"},
   *      },
   *      statusCodes={
   *          204="Returned when successful",
   *          400="Bad request",
   *          500="System error",
   *      }
   * )
   */
  public function logoutAction(Request $request) {
    try {
      $this->init();
      $post = $request->request->all();

      /** @var $clientManager \Vortexgin\UserBundle\Manager\OAuthTokenManager */
      $tokenManager = $this->container->get('vortexgin.oauth.manager.token');

      // request validation
      if (!Validator::validate($post, 'access_token', null, 'empty'))
        return $this->errorResponse('Please insert access token', HttpStatusHelper::HTTP_BAD_REQUEST);
      $token = $this->validateToken($post['access_token']);
      if (!$token)
        return $this->errorResponse('Invalid token', HttpStatusHelper::HTTP_FORBIDDEN);

      $tokenManager->setUser($this->user->getUsername()? : 'ANONYMOUS');
      $tokenManager->delete($token);

      return $this->successResponse(array(), HttpStatusHelper::HTTP_NO_CONTENT);
    } catch (\Exception $e) {
      $this->container->get('logger')->error(sprintf($e->getMessage()));
      return $this->errorResponse('Logout failed, Please try again later. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
    }
  }
  
  /**
   * @ApiDoc(
   *      section="Vortexgin",
   *      resource="Authorization",
   *      description="Validate token",
   *      parameters={
   *          {"name"="access_token", "dataType"="string", "required"=true, "description"="access token"},
   *      },
   *      statusCodes={
   *          204="Returned when successful",
   *          400="Bad request",
   *          500="System error",
   *      }
   * )
   */
  public function validateAction(Request $request) {
    try {
      $this->init();
      $post = $request->request->all();

      /** @var $clientManager \Vortexgin\UserBundle\Manager\OAuthTokenManager */
      $tokenManager = $this->container->get('vortexgin.oauth.manager.token');

      // request validation
      if (!Validator::validate($post, 'access_token', null, 'empty'))
        return $this->errorResponse('Please insert access token', HttpStatusHelper::HTTP_BAD_REQUEST);
      $token = $this->validateToken($post['access_token']);
      if (!$token)
        return $this->errorResponse('Invalid token', HttpStatusHelper::HTTP_FORBIDDEN);

      return $this->successResponse(array());
    } catch (\Exception $e) {
      $this->container->get('logger')->error(sprintf($e->getMessage()));
      return $this->errorResponse('Validate failed, Please try again later. '. $e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
    }
  }

}
