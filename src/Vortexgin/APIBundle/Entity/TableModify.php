<?php

namespace Vortexgin\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableModify.
 *
 * @category Entity
 * @package  Vortexgin\LibraryBundle\Entity
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 * 
 * @ORM\Table(name="table_modify")
 * @ORM\HasLifecycleCallbacks
 */
class TableModify extends Base
{
    /**
     * Container
     * 
     * @var string
     *
     * @ORM\Column(name="container", type="string", length=100, nullable=false)
     */
    protected $container;

    /**
     * Container ID
     * 
     * @var string
     *
     * @ORM\Column(name="container_id", type="string", length=100, nullable=false)
     */
    protected $containerId;

    /**
     * Updated value
     * 
     * @var string
     *
     * @ORM\Column(name="updated_value", type="text", nullable=false)
     */
    protected $updatedValue;

    /**
     * Set container
     * 
     * @param string $container Container
     * 
     * @return self
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container
     * 
     * @return string
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set container ID
     * 
     * @param string $containerId Container ID
     * 
     * @return self
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get container ID
     * 
     * @return string
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set updated value
     * 
     * @param array $updatedValue Updated value
     * 
     * @return self
     */
    public function setUpdatedValue(array $updatedValue)
    {
        $this->updatedValue = json_encode($updatedValue);

        return $this;
    }

    /**
     * Get updated value
     * 
     * @return array
     */
    public function getUpdatedValue()
    {
        return json_decode($this->updatedValue, true);
    }

}
