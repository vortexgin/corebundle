<?php

namespace Vortexgin\AuthBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\AuthBundle\Entity\OAuthClient;

final class OAuthClientManager extends Manager {
    /**
     *
     * @param ContainerInterface $container
     * @param string $class
     *
     */
    public function __construct(ContainerInterface $container, $class) {
        $container->enterScope('request');
        $container->set('request', new Request(), 'request');
        $this->container   = $container;

        $this->listSearchFields = [
                                    'id', 'name', 'description',
                                    'secretKey', 'tokenExpires',
                                  ];
        $this->listOrderBy = array_merge($this->listSearchFields, []);

        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\CoreBundle\Entity\OAuthClient $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof OAuthClient) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\CoreBundle\Entity\OAuthClient $object
     *
     * @return array
     *          - string id
     *          - string name
     *          - string description
     *          - string secret_key
     *          - int token_expires
     *          - DateTime created_at
     *          - string created_by
     *          - DateTime updated_at
     *          - string updated_by
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        return array(
            'id'            => $object->getId(),
            'name'          => $object->getName(),
            'description'   => $object->getDescription(),
            'secret_key'    => $object->getSecretKey(),
            'token_expires' => $object->getTokenExpires(),
            'created_at'    => $object->getCreatedAt(),
            'created_by'    => $object->getCreatedBy(),
            'updated_at'    => $object->getUpdatedAt(),
            'updated_by'    => $object->getUpdatedBy(),
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = parent::generateQuery($filter);
        $queryBuilder->andWhere('er.isActive = 1');

        return $queryBuilder;
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

    /**
     *
     * @param array $param
     *          - string name
     *          - string description
     *          - string secret_key
     *          - int token_expires
     *
     * @return Vortexgin\CoreBundle\Entity\OAuthClient
     *
     */
    public function insert(array $param = array()) {
        try {
            $obj = new OAuthClient();
            $obj->setName($param['name'])
                ->setDescription($param['description'])
                ->setSecretKey($param['secret_key'])
                ->setTokenExpires($param['token_expires'])
                ->setCreatedBy($param['user_log'])
            ;

            $this->manager->persist($obj);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param int $id
     * @param array $param
     *          - string name
     *          - string description
     *          - string secret_key
     *          - int token_expires
     *
     * @return Vortexgin\CoreBundle\Entity\OAuthClient
     *
     */
    public function update($id, $param){
        try {
            $result = $this->get(array('id' => $id), 'id', 'DESC', 1, 1);

            if(!$this->isSupportedObject($result[0]))
                return false;

            $obj = $result[0];
            if(array_key_exists('name', $param) && !empty($param['name'])){
                $obj->setName($param['name']);
            }
            if(array_key_exists('description', $param) && !empty($param['description'])){
                $obj->setDescription($param['description']);
            }
            if(array_key_exists('secret_key', $param) && !empty($param['secret_key'])){
                $obj->setSecretKey($param['secret_key']);
            }
            if(array_key_exists('token_expires', $param) && !empty($param['token_expires'])){
                $obj->setTokenExpires($param['token_expires']);
            }

            $obj->setUpdatedBy($param['user_log']);
            $this->manager->persist($obj);
            $this->logModified($obj, $param['user_log']);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            return false;
        }
    }
}
