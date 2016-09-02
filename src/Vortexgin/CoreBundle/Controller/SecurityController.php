<?php

namespace Vortexgin\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\SecurityController as Base;

class SecurityController extends Base{

    public function renderLogin(array $data){
        $template = 'FOSUserBundle:Security:login.html.twig';

        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest(); // this is the call that breaks ESI
        if ($masterRequest) {
            if($masterRequest->attributes->get('_route') == 'admin_login'){
                $template = 'VortexginCoreBundle:Security:login.html.twig';
            }
        }

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}
?>
