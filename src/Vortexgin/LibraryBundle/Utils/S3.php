<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Kilat Storage S3 PHP class
 * Version: 0.1.3
 *
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  MIT https://en.wikipedia.org/wiki/MIT_License
 * @link     http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 **/
class S3
{
    // ACL flags
    const ACL_PRIVATE = 'private';
    const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    const STORAGE_CLASS_STANDARD = 'STANDARD';
    const STORAGE_CLASS_RRS = 'REDUCED_REDUNDANCY';
    private static $__accessKey; // Kilat Storage Access key
    private static $__secretKey; // Kilat Storage Secret key

    /**
     * Constructor, used if you're not calling the class statically
     *
     * @param string $accessKey Access key
     * @param string $secretKey Secret key
     * 
     * @return void
     */
    public function __construct($accessKey = null, $secretKey = null)
    {
        if($accessKey !== null && $secretKey !== null)
            self::setAuth($accessKey, $secretKey);
    }

    /**
     * Set access information
     *
     * @param string $accessKey Access key
     * @param string $secretKey Secret key
     * 
     * @return void
     */
    public static function setAuth($accessKey, $secretKey)
    {
        self::$__accessKey = $accessKey;
        self::$__secretKey = $secretKey;
    }

    /**
     * Get a list of buckets
     *
     * @return mixed
     */
    public static function listBuckets()
    {
        $rest = new S3Request('GET', '', '');
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200)
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            trigger_error(sprintf("S3::listBuckets(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
            return false;
        }
        $results = array();
        $contents = simplexml_load_string($rest->body);
        if (isset($contents->Buckets)) {
            foreach ($contents->Buckets->Bucket as $bucket) {
                $results[] = array(
                    'name' => (string)$bucket->Name,
                    'time' => strtotime((string)$bucket->CreationDate),
                );
            }
        }

        return $results;
    }

    /**
     * Get contents for a bucket
     *
     * @param string $bucket Bucket name
     * 
     * @return mixed
     */
    public static function getBucket($bucket)
    {
        $rest = new S3Request('GET', $bucket, '');
        $response = $rest->getResponse();
        if ($response->error === false && $response->code !== 200)
            $response->error = array('code' => $response->code, 'message' => 'Unexpected HTTP status');
        if ($response->error !== false) {
            trigger_error(sprintf("S3::getBucket(): [%s] %s", $response->error['code'], $response->error['message']), E_USER_WARNING);
            return false;
        }
        $results = array();
        $contents = simplexml_load_string($response->body);
        if (isset($contents->Contents)) {
            foreach ($contents->Contents as $c) {
                $results[] = array(
                    'name' => (string)$c->Key,
                    'time' => strToTime((string)$c->LastModified),
                    'size' => (int)$c->Size,
                    'hash' => substr((string)$c->ETag, 1, -1)
                );
            }
        }

        return $results;
    }

    /**
     * Put a bucket
     *
     * @param string   $bucket Bucket name
     * @param constant $acl    ACL flag
     * 
     * @return boolean
     */
    public function putBucket($bucket, $acl = self::ACL_PRIVATE)
    {
        $rest = new S3Request('PUT', $bucket, '');
        $rest->setAmzHeader('x-amz-acl', $acl);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200)
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::putBucket({$bucket}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }

