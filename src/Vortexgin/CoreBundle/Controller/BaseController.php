<?php

namespace Vortexgin\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\CoreBundle\Util\Validator;
use Vortexgin\UserBundle\Entity\User;
use Vortexgin\MemberBundle\Entity\Member;
use Vortexgin\MemberBundle\Entity\Company;
use Vortexgin\MemberBundle\Entity\Developer;

class BaseController extends Controller {

    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    protected $em;

    protected $router;

    /**
     * @var \DateTime $timeInit
     */
    protected $timeInit;

    /**
    * @var array $listImageExt
    */
    protected $listImageExt = array(
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    );

    /**
    * @var array $listUrlMethod
    */
    protected $listUrlMethod = array('GET', 'POST', 'PATCH', 'DELETE');

    /**
    * @var \Vortexgin\UserBundle\Entity\User $user
    */
    protected $user;

    /**
    * @var array $dataTemplate
    */
    protected $dataTemplate = array();

    /* @var $redisManager \Vortexgin\CoreBundle\Manager\RedisManager */
    protected $redisManager;

    protected function init() {
        date_default_timezone_set('Asia/Jakarta');
        $this->em       = $this->container->get('doctrine')->getManager();
        $this->router   = $this->container->get("router");
        $this->timeInit = new \DateTime;

        /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
        $userManager = $this->container->get('vortexgin.user.manager.user');
        $this->user = new User();
        if($userManager->getCurrentUser() instanceof User){
            $this->user = $userManager->getCurrentUser();
        }

        $this->redisManager = $this->container->get('vortexgin.core.manager.redis');

        $this->dataTemplate = array(
            //'endpoint' => $this->container->getParameter('vortexgin.core.api_endpoint'),
            'uploads' => $this->container->getParameter('vortexgin.core.host').$this->container->getParameter('vortexgin.core.uploads_dir'),
            'user' => array(
                'id' => $this->user->getId(),
                'email' => $this->user->getEmail(),
            ),
        );
    }

    protected function extractDefaultParameter(Manager $manager, array $get){
        if(Validator::validate($get, 'sort', null, 'empty')){
            $sort = json_decode($get['sort'], true);
            $get['order_by'] = $sort[0]['property'];
            $get['order_type'] = $sort[0]['direction'];
        }

        $orderBy    = array_key_exists('order_by', $get) && !empty($get['order_by']) && in_array($get['order_by'], $manager->getOrderBy())?$get['order_by']:'id';
        $orderSort  = array_key_exists('order_type', $get) && !empty($get['order_type']) && in_array($get['order_type'], array('ASC', 'DESC'))?$get['order_type']:'DESC';
        $limit      = array_key_exists('limit', $get) && !empty($get['limit'])?$get['limit']:20;
        $page       = array_key_exists('page', $get) && !empty($get['page'])?$get['page']:1;

        return array($orderBy, $orderSort, $limit, $page);
    }

    protected function errorResponse($userMessage, $httpStatusCode = 400, array $customHeader = array(), $format = 'json') {
        switch ($format) {
            case 'json' :
                $return = new JsonResponse(array(
                        'message' => $userMessage,
                        'success' => false,
                        'timestamp' => new \DateTime()
                    ), $httpStatusCode, $customHeader);
                break;
        }

        return $return;
    }

    protected function successResponse(array $param, $httpStatusCode = 200, array $customHeader = array(), $format = 'json') {
        $param['timestamp'] = new \DateTime;
        $param['success'] = true;
        switch ($format) {
            case 'json' :
                $return = new JsonResponse($param, $httpStatusCode, $customHeader);
                break;
        }

        return $return;
    }


    protected function validateClient($app_id, $secret_key){
      /** @var $clientManager \Vortexgin\AuthBundle\Manager\OAuthClientManager */
      $clientManager = $this->container->get('vortexgin.oauth.manager.client');
      $listClient = $clientManager->get(array(
        array('name', $app_id),
        array('secretKey', $secret_key),
      ));

      if(count($listClient) <= 0)
        return false;

      return $listClient[0];
    }

    protected function validateToken($token){
      /** @var $clientManager \Vortexgin\AuthBundle\Manager\OAuthTokenManager */
      $tokenManager = $this->container->get('vortexgin.oauth.manager.token');
      $listToken = $tokenManager->get(array(
        array('token', $token),
      ));

      if(count($listToken) <= 0)
        return false;
      if($this->timeInit > $listToken[0]->getExpires())
        return false;

      return $listToken[0];
    }

    protected function validateUser($token){
      /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
      $userManager = $this->container->get('vortexgin.user.manager.user');

      if(!$userManager->isAuthenticated()){
          $auth = $this->validateToken($token);
          if($auth && $auth->getUser()){
              $this->user = $auth->getUser();
              return true;
          }
      }else{
          return true;
      }

      return false;
    }
}
