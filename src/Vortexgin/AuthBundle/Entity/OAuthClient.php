<?php

namespace Vortexgin\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vortexgin\CoreBundle\Entity\Base;

/**
 * OAuthClient
 *
 * @ORM\Table(name="oauth_client")
 * @ORM\Entity
 */
class OAuthClient extends Base {
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="secret_key", type="string", length=100, nullable=false)
     */
    private $secretKey;

    /**
     * @var integer
     *
     * @ORM\Column(name="token_expires", type="integer", nullable=false)
     */
    private $tokenExpires;

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function getSecretKey() {
        return $this->secretKey;
    }

    public function setTokenExpires($tokenExpires) {
        $this->tokenExpires = $tokenExpires;
        return $this;
    }

    public function getTokenExpires() {
        return $this->tokenExpires;
    }
}
