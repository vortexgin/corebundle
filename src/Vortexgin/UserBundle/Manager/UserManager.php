<?php

namespace Vortexgin\UserBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\UserBundle\Entity\User;

final class UserManager extends Manager {
    /**
     *
     * @param ContainerInterface $container
     * @param string $class
     *
     */
    public function __construct(ContainerInterface $container, $class) {
        $container->enterScope('request');
        $container->set('request', new Request(), 'request');
        $this->container = $container;

        $this->listSearchFields = ['id', 'username', 'email'];
        $this->listOrderBy = $this->listSearchFields;

        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\UserBundle\Entity\User $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof User) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\UserBundle\Entity\User $object
     *
     * @return array
     *          - string id
     *          - string username
     *          - string email
     *          - string roles
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        return array(
            'id'          => $object->getId(),
            'username'    => $object->getUsername(),
            'email'       => $object->getEmail(),
            'roles'       => $object->getRoles(),
            'token'       => $object->getToken(),
            'last_login'  => $object->getLastLogin()?$object->getLastLogin()->format('Y-m-d h:i:s'):null,
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = $this->repository->createQueryBuilder('er');
        $queryBuilder->where('er.enabled = 1');
        $queryBuilder->andWhere('er.locked != 1');

        return $this->generateFilter($queryBuilder, $filter);
    }

    public function get(array $param = array(), $orderBy = 'id', $orderSort = 'DESC', $page = 1, $count = 20){
        list($orderBy, $orderSort, $offset, $limit) = $this->generateDefaultParam($orderBy, $orderSort, $page, $count);

        $sql = $this->generateQuery($param);
        $sql->select('er')
            ->orderBy($orderBy, $orderSort)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->getResult($sql);
    }

    public function count(array $param = array()){
        $sql = $this->generateQuery($param);
        $sql->select('count(er.id)');

        return $this->getOneOrNullResult($sql);
    }

    public function insert(array $param = array()) {
        try {
            $obj = $this->createNew();
            $obj->setUsername($param['username'])
                ->setEmail($param['email'])
                ->setPlainPassword($param['password'])
                ->setRoles(array($param['role']))
                ->setToken(sha1($param['username'].date('YMDGis')))
                ->setEnabled(true)
            ;

            $this->manager->persist($obj);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return false;
        }
    }

    public function update(User $obj, $param){
        try {
            if(array_key_exists('email', $param) && !empty($param['email']))
                $obj->setEmail($param['email']);
            if(array_key_exists('username', $param) && !empty($param['username']))
                $obj->setUsername($param['username']);
            if(array_key_exists('password', $param) && !empty($param['password']))
                $obj->setPlainPassword($param['password']);
            if(array_key_exists('role', $param) && !empty($param['role']))
                $obj->setRoles(array($param['role']));
            if(array_key_exists('enabled', $param) && !is_null($param['enabled']))
                $obj->setEnabled($param['enabled']);

            $this->manager->persist($obj);
            $this->logModified($obj, $param['user_log']);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return false;
        }
    }

    public function  getCurrentUser()
    {
        if(!empty($this->container->get('security.context')->getToken()))
            return $this->container->get('security.context')->getToken()->getUser();

        return false;
    }

    public function isAuthenticated()
    {
        return $this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN');
    }

    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted($role);
    }
  }
