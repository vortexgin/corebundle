<?php

namespace Vortexgin\AuthBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vortexgin\CoreBundle\Manager\Manager;
use Vortexgin\UserBundle\Entity\User;
use Vortexgin\AuthBundle\Entity\OAuthToken;
use Vortexgin\AuthBundle\Entity\OAuthClient;

final class OAuthTokenManager extends Manager {
    /**
     *
     * @param ContainerInterface $container
     * @param string $class
     *
     */
    public function __construct(ContainerInterface $container, $class) {
        $container->enterScope('request');
        $container->set('request', new Request(), 'request');
        $this->container   = $container;

        $this->listSearchFields = [
                                    'id', 'token', 'username',
                                    'email', 'name', 'description',
                                    'secretKey', 'tokenExpires',
                                  ];
        $this->listOrderBy = array_merge($this->listSearchFields, []);
        parent::__construct($container->get('request'), $container->get('doctrine'), $class);
    }

    /**
     *
     * @param Vortexgin\CoreBundle\Entity\OAuthToken $object
     *
     * @return boolean
     *
     */
    protected function isSupportedObject($object) {
        if ($object instanceof OAuthToken) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param Vortexgin\CoreBundle\Entity\OAuthToken $object
     *
     * @return array
     *          - string id
     *          - string token
     *          - Vortexgin\CoreBundle\Entity\OAuthClient oauth_client
     *          - Vortexgin\CoreBundle\Entity\User user
     *          - DateTime expires
     *          - DateTime created_at
     *          - string created_by
     *          - DateTime updated_at
     *          - string updated_by
     *
     */
    public function serialize($object) {
        if(! $this->isSupportedObject($object))
            return false;

        $managerClient = $this->container->get('vortexgin.oauth.manager.client');
        return array(
            'id'            => $object->getId(),
            'token'         => $object->getToken(),
            'oauth_client'  => $object->getOAuthClient()?$managerClient->serialize($object->getOAuthClient()):null,
            'user'          => $object->getUser(),
            'expires'       => $object->getExpires(),
            'created_at'    => $object->getCreatedAt(),
            'created_by'    => $object->getCreatedBy(),
            'updated_at'    => $object->getUpdatedAt(),
            'updated_by'    => $object->getUpdatedBy(),
        );
    }

    protected function generateQuery($filter){
        $queryBuilder = parent::generateQuery($filter);
        $queryBuilder->join('er.oauthClient', 'oauthClient');
        $queryBuilder->andWhere('er.isActive = 1');

        return $queryBuilder;
    }

    public function get(array $param = array(), $orderBy = 'id', $orderSort = 'DESC', $page = 1, $count = 20){
        list($orderBy, $orderSort, $offset, $limit) = $this->generateDefaultParam($orderBy, $orderSort, $page, $count);

        $sql = $this->generateQuery($param);
        $sql->select('er')
            ->orderBy($orderBy, $orderSort)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this->getResult($sql);
    }

    public function count(array $param = array()){
        $sql = $this->generateQuery($param);
        $sql->select('count(er.id)');

        return $this->getOneOrNullResult($sql);
    }

    /**
     *
     * @param array $param
     *          - string token
     *          - Vortexgin\CoreBundle\Entity\OAuthClient oauth_client
     *          - Vortexgin\CoreBundle\Entity\User user
     *          - DateTime expires
     *
     * @return \Vortexgin\CoreBundle\Entity\OAuthToken
     *
     */
    public function insert(array $param = array()) {
        try {
            $obj = new OAuthToken();
            $obj->setToken($param['token'])
                ->setOauthClient($param['oauth_client'])
                ->setExpires($param['expires'])
                ->setCreatedBy($param['user_log'])
            ;

            if(array_key_exists('user', $param) && !empty($param['user']))
                $obj->setUser($param['user']);

            $this->manager->persist($obj);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param int $id
     * @param array $param
     *          - string token
     *          - Vortexgin\CoreBundle\Entity\OAuthClient oauth_client
     *          - Vortexgin\CoreBundle\Entity\User user
     *          - DateTime expires
     *
     * @return Vortexgin\CoreBundle\Entity\OAuthToken
     *
     */
    public function update($id, $param){
        try {
            $result = $this->get(array('id' => $id), 'id', 'DESC', 1, 1);

            if(!$this->isSupportedObject($result[0]))
                return false;

            $obj = $result[0];
            if(array_key_exists('token', $param) && !empty($param['token']))
                $obj->setToken($param['token']);
            if(array_key_exists('oauth_client', $param) && !empty($param['oauth_client']))
                $obj->setOauthClient($param['oauth_client']);
            if(array_key_exists('user', $param) && !empty($param['user']))
                $obj->setUser($param['user']);
            if(array_key_exists('expires', $param) && !empty($param['expires']))
                $obj->setExpires($param['expires']);

            $obj->setUpdatedBy($param['user_log']);
            $this->manager->persist($obj);
            $this->logModified($obj, $param['user_log']);
            $this->manager->flush();

            return $obj;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Function to generate token.
     *
     * @param OAuthClient     $client
     * @param User            $whoAmI
     * @param int             $lifetime   (minutes)
     *
     * @return OauthToken | JsonResponse
     */
    public function generateToken(OAuthClient $client, $whoAmI, $lifetime = 120) {
        try {
            if ($lifetime < 120) {
                return 'Lifetime should be at least 2 hours';
            }
            if ($lifetime > 43200) {
                return 'Lifetime cannot be greater than 30 days';
            }

            $newToken = sha1($client->getId() . $whoAmI->getId() . time());
            $expires = new \DateTime("+{$lifetime} minutes");
            $param = array(
                'token' => $newToken,
                'oauth_client' => $client,
                'expires' => $expires,
                'user_log' => 'SYSTEM',
            );
            $param['user'] = $whoAmI;
            $token = $this->insert($param);

            return $token;
        } catch (\Exception $e) {
            return false;
        }
    }

}
