<?php

namespace Vortexgin\LibraryBundle\Utils;

/**
 * Mailer functions
 * 
 * @category Utils
 * @package  Vortexgin\LibraryBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class Mailer
{

    /**
     * Mailer
     * 
     * @var mixed
     */
    private $_mailer;

    /**
     * Class Construct
     * 
     * @param mixed $mailer Mailer provider
     * 
     * @return void
     */
    public function __construct($mailer)
    {
        $this->_mailer = $mailer;
    }

    /**
     * Function to send message
     * 
     * @param string       $subject          Email Subject
     * @param string       $renderedTemplate Email body
     * @param array|string $fromEmail        From email
     * @param array|string $toEmail          To email
     * 
     * @return void
     */
    protected function sendEmailMessage($subject, $renderedTemplate, $fromEmail, $toEmail)
    {
        if ($this->_mailer instanceof \Swift_Mailer) {
            $this->_swiftSend($subject, $renderedTemplate, $fromEmail, $toEmail);
        }
    }

    /**
     * Function to send message from swift mailer
     * 
     * @param string       $subject          Email Subject
     * @param string       $renderedTemplate Email body
     * @param array|string $fromEmail        From email
     * @param array|string $toEmail          To email
     * 
     * @return void
     */
    private function _swiftSend($subject, $renderedTemplate, $fromEmail, $toEmail)
    {
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($renderedTemplate);

        $this->_mailer->send($message);
    }
}