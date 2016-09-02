<?php

namespace Vortexgin\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vortexgin\CoreBundle\Entity\Base as BaseEntity;
use Vortexgin\LocationBundle\Entity\Provinsi;

/**
 * Soal
 *
 * @ORM\Table(name="mst_kota")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Kota extends BaseEntity {

    /**
     * @var Provinsi
     *
     * @ORM\ManyToOne(targetEntity="Provinsi")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provinsi_id", referencedColumnName="id")
     * })
     */
    private $provinsi;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Set provinsi
     *
     * @param Provinsi $provinsi
     * @return Kota
     */
    public function setProvinsi(Provinsi $provinsi) {
        $this->provinsi = $provinsi;

        return $this;
    }

    /**
     * Get provinsi
     *
     * @return Provinsi
     */
    public function getProvinsi() {
        return $this->provinsi;
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
