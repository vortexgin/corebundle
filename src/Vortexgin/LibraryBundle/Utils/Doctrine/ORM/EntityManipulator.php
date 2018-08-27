<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Vortexgin\LibraryBundle\Utils\CamelCasizer;
use Vortexgin\APIBundle\Utils\LogEntityChanges;

/**
 * Entity manipulator functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils\Doctrine\ORM
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class EntityManipulator extends CacheManipulator
{
    /**
     * Object entity
     * 
     * @var object
     */
    private $_entity;

    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Class repository
     * 
     * @var \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    private $_repo;

    /**
     * Construct
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager Entity Manager
     * @param object                      $object        Object entity
     * 
     * @return void
     */
    public function __construct(EntityManager $entityManager, $object)
    {
        $this->_em = $entityManager;
        $reflectionClass = new \ReflectionClass($object);
        $this->_repo = $entityManager->getRepository($reflectionClass->getName());
        $this->_entity = $object;

        parent::__construct($entityManager);
    }

    /**
     * Function to get entity short name
     * 
     * @return string
     */
    public function getEntityShortName()
    {
        $reflectionClass = new \ReflectionClass($this->_entity);

        return $reflectionClass->getShortName();
    }

    /**
     * Function to bind data from array into object entity
     * 
     * @param array $data Array of data

     * @return mixed
     */
    public function bindData(array $data = array())
    {
        if (!is_object($this->_entity)) {
            return;
        }

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value) && array_key_exists('date', $value) && array_key_exists('timezone_type', $value) && array_key_exists('timezone', $value)) {
                $value = \DateTime::createFromFormat('Y-m-d G:i:s.000000', $value['date']);
            }
            $method = CamelCasizer::underScoreToCamelCase(sprintf('set_%s', $key));

            if (method_exists($this->_entity, $method)) {
                call_user_func_array(array($this->_entity, $method), array($value));
            } else {
                $method = CamelCasizer::underScoreToCamelCase($key);

                if (method_exists($this->_entity, $method)) {
                    call_user_func_array(array($this->_entity, $method), array($value));
                } else {
                    $method = CamelCasizer::underScoreToCamelCase(sprintf('is_%s', $key));

                    if (method_exists($this->_entity, $method)) {
                        call_user_func_array(array($this->_entity, $method), array($value));
                    }
                }
            }
        }

        return $this->_entity;
    }

    /**
     * Function to commit into database
     * 
     * @return void
     */
    public function commit()
    {
        $this->_em->persist($this->_entity);

        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), serialize($this->_entity->getId()));
        if ($this->isExistCache($cacheId)) {
            $this->deleteCache($cacheId);
        }

        $this->_em->flush($this->_entity);
    }

    /**
     * Function to save data into object entity
     * 
     * @param array $data Array of data
     * 
     * @return mixed
     */
    public function save(array $data = array())
    {
        $this->bindData($data);

        if (method_exists($this->_entity, 'setUpdatedAt')) {
            $this->_entity->setUpdatedAt(new \DateTime());            
        }
        if (method_exists($this->_entity, 'setUpdatedBy')) {
            $this->_entity->setUpdatedBy('Anon.');            
        }
        if (!$this->_entity->getId()) {
            if (method_exists($this->_entity, 'setCreatedAt')) {
                $this->_entity->setCreatedAt(new \DateTime());            
            }
            if (method_exists($this->_entity, 'setCreatedBy')) {
                $this->_entity->setCreatedBy('Anon.');            
            }
        } else {
            $logManager = new LogEntityChanges($this->_em);
            //$logManager->log($this->_entity, 'Anon.');
        }

        $this->commit();
    }

    /**
     * Function to delete object entity
     * 
     * @return mixed
     */    
    public function delete()
    {
        $this->_entity->setIsActive(false);
        $this->save();
    }

    /**
     * Function to find data by id
     * 
     * @param string $id ID of entity
     * 
     * @return mixed
     */
    public function find($id)
    {
        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $id);
        $object = $this->fetchFromCache($cacheId);

        if (!$object) {
            $object = $this->_repo->find($id);
            $this->saveCache($cacheId, $object);
        }

        return $object;
    }

    /**
     * Function to find data by id with returning format is array
     * 
     * @param string $id ID of entity
     * 
     * @return mixed
     */
    public function findArray($id)
    {
        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $id);
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->_repo->createQueryBuilder('o');
            $queryBuilder->andWhere($queryBuilder->expr()->eq('o.id', $id));
            $result = $this->getOneOrNullResult(Query::HYDRATE_ARRAY);

            $this->saveCache($cacheId, $result);
        }

        return $result;
    }

    /**
     * Function to find data by array criteria
     * 
     * @param array $criteria Array of criteria
     * 
     * @return mixed
     */
    public function findBy(array $criteria)
    {
        $object = $this->_repo->findOneBy($criteria);
        if ($object) {
            $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $object->getId());
            $this->saveCache($cacheId, $object);

            return $object;
        }
    }

    /**
     * Function to find data by array criteria with returning array format
     * 
     * @param array $criteria Array of criteria
     * 
     * @return mixed
     */
    public function findByArray(array $criteria)
    {
        $queryBuilder = $this->_repo->createQueryBuilder('o');
        foreach ($criteria as $key => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('o.%s', $key), sprintf(':%s', $key)));
            $queryBuilder->setParameter($key, $value);
        }

        return $this->getResult($queryBuilder, Query::HYDRATE_ARRAY);
    }
}