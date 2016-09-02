<?php

namespace Vortexgin\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vortexgin\CoreBundle\Controller\BaseController;

class DefaultController extends BaseController{
  function indexAction(Request $req) {
      return $this->render('VortexginCoreBundle:Default:index.html.twig', array());
  }
}
