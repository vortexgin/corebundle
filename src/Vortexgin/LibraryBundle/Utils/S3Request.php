<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * S3 Request Http
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  MIT https://en.wikipedia.org/wiki/MIT_License
 * @link     http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 */
final class S3Request
{

    private $_verb, $_bucket, $_uri, $_resource = '', 
    $_parameters = array(), $_amzHeaders = array(), 
    $_headers = array(
        'Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => ''
    );

    public $fp = false, $size = 0, $data = false, $response;

    /**
     * Constructor
     *
     * @param string $verb   Verb
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * 
     * @return mixed
     */
    function __construct($verb, $bucket = '', $uri = '')
    {
        $this->_verb = $verb;
        $this->_bucket = strtolower($bucket);
        $this->_uri = $uri !== '' ? '/'.$uri : '/';
        if ($this->_bucket !== '') {
            $this->_bucket = explode('/', $this->_bucket);
            $this->_resource = '/'.$this->_bucket[0].$this->_uri;
            $this->_headers['Host'] = $this->_bucket[0].'.kilatstorage.com';
            $this->_bucket = implode('/', $this->_bucket);
        } else {
            $this->_headers['Host'] = 'kilatstorage.com';
            if (strlen($this->_uri) > 1)
                $this->_resource = '/'.$this->_bucket.$this->_uri;
            else $this->_resource = $this->_uri;
        }

        $this->_headers['Date'] = gmdate('D, d M Y H:i:s T');
        $this->response = new \STDClass;
        $this->response->error = false;
    }

    /**
     * Set request parameter
     *
     * @param string $key   Key
     * @param string $value Value
     * 
     * @return void
     */
    public function setParameter($key, $value)
    {
        $this->_parameters[$key] = $value;
    }

    /**
     * Set request header
     *
     * @param string $key   Key
     * @param string $value Value
     * 
     * @return void
     */
    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;
    }

    /**
     * Set x-amz-meta-* header
     *
     * @param string $key   Key
     * @param string $value Value
     * 
     * @return void
     */
    public function setAmzHeader($key, $value)
    {
        $this->_amzHeaders[$key] = $value;
    }

    /**
     * Get the S3 response
     *
     * @return mixed
     */
    public function getResponse()
    {
        $query = '';
        if (sizeof($this->_parameters) > 0) {
            $query = substr($this->_uri, -1) !== '?' ? '?' : '&';
            foreach ($this->_parameters as $var => $value) {
                if ($value == null || $value == '') {
                    $query .= $var.'&';
                } else {
                    $query .= $var.'='.$value.'&';
                }
            }

            $query = substr($query, 0, -1);
            $this->_uri .= $query;
            if (isset($this->_parameters['acl']) || !isset($this->_parameters['logging']))
                $this->_resource .= $query;
        }

        $url = (extension_loaded('openssl')?'https://':'http://').$this->_headers['Host'].$this->_uri;

        // Basic setup
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'S3/php');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);

        // Headers
        $headers = array(); $amz = array();
        foreach ($this->_amzHeaders as $header => $value)
            if (strlen($value) > 0) $headers[] = $header.': '.$value;
        foreach ($this->_headers as $header => $value)
            if (strlen($value) > 0) $headers[] = $header.': '.$value;
        foreach ($this->_amzHeaders as $header => $value)
            if (strlen($value) > 0) $amz[] = strToLower($header).':'.$value;
        $amz = (sizeof($amz) > 0) ? "\n".implode("\n", $amz) : '';
        
        // Authorization string
        $headers[] = 'Authorization: ' . S3::getSignature(
            $this->_verb."\n".
            $this->_headers['Content-MD5']."\n".
            $this->_headers['Content-Type']."\n".
            $this->_headers['Date'].$amz."\n".$this->_resource
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '_responseWriteCallback'));
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, '_responseHeaderCallback'));

        // Request types
        switch ($this->_verb) {
        case 'GET': 
            break;
        case 'PUT':
            if ($this->fp !== false) {
                curl_setopt($curl, CURLOPT_PUT, true);
                curl_setopt($curl, CURLOPT_INFILE, $this->fp);
                if ($this->size > 0)
                    curl_setopt($curl, CURLOPT_INFILESIZE, $this->size);
            } elseif ($this->data !== false) {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                if ($this->size > 0)
                    curl_setopt($curl, CURLOPT_BUFFERSIZE, $this->size);
            } else
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;
        case 'HEAD':
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($curl, CURLOPT_NOBODY, true);
            break;
        case 'DELETE':
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        default: 
            break;
        }
        
        // Execute, grab errors
        if (curl_exec($curl))
            $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        else
            $this->response->error = array(
                'code' => curl_errno($curl),
                'message' => curl_error($curl),
                'resource' => $this->_resource
            );
        @curl_close($curl);

        // Parse body into XML
        if ($this->response->error === false && isset($this->response->headers['type']) 
            && $this->response->headers['type'] == 'application/xml' && isset($this->response->body)
        ) {
            $this->response->body = simplexml_load_string($this->response->body);

            // Grab S3 errors
            if (!in_array($this->response->code, array(200, 204)) 
                && isset($this->response->body->Code, $this->response->body->Message)
            ) {
                $this->response->error = array(
                    'code' => (string)$this->response->body->Code,
                    'message' => (string)$this->response->body->Message
                );
                if (isset($this->response->body->Resource))
                    $this->response->error['resource'] = (string)$this->response->body->Resource;
                unset($this->response->body);
            }
        }

        // Clean up file resources
        if ($this->fp !== false && is_resource($this->fp)) fclose($this->fp);
        return $this->response;
    }

    /**
     * CURL write callback
     *
     * @param resource $curl CURL resource
     * @param string   $data Data
     * 
     * @return integer
     */
    private function _responseWriteCallback(&$curl, &$data)
    {
        $this->response->body = '';
        if ($this->response->code == 200 && $this->fp !== false)
            return fwrite($this->fp, $data);
        else
            $this->response->body .= $data;
        return strlen($data);
    }

    /**
     * CURL header callback
     *
     * @param resource $curl CURL resource
     * @param string   $data Data
     * 
     * @return integer
     */
    private function _responseHeaderCallback(&$curl, &$data)
    {
        if (($strlen = strlen($data)) <= 2) return $strlen;
        if (substr($data, 0, 4) == 'HTTP')
            $this->response->code = (int)substr($data, 9, 3);
        else {
            list($header, $value) = explode(': ', trim($data));
            if ($header == 'Last-Modified')
                $this->response->headers['time'] = strtotime($value);
            elseif ($header == 'Content-Length')
                $this->response->headers['size'] = (int)$value;
            elseif ($header == 'Content-Type')
                $this->response->headers['type'] = $value;
            elseif ($header == 'ETag')
                $this->response->headers['hash'] = substr($value, 1, -1);
            elseif (preg_match('/^x-amz-meta-.*$/', $header))
                $this->response->headers[$header] = is_numeric($value) ? (int)$value : $value;
        }

        return $strlen;
    }
}
