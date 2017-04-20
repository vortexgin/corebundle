<?php

namespace Vortexgin\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser{
    const ROLE_SUPER_ADMIN  = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN        = 'ROLE_ADMIN';
    const ROLE_USER         = 'ROLE_USER';
    const ROLE_MEMBER       = 'ROLE_MEMBER';
    const ROLE_MERCHANT     = 'ROLE_MERCHANT';

    public static $listRole = [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_USER, self::ROLE_MEMBER, self::ROLE_MERCHANT];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=100, nullable=true)
     */
    private $token;

    /**
     * Set token
     *
     * @param string $token
     * @return User
     */
    public function setToken($token) {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param string|null $salt
     */
    public function setSalt($salt)
    {
        // TODO: Implement setSalt() method.
    }

    /**
     * Sets the locking status of the user.
     *
     * @param boolean $boolean
     *
     * @return self
     */
    public function setLocked($boolean)
    {
        // TODO: Implement setLocked() method.
    }
}
?>
