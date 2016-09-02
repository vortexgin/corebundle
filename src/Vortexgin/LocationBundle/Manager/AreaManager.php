<?php

namespace Vortexgin\LocationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\LocationBundle\Entity\Area;

final class AreaManager extends Manager {
    private $kotaManager;

    /**
     *
     * @param ContainerInterface $container
     * @param string $class
     *
     */
    public function __construct(ContainerInterface $container, $class, KotaManager $kotaManager) {
        $container->enterScope('request');
        $container->set('request', new Request(), 'request');
        $this->container = $container;
        $this->kotaManager = $kotaManager;

        $this->listSearchFields = ['id', 'name', 'zipcode', 'code'];
        $this->listOrderBy = array_merge($this->listSearchFields, ['prov.id', 'prov.name', 'kota.id', 'kota.name']);

        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Area $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof Area) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Area $object
     *
     * @return array
     *          - int id
     *          - DS\LocationBundle\Entity\Provinsi provinsi
     *          - string name
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        return array(
            'id'          => $object->getId(),
            'kota'        => $object->getKota()?$this->kotaManager->serialize($object->getKota()):null,
            'name'        => $object->getName(),
            'zipcode'     => $object->getZipcode(),
            'created_at'  => $object->getCreatedAt()?$object->getCreatedAt()->format('Y-m-d H:i:s'):null,
            'updated_at'  => $object->getUpdatedAt()?$object->getUpdatedAt()->format('Y-m-d H:i:s'):null,
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = parent::generateQuery($filter);
        $queryBuilder->join('er.kota', 'kota');
        $queryBuilder->join('kota.provinsi', 'prov');

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

    public function insert(array $param = array()) {
        try {
            $obj = $this->createNew();
            $obj->setKota($param['kota'])
                ->setName($param['name'])
                ->setCreatedBy($param['user_log'])
            ;

            if(array_key_exists('zipcode', $param) && !is_null($param['zipcode']))
                $obj->setZipcode($param['zipcode']);

            $this->manager->persist($obj);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return false;
        }
    }

    public function update(Area $obj, $param){
        try {
            if(array_key_exists('kota', $param) && !empty($param['kota']))
                $obj->setKota($param['kota']);
            if(array_key_exists('name', $param) && !empty($param['name']))
                $obj->setName($param['name']);
            if(array_key_exists('zipcode', $param) && !empty($param['zipcode']))
                $obj->setZipcode($param['zipcode']);

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
