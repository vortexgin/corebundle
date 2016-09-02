<?php

namespace Vortexgin\LocationBundle\Util;

use Vortexgin\CoreBundle\Model\FilterGeneratorInterface;
use Vortexgin\CoreBundle\Util\Validator;

class AreaFilterGenerator implements FilterGeneratorInterface{
    static public function generateFilter(array $param = array()){
      try{
        $filter = array();

        if(Validator::validate($get, 'query', null, 'empty')){
            if(Validator::validate($get, 'fields', null, 'empty')){
                $fields = json_decode($get['fields'], true);
                foreach($fields as $field){
                    $get[$field] = $get['query'];
                }
            }else{
                $get['name'] = $get['query'];
            }
        }

        if(Validator::validate($param, 'id', null, 'empty'))
            $filter[] = array('id', $param['id']);
        if(Validator::validate($param, 'name', null, 'empty'))
            $filter[] = array('name', $param['name'], 'like');
        if(Validator::validate($param, 'zipcode', null, 'empty'))
            $filter[] = array('zipcode', $param['zipcode']);
        if(Validator::validate($param, 'kota_id', null, 'empty'))
            $filter[] = array('id', $param['kota_id'], 'equal', 'and', 'kota');
        if(Validator::validate($param, 'kota_name', null, 'empty'))
            $filter[] = array('name', $param['kota_name'], 'like', 'and', 'kota');
        if(Validator::validate($param, 'prov_id', null, 'empty'))
            $filter[] = array('id', $param['prov_id'], 'equal', 'and', 'prov');
        if(Validator::validate($param, 'prov_name', null, 'empty'))
            $filter[] = array('name', $param['prov_name'], 'like', 'and', 'prov');
        if(Validator::validate($param, 'prov_code', null, 'empty'))
            $filter[] = array('code', $param['prov_code'], 'equal', 'and', 'prov');
        if(Validator::validate($param, 'term', null, 'empty')){
            $filter[] = array('name', $param['term'], 'like');
            $filter[] = array('name', $param['term'], 'like', 'or', 'kota');
            $filter[] = array('name', $param['term'], 'like', 'or', 'prov');
        }

        return $filter;
      }catch(\Exception $e){
        return array();
      }
    }
}
