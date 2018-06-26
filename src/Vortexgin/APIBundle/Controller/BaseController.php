<?php

namespace Vortexgin\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Doctrine\ORM\QueryBuilder;
use Vortexgin\LibraryBundle\Model\EntityInterface;
use Vortexgin\LibraryBundle\Utils\CamelCasizer;
use Vortexgin\LibraryBundle\Utils\HttpStatusHelper;
use Vortexgin\LibraryBundle\Utils\Doctrine\ORM\FilterGenerator;
use Vortexgin\LibraryBundle\Utils\Validator;

/**
 * Base controller class 
 * 
 * @category Controller
 * @package  Vortexgin\APIBundle\Controller
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class BaseController extends Controller
{

    /**
     * Class entity
     * 
     * @var object 
     */
    protected $class;

    /**
     * Class name
     * 
     * @var string 
     */
    protected $className;

    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Class repository
     * 
     * @var \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
     */
    protected $repo;

    /**
     * Object serializer
     * 
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    /**
     * Current time
     * 
     * @var \DateTime
     */
    protected $timeInit;

    /**
     * Create new class object
     * 
     * @return mixed
     */
    protected function createNew()
    {
        return new $this->className();
    }

    /**
     * Init function to defined object, manager, repository 
     * 
     * @param string $class Class entity
     * 
     * @return void
     */
    protected function init($class)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->repo = $this->em->getRepository($class);

        $this->class = $class;
        $this->className = $this->em->getClassMetadata($class)->getName();
        
        $encoders = array(new XmlEncoder(), new JsonEncoder(), new YamlEncoder(), new CsvEncoder());

        $methodNormalizer = new GetSetMethodNormalizer();
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
        
        $this->timeInit = new \DateTime;
    }

    /**
     * Function to return error response
     * 
     * @param string $userMessage    User error message
     * @param int    $httpStatusCode Http status code
     * @param array  $customHeader   Custom http header
     * @param string $format         Response format
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function errorResponse($userMessage, $httpStatusCode = 400, array $customHeader = array(), $format = 'json')
    {
        $content = array(
            'message' => $userMessage,
            'success' => false,
            'timestamp' => new \DateTime()
        );

        switch ($format) {
        default :
            $return = new JsonResponse($content, $httpStatusCode, $customHeader);
            break;
        }

        return $return;
    }

    /**
     * Function to return success response
     * 
     * @param array  $param          User parameter response
     * @param int    $httpStatusCode Http status code
     * @param array  $customHeader   Custom http header
     * @param string $format         Response format
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function successResponse(array $param, $httpStatusCode = 200, array $customHeader = array(), $format = 'json') {
        if (!Validator::validate($param, 'data', 'array', 'empty')) {
            throw new \InvalidArgumentException('Success response needs "data" child', 500);
        }
        
        foreach ($param['data'] as $key=>$value) {
            if ($value instanceof EntityInterface) {
                $param['data'][$key] = $this->serializer->serialize($value);
            }
        }
        $param['timestamp'] = $this->timeInit;
        $param['success'] = true;

        switch ($format) {
        default:
            $return = new JsonResponse($param, $httpStatusCode, $customHeader);
            break;
        }

        return $return;
    }

    /**
     * Function to generate query
     * 
     * @param array $filter Filter query
     * 
     * @return Doctrine\ORM\QueryBuilder
     */
    protected function generateQuery(array $filter)
    {
        $queryBuilder = $this->repo->createQueryBuilder('er');
        $queryBuilder->where('1 = 1');

        return FilterGenerator::generate($queryBuilder, $this->class, $filter);
    }

    /**
     * Show data from entity
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * @param string                                    $class   Class entity
     * 
     * @return mixed
     */
    public function showAction(Request $request, $class)
    {
        try {
            $get = $request->query->all();
            $this->init($this->container->getParameter($class));

            $orderBy = Validator::validate($get, 'order_by', null, 'empty')?$get['order_by']:'id';
            $orderSort = Validator::validate($get, 'order_sort', null, 'empty')?$get['order_sort']:'DESC';
            $limit = Validator::validate($get, 'limit', null, 'empty')?$get['limit']:20;
            $page = Validator::validate($get, 'page', null, 'empty')?$get['page']:1;
            $format = Validator::validate($get, '_format', null, 'empty')?$get['_format']:'json';
            
            $filters = Validator::validate($get, 'filters', 'array', 'empty')?$get['filters']:array();
            $where = FilterGenerator::queryParameter($filters);

            $objects = $this->repo->findBy($filters, [$orderBy => $orderSort], $limit, ($page * $limit) - $limit);
            if (!$objects) {
                return $this->errorResponse($this->class.' not found', HttpStatusHelper::HTTP_NOT_FOUND);
            }

            return $this->successResponse(
                array(
                    'data' => $objects
                ), 
                HttpStatusHelper::HTTP_OK, 
                array(), 
                $format
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Show data error. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Find data from entity by id
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * @param string                                    $class   Class entity
     * @param int                                       $id      Id of entity
     * 
     * @return mixed
     */
    public function findAction(Request $request, $class, $id)
    {
        try {
            $get = $request->query->all();
            $this->init($this->container->getParameter($class));

            $format = Validator::validate($get, '_format', null, 'empty')?$get['_format']:'json';

            $object = $this->repo->find($id);
            if (!$object) {
                return $this->errorResponse($this->class.' not found', HttpStatusHelper::HTTP_NOT_FOUND);
            }

            return $this->successResponse(
                array(
                    'data' => $object
                ), 
                HttpStatusHelper::HTTP_OK, 
                array(), 
                $format
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Find data error. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Post data for entity
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * @param string                                    $class   Class entity
     * 
     * @return mixed
     */
    public function postAction(Request $request, $class)
    {
        try {
            $post = $request->request->all();
            $get = $request->query->all();
            $this->init($this->container->getParameter($class));

            $format = Validator::validate($get, '_format', null, 'empty')?$get['_format']:'json';

            $object = $this->createNew();
            if (Validator::validate($post, 'id', null, 'empty')) {
                $object = $this->repo->find($post['id']);
            }

            if (Validator::validate($post, 'params', 'array', 'empty')) {
                foreach ($post['params'] as $key=>$value) {
                    $method = 'set'.ucfirst(CamelCasizer::underScoreToCamelCase($key));
                    $object->$method($value);
                }
            }

            $this->em->persist($object);
            $this->em->flush();

            return $this->successResponse(
                array(
                    'data' => $object
                ), 
                HttpStatusHelper::HTTP_ACCEPTED, 
                array(), 
                $format
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Post data error. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Delete data from entity by id
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * @param string                                    $class   Class entity
     * @param int                                       $id      Id of entity
     * 
     * @return mixed
     */
    public function deleteAction(Request $request, $class, $id)
    {
        try {
            $get = $request->query->all();
            $this->init($this->container->getParameter($class));

            $format = Validator::validate($get, '_format', null, 'empty')?$get['_format']:'json';

            $object = $this->repo->find($id);
            if (!$object) {
                return $this->errorResponse($this->class.' not found', HttpStatusHelper::HTTP_NOT_FOUND);
            }

            $this->em->remove($object);
            $this->em->flush();
            
            return $this->successResponse(
                array(), 
                HttpStatusHelper::HTTP_NO_CONTENT, 
                array(), 
                $format
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Delete data error. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}