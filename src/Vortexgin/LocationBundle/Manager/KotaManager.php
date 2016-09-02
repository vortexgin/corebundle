<?php

namespace Vortexgin\LocationBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\LocationBundle\Entity\Kota;

final class KotaManager extends Manager {
    private $provinsiManager;

    /**
     *
     * @param ContainerInterface $container
     * @param string $class
     *
     */
    public function __construct(ContainerInterface $container, $class, ProvinsiManager $provinsiManager) {
        $container->enterScope('request');
        $container->set('request', new Request(), 'request');
        $this->container = $container;
        $this->provinsiManager = $provinsiManager;

        $this->listSearchFields = ['id', 'name'];
        $this->listOrderBy = array_merge($this->listSearchFields, ['prov.id', 'prov.name']);

        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Kota $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof Kota) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\LocationBundle\Entity\Kota $object
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
            'provinsi'    => $object->getProvinsi()?$this->provinsiManager->serialize($object->getProvinsi()):null,
            'name'        => $object->getName(),
            'created_at'  => $object->getCreatedAt()?$object->getCreatedAt()->format('Y-m-d H:i:s'):null,
            'updated_at'  => $object->getUpdatedAt()?$object->getUpdatedAt()->format('Y-m-d H:i:s'):null,
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = parent::generateQuery($filter);
        $queryBuilder->join('er.provinsi', 'prov');

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
            $obj->setProvinsi($param['provinsi'])
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

    public function update(Kota $obj, $param){
        try {
            if(array_key_exists('provinsi', $param) && !empty($param['provinsi']))
                $obj->setProvinsi($param['provinsi']);
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
