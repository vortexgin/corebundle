<?php

namespace Vortexgin\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\SecurityController as BaseController;

class SecurityController extends BaseController{

    public function renderLogin(array $data){
        $template = 'FOSUserBundle:Security:login.html.twig';

        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest(); // this is the call that breaks ESI
        if ($masterRequest) {
            if($masterRequest->attributes->get('_route') == 'admin_login'){
                $template = 'VortexginAdminBundle:Security:login.html.twig';
            }
        }

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}
?>
