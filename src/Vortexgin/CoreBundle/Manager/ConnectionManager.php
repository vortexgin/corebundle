<?php

namespace Vortexgin\CoreBundle\Manager;

use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Psr7\Stream as Psr7Stream;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Vortexgin\CoreBundle\Util\StringUtils;

class ConnectionManager{

  /* @var $baseUrl mixed */
  private $baseUrl;

  protected $allowMethod = ['GET', 'POST', 'PATCH', 'DELETE'];

  public function __construct($baseUrl = ''){
    $this->baseUrl = $baseUrl;
  }

  public function stream($url, $method = 'GET', array $_param = array(), array $header = array(), $toArray = false){
    try{
        if(!in_array($method, $this->allowMethod))
            return false;
        
        $param = array();
        if(in_array($method, ['POST', 'PATCH'])){
          $param = ['form_params' => $_param];
        }else{
          $param = ['query' => $_param];
        }
        if(count($header) > 0){
          $param['headers'] =  $header;
        }

        $client = new Client();
        $apiResponse = $client->request($method, $this->baseUrl.$url, $param);
        $content = $apiResponse->getBody();
        if($apiResponse->getBody() instanceof Stream){
            $content = $apiResponse->getBody()->getContents();
        }elseif($content instanceof Psr7Stream){
          $stream = (string) $content;
          $content = $content->getContents();
          if(empty($content)){
            $content = $stream;
          }
        }
        
        if($toArray === true && StringUtils::isJson($content)){
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
    }catch(\Exception $e){
      return false;
    }
  }
}
?>
