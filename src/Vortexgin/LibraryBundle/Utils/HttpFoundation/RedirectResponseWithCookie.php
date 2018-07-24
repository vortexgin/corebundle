<?php

namespace Vortexgin\LibraryBundle\Utils\HttpFoundation;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * RedirectResponseWithCookie represents an HTTP response doing a redirect and sending cookies.
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class RedirectResponseWithCookie extends RedirectResponse
{

    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string                                    $url     The URL to redirect to
     * @param integer                                   $status  The status code (302 by default)
     * @param Symfony\Component\HttpFoundation\Cookie[] $cookies An array of Cookie objects
     * 
     * @return void
     */
    public function __construct($url, $status = 302, $cookies = array ())
    {
        parent::__construct($url, $status);

        foreach ($cookies as $cookie) {
            if (!$cookie instanceof Cookie) {
                throw new \InvalidArgumentException(sprintf('Third parameter is not a valid Cookie object.'));
            }
            
            $this->headers->setCookie($cookie);
        }
    }
}