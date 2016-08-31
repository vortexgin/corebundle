<?php

namespace Vortexgin\CoreBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Codeception\Lib\Driver\Redis;
use Vortexgin\CoreBundle\Util\String;

/**
 * RedisAction cache operation
 *
 * @author Tommy Dian P
 * @created 2015-07-22
 */
class RedisManager {

    private $generalKey = [
      '_format', 'access_token', 'lang',
      'app_id', 'secret_key', 'api',
      'query_string', 'utm_campaign', 'utm_source',
      'utm_medium',
    ];
    private $listDB = [
      'snc_redis.default', 'snc_redis.session', 'snc_redis.monolog',
      'snc_redis.cache', 'snc_redis.metadata', 'snc_redis.result',
    ];
    private $defaultDB = 'snc_redis.default';
    private $redis;
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->switchDB($this->defaultDB);
    }

    public function getData($key, $param) {
        try {
            $data = null;

            if (is_array($param))
                $key .= $this->generateAdditionalKey($param);
            if ($this->isExists($key))
                $data = $this->getCache($key);

            return $data;
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function getCache($key) {
        try {
            if ($this->isExists($key)) {
                $ttl = $this->redis->ttl($key);
                if($ttl > 0){
                    $value = $this->redis->get($key);
                    if (String::isJson($value)) {
                        $value = json_decode($value, true);
                    }
                    return $value;
                }
            }
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function setCache($key, $value, $lifetime=7200) {
        try {
            $this->redis->del($key);

            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->redis->append($key, $value);
            $this->redis->expire($key, $lifetime);
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function updateLifetime($key, $lifetime = 7200){
        try {
            if ($this->isExists($key)) {
                $this->redis->expire($key, $lifetime);
            }
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function getKeyList($keyword){
      try {
        return $this->redis->keys($keyword);
      } catch (Exception $e) {
        $this->container->get('logger')->error($e->getMessage());
        $this->container->get('logger')->error($e->getTraceAsString());
      }
    }

    public function isExists($key) {
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function deleteCache($keyword) {
        try {
            $listKey = $this->redis->keys($keyword);
            if (count($listKey) > 0) {
                for ($index = 0; $index < count($listKey); $index++) {
                    $this->redis->del($listKey[$index]);
                    $this->redis->persist($listKey[$index]);
                }
            }
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function switchDB($db) {
        try {
            if (!in_array($db, $this->listDB))
                return false;

            $this->redis = $this->container->get($db);
        } catch (Exception $e) {
            return false;
        }
    }

    public function flushDB($db) {
        try {
            if($this->switchDB($db)){
                $this->deleteCache('*');
                $this->switchDB($this->defaultDB);
            }
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function flushAll() {
        try {
            foreach($this->listDB as $db){
              $this->flushDB($db);
            }

            $this->switchDB($this->defaultDB);
        } catch (Exception $e) {
          $this->container->get('logger')->error($e->getMessage());
          $this->container->get('logger')->error($e->getTraceAsString());
        }
    }

    public function generateAdditionalKey(array $param = array()){
      $addKey = null;
      if (is_array($param)) {
          $addKey = '';
          foreach ($param as $keys => $value) {
              if (!in_array($keys, $this->generalKey)) {
                  $addKey .= '|' . $keys . '=' . $value;
              }
          }
      }

      return $addKey;
    }

}
