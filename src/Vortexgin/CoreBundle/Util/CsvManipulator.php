<?php

namespace Vortexgin\CoreBundle\Util;

use SplFileObject;

class CsvManipulator
{
    protected $file;

    protected function __construct(SplFileObject $file, $append = true)
    {
        $this->file = $file;

        if (! $append) {
            $this->truncate(0);
        }
    }

    protected function __clone()
    {
    }

    public static function loadFile($filename, $append = true, $openMode = 'r', $useIncludePath = false)
    {
        $file = new SplFileObject($filename, $openMode, $useIncludePath);

        return new static($file, $append);
    }

    public function insert(array $data)
    {
        $this->file->fputcsv($data);
    }

    public function get()
    {
        return $this->file->fgetcsv();
    }

    public function truncate($length)
    {
        $this->file->ftruncate($length);
    }

    public function flush()
    {
        $this->file->fflush();
    }

    public function getCurrentLine()
    {
        return $this->file->current();
    }

    public function isEndOfLine()
    {
        return $this->file->eof();
    }

    public function lock($lockMode, $wouldBlock)
    {
        return $this->file->flock($lockMode, $wouldBlock);
    }

    public function getSplObject()
    {
        return $this->file;
    }

    public function read($length)
    {
        return $this->file->fread($length);
    }
}
