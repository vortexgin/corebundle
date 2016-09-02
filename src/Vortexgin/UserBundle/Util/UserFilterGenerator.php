<?php

namespace Vortexgin\UserBundle\Util;

use Vortexgin\CoreBundle\Model\FilterGeneratorInterface;
use Vortexgin\CoreBundle\Util\Validator;

class UserFilterGenerator implements FilterGeneratorInterface{
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

        if(Validator::validate($get, 'id', null, 'empty'))
            $filter[] = array('id', $get['id']);
        if(Validator::validate($get, 'email', null, 'empty'))
            $filter[] = array('email', $get['email'], 'like');
        if(Validator::validate($get, 'username', null, 'empty'))
            $filter[] = array('username', $get['username'], 'like');
        if(Validator::validate($param, 'term', null, 'empty')){
            $filter[] = array('email', $param['term'], 'like');
            $filter[] = array('username', $param['term'], 'like', 'or');
        }

        return $filter;
      }catch(\Exception $e){
        return array();
      }
    }
}
