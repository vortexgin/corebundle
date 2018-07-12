<?php

namespace Vortexgin\LibraryBundle\Manager;

use Predis\Client;
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
     * Redis
     * 
     * @var \Predis\Client
     */
    private $_redis;

    /**
     * Construct
     * 
     * @param \Predis\Client $redis Redis client
     * 
     * @return void
     */
    public function __construct(Client $redis)
    {
        $this->_redis = $redis;
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
            return false;
        }
    }

    /**
     * Function to flush cache data on selected db
     * 
     * @return mixed
     */
    public function flushDB()
    {
        try {
            $this->deleteCache('*');
        } catch (Exception $e) {
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
            $this->flushDB();
        } catch (Exception $e) {
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
