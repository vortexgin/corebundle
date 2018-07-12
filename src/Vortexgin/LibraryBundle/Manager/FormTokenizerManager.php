<?php

namespace Vortexgin\LibraryBundle\Manager;

/**
 * Form tokenizer manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class FormTokenizerManager
{

    private $_cacheManager = null;

    /**
     * Construct
     * 
     * @param \Vortexgin\LibraryBundle\Manager\RedisManager $cacheManager Cache manager
     * 
     * @return void
     */
    public function __construct($cacheManager) 
    {
        $this->_cacheManager = $cacheManager;
    }

    /**
     * Function to generate token
     * 
     * @param string $provider Form provider
     * 
     * @return string
     */
    public function generateToken($provider='') 
    {
        $formTokenKey = SHA1($provider.date('YmdHis').str_replace(' ', '', microtime()));
        $this->_cacheManager->setCache($formTokenKey, 0, 300);
        return $formTokenKey;
    }

    /**
     * Function to validate token
     * 
     * @param string $formTokenKey Form token key
     * 
     * @return boolean
     */
    public function validateToken($formTokenKey)
    {
        $data = $this->_cacheManager->getData($formTokenKey, null);
        if ($data === null) {
            return false;
        }
        if ($data == 1) {
            return false;
        }

        return true;
    }

    /**
     * Function to expiring token key
     * 
     * @param string $formTokenKey Form token key
     * 
     * @return void
     */
    public function useToken($formTokenKey) 
    {
        $this->_cacheManager->setCache($formTokenKey, 1, 1);
        $this->_cacheManager->deleteCache($formTokenKey);
    }
}