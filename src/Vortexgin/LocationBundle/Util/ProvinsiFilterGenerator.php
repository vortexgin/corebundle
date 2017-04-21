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

        if(Validator::validate($param, 'id', null, 'empty'))
            $filter[] = array('id', $param['id']);
        if(Validator::validate($param, 'code', null, 'empty'))
            $filter[] = array('code', $param['code']);
        if(Validator::validate($param, 'name', null, 'empty'))
            $filter[] = array('name', $param['name'], 'like');
        if(Validator::validate($param, 'term', null, 'empty')){
            $filter[] = array('name', $param['term'], 'like');
        }

        return $filter;
      }catch(\Exception $e){
        return array();
      }
    }
}
