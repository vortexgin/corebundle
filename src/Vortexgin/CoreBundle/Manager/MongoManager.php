<?php

namespace Vortexgin\CoreBundle\Manager;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\Common\Cache\ArrayCache;

abstract class MongoManager extends Manager
{
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

        $this->cache = new ArrayCache();
    }

    public function delete($object)
    {
        $object->setIsActive(false);

        $object->setUpdatedBy($this->getUser());
        $this->manager->persist($object);
        $this->logModified($object, $this->getUser());
        $this->manager->flush();
    }

    public function getRepository(){
        return $this->repository;
    }

    protected function generateQuery($filter){
        $queryBuilder = $this->repository->createQueryBuilder('er');
        $queryBuilder->hydrate(false);
        $queryBuilder->field('isActive')->equals(true);

        return $this->generateFilter($queryBuilder, $filter);
    }

    /**
     * Function to generate filter query.
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
     *
     * @return QueryBuilder
     */
    protected function generateFilter($queryBuilder, array $param)
    {
        if (is_array($param) && count($param) > 0) {
            $index = 0;
            foreach ($param as $key => $value) {
                if (!is_null($value[0]) && !is_null($value[1])) {
                    if (in_array($value[0], $this->listSearchFields)) {
                        $annotation = 'addAnd';
                        $field = $queryBuilder->expr()->field($value[0]);
                        $operator = array_key_exists(2, $value) ? $value[2] : null;

                        if (array_key_exists(3, $value) && strtolower($value[3]) == 'or') {
                            $annotation = 'addOr';
                        }

                        switch (strtolower($operator)) {
                          case 'lt' : $function = 'lt';break;
                          case 'gt' : $function = 'gt';break;
                          case 'notin' :$function = 'notIn';break;
                          case 'in' :$function = 'in';break;
                          case 'notequal' :$function = 'notEqual';break;
                          default:$function = 'equals';break;
                        }

                         if(strtolower($operator) == 'isnull') {
                            $queryBuilder->$annotation($field->equals(null));
                        } elseif (strtolower($operator) == 'notnull') {
                            $queryBuilder->$annotation($field->notEqual(null));
                        } else {
                            $queryBuilder->$annotation($field->$function($value[1]));
                        }
                    }
                }
                ++$index;
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
        $orderBy    = !empty($orderBy) && in_array($orderBy, $this->listOrderBy)?$orderBy:'id';
        if($orderBy == 'created_at'){
          $orderBy = 'createdAt';
        }
        //$orderBy    = strstr($orderBy, '.')?$orderBy:'er.'.$orderBy;
        $orderSort  = !empty($orderSort) && in_array($orderSort, array('ASC', 'DESC'))?$orderSort:'DESC';
        $page       = !empty($page)?$page:1;
        $limit      = !empty($count)?$count:20;
        $offset     = ($page * $count) - $count;

        return array($orderBy, $orderSort, $offset, $limit);
    }

    protected function getResult($queryBuilder, $hydration = false, $useCache = true, $lifetime = 5)
    {
        $query = $queryBuilder->getQuery();
        $result = $query->execute();

        return $result;
    }

    protected function getOneOrNullResult( $queryBuilder, $hydration = false, $useCache = true, $lifetime = 5)
    {
        $query = $queryBuilder->getQuery();
        $result = $query->getSingleResult($hydration);

        return $result;
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
            return true;
            $modify     = new \DS\CoreBundle\Entity\TableModify();
            $uow        = $this->manager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset  = $uow->getDocumentChangeSet($entity);
            $tableName  = $this->manager->getClassMetadata(get_class($entity))->getName();

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

            throw new \InvalidArgumentException($ex->getMessage(), 500);
        }
    }
}
