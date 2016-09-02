<?php

namespace Vortexgin\LocationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\LocationBundle\Entity\Provinsi;

final class ProvinsiManager extends Manager {
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

        $this->listSearchFields = ['id', 'code', 'name'];
        $this->listOrderBy = $this->listSearchFields;

        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Provinsi $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof Provinsi) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Provinsi $object
     *
     * @return array
     *          - int id
     *          - string code
     *          - string name
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        return array(
            'id'          => $object->getId(),
            'code'        => $object->getCode(),
            'name'        => $object->getName(),
            'created_at'  => $object->getCreatedAt()?$object->getCreatedAt()->format('Y-m-d H:i:s'):null,
            'updated_at'  => $object->getUpdatedAt()?$object->getUpdatedAt()->format('Y-m-d H:i:s'):null,
        );
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
            $obj->setCode($param['code'])
                ->setName($param['name'])
                ->setCreatedBy($param['user_log'])
            ;

            $this->manager->persist($obj);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return false;
        }
    }

    public function update(Provinsi $obj, $param){
        try {
            if(array_key_exists('code', $param) && !empty($param['code']))
                $obj->setCode($param['code']);
            if(array_key_exists('name', $param) && !empty($param['name']))
                $obj->setName($param['name']);

            $obj->setUpdatedBy($param['user_log']);
            $this->manager->persist($obj);
            $this->logModified($obj, $param['user_log']);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return false;
        }
    }
}
