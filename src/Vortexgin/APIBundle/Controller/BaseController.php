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
use Vortexgin\LibraryBundle\Utils\Validator;
use Vortexgin\LibraryBundle\Utils\Doctrine\ORM\FilterGenerator;
use Vortexgin\LibraryBundle\Utils\Doctrine\ORM\EntityManipulator;

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
     * @return mixed
     */
    protected function errorResponse($userMessage, $httpStatusCode = 400, array $customHeader = array(), $format = 'json')
    {
        $content = array(
            'message' => $userMessage,
            'success' => false,
            'timestamp' => new \DateTime()
        );

        switch ($format) {
        case 'yaml':
            $response = new Response(
                Yaml::dump($param),
                $httpStatusCode,
                $customHeader
            );
            break;
        case 'csv':
            $customHeader['content-type'] = 'text/csv';
            $response = new Response($this->serializer->serialize($param, 'csv'), $httpStatusCode, $customHeader);
            break;
        case 'xml':
            $customHeader['content-type'] = 'text/xml';
            $response = new Response($this->serializer->serialize($param, 'xml'), $httpStatusCode, $customHeader);
            break;
        default :
            $customHeader['content-type'] = 'text/yaml';
            $response = new JsonResponse($content, $httpStatusCode, $customHeader);
            break;
        }

        return $response;
    }

    /**
     * Function to return success response
     * 
     * @param array  $param          User parameter response
     * @param int    $httpStatusCode Http status code
     * @param array  $customHeader   Custom http header
     * @param string $format         Response format
     * 
     * @return mixed
     */
    protected function successResponse(array $param, $httpStatusCode = 200, array $customHeader = array(), $format = 'json')
    {
        if (!Validator::validate($param, 'data', null, 'empty')) {
            throw new \InvalidArgumentException('Success response needs "data" child', 500);
        }

        $title = array();
        if (is_array($param['data'])) {
            foreach ($param['data'] as $key=>$value) {
                if ($value instanceof EntityInterface) {
                    $param['data'][$key] = json_decode($this->serializer->serialize($value, 'json'), true);
                    if (strtolower($format) == 'csv') {
                        $title = array_keys($param['data'][$key]);
                        $row = array();
                        foreach ($param['data'][$key] as $object) {
                            $row[] = is_array($object) || is_object($object)?'[Object]':$object;
                        }
                        $param['data'][$key] = $row;
                    }
                }
            }    
        } else {
            if ($param['data'] instanceof EntityInterface) {
                $param['data'] = json_decode($this->serializer->serialize($param['data'], 'json'), true);
                if (strtolower($format) == 'csv') {
                    $title = array();
                    $row = array();
                    foreach ($param['data'] as $object) {
                        $row[] = is_array($object) || is_object($object)?'[Object]':$object;
                    }
                    $param['data'] = $row;
                }
            }
        }
        $param['timestamp'] = $this->timeInit;
        $param['success'] = true;

        switch ($format) {
        case 'yaml':
            $customHeader['content-type'] = 'text/yaml';
            $response = new Response(Yaml::dump($param), $httpStatusCode, $customHeader);
            break;
        case 'csv':
            $customHeader['content-type'] = 'text/csv';
            array_unshift($param['data'], $title);
            $response = new Response($this->serializer->serialize($param['data'], 'csv'), $httpStatusCode, $customHeader);
            break;
        case 'xml':
            $customHeader['content-type'] = 'text/xml';
            $response = new Response($this->serializer->serialize($param, 'xml'), $httpStatusCode, $customHeader);
            break;
        default:
            $response = new JsonResponse($param, $httpStatusCode, $customHeader);
            break;
        }

        return $response;
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

            $entityManipulator = new EntityManipulator($this->em, $this->class);
            $object = $entityManipulator->find($id);

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

            $validator = new Validator($this->em);
            $post['params'] = Validator::validate($post, 'params', null, 'empty')?$post['params']:array();
            $post['params'] = $validator->entity($this->class, $post['params']);
            if (!is_array($post['params'])) {
                return $this->errorResponse($post['params'], HttpStatusHelper::HTTP_BAD_REQUEST);
            }

            if (Validator::validate($post, 'params', 'array', 'empty')) {
                $entityManipulator = new EntityManipulator($this->em, $object);
                $entityManipulator->save($post['params']);
            }

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

            $entityManipulator = new EntityManipulator($this->em, $object);
            $entityManipulator->delete();
            
            return $this->successResponse(
                array(
                    'data' => $object,
                ), 
                HttpStatusHelper::HTTP_NO_CONTENT, 
                array(), 
                $format
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Delete data error. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}