<?php

namespace Vortexgin\CoreBundle\Manager;

class FirebaseManager {

    private $_API_ACCESS_KEY = 'AAAAAbfpQOw:APA91bHNCVXj8S-k32NyzHlV4CjNunUUejhCTKjxk8Vy_Lwwv9RZ7ApsA3GbV2gtJrtgVm_h8ftME83Fn4uEjht9y7gQIxWHBg2NOGn9VGa3KlV2c_baRBDqt_Dx4LYP1iDRccD33LRR';

    public function FirebaseManager ($apiKey = '') {
        $this->_API_ACCESS_KEY = !empty($apiKey)?$apiKey:$_API_ACCESS_KEY;
    }

    public function sendPushNotification($registrationId, $title, $body) {
        try{
            $msg = array(
                'body' 	=> $body,
                'title'	=> $title,
                'icon'	=> 'myicon',
                'sound' => 'mySound'
            );
            $fields = array(
                'to'		=> $registrationId,
                'notification'	=> $msg
            );
            $headers = array(
                'Authorization: key=' . $this->_API_ACCESS_KEY,
                'Content-Type: application/json'
            );
    
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
            
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

}