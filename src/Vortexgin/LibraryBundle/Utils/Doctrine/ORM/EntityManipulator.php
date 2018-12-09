<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
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
     * Result Manipulator
     * 
     * @var \Vortexgin\LibraryBundle\Utils\Doctrine\ORM\ResultManipulator
     */
    private $_resultManipulator;

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
        $this->_resultManipulator = new ResultManipulator();

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
     * Function to get active repo
     * 
     * @return \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    public function getRepo()
    {
        return $this->_repo;
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
    public function find($id, $hydration = Query::HYDRATE_OBJECT)
    {
        $cacheId = sprintf('find_%s_%s', $this->getEntityShortName(), $id);
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->_repo->createQueryBuilder('o');
            $queryBuilder->andWhere($queryBuilder->expr()->eq('o.id', $id));
            $result = $this->_resultManipulator->getOneOrNullResult($queryBuilder, $hydration);

            $this->saveCache($cacheId, $result);
        }

        return $result;
    }

    /**
     * Function to find data by array criteria
     * 
     * @param array $criteria  Array of criteria
     * @param array $orderBy   Sort order
     * @param array $limit     Data limit
     * @param array $offset    Data offsett
     * @param int   $hydration Hydration type
     * 
     * @return mixed
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null, $hydration = Query::HYDRATE_OBJECT)
    {
        $cacheId = sprintf(
            'find_by_%s_%s', $this->getEntityShortName(), 
            serialize(
                array(
                    'criteria' => $criteria, 
                    'orderBy' => $orderBy, 
                    'limit' => $limit, 
                    'offset' => $offset, 
                )
            )
        );
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->_repo->createQueryBuilder('o');
            foreach ($criteria as $key => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('o.%s', $key), sprintf(':%s', $key)));
                $queryBuilder->setParameter($key, $value);
            }
            if (!empty($orderBy) && is_array($orderBy)) {
                foreach ($orderBy as $key=>$value) {
                    $queryBuilder->orderBy(sprintf('o.%s', $key), $value);
                }
            }
            if (!empty($limit)) {
                $queryBuilder->setMaxResults($limit);
            }
            if (!empty($offset)) {
                $queryBuilder->setFirstResult($offset);
            }
            $result = $this->_resultManipulator->getResult($queryBuilder, $hydration);
    
            $this->saveCache($cacheId, $result);
        }

        return $result;
    }

    /**
     * Function to find one data by array criteria
     * 
     * @param array $criteria  Array of criteria
     * @param array $orderBy   Sort order
     * @param int   $hydration Hydration type
     * 
     * @return mixed
     */
    public function findOneBy(array $criteria, array $orderBy = null, $hydration = Query::HYDRATE_OBJECT)
    {
        $cacheId = sprintf(
            'find_one_by_%s_%s', $this->getEntityShortName(), 
            serialize(
                array(
                    'criteria' => $criteria, 
                    'orderBy' => $orderBy, 
                )
            )
        );
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->_repo->createQueryBuilder('o');
            foreach ($criteria as $key => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('o.%s', $key), sprintf(':%s', $key)));
                $queryBuilder->setParameter($key, $value);
            }
            if (!empty($orderBy) && is_array($orderBy)) {
                foreach ($orderBy as $key=>$value) {
                    $queryBuilder->orderBy(sprintf('o.%s', $key), $value);
                }
            }
            $result = $this->_resultManipulator->getOneOrNullResult($queryBuilder, $hydration);
    
            $this->saveCache($cacheId, $result);
        }

        return $result;
    }

    /**
     * Function to count data by array criteria
     * 
     * @param array $criteria Array of criteria
     * 
     * @return mixed
     */
    public function count(array $criteria)
    {
        $cacheId = sprintf('count_%s_%s', $this->getEntityShortName(), serialize($criteria));
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->_repo->createQueryBuilder('o');
            $queryBuilder->select('count(o.id)');
            foreach ($criteria as $key => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('o.%s', $key), sprintf(':%s', $key)));
                $queryBuilder->setParameter($key, $value);
            }
            $result = $this->_resultManipulator->getOneOrNullResult($queryBuilder, Query::HYDRATE_ARRAY);
    
            $this->saveCache($cacheId, $result);
        }

        return $result[1];
    }
}