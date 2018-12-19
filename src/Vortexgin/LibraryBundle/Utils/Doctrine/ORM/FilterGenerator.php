<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter generator utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils\Doctrine\ORM
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class FilterGenerator
{

    /**
     * Function to generate filter query
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder Query Builder
     * @param array                     $param        Filter parameters
     *                                                - field
     *                                                - value
     *                                                - operator : equal | like | lt | gt
     *                                                - condition : and | or
     *                                                - join : alias entity
     *                                                - function : array(function name, extra param)
     *                                                - group : group filter
     * 
     * @return Doctrine\ORM\QueryBuilder
     */
    public static function generate(QueryBuilder $queryBuilder, array $param)
    {
        if (is_array($param) && count($param) > 0) {
            $index = 0;
            foreach ($param as $key=>$value) {
                if (!is_null($value[0]) && !is_null($value[1])) {
                    $annotation = 'andWhere';
                    $field = 'er.'.$value[0];
                    $operator = array_key_exists(2, $value)?$value[2]:null;

                    if(array_key_exists(3, $value) && strtolower($value[3]) == 'or')
                        $annotation = 'orWhere';
                    if(array_key_exists(4, $value))
                        $field = $value[4].'.'.$value[0];
                    if (array_key_exists(5, $value)) {
                        $function = $value[5];
                        if (array_key_exists(1, $value[5])) {
                            $field = "{$function[0]}({$field}, '{$function[1]}')";
                        } else {
                            $field = "{$function[0]}({$field})";
                        }
                    }

                    switch(strtolower($operator)){
                    case 'notnull' : 
                        $queryBuilder->$annotation("{$field} IS NOT NULL");
                        break;
                    case 'null' : 
                        $queryBuilder->$annotation("{$field} IS NULL");
                        break;
                    case 'notlike' : 
                        $queryBuilder->$annotation("{$field} NOT LIKE '%{$value[1]}%'");
                        break;
                    case 'like' : 
                        $queryBuilder->$annotation("{$field} LIKE '%{$value[1]}%'");
                        break;
                    case 'lt' : 
                        $queryBuilder->$annotation("{$field} <= (:valueLt{$index})")->setParameter("valueLt{$index}", $value[1]);
                        break;
                    case 'gt' : 
                        $queryBuilder->$annotation("{$field} >= (:valueGt{$index})")->setParameter("valueGt{$index}", $value[1]);
                        break;
                    case 'notin' :
                        $queryBuilder->$annotation("{$field} NOT IN (:valueNotIn{$index})")->setParameter("valueNotIn{$index}", $value[1]);
                        break;
                    case 'in' :
                        $queryBuilder->$annotation("{$field} IN (:valueIn{$index})")->setParameter("valueIn{$index}", $value[1]);
                        break;
                    case 'notequal' :
                        $queryBuilder->$annotation("{$field} <> :valueNoEqual{$index}")->setParameter("valueNoEqual{$index}", $value[1]);
                        break;
                    case 'regexp' :
                        $queryBuilder->$annotation("REGEXP({$field}, :valueRegexp{$index}) = true")->setParameter("valueRegexp{$index}", $value[1]);
                        break;
                    default:
                        $queryBuilder->$annotation("{$field} = :valueEqual{$index}")->setParameter("valueEqual{$index}", $value[1]);
                        break;
                    }
                }
                $index++;
            }
        }

        return $queryBuilder;
    }

    /**
     * Function to generate pre filter from query parameter
     * 
     * @param array $filters Pre filter
     * 
     * @return array;
     */
    public static function queryParameter(array $filters)
    {
        $where = array();
        if (count($filters) > 0) {
            foreach ($filters as $key=>$filter) {
                if (is_array($filter)) {
                    $pre = array($key, $filter[0]);
                    if (array_key_exists(1, $filter)) {
                        $pre[] = $filter[1];
                    } else {
                        $pre[] = 'equal';
                    }

                    if (array_key_exists(2, $filter)) {
                        $pre[] = $filter[2];
                    } else {
                        $pre[] = 'and';
                    }

                    if (array_key_exists(3, $filter)) {
                        $pre[] = $filter[3];
                    }

                    $where[] = $pre;
                } else {
                    $where[] = array($key, $filter);
                }
            }
        }

        return $where;
    }
}