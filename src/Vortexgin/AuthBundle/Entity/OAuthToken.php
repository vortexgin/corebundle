<?php

namespace Vortexgin\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vortexgin\CoreBundle\Entity\Base;
use Vortexgin\UserBundle\Entity\User;

/**
 * OAuthToken
 *
 * @ORM\Table(name="oauth_token")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class OAuthToken extends Base
{
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=100, nullable=false)
     */
    private $token;

    /**
     * @var \OauthClient
     *
     * @ORM\ManyToOne(targetEntity="\Vortexgin\AuthBundle\Entity\OAuthClient")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="oauth_client_id", referencedColumnName="id")
     * })
     */
    private $oauthClient;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\Vortexgin\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private $expires;

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param \Vortexgin\AuthBundle\Entity\OAuthClient $client
     * @return OAuthToken
     */
    public function setOAuthClient(OAuthClient $client = null)
    {
        $this->oauthClient = $client;
        return $this;
    }

    /**
     * @return \Vortexgin\AuthBundle\Entity\OAuthClient
     */
    public function getOAuthClient()
    {
        return $this->oauthClient;
    }

    /**
     * @param \Vortexgin\UserBundle\Entity\User $user
     * @return OAuthToken
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \Vortexgin\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \DateTime $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

}
