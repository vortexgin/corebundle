<?php

namespace DS\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DS\CoreBundle\Entity\Base as BaseEntity;
use DS\LocationBundle\Entity\Kota;

/**
 * Soal
 *
 * @ORM\Table(name="mst_area")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Area extends BaseEntity {

    /**
     * @var Kota
     *
     * @ORM\ManyToOne(targetEntity="Kota")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="kota_id", referencedColumnName="id")
     * })
     */
    private $kota;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=6, nullable=true)
     */
    private $zipcode;

    /**
     * Set kota
     *
     * @param Kota $kota
     * @return Area
     */
    public function setKota(Kota $kota) {
        $this->kota = $kota;

        return $this;
    }

    /**
     * Get kota
     *
     * @return Kota
     */
    public function getKota() {
        return $this->kota;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Area
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

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return Area
     */
    public function setZipcode($zipcode) {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode() {
        return $this->zipcode;
    }
}
