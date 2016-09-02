<?php

namespace Vortexgin\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Vortexgin\CoreBundle\Controller\BaseController;
use Vortexgin\CoreBundle\Util\HttpStatusHelper;
use Vortexgin\CoreBundle\Util\Validator;
use Vortexgin\UserBundle\Entity\User;
use Vortexgin\UserBundle\Util\UserFilterGenerator;

class UserController extends BaseController{
    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="User",
     *      description="Create user",
     *      parameters={
     *          {"name"="email",            "dataType"="string", "required"=true, "description"="email of user"},
     *          {"name"="username",         "dataType"="string", "required"=true, "description"="username of user"},
     *          {"name"="password",         "dataType"="string", "required"=true, "description"="password of user"},
     *          {"name"="password_confirm", "dataType"="string", "required"=true, "description"="password confirmation"},
     *          {"name"="role",             "dataType"="string", "required"=true, "description"="role user"},
     *      },
     *      statusCodes={
     *          201="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function createAction(Request $request){
        try{
            $this->init();
            $post = $request->request->all();

            /** @var $fosUserManager \FOS\UserBundle\Model\UserManagerInterface */
            $fosUserManager = $this->container->get('fos_user.user_manager');
            /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
            $userManager = $this->container->get('vortexgin.user.manager.user');

            // request validation
            if(!Validator::validate($post, 'email', null, 'empty', 'FILTER_EMAIL'))
                return $this->errorResponse('Email invalid', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'username', null, 'empty', 'FILTER_USERNAME'))
                return $this->errorResponse('Username invalid', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'password', null, 'empty'))
                return $this->errorResponse('Please insert password', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'password_confirm', null, 'empty'))
                return $this->errorResponse('Please confirm password', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'role', null, 'empty'))
                return $this->errorResponse('Please insert role', HttpStatusHelper::HTTP_BAD_REQUEST);
            $duplicateEmail = $fosUserManager->findUserBy(array('email' => $post['email']));
            if($duplicateEmail)
                return $this->errorResponse('Email has been used', HttpStatusHelper::HTTP_CONFLICT);
            $duplicateUsername = $fosUserManager->findUserBy(array('username' => $post['username']));
            if($duplicateUsername)
                return $this->errorResponse('Username has been used', HttpStatusHelper::HTTP_CONFLICT);
            if($post['password'] != $post['password_confirm'])
                return $this->errorResponse('Password doesn\'t match', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!in_array($post['role'], User::$listRole))
                return $this->errorResponse('Invalid role', HttpStatusHelper::HTTP_BAD_REQUEST);

            $param = array(
                'username'  => $post['username'],
                'email'     => $post['email'],
                'password'  => $post['password'],
                'role'      => $post['role'],
            );
            $user = $userManager->insert($param);
            if(!$user)
                return $this->errorResponse('Create user failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);
            $fosUserManager->updateUser($user);

            return $this->successResponse($userManager->serialize($user), HttpStatusHelper::HTTP_CREATED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Create user failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="User",
     *      description="Read user",
     *      parameters={
     *          {"name"="limit",      "dataType"="integer", "required"=false, "description"="data limit, default 20"},
     *          {"name"="page",       "dataType"="integer", "required"=false, "description"="data offset, default 0"},
     *          {"name"="order_by",   "dataType"="string", "required"=false, "format"="id|expired_date", "description"="data order by, default id"},
     *          {"name"="order_type", "dataType"="string", "required"=false, "format"="ASC|DESC", "description"="data order type, default DESC"},
     *          {"name"="id",         "dataType"="string", "required"=false, "description"="id of user"},
     *          {"name"="email",      "dataType"="string", "required"=false, "description"="email of user"},
     *          {"name"="username",   "dataType"="string", "required"=false, "description"="username of user"},
     *      },
     *      statusCodes={
     *          200="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function readAction(Request $request){
        try{
            $this->init();
            $get = $request->query->all();

            /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
            $userManager = $this->container->get('vortexgin.user.manager.user');

            $filter = UserFilterGenerator::generateFilter($get);

            list($orderBy, $orderSort, $limit, $page) = $this->extractDefaultParameter($userManager, $get);

            $listUser   = $userManager->get($filter, $orderBy, $orderSort, $page, $limit);
            $totalUser  = $userManager->count($filter);

            if(count($listUser) <= 0)
                return $this->errorResponse('User not found', HttpStatusHelper::HTTP_NOT_FOUND);

            foreach($listUser as $key=>$value){
                $data[] = $userManager->serialize($value);
            }

            return $this->successResponse(array(
                'user'      => $data,
                'count'     => array(
                    'total' => count($listUser),
                    'all'   => (int) $totalUser[1],
                )
            ));
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Read user failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="User",
     *      description="Update user",
     *      parameters={
     *          {"name"="email",            "dataType"="string", "required"=false, "description"="email of user"},
     *          {"name"="username",         "dataType"="string", "required"=false, "description"="username of user"},
     *          {"name"="password",         "dataType"="string", "required"=false, "description"="password of user"},
     *          {"name"="password_confirm", "dataType"="string", "required"=false, "description"="password confirmation"},
     *          {"name"="role",             "dataType"="string", "required"=false, "description"="role user"},
     *      },
     *      statusCodes={
     *          202="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function updateAction(Request $request, $id){
        try{
            $this->init();
            $post = $request->request->all();

            /** @var $fosUserManager \FOS\UserBundle\Model\UserManagerInterface */
            $fosUserManager = $this->container->get('fos_user.user_manager');
            /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
            $userManager = $this->container->get('vortexgin.user.manager.user');

            $detail = $userManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('User not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $user = $detail[0];
            // request validation
            if(Validator::validate($post, 'email', null, 'empty', 'FILTER_EMAIL')){
                $duplicateEmail = $fosUserManager->findUserBy(array('email' => $post['email']));
                if($duplicateEmail->getId() != $id){
                    if($duplicateEmail)
                        return $this->errorResponse('Email has been used', HttpStatusHelper::HTTP_CONFLICT);
                }
            }
            if(Validator::validate($post, 'username', null, 'empty', 'FILTER_USERNAME')){
                $duplicateUsername = $fosUserManager->findUserBy(array('username' => $post['username']));
                if($duplicateUsername->getId() != $id){
                    if($duplicateUsername)
                        return $this->errorResponse('Username has been used', HttpStatusHelper::HTTP_CONFLICT);
                }
            }
            if(Validator::validate($post, 'password', null, 'empty')){
                if($post['password'] != $post['password_confirm'])
                    return $this->errorResponse('Password doesn\'t match', HttpStatusHelper::HTTP_BAD_REQUEST);
            }
            if(Validator::validate($post, 'role', null, 'empty')){
                if(!in_array($post['role'], User::$listRole))
                    return $this->errorResponse('Invalid role', HttpStatusHelper::HTTP_BAD_REQUEST);
            }

            $param = array(
                'username'  => Validator::validate($post, 'username', null, 'empty', 'FILTER_USERNAME')?$post['username']:null,
                'email'     => Validator::validate($post, 'email', null, 'empty', 'FILTER_EMAIL')?$post['email']:null,
                'password'  => Validator::validate($post, 'password', null, 'empty')?$post['password']:null,
                'role'      => Validator::validate($post, 'role', null, 'empty')?$post['role']:null,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $newUser = $userManager->update($user, $param);
            if(!$newUser)
                return $this->errorResponse('Update user failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);
            $fosUserManager->updateUser($newUser);

            return $this->successResponse($userManager->serialize($newUser), HttpStatusHelper::HTTP_ACCEPTED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Update user failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="User",
     *      description="Delete user",
     *      statusCodes={
     *          204="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function deleteAction(Request $request, $id){
        try{
            $this->init();

            /** @var $fosUserManager \FOS\UserBundle\Model\UserManagerInterface */
            $fosUserManager = $this->container->get('fos_user.user_manager');
            /** @var $userManager \Vortexgin\UserBundle\Manager\UserManager */
            $userManager = $this->container->get('vortexgin.user.manager.user');

            $detail = $userManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('User not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $user = $detail[0];

            $param = array(
                'enabled'  => false,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $newUser = $userManager->update($user, $param);
            $fosUserManager->updateUser($newUser);

            return $this->successResponse(array(), HttpStatusHelper::HTTP_NO_CONTENT);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Delete user failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}
