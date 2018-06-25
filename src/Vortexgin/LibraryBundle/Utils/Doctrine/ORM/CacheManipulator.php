<?php

namespace Vortexgin\LibraryBundle\Utils\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;

/**
 * Cache manipulator functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils\Doctrine\ORM
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class CacheManipulator
{

    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Cache manager
     * 
     * @var \Doctrine\Common\Cache\Cache
     */
    private $_cache;

    /**
     * Construct
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager Entity Manager
     * 
     * @return void
     */
    public function __construct(EntityManager $entityManager)
    {
        $cache = $entityManager->getConfiguration()->getHydrationCacheImpl();
        $this->_cache = $cache ?: new ArrayCache();

        $this->_em = $entityManager;
    }

    /**
     * Function to generate cache key
     * 
     * @param string $value Value to convert
     * 
     * @return string
     */
    protected function generateCacheKey($value)
    {
        return md5($value);
    }

    /**
     * Function to save cache 
     * 
     * @param string $id       Unique id
     * @param object $object   Object of entity
     * @param int    $lifetime time of live
     * 
     * @return void
     */
    protected function saveCache($id, $object, $lifetime = 2700)
    {
        $this->_cache->save($this->generateCacheKey($id), $object, $lifetime);
    }

    /**
     * Function to fetch data from cache
     * 
     * @param string $id Unique id
     * 
     * @return mixed
     */
    protected function fetchFromCache($id)
    {
        $object = $this->_cache->fetch($this->generateCacheKey($id));

        if (! $object) {
            return null;
        }

        if (is_object($object)) {
            return $this->_em->merge($object);
        }

        return $object;
    }

    /**
     * Function to check cache is exists
     * 
     * @param string $id Unique id
     * 
     * @return mixed
     */
    protected function isExistCache($id)
    {
        return $this->_cache->contains($this->generateCacheKey($id));
    }

    /**
     * Function to delete cache
     * 
     * @param string $id Unique id
     * 
     * @return mixed
     */
    protected function deleteCache($id)
    {
        $this->_cache->delete($this->generateCacheKey($id));
    }
}