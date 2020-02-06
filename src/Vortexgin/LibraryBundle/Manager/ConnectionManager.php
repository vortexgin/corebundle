<?php

namespace Vortexgin\LibraryBundle\Manager;

use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Psr7\Stream as Psr7Stream;
use Vortexgin\LibraryBundle\Utils\StringUtils;

/**
 * Connection manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class ConnectionManager
{

    /* @var $baseUrl mixed */
    public $baseUrl;

    protected $allowMethod = ['GET', 'POST', 'PATCH', 'DELETE'];

    /**
     * Construct
     * 
     * @param string $baseUrl Base connection url
     * 
     * @return void
     */
    public function __construct($baseUrl = '')
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Stream connection
     * 
     * @param string  $url     URL
     * @param string  $method  HTTP method
     * @param array   $param   HTTP parameter
     * @param array   $header  HTTP header
     * @param boolean $toArray Convert to array
     * 
     * @return mixed
     */
    public function stream($url, $method = 'GET', array $param = array(), array $header = array(), $toArray = false)
    {
        try {
            if (!in_array(strtoupper($method), $this->allowMethod))
                return false;

            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $_param = ['form_params' => $param];
            } elseif ($method == 'GET') {
                $_param = ['query' => $param];
            } else {
                $_param = ['json' => $param];
            }
            foreach ($header as $key => $value) {
                if (strtolower($key) == 'content-type') {
                    if (strtolower($value) == 'application/json') {
                        unset($_param);
                        $_param = ['json' => $param];
                        break;
                    } elseif (strtolower($value) == 'multipart/form-data') {
                        unset($_param);
                        $_param = array('multipart' => array());
                        foreach ($param as $key=>$value) {
                            if (is_array($value)) {
                                foreach ($value as $subKey=>$subValue) {
                                    $_param['multipart'][] = array(
                                        'name' => sprintf('%s[%s]', $key, $subKey), 
                                        'contents' => $subValue, 
                                    );        
                                }
                            } else {
                                $_param['multipart'][] = array(
                                    'name' => $key, 
                                    'contents' => $value, 
                                );    
                            }
                        }
                        if (array_key_exists('content-type', $header)) {
                            unset($header['content-type']);
                        } elseif (array_key_exists('Content-Type', $header)) {
                            unset($header['Content-Type']);
                        }

                        break;
                    }
                } elseif (strtolower($key) == 'cookie') {
                    $cookieJar = new \GuzzleHttp\Cookie\CookieJar(true);
                    foreach ($value as $cookie) {
                        $newCookie = \GuzzleHttp\Cookie\SetCookie::fromString($cookie);
                        $cookieJar->setCookie($newCookie);
                    }
                    $_param['cookies'] = $cookieJar;
                }
            }
            $options = array_merge($_param, array('headers' => $header));
    
            $client = new Client(
                [
                    'base_uri' => $this->baseUrl, 
                    'verify' => false
                ]
            );
            $apiResponse = $client->request($method, $this->baseUrl . $url, $options);
            $content = $apiResponse->getBody();
            if ($apiResponse->getBody() instanceof Stream) {
                $content = $apiResponse->getBody()->getContents();
            }

            if ($toArray === true && StringUtils::isJson($content)) {
                return json_decode($content, true);
            }

            $response = new JsonResponse();
            $response->setData(json_decode($content));
            $response->setStatusCode($apiResponse->getStatusCode());
            $headers = [
                'Server' => 'Apache/2.4.9 (Unix) PHP/5.5.14 OpenSSL/0.9.8za',
                'X-Powered-By' => 'PHP/5.5.14',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Credentials' => true,
                'Cache-Control' => 'no-cache',
                'X-Debug-Token' => '959f63',
                'X-Debug-Token-Link' => '/_profiler/959f63',
                'Content-Type' => 'application/json',
            ];
            foreach ($headers as $header => $headerValue) {
                $response->headers->set($header, $headerValue);
            }

            return $response;
        } catch (BadResponseException $e) {
            $content = $e->getResponse()->getBody();
            if ($e->getResponse()->getBody() instanceof Stream) {
                $content = $e->getResponse()->getBody()->getContents();
            }
            if ($e->getResponse()->getBody() instanceof Psr7Stream) {
                $content = $e->getResponse()->getBody()->getContents();
            }
            if ($toArray === true && StringUtils::isJson($content)) {
                return json_decode($content, true);
            }

            $response = new JsonResponse();
            $response->setData(json_decode($content));
            $response->setStatusCode($e->getResponse()->getStatusCode());
            $headers = [
                'Server' => 'Apache/2.4.9 (Unix) PHP/5.5.14 OpenSSL/0.9.8za',
                'X-Powered-By' => 'PHP/5.5.14',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Credentials' => true,
                'Cache-Control' => 'no-cache',
                'X-Debug-Token' => '959f63',
                'X-Debug-Token-Link' => '/_profiler/959f63',
                'Content-Type' => 'application/json',
            ];
            foreach ($headers as $header => $headerValue) {
                $response->headers->set($header, $headerValue);
            }

            return $response;
        } catch (\Exception $e) {
            return false;
        }
    }
}