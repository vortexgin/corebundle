<?php

namespace Vortexgin\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableModify.
 *
 * @ORM\Table(name="table_modify")
 * @ORM\Entity(repositoryClass="ORORI\CoreBundle\Repository\TableModifyRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TableModify
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="container", type="string", length=100, nullable=false)
     */
    private $container;

    /**
     * @var string
     *
     * @ORM\Column(name="container_id", type="string", length=100, nullable=false)
     */
    private $containerId;

    /**
     * @var string
     *
     * @ORM\Column(name="updated_value", type="text", nullable=false)
     */
    private $updatedValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", length=100, nullable=false)
     */
    private $createdBy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    public function getContainerId()
    {
        return $this->containerId;
    }

    public function setUpdatedValue(array $updatedValue)
    {
        $this->updatedValue = json_encode($updatedValue);

        return $this;
    }

    public function getUpdatedValue()
    {
        return json_decode($this->updatedValue);
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedBy($val)
    {
        $this->createdBy = $val;

        return $this;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }
}
