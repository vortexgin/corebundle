<?php

namespace Vortexgin\LibraryBundle\Manager;

/**
 * Firebase manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class FirebaseManager
{

    /**
     * API Access Key
     * 
     * @var string
     */
    private $_API_ACCESS_KEY = 'AAAAAbfpQOw:APA91bHNCVXj8S-k32NyzHlV4CjNunUUejhCTKjxk8Vy_Lwwv9RZ7ApsA3GbV2gtJrtgVm_h8ftME83Fn4uEjht9y7gQIxWHBg2NOGn9VGa3KlV2c_baRBDqt_Dx4LYP1iDRccD33LRR';

    /**
     * Construct
     * 
     * @param string $apiKey API Key
     * 
     * @return void
     */
    public function FirebaseManager($apiKey = '')
    {
        if (!empty($apiKey)) {
            $this->_API_ACCESS_KEY = $apiKey;
        }
    }

    /**
     * Send Push Notification
     * 
     * @param string $registrationId Registration token
     * @param string $title          Title of message
     * @param string $body           Body of message
     * 
     * @return void
     */
    public function sendPushNotification($registrationId, $title, $body)
    {
        try{
            $msg = array(
                'body' => $body,
                'title' => $title,
                'icon' => 'myicon',
                'sound' => 'mySound'
            );
            $fields = array(
                'to' => $registrationId,
                'notification' => $msg
            );
            $headers = array(
                'Authorization: key=' . $this->_API_ACCESS_KEY,
                'Content-Type: application/json'
            );
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

}