        return true;
    }

    /**
     * Delete an empty bucket
     *
     * @param string $bucket Bucket name
     * 
     * @return boolean
     */
    public function deleteBucket($bucket = '')
    {
        $rest = new S3Request('DELETE', $bucket);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 204)
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::deleteBucket({$bucket}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );

            return false;
        }

        return true;
    }

    /**
     * Create input info array for putObject()
     *
     * @param string $file   Input file
     * @param mixed  $md5sum Use MD5 hash (supply a string if you want to use your own)
     * 
     * @return mixed
     */
    public static function inputFile($file, $md5sum = true)
    {
        if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
            trigger_error('S3::inputFile(): Unable to open input file: '.$file, E_USER_WARNING);
            return false;
        }
        return array(
            'file' => $file, 
            'size' => filesize($file),
            'md5sum' => $md5sum !== false ? (is_string($md5sum) ? $md5sum : base64_encode(md5_file($file, true))) : ''
        );
    }

    /**
     * Use a resource for input
     *
     * @param string  $resource   Input file
     * @param integer $bufferSize Input byte size
     * @param string  $md5sum     MD5 hash to send (optional)
     * 
     * @return mixed
     */
    public static function inputResource(&$resource, $bufferSize, $md5sum = '')
    {
        if (!is_resource($resource) || $bufferSize <= 0) {
            trigger_error('S3::inputResource(): Invalid resource or buffer size', E_USER_WARNING);
            return false;
        }
        $input = array('size' => $bufferSize, 'md5sum' => $md5sum);
        $input['fp'] =& $resource;
        return $input;
    }

    /**
     * Put an object
     *
     * @param mixed    $input       Input data
     * @param string   $bucket      Bucket name
     * @param string   $uri         Object URI
     * @param constant $acl         ACL constant
     * @param array    $metaHeaders Array of x-amz-meta-* headers
     * @param string   $contentType Content type
     * 
     * @return boolean
     */
    public static function putObject($input, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = null)
    {
        // temporarily disable this line of code.
        //if ($input == false) return "yahoo";
        $rest = new S3Request('PUT', $bucket, $uri);
        if (is_string($input)) $input = array(
            'data' => $input, 'size' => strlen($input),
            'md5sum' => base64_encode(md5($input, true))
        );
        
        // Data
        if (isset($input['fp']))
            $rest->fp =& $input['fp'];
        elseif (isset($input['file']))
            $rest->fp = @fopen($input['file'], 'rb');
        elseif (isset($input['data']))
            $rest->data = $input['data'];

        // Content-Length (required)
        if (isset($input['size']) && $input['size'] > 0)
            $rest->size = $input['size'];
        else {
            if (isset($input['file']))
                $rest->size = filesize($input['file']);
            elseif (isset($input['data']))
                $rest->size = strlen($input['data']);
        }

        // Content-Type
        if ($contentType !== null)
            $input['type'] = $contentType;
        elseif (!isset($input['type']) && isset($input['file']))
            $input['type'] = self::getMimeType($input['file']);
        else
            $input['type'] = 'application/octet-stream';
        
        // We need to post with the content-length and content-type, MD5 is optional
        if ($rest->size > 0 && ($rest->fp !== false || $rest->data !== false)) {
            $rest->setHeader('Content-Type', $input['type']);
            if (isset($input['md5sum'])) $rest->setHeader('Content-MD5', $input['md5sum']);
            $rest->setAmzHeader('x-amz-acl', $acl);
            foreach ($metaHeaders as $h => $v) $rest->setAmzHeader('x-amz-meta-'.$h, $v);
            $rest->getResponse();
        } else
            $rest->response->error = array('code' => 0, 'message' => 'Missing input parameters');
        
        if ($rest->response->error === false && $rest->response->code !== 200)
            $rest->response->error = array('code' => $rest->response->code, 'message' => 'Unexpected HTTP status');
        if ($rest->response->error !== false) {
            trigger_error(sprintf("S3::putObject(): [%s] %s", $rest->response->error['code'], $rest->response->error['message']), E_USER_WARNING);
            return false;
        }
        
        return true;
    }

    /**
     * Puts an object from a file (legacy function)
     *
     * @param string   $file        Input file path
     * @param string   $bucket      Bucket name
     * @param string   $uri         Object URI
     * @param constant $acl         ACL constant
     * @param array    $metaHeaders Array of x-amz-meta-* headers
     * @param string   $contentType Content type
     * 
     * @return boolean
     */
    public static function putObjectFile($file, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = null)
    {
        return self::putObject(S3::inputFile($file), $bucket, $uri, $acl, $metaHeaders, $contentType);
    }

    /**
     * Put an object from a string (legacy function)
     *
     * @param string   $string      Input data
     * @param string   $bucket      Bucket name
     * @param string   $uri         Object URI
     * @param constant $acl         ACL constant
     * @param array    $metaHeaders Array of x-amz-meta-* headers
     * @param string   $contentType Content type
     * 
     * @return boolean
     */
    public function putObjectString($string, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = 'text/plain')
    {
        return self::putObject($string, $bucket, $uri, $acl, $metaHeaders, $contentType);
    }

    /**
     * Get an object
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * @param mixed  $saveTo Filename or resource to write to
     * 
     * @return mixed
     */
    public static function getObject($bucket = '', $uri = '', $saveTo = false)
    {
        $rest = new S3Request('GET', $bucket, $uri);
        if ($saveTo !== false) {
            if (is_resource($saveTo))
                $rest->fp =& $saveTo;
            else
                if (($rest->fp = @fopen($saveTo, 'wb')) == false)
                    $rest->response->error = array('code' => 0, 'message' => 'Unable to open save file for writing: '.$saveTo);
        }
        if ($rest->response->error === false) $rest->getResponse();
        if ($rest->response->error === false && $rest->response->code !== 200)
            $rest->response->error = array('code' => $rest->response->code, 'message' => 'Unexpected HTTP status');
        if ($rest->response->error !== false) {
            trigger_error(
                sprintf(
                    "S3::getObject({$bucket}, {$uri}): [%s] %s",
                    $rest->response->error['code'], $rest->response->error['message']
                ), E_USER_WARNING
            );
            return false;
        }
        $rest->file = realpath($saveTo);

        return $rest->response;
    }

    /**
     * Get object information
     *
     * @param string  $bucket     Bucket name
     * @param string  $uri        Object URI
     * @param boolean $returnInfo Return response information
     * 
     * @return mixed
     */
    public static function getObjectInfo($bucket = '', $uri = '', $returnInfo = true)
    {
        $rest = new S3Request('HEAD', $bucket, $uri);
        $rest = $rest->getResponse();
        if ($rest->error === false && ($rest->code !== 200 && $rest->code !== 404))
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::getObjectInfo({$bucket}, {$uri}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }

        return $rest->code == 200 ? $returnInfo ? $rest->headers : true : false;
    }

    /**
     * Copy an object
     *
     * @param string   $srcBucket      Source bucket name
     * @param string   $srcUri         Source object URI
     * @param string   $bucket         Destination bucket name
     * @param string   $uri            Destination object URI
     * @param constant $acl            ACL constant
     * @param array    $metaHeaders    Optional array of x-amz-meta-* headers
     * @param array    $requestHeaders Optional array of request headers (content type, disposition, etc.)
     * @param constant $storageClass   Storage class constant
     * 
     * @return mixed
     */
    public static function copyObject($srcBucket, $srcUri, $bucket, $uri, $acl = self::ACL_PUBLIC_READ, $metaHeaders = array(), $requestHeaders = array(), $storageClass = self::STORAGE_CLASS_STANDARD)
    {
        $rest = new S3Request('PUT', $bucket, $uri);
        $rest->setHeader('Content-Length', 0);
        foreach ($requestHeaders as $h => $v) $rest->setHeader($h, $v);
        foreach ($metaHeaders as $h => $v) $rest->setAmzHeader('x-amz-meta-'.$h, $v);
        if ($storageClass !== self::STORAGE_CLASS_STANDARD)
            $rest->setAmzHeader('x-amz-storage-class', $storageClass);
        $rest->setAmzHeader('x-amz-acl', $acl);
        $rest->setAmzHeader('x-amz-copy-source', sprintf('/%s/%s', $srcBucket, rawurlencode($srcUri)));
        if (sizeof($requestHeaders) > 0 || sizeof($metaHeaders) > 0)
            $rest->setAmzHeader('x-amz-metadata-directive', 'REPLACE');
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200)
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            self::__triggerError(
                sprintf(
                    "S3::copyObject({$srcBucket}, {$srcUri}, {$bucket}, {$uri}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), __FILE__, __LINE__
            );
            return false;
        }

        $results = array();
        $contents = simplexml_load_string($rest->body);
        if (isset($contents->LastModified, $contents->ETag)) {
            $results = array(
                'time' => strToTime((string)$contents->LastModified),
                'hash' => substr((string)$contents->ETag, 1, -1)
            );
        }

        return $results;
    }

    /**
     * Set logging for a bucket
     *
     * @param string $bucket       Bucket name
     * @param string $targetBucket Target bucket (where logs are stored)
     * @param string $targetPrefix Log prefix (e,g; domain.com-)
     * 
     * @return boolean
     */
    public static function setBucketLogging($bucket, $targetBucket, $targetPrefix)
    {
        $dom = new DOMDocument;
        $bucketLoggingStatus = $dom->createElement('BucketLoggingStatus');
        $bucketLoggingStatus->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
        $loggingEnabled = $dom->createElement('LoggingEnabled');
        $loggingEnabled->appendChild($dom->createElement('TargetBucket', $targetBucket));
        $loggingEnabled->appendChild($dom->createElement('TargetPrefix', $targetPrefix));
        // TODO: Add TargetGrants
        $bucketLoggingStatus->appendChild($loggingEnabled);
        $dom->appendChild($bucketLoggingStatus);
        $rest = new S3Request('PUT', $bucket, '');
        $rest->setParameter('logging', null);
        $rest->data = $dom->saveXML();
        $rest->size = strlen($rest->data);
        $rest->setHeader('Content-Type', 'application/xml');
        $rest = $rest->getResponse();

        if ($rest->error === false && $rest->code !== 200)
            $rest->error = array(
                'code' => $rest->code, 
                'message' => 'Unexpected HTTP status'
            );
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::setBucketLogging({$bucket}, {$uri}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }

        return true;
    }

    /**
     * Get logging status for a bucket
     *
     * This will return false if logging is not enabled.
     * Note: To enable logging, you also need to grant write access to the log group
     *
     * @param string $bucket Bucket name
     * 
     * @return mixed
     */
    public static function getBucketLogging($bucket = '')
    {
        $rest = new S3Request('GET', $bucket, '');
        $rest->setParameter('logging', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = array(
                'code' => $rest->code, 
                'message' => 'Unexpected HTTP status'
            );
        }
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::getBucketLogging({$bucket}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }
        if (!isset($rest->body->LoggingEnabled)) {
            return false; // No logging
        } 

        return array(
            'targetBucket' => (string)$rest->body->LoggingEnabled->TargetBucket,
            'targetPrefix' => (string)$rest->body->LoggingEnabled->TargetPrefix,
        );
    }

    /**
     * Set object or bucket Access Control Policy
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * @param array  $acp    Access Control Policy Data
     * 
     * @return boolean
     */
    public static function setAccessControlPolicy($bucket, $uri = '', $acp = array())
    {
        $dom = new DOMDocument;
        $dom->formatOutput = true;
        $accessControlPolicy = $dom->createElement('AccessControlPolicy');
        $accessControlList = $dom->createElement('AccessControlList');
        // It seems the owner has to be passed along too
        $owner = $dom->createElement('Owner');
        $owner->appendChild($dom->createElement('ID', $acp['owner']['id']));
        $owner->appendChild(
            $dom->createElement('DisplayName', $acp['owner']['name'])
        );
        $accessControlPolicy->appendChild($owner);

        foreach ($acp['acl'] as $g) {
            $grant = $dom->createElement('Grant');
            $grantee = $dom->createElement('Grantee');
            $grantee->setAttribute(
                'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance'
            );
            if (isset($g['id'])) { // CanonicalUser (DisplayName is omitted)
                $grantee->setAttribute('xsi:type', 'CanonicalUser');
                $grantee->appendChild($dom->createElement('ID', $g['id']));
            } elseif (isset($g['email'])) { // AmazonCustomerByEmail
                $grantee->setAttribute('xsi:type', 'AmazonCustomerByEmail');
                $grantee->appendChild(
                    $dom->createElement('EmailAddress', $g['email'])
                );
            } elseif ($g['type'] == 'Group') { // Group
                $grantee->setAttribute('xsi:type', 'Group');
                $grantee->appendChild($dom->createElement('URI', $g['uri']));
            }
            $grant->appendChild($grantee);
            $grant->appendChild($dom->createElement('Permission', $g['permission']));
            $accessControlList->appendChild($grant);
        }

        $accessControlPolicy->appendChild($accessControlList);
        $dom->appendChild($accessControlPolicy);
        $rest = new S3Request('PUT', $bucket, '');
        $rest->setParameter('acl', null);
        $rest->data = $dom->saveXML();
        $rest->size = strlen($rest->data);
        $rest->setHeader('Content-Type', 'application/xml');
        $rest = $rest->getResponse();

        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = array(
                'code' => $rest->code, 
                'message' => 'Unexpected HTTP status'
            );
        }
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::setAccessControlPolicy({$bucket}, {$uri}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }

        return true;
    }

    /**
     * Get object or bucket Access Control Policy
     *
     * Currently this will trigger an error if there is no ACL on an object
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * 
     * @return mixed
     */
    public static function getAccessControlPolicy($bucket, $uri = '')
    {
        $rest = new S3Request('GET', $bucket, $uri);
        $rest->setParameter('acl', null);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 200) {
            $rest->error = array(
                'code' => $rest->code, 
                'message' => 'Unexpected HTTP status'
            );
        }
        if ($rest->error !== false) {
            trigger_error(
                sprintf(
                    "S3::getAccessControlPolicy({$bucket}, {$uri}): [%s] %s",
                    $rest->error['code'], $rest->error['message']
                ), E_USER_WARNING
            );
            return false;
        }

        $acp = array();
        $contents = simplexml_load_string($rest->body);
        if (isset($contents->Owner, $contents->Owner->ID, $contents->Owner->DisplayName)) {
            $acp['owner'] = array(
                'id' => (string)$contents->Owner->ID, 'name' => (string)$contents->Owner->DisplayName
            );
        }
        
        if (isset($contents->AccessControlList)) {
            $acp['acl'] = array();
            foreach ($contents->AccessControlList->Grant as $grant) {
                foreach ($grant->Grantee as $grantee) {
                    if (isset($grantee->ID, $grantee->DisplayName)) // CanonicalUser
                        $acp['acl'][] = array(
                            'type' => 'CanonicalUser',
                            'id' => (string)$grantee->ID,
                            'name' => (string)$grantee->DisplayName,
                            'permission' => (string)$grant->Permission
                        );
                    elseif (isset($grantee->EmailAddress)) // AmazonCustomerByEmail
                        $acp['acl'][] = array(
                            'type' => 'AmazonCustomerByEmail',
                            'email' => (string)$grantee->EmailAddress,
                            'permission' => (string)$grant->Permission
                        );
                    elseif (isset($grantee->URI)) // Group
                        $acp['acl'][] = array(
                            'type' => 'Group',
                            'uri' => (string)$grantee->URI,
                            'permission' => (string)$grant->Permission
                        );
                    else continue;
                }
            }
        }

        return $acp;
    }

    /**
     * Delete an object
     *
     * @param string $bucket Bucket name
     * @param string $uri    Object URI
     * 
     * @return mixed
     */
    public static function deleteObject($bucket = '', $uri = '')
    {
        $rest = new S3Request('DELETE', $bucket, $uri);
        $rest = $rest->getResponse();
        if ($rest->error === false && $rest->code !== 204)
            $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
        if ($rest->error !== false) {
            trigger_error(sprintf("S3::deleteObject(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
            return false;
        }
        return true;
        // For debugging only.
        //return $rest->error['message'];
    }

    /**
     * Get MIME type for file
     *
     * @param string $file File path
     * 
     * @internal Used to get mime types
     * 
     * @return string
     */
    public static function getMimeType(&$file)
    {
        $type = false;
        // Fileinfo documentation says fileinfo_open() will use the
        // MAGIC env var for the magic file
        $finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC']);
        if (extension_loaded('fileinfo') 
            && isset($_ENV['MAGIC']) 
            && $finfo !== false
        ) {
            if (($type = finfo_file($finfo, $file)) !== false) {
                // Remove the charset and grab the last content-type
                $type = explode(' ', str_replace('; charset=', ';charset=', $type));
                $type = array_pop($type);
                $type = explode(';', $type);
                $type = array_shift($type);
            }
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type'))
            // If anyone is still using mime_content_type()
            $type = mime_content_type($file);
        
        if ($type !== false && strlen($type) > 0) return $type;
        // Otherwise do it the old fashioned way

        static $exts = array(
            'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png',
            'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon',
            'swf' => 'application/x-shockwave-flash', 'pdf' => 'application/pdf',
            'zip' => 'application/zip', 'gz' => 'application/x-gzip',
            'tar' => 'application/x-tar', 'bz' => 'application/x-bzip',
            'bz2' => 'application/x-bzip2', 'txt' => 'text/plain',
            'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
            'xml' => 'text/xml', 'xsl' => 'application/xsl+xml',
            'ogg' => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
            'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
            'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php'
        );
        $ext = strToLower(pathInfo($file, PATHINFO_EXTENSION));

        return isset($exts[$ext]) ? $exts[$ext] : 'application/octet-stream';
    }

    /**
     * Generate the auth string: "AWS AccessKey:Signature"
     *
     * This uses the hash extension if loaded
     *
     * @param string $string String to sign
     * 
     * @internal Signs the request
     * 
     * @return string
     */
    public static function getSignature($string)
    {
        $contentSignature = extension_loaded('hash') ?
        hash_hmac('sha1', $string, self::$__secretKey, true) : pack(
            'H*', sha1(
                (str_pad(self::$__secretKey, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
                pack(
                    'H*', sha1(
                        (str_pad(self::$__secretKey, 64, chr(0x00)) ^
                        (str_repeat(chr(0x36), 64))) . $string
                    )
                )
            )
        );

        return 'AWS '.self::$__accessKey.':'.base64_encode($contentSignature);
    }
}
