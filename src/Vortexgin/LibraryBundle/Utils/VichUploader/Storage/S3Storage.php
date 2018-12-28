<?php

namespace Vortexgin\LibraryBundle\Utils\VichUploader\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\AbstractStorage;
use Vortexgin\LibraryBundle\Utils\S3;

/**
 * S3Storage.
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  MIT https://en.wikipedia.org/wiki/MIT_License
 * @link     http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 *
 */
class S3Storage extends AbstractStorage
{

    /**
     * S3 Host
     * 
     * @var string
     */
    private $_host;

    /**
     * S3 Bucket Assets
     * 
     * @var string
     */
    private $_bucket;

    /**
     * S3 Access Key
     * 
     * @var string
     */
    private $_accessKey;

    /**
     * S3 Secret Key
     * 
     * @var string
     */
    private $_secretKey;

    /**
     * Path to tmp folder
     * 
     * @var string
     */
    private $_tmp;

    /**
     * S3 Instance
     * 
     * @var \Vortexgin\LibraryBundle\Utils\S3
     */
    private $_s3;

    public function __construct(PropertyMappingFactory $factory, $host, $bucket, $accessKey, $secretKey, $tmp = '/tmp/')
    {
        parent::__construct($factory);

        $this->_host = $host;
        $this->_bucket = $bucket;
        $this->_accessKey = $accessKey;
        $this->_secretKey = $secretKey;
        $this->_tmp = $tmp;

        $this->_s3 = new S3($this->_accessKey, $this->_secretKey, $this->_host);
    }
    
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name)
    {
        var_dump($dir);
        var_dump($name);
        die;
        $file->move($this->_tmp, $name);
        return $this->_s3->putObjectFile($this->_tmp.$name, $this->_bucket, $dir, $s3::ACL_PUBLIC_READ);
    }

    protected function doRemove(PropertyMapping $mapping, ?string $dir, string $name): ?bool
    {
        return $this->_s3->deleteObject($this->_bucket, $dir.DIRECTORY_SEPARATOR.$name);
    }

    protected function doResolvePath(PropertyMapping $mapping, ?string $dir, string $name, ?bool $relative = false): string
    {
        $path = !empty($dir) ? $dir.DIRECTORY_SEPARATOR.$name : $name;
        if ($relative) {
            return $path;
        }
        return sprintf('https://%s.%s/%s', $this->_bucket, $this->_host, $path);
    }

    /*
    public function resolveUri($obj, string $mappingName, string $className = null): ?string
    {
        [$mapping, $name] = $this->getFilename($obj, $mappingName, $className);
        if (empty($name)) {
            return null;
        }
        $uploadDir = $this->convertWindowsDirectorySeparator($mapping->getUploadDir($obj));
        $uploadDir = empty($uploadDir) ? '' : $uploadDir.'/';

        return sprintf('https://%s.%s/%s', $this->_bucket, $this->_host, $uploadDir.$name);
    }

    private function convertWindowsDirectorySeparator(string $string): string
    {
        return str_replace('\\', '/', $string);
    }
    */
}