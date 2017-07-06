<?php

namespace Vortexgin\UserBundle\Manager;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\MongoManager as Manager;
use Vortexgin\UserBundle\Document\User;
use Vortexgin\CoreBundle\Util\String;

final class UserMongoManager extends Manager {
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

        $this->listSearchFields = ['id', 'username', 'email', 'token', 'telegramId'];
        $this->listOrderBy = $this->listSearchFields;

        parent::__construct($container->get('request'), $container->get('doctrine_mongodb'), $class);
    }

    /**
     *
     * @param DS\UserBundle\Document\User $object
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
     * @param DS\UserBundle\Document\User $object
     *
     * @return array
     *          - int id
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        return array(
            'id'          => $object->getId(),
            'username'    => $object->getUsername(),
            'email'       => $object->getEmail(),
            'settings'    => String::isJson($object->getSettings())?json_decode($object->getSettings(), true):$object->getSettings(),
            'roles'       => $object->getRoles(),
            'last_login'  => $object->getLastLogin()?$object->getLastLogin()->format('Y-m-d h:i:s'):null,
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = $this->repository->createQueryBuilder('er');
        $queryBuilder ->field('enabled')->equals(true)
                      ->field('locked')->equals(false);

        return $this->generateFilter($queryBuilder, $filter);
    }

    public function get(array $param = array(), $orderBy = 'id', $orderSort = 'DESC', $page = 1, $count = 20){
        list($orderBy, $orderSort, $offset, $limit) = $this->generateDefaultParam($orderBy, $orderSort, $page, $count);

        $sql = $this->generateQuery($param);
        $sql->sort($orderBy, $orderSort)
            ->skip($offset)
            ->limit($limit);

        return $this->getResult($sql);
    }

    public function count(array $param = array()){
        $sql = $this->generateQuery($param);
        $sql->requireIndexes(false)
            ->count();

        return $this->getResult($sql);
    }

    public function insert(array $param = array()) {
        try {
            $obj = $this->createNew();
            $obj->setUsername($param['username'])
                ->setEmail($param['email'])
                ->setPlainPassword($param['password'])
                ->setRoles(array($param['role']))
                ->setEnabled(true)
            ;

            if(array_key_exists('settings', $param) && is_array($param['settings']))
                $obj->setSettings(json_encode($param['settings']));

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
            if(array_key_exists('settings', $param) && is_array($param['settings']))
                $obj->setSettings(json_encode($param['settings']));
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

    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted($role);
    }
}
