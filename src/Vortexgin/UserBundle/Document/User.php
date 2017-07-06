<?php

namespace DS\UserBundle\Document;

use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\Document(collection="user")
 */
class User extends BaseUser
{
    const ROLE_ADMIN  = 'ROLE_ADMIN';
    const ROLE_USER   = 'ROLE_USER';

    public static $listRole = [self::ROLE_ADMIN, self::ROLE_USER];

    /**
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;

    /**
    * @MongoDB\Field(type="string")
    * @Assert\Blank()
     */
    private $token;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\Blank()
     */
    protected $telegramId;

    /**
     * Set token
     *
     * @param string $token
     * @return Member
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $telegramId
     */
    public function setTelegramId($telegramId)
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelegramId()
    {
        return $this->telegramId;
    }

    /**
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }
}
