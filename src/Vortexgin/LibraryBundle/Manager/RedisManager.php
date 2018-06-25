<?php

namespace Vortexgin\LibraryBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Codeception\Lib\Driver\Redis;
use Vortexgin\LibraryBundle\Utils\Validator;
use Vortexgin\LibraryBundle\Utils\StringUtils;

/**
 * Redis Manager
 *
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class RedisManager
{

    /**
     * General key
     * 
     * @var array
     */
    private $_generalKey = [
      '_format', 'access_token', 'lang',
      'app_id', 'secret_key', 'api',
      'query_string', 'utm_campaign', 'utm_source',
      'utm_medium',
    ];

    /**
     * List DB
     * 
     * @var array
     */
    private $_listDB = [
      'snc_redis.default', 'snc_redis.session', 'snc_redis.monolog',
      'snc_redis.cache', 'snc_redis.metadata', 'snc_redis.result',
      'snc_redis.query', 'snc_redis.page', 'snc_redis.revive',
      'snc_redis.ga',
    ];

    /**
     * Default DB
     * 
     * @var string
     */
    private $_defaultDB = 'snc_redis.default';

    /**
     * Redis
     * 
     * @var \Codeception\Lib\Driver\Redis
     */
    private $_redis;

    /**
     * Container
     * 
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $_container;

    /**
     * Construct
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container interface
     * 
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;

        $this->switchDB($this->_defaultDB);
    }

    /**
     * Function get cache data
     * 
     * @param string $key   Cache key
     * @param array  $param Additional parameter
     * 
     * @return mixed
     */
    public function getData($key, array $param = array())
    {
        try {
            if (is_array($param) && !empty($param)) {
                $key .= $this->generateAdditionalKey($param);
            }
            if ($this->isExists($key)) {
                return $this->getCache($key);
            }
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to get cache
     * 
     * @param string $key Cache key
     * 
     * @return mixed
     */
    public function getCache($key)
    {
        try {
            if ($this->isExists($key)) {
                $ttl = $this->_redis->ttl($key);
                if ($ttl > 0) {
                    $value = $this->_redis->get($key);
                    if (StringUtils::isJson($value)) {
                        $value = json_decode($value, true);
                    }
                    return $value;
                }
            }
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to set cache
     * 
     * @param string $key      Cache key
     * @param mixed  $value    Value to cached
     * @param int    $lifetime Cache lifetime
     * 
     * @return mixed
     */
    public function setCache($key, $value, $lifetime=7200)
    {
        try {
            if ($this->isExists($key)) {
                $this->_redis->del($key);
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->_redis->append($key, $value);
            $this->_redis->expire($key, $lifetime);
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to update cache lifetime
     * 
     * @param string $key      Cache key
     * @param int    $lifetime Cache lifetime
     * 
     * @return mixed 
     */
    public function updateLifetime($key, $lifetime = 7200)
    {
        try {
            if ($this->isExists($key)) {
                $this->_redis->expire($key, $lifetime);
            }
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to get key list
     * 
     * @param string $keyword Keyword to search
     * 
     * @return mixed
     */
    public function getKeyList($keyword)
    {
        try {
            return $this->_redis->keys($keyword);
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to check if cache key is exists
     * 
     * @param string $key Cache key
     * 
     * @return mixed
     */
    public function isExists($key)
    {
        try {
            return $this->_redis->exists($key);
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to delete cache
     * 
     * @param string $keyword Cache keyword to delete
     * 
     * @return mixed
     */
    public function deleteCache($keyword)
    {
        try {
            $listKey = $this->_redis->keys($keyword);
            if (count($listKey) > 0) {
                for ($index = 0; $index < count($listKey); $index++) {
                    $this->_redis->del($listKey[$index]);
                    $this->_redis->persist($listKey[$index]);
                }
            }
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to switch DB
     * 
     * @param string $db Cache DB
     * 
     * @return mixed
     */
    public function switchDB($db)
    {
        try {
            if (!in_array($db, $this->_listDB))
                return false;

            $this->_redis = $this->_container->get($db);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Function to flush cache data on selected db
     * 
     * @param string $db DB to flush
     * 
     * @return mixed
     */
    public function flushDB($db)
    {
        try {
            if ($this->switchDB($db)) {
                $this->deleteCache('*');
                $this->switchDB($this->_defaultDB);
            }
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to flush all data
     * 
     * @return mixed
     */
    public function flushAll()
    {
        try {
            foreach ($this->_listDB as $db) {
                $this->flushDB($db);
            }

            $this->switchDB($this->_defaultDB);
        } catch (Exception $e) {
            $this->_container->get('logger')->error($e->getMessage());
            $this->_container->get('logger')->error($e->getTraceAsString());

            return false;
        }
    }

    /**
     * Function to generate additional unique key
     * 
     * @param array $param Parameter
     * 
     * @return string
     */
    public function generateAdditionalKey(array $param = array())
    {
        $addKey = null;
        if (is_array($param)) {
            $addKey = '';
            foreach ($param as $keys => $value) {
                if (!in_array($keys, $this->_generalKey)) {
                    $addKey .= '|' . $keys . '=' . $value;
                }
            }
        }

        return $addKey;
    }

}
