<?php

namespace Vortexgin\WebBundle\Controller;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use AlterPHP\EasyAdminExtensionBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Vortexgin\LibraryBundle\Utils\Doctrine\ORM\EntityManipulator;
use Vortexgin\LibraryBundle\Utils\CsvExporter;
use Vortexgin\LibraryBundle\Utils\StringUtils;
use Vortexgin\LibraryBundle\Utils\Validator;
use App\Entity\User;

/**
 * EasyAdmin controller class 
 * 
 * @category Controller
 * @package  App\Controller
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class EasyAdminController extends BaseAdminController
{

    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        $this->dispatch(EasyAdminEvents::POST_LIST, array('paginator' => $paginator));

        $parameters = array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        );

        return $this->render('@VortexginWebBundle/EasyAdmin/list.html.twig', $parameters);                
    }

    /**
     * It persists and flushes the given Doctrine entity. It allows to modify the entity
     * before/after being saved in the database (e.g. to transform a DTO into a Doctrine entity)
     * 
     * @param object $entity Object of entity
     * 
     * @return mixed
     */
    protected function persistEntity($entity)
    {
        $entity = $this->updateSlug($entity);
        $entity = $this->createEntityLog($entity);

        parent::persistEntity($entity);
    }

    /**
     * It flushes the given Doctrine entity to save its changes. It allows to modify
     * the entity before it's saved in the database.
     * 
     * @param object $entity Object of entity
     * 
     * @return mixed
     */
    protected function updateEntity($entity)
    {
        $entity = $this->updateSlug($entity);
        $entity = $this->updateEntityLog($entity);

        parent::updateEntity($entity);
    }

    /**
     * It updates the value of some property of some entity to the new given value.
     * Use for xmlhttprequest
     *
     * @param mixed  $entity   The instance of the entity to modify
     * @param string $property The name of the property to change
     * @param bool   $value    The new value of the property
     *
     * @throws \RuntimeException
     * 
     * @return mixed
     */
    protected function updateEntityProperty($entity, $property, $value)
    {
        if ($property == 'attend' && method_exists($entity, 'setAttendingTime')) {
            if ($value === true) {
                $entity->setAttendingTime(new \DateTime());
            } else {
                $entity->setAttendingTime(null);
            }
        }
        parent::updateEntityProperty($entity, $property, $value);
    }
    /**
     * Function to create FOS User entity
     * 
     * @return \FOS\UserBundle\Model\User
     */
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    /**
     * Function to update FOS User Entity
     * 
     * @param \FOS\UserBundle\Model\User $user FOS User entity
     * 
     * @return void
     */
    public function updateUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::updateEntity($user);
    }

    /**
     * Function to persist and flush to the user entity
     * 
     * @param \FOS\UserBundle\Model\User $user FOS User entity
     * 
     * @return void
     */
    public function persistUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::persistEntity($user);
    }

    /**
     * Function to create entity log
     * 
     * @param object $entity Object of entity
     * 
     * @return object
     */
    protected function createEntityLog($entity)
    {
        $who = !empty($this->get('security.token_storage')->getToken())?$this->get('security.token_storage')->getToken()->getUser():'Anonymous';
        if (method_exists($entity, 'setCreatedBy')) {
            $entity->setCreatedBy($who);
        }

        return $entity;
    }

    /**
     * Function to update entity log
     * 
     * @param object $entity Object of entity
     * 
     * @return object
     */
    protected function updateEntityLog($entity)
    {
        $who = !empty($this->get('security.token_storage')->getToken())?$this->get('security.token_storage')->getToken()->getUser():'Anonymous';
        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($who);
        }
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime());
        }

        return $entity;
    }

    /**
     * Function to create entity slug
     * 
     * @param object $entity Object of entity
     * 
     * @return object
     */
    protected function updateSlug($entity)
    {
        if (method_exists($entity, 'setSlug') and method_exists($entity, 'getTitle')) {
            $entity->setSlug(StringUtils::createSlug($entity->getTitle()));
        }

        return $entity;
    }

    /**
     * Function to export entity
     * 
     * @return \Exception
     */
    public function exportAction()
    {
        $get = $this->request->query->all();

        $sortDirection = Validator::validate($get, 'sortDirection', null, 'empty')?$get['sortDirection']:'DESC';
        $sortField = Validator::validate($get, 'sortField', null, 'empty')?$get['sortField']:'id';
        $filters = Validator::validate($get, 'filters', null, 'empty')?$get['filters']:array();
        $dqlFilter = $this->entity['list']['dql_filter'];
        foreach ($filters as $field=>$value) {
            $dqlFilter.= sprintf(" AND %s=%s", $field, $value);
        }

        $queryBuilder = $this->createListQueryBuilder(
            $this->entity['class'],
            $sortDirection,
            $sortField,
            $dqlFilter
        );

        $csvExporter = new CsvExporter();
        return $csvExporter->getResponseFromQueryBuilder($queryBuilder);
    }

    /**
     * Function to import csv data into entity
     * 
     * @return \Exception
     */
    public function importAction()
    {
        $get = $this->request->query->all();
        $post = $this->request->request->all();
        $files = $this->request->files->all();

        $data = array();

        $em = $this->container->get('doctrine')->getManager();
        $reflector = new \ReflectionClass($this->entity['class']);
        $properties = $reflector->getProperties();
        $validator = new Validator($em);

        if (Validator::validate($post, 'submit', null, 'null')) {
            if (Validator::validate($files, 'file', null, 'empty')) {
                if (in_array($files['file']->getMimeType(), ['text/plain', 'text/csv', 'application/vnd.ms-excel'])) {
                    if (Validator::validate($post, 'fields', 'array', 'empty')) {
                        $filename = StringUtils::generateRand(8);
                        $files['file']->move('/tmp/', $filename);
                        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
                        $data = $serializer->decode(file_get_contents('/tmp/'.$filename), 'csv');

                        foreach ($data as $key=>$row) {
                            $new = new $reflector->name;
                            $params = array();
                            foreach ($post['fields'] as $urut=>$field) {
                                if (!empty($field)) {
                                    $index = 0;
                                    foreach ($row as $keyField=>$col) {
                                        if ($urut == $index) {
                                            $params[$field] = $col;
                                            break;
                                        }
                                        $index++;
                                    }
                                }
                            }

                            try {
                                $valid = $validator->entity(get_class($new), $params, array('password', 'passwordPlain'));
                                if (is_array($valid)) {
                                    if ($new instanceof User) {
                                        $fosUserManager = $this->container->get('fos_user.user_manager');
                                        $entityManipulator = new EntityManipulator($em, $new);

                                        $new = $entityManipulator->bindData($valid);
                                        $fosUserManager->updateUser($new);
                                    } else {
                                        $entityManipulator = new EntityManipulator($em, $new);
                                        $entityManipulator->save($valid);    
                                    }
                                } else {
                                    $data['err'][] = $valid.' on row '.$key;
                                }
                            } catch (\Exception $e) {
                                $data['err'][] = 'Error on row '.$key.' :'.$e->getMessage();
                                $em = $this->getDoctrine()->resetManager();
                            }
                        }
                    }    
                }
            }
        }

        $properties = $reflector->getProperties();
        if (count($properties) > 0) {
            foreach ($properties as $property) {
                $data['fields'][] = $property->getName();
            }
        }

        return $this->render('@VortexginWebBundle/EasyAdmin/import.html.twig', $data);                
    }

}