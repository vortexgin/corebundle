<?php
namespace Vortexgin\CoreBundle\Util;

class Image
{
    /**
     * Function to upload image from base64data
     *
     * @param string $base64
     * @param string $path
     * @return string
     */
    static public function uploadBase64Image($base64, $path){
          $exp1 = explode(';', $base64);
          if(!array_key_exists('1', $exp1))
              return false;
          $exp2 = explode(':', $exp1[0]);
          if(!array_key_exists('1', $exp2))
              return false;
          $exp3 = explode('base64,', $exp1[1]);
          if(!array_key_exists('1', $exp3))
              return false;

          $ext = 'png';
          switch($exp2[1]){
              case 'image/jpg':$ext = '.jpg';break;
              case 'image/jpeg':$ext = '.jpg';break;
              case 'image/pjpeg':$ext = '.jpg';break;
              case 'image/gif':$ext = '.gif';break;
              default:$ext = '.png';break;
          }
          $base64 = $exp3[1];

          $filename = date('YmdGis').str_replace(' ', '', microtime()).$ext;
          $targetFile = $path.$filename;

          $imagine  = new \Imagine\Gd\Imagine();
          $imagine->load(base64_decode($base64))
                  ->save($targetFile);

          return $filename;
    }
}
