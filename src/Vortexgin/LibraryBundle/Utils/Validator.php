<?php

namespace Vortexgin\LibraryBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Vortexgin\LibraryBundle\Model\EntityInterface;


/**
 * Validation utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class Validator
{

    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Construct
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager Entity Manager
     * 
     * @return void
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->_em = $entityManager;
    }

    /**
     * Function to validate input user
     *
     * @param array  $input  Array input
     * @param string $key    Key to validate
     * @param string $is     IS checker. Format: boolean, float, int, array
     * @param string $not    NOT checker. Format: null, empty
     * @param string $filter FILTER checker. Format FILTER_EMAIL, FILTER_URL, FILTER_USERNAME, FILTER_PHONE, FILTER_HANDPHONE
     * @param string $regExp REGEX checker
     * 
     * @return boolean
     */
    static public function validate(array $input, $key, $is = null, $not = 'null', $filter = null, $regExp = null)
    {
        if(!is_array($input))
          return false;
        if(!array_key_exists($key, $input))
        return false;
        if (!is_null($is)) {
            switch(strtolower($is)){
            case 'boolean':
                if(!filter_var($input[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
                    return false;
                break;
            case 'float':
                if(!filter_var($input[$key], FILTER_VALIDATE_FLOAT))
                    return false;
                break;
            case 'int':
                if(!filter_var($input[$key], FILTER_VALIDATE_INT))
                    return false;
                break;
            case 'array':
                if(!is_array($input[$key]))
                    return false;
                break;
            case 'date':
                $validDate = \DateTime::createFromFormat('Y-m-d', $input[$key]);
                if (!$validDate)
                    return false;
                break;
            case 'datetime':
                $validDate = \DateTime::createFromFormat('Y-m-d H:i:s', $input[$key]);
                if (!$validDate)
                    return false;
                break;
            default:
                break;
            }
        }
        if (strtolower($not) == 'empty') {
            if(empty($input[$key]))
                return false;
        } else {
            if(is_null($input[$key]))
                return false;
        }
        if (!is_null($filter)) {
            switch(strtoupper($filter)){
            case 'FILTER_EMAIL':
                if(!filter_var($input[$key], FILTER_VALIDATE_EMAIL))
                    return false;
                break;
            case 'FILTER_URL':
                if(!filter_var($input[$key], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
                    return false;
                break;
            case 'FILTER_USERNAME':
                if(!preg_match('/^[A-Za-z][A-Za-z0-9]{5,31}$/', $input[$key]))
                    return false;
                break;
            case 'FILTER_PHONE':
                if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{3,5}$/", $input[$key]))
                    return false;
                break;
            case 'FILTER_HANDPHONE':
                if(!preg_match("/^[0-9]{4}-[0-9]{4}-[0-9]{3,6}$/", $input[$key]))
                    return false;
                break;
            default:
                break;
            }
        }
        if (!is_null($regExp)) {
            if (!preg_match($regExp, $input[$key]))
                return false;
        }

        return true;
    }

    /**
     * Function to validate entity
     * 
     * @param object $class  Class entity
     * @param array  $params Parameter
     * 
     * @return boolean
     */
    public function entity($class, $params)
    {
        try {
            $phpDocExtractor = new PhpDocExtractor();
            $doctrineExtractor = new DoctrineExtractor($this->_em->getMetadataFactory());
    
            $properties = $doctrineExtractor->getProperties($class);
            foreach ($properties as $property) {
                if (in_array($property, ['id', 'isActive', 'createdAt', 'createdBy', 'updatedAt', 'updatedBy'])) {
                    continue;
                }

                $types = $doctrineExtractor->getTypes($class, $property);
                if (count($types) > 0) {
                    $type = $types[0];
                    $shortDesc = $phpDocExtractor->getShortDescription($class, $property)?:$type;
                    
                    if (!$type->isNullable()) {
                        if (in_array($type->getBuiltinType(), ['string'])) {
                            if (!self::validate($params, $property, null, 'empty')) {
                                return $shortDesc.' cannot be empty';
                            }
                        } else {
                            if (!self::validate($params, $property, 'null', 'empty')) {
                                return $shortDesc.' cannot be empty';
                            }
                        }
                    }
    
                    if (!self::validate($params, $property, 'null', 'empty')) {
                        continue;
                    }
                    if (in_array($type->getBuiltinType(), ['object'])) {
                        if (strtolower($type->getClassName()) == 'datetime') {
                            $validDate = \DateTime::createFromFormat('Y-m-d H:i:s', $params[$property]);
                            if (!$validDate) {
                                return $shortDesc.' invalid. Use this format "Y-m-d H:i:s"';
                            }
                            $params[$property] = $validDate;
                        } elseif (strtolower($type->getClassName()) == 'date') {
                            $validDate = \DateTime::createFromFormat('Y-m-d', $params[$property]);
                            if (!$validDate) {
                                return $shortDesc.' invalid. Use this format "Y-m-d"';
                            }        
                            $params[$property] = $validDate;
                        } else {
                            if (!$type->isNullable()) {
                                $repo = $this->_em->getRepository($type->getClassName());
                                $object = $repo->find($params[$property]);
                                if (!$object) {
                                    return $shortDesc.' not found';
                                }
                                $params[$property] = $object;
                            }
                        }
                    }
                }
            }
    
            return $params;    
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
