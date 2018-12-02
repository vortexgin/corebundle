<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

/**
 * Query result manipulator functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils\Doctrine\ORM
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class ResultManipulator
{

    /**
     * Function to get result from query builder
     * 
     * @param Doctrine\ORM\QueryBuilder $queryBuilder Query Builder
     * @param int                       $hydration    Data return format
     * @param boolean                   $useCache     Caching status
     * @param int                       $lifetime     Cache lifetime
     * 
     * @return mixed
     */
    public function getResult(QueryBuilder $queryBuilder, $hydration = Query::HYDRATE_OBJECT, $useCache = true, $lifetime = 60)
    {
        $query = $queryBuilder->getQuery();
        $query->useResultCache($useCache, $lifetime, $query->getSQL());
        $query->useQueryCache($useCache);
        $result = $query->getResult($hydration);

        return $result;
    }

    /**
     * Function to get one or null result from query builder
     * 
     * @param Doctrine\ORM\QueryBuilder $queryBuilder Query Builder
     * @param int                       $hydration    Data return format
     * @param boolean                   $useCache     Caching status
     * @param int                       $lifetime     Cache lifetime
     * 
     * @return mixed
     */
    public function getOneOrNullResult(QueryBuilder $queryBuilder, $hydration = Query::HYDRATE_OBJECT, $useCache = true, $lifetime = 60)
    {
        $query = $queryBuilder->getQuery();
        $query->useResultCache($useCache, $lifetime, $query->getSQL());
        $query->useQueryCache($useCache);
        $result = $query->getOneOrNullResult($hydration);

        return $result;
    }

    /**
     * Function to get single result from query builder
     * 
     * @param Doctrine\ORM\QueryBuilder $queryBuilder Query Builder
     * @param boolean                   $useCache     Caching status
     * @param int                       $lifetime     Cache lifetime
     * 
     * @return mixed
     */
    public function getSingleScalarResult(QueryBuilder $queryBuilder, $useCache = true, $lifetime = 60)
    {
        $query = $queryBuilder->getQuery();
        $query->useResultCache($useCache, $lifetime, $query->getSQL());
        $query->useQueryCache($useCache);
        $result = $query->getSingleScalarResult();

        return $result;
    }
}