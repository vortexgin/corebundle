<?php

namespace Vortexgin\CoreBundle\Manager;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vortexgin\CoreBundle\Util\CamelCasizer;
use Doctrine\ORM\Query;

abstract class Manager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var mixed
     */
    protected $classObject;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var ManagerRegistry
     */
    protected $registerManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @var string
     */
    private $user;

    /*
    * @var \DateTime
    */
    protected $timeInit;

    /**
     * @var array
     */
    protected $listSearchFields = ['id'];

    /**
     * @var array
     */
    protected $listOrderBy = ['id'];

    /**
     * @var array
     */
    protected $return = [];

    abstract protected function isSupportedObject($object);

    abstract public function serialize($object);

    public function __construct(Request $request, ManagerRegistry $managerRegistry, $class)
    {
        $objectManager = $managerRegistry->getManager();
        $this->registerManager = $managerRegistry;
        $this->manager = $objectManager;
        $this->repository = $objectManager->getRepository($class);
        $this->class = $objectManager->getClassMetadata($class)->getName();
        $this->classObject = $class;
        $this->request = $request;

        $this->timeInit = new \DateTime();

        $cache = $objectManager->getConfiguration()->getHydrationCacheImpl();
        $this->cache = $cache ?: new ArrayCache();
    }

    public function resetManager()
    {
        $this->registerManager->resetManager();
    }

    public function createNew()
    {
        return new $this->class();
    }

    public function save($object, array $data = array())
    {
        $this->bindData($object, $data);

        $object->setUpdatedAt(new \DateTime());
        $object->setUpdatedBy($this->getUser());

        if (!$object->getId()) {
            $object->setCreatedAt(new \DateTime());
            $object->setCreatedBy($this->getUser());
        }else{
            $this->logModified($object, $this->getUser());
        }

        $this->commit($object);
    }

    public function delete($object)
    {
        $object->setIsActive(false);
        $this->save($object);
    }

    public function find($id)
    {
        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $id);
        $object = $this->fetchFromCache($cacheId);

        if (!$object) {
            $object = $this->repository->find($id);
            $this->saveCache($cacheId, $object);
        }

        return $object;
    }

    public function findArray($id)
    {
        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $id);
        $result = $this->fetchFromCache($cacheId);

        if (!$result) {
            $queryBuilder = $this->repository->createQueryBuilder('o');
            $queryBuilder->andWhere($queryBuilder->expr()->eq('o.id', $id));
            $result = $this->getOneOrNullResult(Query::HYDRATE_ARRAY);

            $this->saveCache($cacheId, $result);
        }

        return $result;
    }

    public function findByArray(array $criteria, $isActive = true)
    {
        $queryBuilder = $this->repository->createQueryBuilder('o');
        foreach ($criteria as $key => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('o.%s', $key), sprintf(':%s', $key)));
            $queryBuilder->setParameter($key, $value);
        }
        $queryBuilder->andWhere($queryBuilder->expr()->eq('o.isActive', $queryBuilder->expr()->literal($isActive)));

        return $this->getResult($queryBuilder, Query::HYDRATE_ARRAY);
    }

    public function findBy(array $criteria, $isActive = true)
    {
        if ($isActive) {
            $criteria = array_merge($criteria, array('isActive' => true));
        }

        $object = $this->repository->findOneBy($criteria);
        if ($object) {
            $cacheId = sprintf('%s_%s', $this->getEntityShortName(), $object->getId());
            $this->saveCache($cacheId, $object);

            return $object;
        }
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function unserialize(array $data)
    {
        $object = $this->createNew();

        return $this->bindData($object, $data);
    }

    protected function commit($object)
    {
        if (!$this->isSupportedObject($object)) {
            throw new \InvalidArgumentException(sprintf('The class must be instance of %s', $this->class));
        }

        $this->manager->persist($object);

        $cacheId = sprintf('%s_%s', $this->getEntityShortName(), serialize($object->getId()));
        if ($this->isExistCache($cacheId)) {
            $this->deleteCache($cacheId);
        }

        $this->manager->flush($object);
    }

    protected function getResult($queryBuilder, $hydration = Query::HYDRATE_OBJECT, $useCache = true, $lifetime = 5)
    {
        $query = $queryBuilder->getQuery();
        //$query->useResultCache($useCache, $lifetime, sprintf('%s_%s', $this->class, serialize($query->getParameters())));
        //$query->useQueryCache($useCache);
        $result = $query->getResult($hydration);

        return $result;
    }

    protected function getOneOrNullResult($queryBuilder, $hydration = Query::HYDRATE_OBJECT, $useCache = true, $lifetime = 5)
    {
        $query = $queryBuilder->getQuery();
        $query->useResultCache($useCache, $lifetime, sprintf('%s_%s', $this->class, serialize($query->getParameters())));
        $query->useQueryCache($useCache);
        $result = $query->getOneOrNullResult($hydration);

        return $result;
    }

    protected function getSingleScalarResult(QueryBuilder $queryBuilder, $useCache = true, $lifetime = 5)
    {
        $query = $queryBuilder->getQuery();
        $query->useResultCache($useCache, $lifetime, sprintf('%s_%s', $this->class, serialize($query->getParameters())));
        $query->useQueryCache($useCache);
        $result = $query->getSingleScalarResult();

        return $result;
    }

    protected function generateCacheKey($value)
    {
        return md5($value);
    }

    protected function saveCache($id, $object, $lifetime = 2700)
    {
        $this->cache->save($this->generateCacheKey($id), $object, $lifetime);
    }

    protected function fetchFromCache($id)
    {
        $object = $this->cache->fetch($this->generateCacheKey($id));

        if (! $object) {
            return null;
        }

        if (is_object($object)) {
            return $this->manager->merge($object);
        }

        return $object;
    }

    protected function isExistCache($id)
    {
        return $this->cache->contains($this->generateCacheKey($id));
    }

    protected function deleteCache($id)
    {
        $this->cache->delete($this->generateCacheKey($id));
    }

    /**
     * @param $object
     * @param mixed $data
     * @return mixed
     */
    protected function bindData($object, array $data = array())
    {
        if (!is_object($object)) {
            return;
        }

        foreach ($data as $key => $value) {
            $method = CamelCasizer::underScoretToCamelCase(sprintf('set_%s', $key));

            if (method_exists($object, $method)) {
                call_user_func_array(array($object, $method), array($value));
            } else {
                $method = CamelCasizer::underScoretToCamelCase($key);

                if (!method_exists($object, $method)) {
                    $method = CamelCasizer::underScoretToCamelCase(sprintf('is_%s', $key));
                }

                call_user_func_array(array($object, $method), array($value));
            }
        }

        return $object;
    }

    protected function getEntityShortName()
    {
        $reflectionClass = new \ReflectionClass($this->class);

        return $reflectionClass->getShortName();
    }

    /**
     * Function to log any changes to table
     *
     * @param type      $entity Doctrine Entity
     * @param string    $who    who update this
     * @param mixed     $key    usually table id
     * @return boolean
     */
    protected function logModified($entity, $who, $key = null)
    {
        try {
            $modify     = new \Vortexgin\CoreBundle\Entity\TableModify();
            $uow        = $this->manager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset  = $uow->getEntityChangeSet($entity);
            $tableName  = $this->manager->getClassMetadata(get_class($entity))->getTableName();

            $id = $key ? $key : $entity->getId();
            unset($changeset['updatedAt']);
            unset($changeset['updatedBy']);

            // Only save if there is any changes
            if (!empty($changeset)) {
                foreach ($changeset as $key => $val) {
                    if ($val[0] instanceof \DateTime) {
                        $changeset[$key][0] = $val[0]->format('Y-m-d H:i:s');
                        $changeset[$key][1] = $val[1]->format('Y-m-d H:i:s');
                    }
                    else if (is_object($val[0])) {
                        $changeset[$key][0] = $val[0]->getId();
                        $changeset[$key][1] = $val[1]->getId();
                    }
                }

                $modify->setContainer($tableName)
                        ->setContainerId($id)
                        ->setCreatedBy($who)
                        ->setUpdatedValue($changeset);

                $this->manager->persist($modify);
                $this->manager->flush();
            }

            return true;
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException('Logging Error', 500);
        }
    }

    protected function generateQuery($filter){
        $queryBuilder = $this->repository->createQueryBuilder('er');
        $queryBuilder->where('er.isActive = 1');

        return $this->generateFilter($queryBuilder, $filter);
    }

    /**
     * Function to generate filter query
     *
     * @param QueryBuilder      $queryBuilder Query Builder
     * @param array             $param    filter parameters
     *                          - field
     *                          - value
     *                          - operator : equal | like | lt | gt
     *                          - condition : and | or
     *                          - join : alias entity
     *                          - function : array(function name, extra param)
     *                          - group : group filter
     * @return QueryBuilder
     */
    protected function generateFilter($queryBuilder, array $param){
        if(is_array($param) && count($param) > 0){
            $index = 0;
            foreach($param as $key=>$value){
                if(!is_null($value[0]) && !is_null($value[1])){
                    if(in_array($value[0], $this->listSearchFields)){
                        $annotation = 'andWhere';
                        $field = 'er.'.$value[0];
                        $operator = array_key_exists(2, $value)?$value[2]:null;

                        if(array_key_exists(3, $value) && strtolower($value[3]) == 'or')
                            $annotation = 'orWhere';
                        if(array_key_exists(4, $value))
                            $field = $value[4].'.'.$value[0];
                        if(array_key_exists(5, $value)){
                            $function = $value[5];
                            if(array_key_exists(1, $value[5])){
                                $field = "{$function[0]}({$field}, '{$function[1]}')";
                            }else{
                                $field = "{$function[0]}({$field})";
                            }
                        }

                        switch(strtolower($operator)){
                          case 'notnull' : $queryBuilder->$annotation("{$field} IS NOT NULL");break;
                          case 'null' : $queryBuilder->$annotation("{$field} IS NULL");break;
                          case 'notlike' : $queryBuilder->$annotation("{$field} NOT LIKE '%{$value[1]}%'");break;
                          case 'like' : $queryBuilder->$annotation("{$field} LIKE '%{$value[1]}%'");break;
                          case 'lt' : $queryBuilder->$annotation("{$field} <= (:valueLt{$index})")->setParameter("valueLt{$index}", $value[1]);break;
                          case 'gt' : $queryBuilder->$annotation("{$field} >= (:valueGt{$index})")->setParameter("valueGt{$index}", $value[1]);break;
                          case 'notin' :$queryBuilder->$annotation("{$field} NOT IN (:valueNotIn{$index})")->setParameter("valueNotIn{$index}", $value[1]);break;
                          case 'in' :$queryBuilder->$annotation("{$field} IN (:valueIn{$index})")->setParameter("valueIn{$index}", $value[1]);break;
                          case 'notequal' :$queryBuilder->$annotation("{$field} <> :valueNoEqual{$index}")->setParameter("valueNoEqual{$index}", $value[1]);break;
                          default:$queryBuilder->$annotation("{$field} = :valueEqual{$index}")->setParameter("valueEqual{$index}", $value[1]);break;
                        }
                    }
                }
                $index++;
            }
        }

        return $queryBuilder;
    }

    /**
     * @param string $orderBy
     * @param string $orderSort
     * @param integer $page
     * @param integer $count
     *
     * @return array
     */
    protected function generateDefaultParam($orderBy = 'id', $orderSort = 'DESC', $page = 1, $count = 20){
        if(strtolower($orderBy) != 'rand'){          
          $orderBy    = !empty($orderBy) && in_array($orderBy, $this->listOrderBy)?$orderBy:'id';
          $orderBy    = strstr($orderBy, '.')?$orderBy:'er.'.$orderBy;
          $orderSort  = !empty($orderSort) && in_array($orderSort, array('ASC', 'DESC'))?$orderSort:'DESC';          
        }
        $page       = !empty($page)?$page:1;
        $limit      = !empty($count)?$count:20;
        $offset     = ($page * $count) - $count;

        return array($orderBy, $orderSort, $offset, $limit);
    }

    public function get(array $param = array(), $orderBy = 'id', $orderSort = 'DESC', $page = 1, $count = 20){
        list($orderBy, $orderSort, $offset, $limit) = $this->generateDefaultParam($orderBy, $orderSort, $page, $count);

        $sql = $this->generateQuery($param);
        if(strtolower($orderBy) == 'rand'){
          $sql->select('er, RAND() AS HIDDEN rs')
              ->orderBy('rs', 'ASC')
              ->setFirstResult($offset)
              ->setMaxResults($limit);
        }else{
          $sql->select('er')
              ->orderBy($orderBy, $orderSort)
              ->setFirstResult($offset)
              ->setMaxResults($limit);          
        }
        
        return $this->getResult($sql);
    }
    
    public function count(array $param = array()){
        $sql = $this->generateQuery($param);
        $sql->select('count(er.id)');

        return $this->getOneOrNullResult($sql);
    }
    
    /**
     * @return array
     */
    public function getOrderBy(){
        return $this->listOrderBy;
    }
    /**
     * @return array
     */

    public function getSearchFields(){
        return $this->listSearchFields;
    }

    /**
     * @return string
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user){
        $this->user = $user;
    }
}
