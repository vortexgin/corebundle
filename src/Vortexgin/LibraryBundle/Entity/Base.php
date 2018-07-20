<?php

namespace Vortexgin\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vortexgin\LibraryBundle\Utils\CamelCasizer;

/**
 * Base Entity.
 *
 * @category Entity
 * @package  Vortexgin\LibraryBundle\Entity
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 * 
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class Base
{
    /**
     * ID
     * 
     * @var int
     *
     * @ORM\Column(name="id",                   type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Active status
     * 
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false, options={"default": 1})
     */
    protected $isActive = true;

    /**
     * Created at
     * 
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     * Created by
     * 
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=100, nullable=false)
     */
    protected $createdBy;

    /**
     * Updated at
     * 
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $updatedAt;

    /**
     * Updated by
     * 
     * @var string
     *
     * @ORM\Column(name="updated_by", type="string", length=100, nullable=true)
     */
    protected $updatedBy;

    /**
     * Convert entity into array
     * 
     * @return array
     */
    public function toArray()
    {
        $return = array();
        $reflector = new \ReflectionClass($this);
        $properties = $reflector->getProperties();
        if (count($properties) > 0) {
            foreach ($properties as $property) {
                if (stristr($property->getDocComment(), 'OneToMany')) {
                } elseif (stristr($property->getDocComment(), 'ManyToOne')) {
                    if (method_exists($this, CamelCasizer::underScoreToCamelCase('get'.$property->getName()))) {
                        $method = CamelCasizer::underScoreToCamelCase('get'.$property->getName());
                        $return[$property->getName()] = $this->$method();
                    }
                } else {
                    if (method_exists($this, CamelCasizer::underScoreToCamelCase('get'.$property->getName()))) {
                        $method = CamelCasizer::underScoreToCamelCase('get'.$property->getName());
                        $return[$property->getName()] = $this->$method();
                    } elseif (method_exists($this, CamelCasizer::underScoreToCamelCase('has'.$property->getName()))) {
                        $method = CamelCasizer::underScoreToCamelCase('has'.$property->getName());
                        $return[$property->getName()] = $this->$method();
                    }    
                }
            }
        }

        return $return;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set active status
     * 
     * @param boolean $isActive Active status
     * 
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get active status
     * 
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set created at
     * 
     * @param \DateTime $createdAt Created at
     * 
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created at
     * 
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set created by
     * 
     * @param string $createdBy Created by
     * 
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get created by
     * 
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updated at
     * 
     * @param \DateTime $updatedAt Updated at
     * 
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updated at
     * 
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updated by
     * 
     * @param string $updatedBy Updated by
     * 
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get update by
     * 
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Prepersist created at value
     * 
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Preupdate updated at value
     * 
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
