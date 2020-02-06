<?php

namespace Vortexgin\LibraryBundle\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSV Exporter Class
 * 
 * @category Utils
 * @package  App\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class CsvExporter
{

    /**
     * Object serializer
     * 
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    public function __construct()
    {
        $encoders = array(new JsonEncoder(), new CsvEncoder());

        $methodNormalizer = new GetSetMethodNormalizer();
        $methodNormalizer->setCircularReferenceHandler(
            function ($object) {
                return $object->getId();
            }
        );
        $callbackInt = function ($var) {
            return (int) $var;
        };
        $callbackDateTime = function ($dateTime) {
            return !empty($dateTime) && $dateTime instanceof \DateTime
                ? $dateTime->format(\DateTime::ISO8601)
                : '';
        };
        $methodNormalizer->setCallbacks(
            array(
                'id' => $callbackInt,
                'createdAt' => $callbackDateTime,
                'updatedAt' => $callbackDateTime,
            )
        );

        $normalizers = array($methodNormalizer);
        $this->serializer = new Serializer($normalizers, $encoders);        
    }

    /**
     * Function to stream query builder result into csv
     * 
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param string                     $columns      Columns of query
     * @param string                     $filename     File name
     * 
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getResponseFromQueryBuilder(QueryBuilder $queryBuilder)
    {
        $entities = new ArrayCollection($queryBuilder->getQuery()->getResult());
        if ($entities->count() > 0) {
            $rows = array();

            foreach ($entities as $entity) {
                if (method_exists($entity, 'exportFields')) {
                    $rows[] = $entity->exportFields();
                } elseif (method_exists($entity, 'toArray')) {
                    $rows[] = $entity->toArray();
                } else {
                    $rows[] = $this->getValue($entity);
                }
            }
            
            return $this->getResponseFromArray($rows);
        }

        throw new \InvalidArgumentException('No result from this entity', 404);
    }

    /**
     * Function to stream array into csv
     * 
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder
     * @param string                     $columns      Columns of query
     * @param string                     $filename     File name
     * 
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getResponseFromArray(array $rows = array())
    {
        return new Response(
            $this->serializer->serialize($rows, 'csv'), 
            200, 
            array(
                'Content-Type' => 'text/csv; charset=utf-8', 
                'Content-Disposition' => 'attachment; filename="export.csv"', 
            )
        );
    }

    /**
     * Function to get title rows
     * 
     * @param array $entities Array of entity
     * 
     * @return array
     */
    private function getTitle($entities)
    {
        $reflectionClass = new \ReflectionClass($entities[0]);
        $cols = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $cols[] = $property->getName();
        }

        return $cols;
    }

    /**
     * Function to get value from entity
     * 
     * @param object $entity Object of entity
     * 
     * @return array
     */
    private function getValue($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);
        $cols = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $method = CamelCasizer::underScoreToCamelCase(sprintf('get_%s', $property->getName()));
            if (method_exists($entity, $method)) {
                $val = $entity->$method();
                if ($val instanceof \DateTime) {
                    $cols[] = $val->format('Y-m-d H:i:s');
                } elseif (is_object($val)) {
                    if (method_exists($val, 'getName')) {
                        $cols[] = $val->getName();
                    } elseif (method_exists($val, '__toString')) {
                        $cols[] = (string) $val;
                    } elseif (method_exists($val, 'toArray')) {
                        $cols[] = $val->toArray();
                    } elseif (method_exists($val, 'getId')) {
                        $cols[] = $val->getId();
                    } else {
                        $cols[] = null;
                    }
                } else {
                    $cols[] = $val;
                }    
            } else {
                $cols[] = null;
            }
        }

        return $cols;
    }
}