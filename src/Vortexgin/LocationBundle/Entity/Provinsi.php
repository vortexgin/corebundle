<?php

namespace Vortexgin\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vortexgin\CoreBundle\Entity\Base as BaseEntity;

/**
 * User
 *
 * @ORM\Table(name="mst_provinsi")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Provinsi extends BaseEntity {

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Set code
     *
     * @param string $code
     * @return Provinsi
     */
    public function setCode($code) {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Provinsi
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}
