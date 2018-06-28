<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * StatusCodes provides named constants for
 * HTTP protocol status codes. Written for the
 * Recess Framework (http://www.recessframework.com/)
 *
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   Kris Jordan <vortexgin@gmail.com>
 * @license  MIT https://en.wikipedia.org/wiki/MIT_License
 * @link     http://www.recessframework.com/
 */
abstract class HttpStatusHelper
{
    // [Informational 1xx]
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    // [Successful 2xx]
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    // [Redirection 3xx]
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_UNUSED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    // [Client Error 4xx]
    const ERROR_CODES_BEGIN_AT = 400;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    // [Server Error 5xx]
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    // [entity status ]
    const ENTITY_DELETED = -1;
    const ENTITY_ACTIVE = 1;

    public static $messages = array(
        // [Informational 1xx]
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        102 => '102 Server has received and is processing the request',
        103 => '103 Resume aborted PUT or POST requests',
        122 => '122 URI is longer than a maximum of 2083 characters',
        // [Successful 2xx]
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        207 => '207 XML, can contain multiple separate responses',
        208 => '208 Results previously returned',
        226 => '226 Request fulfilled, response is instance-manipulations',
        // [Redirection 3xx]
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        308 => '308 Connect again to a different URI using the same method',
        // [Client Error 4xx]
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        // [Server Error 5xx]
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported'
    );

    /**
     * Get http header of a HTTP status
     *
     * @param int $code HTTP status code
     * 
     * @return string
     */
    public static function httpHeaderFor( $code )
    {
        return 'HTTP/1.1 ' . self::$messages[$code];
    }

    /**
     * Get http message of a HTTP status
     *
     * @param int $code HTTP status code
     * 
     * @return string
     */
    public static function getMessageForCode( $code )
    {
        return self::$messages[$code];
    }

    /**
     * Does a status is an error
     *
     * @param int $code HTTP status code
     * 
     * @return bool
     */
    public static function isError( $code )
    {
        return is_numeric($code) && $code >= self::HTTP_BAD_REQUEST;
    }

    /**
     * Is content-body allowed
     *
     * @param int $code HTTP code
     * 
     * @return bool
     */
    public static function canHaveBody( $code )
    {
        // True if not in 100s
        return
            ($code < self::HTTP_CONTINUE || $code >= self::HTTP_OK) && // and not 204 NO CONTENT
            $code != self::HTTP_NO_CONTENT && // and not 304 NOT MODIFIED
            $code != self::HTTP_NOT_MODIFIED;
    }

    /**
     * Is http response
     *
     * @param int $code HTTP code
     * 
     * @return bool
     */
    public static function isHttpResponseCode( $code )
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $const      = array_search($code, $reflection->getConstants());

        return is_string($const);
    }
}