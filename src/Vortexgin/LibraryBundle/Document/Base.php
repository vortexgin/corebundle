<?php

namespace Vortexgin\LibraryBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert; 

/**
 * Base Entity for MongoDB.
 *
 * @category Document
 * @package  Vortexgin\LibraryBundle\Document
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 * 
 * @MongoDB\MappedSuperclass
 */
class Base
{
    /**
     * ID
     * 
     * @var int
     * 
     * @MongoDB\Id
     */
    protected $id;

    /**
     * Active status
     * 
     * @var boolean
     * 
     * @MongoDB\Field(type="boolean")
     * @Assert\NotBlank()
     */
    protected $isActive = true;

    /**
     * Created at
     * 
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     * @Assert\NotBlank()
     */
    protected $createdAt;

    /**
     * Created by
     * 
     * @var string
     *
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $createdBy;

    /**
     * Updated at
     * 
     * @var \DateTime
     *
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    /**
     * Updated by
     * 
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    protected $updatedBy;

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
}
