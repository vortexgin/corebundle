<?php

namespace Vortexgin\LocationBundle\Util;

use Vortexgin\CoreBundle\Model\FilterGeneratorInterface;
use Vortexgin\CoreBundle\Util\Validator;

class ProvinsiFilterGenerator implements FilterGeneratorInterface{
    static public function generateFilter(array $param = array()){
      try{
        $filter = array();

        if(Validator::validate($param, 'query', null, 'empty')){
            if(Validator::validate($param, 'fields', null, 'empty')){
                $fields = json_decode($param['fields'], true);
                foreach($fields as $field){
                    $param[$field] = $param['query'];
                }
            }else{
                $param['name'] = $param['query'];
            }
        }

        if(Validator::validate($get, 'id', null, 'empty'))
            $filter[] = array('id', $get['id']);
        if(Validator::validate($get, 'code', null, 'empty'))
            $filter[] = array('code', $get['code']);
        if(Validator::validate($get, 'name', null, 'empty'))
            $filter[] = array('name', $get['name'], 'like');
        if(Validator::validate($param, 'term', null, 'empty')){
            $filter[] = array('name', $param['term'], 'like');
        }

        return $filter;
      }catch(\Exception $e){
        return array();
      }
    }
}
