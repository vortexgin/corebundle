<?php

namespace Vortexgin\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vortexgin\LibraryBundle\Utils\HttpStatusHelper;
use Vortexgin\LibraryBundle\Utils\Validator;
use Vortexgin\LibraryBundle\Manager\ConnectionManager;

/**
 * Bridge controller class 
 * 
 * @category Controller
 * @package  Vortexgin\WebBundle\Controller
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class BridgeController extends Controller
{

    /**
     * Bridge API Endpoint
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Http request
     * @param string                                    $token   Request token
     * 
     * @return mixed
     */
    public function apiAction(Request $request, $token)
    {
        $post = $request->request->all();
        try {
            $formTokenizer = $this->container->get('vortexgin.library.manager.form.tokenizer');
            if (!$formTokenizer->validateToken($token)) {
                return $this->errorResponse('Invalid token', HttpStatusHelper::HTTP_BAD_REQUEST);
            }

            if (!Validator::validate($post, 'url', null, 'empty')) {
                return $this->errorResponse('Please insert url', HttpStatusHelper::HTTP_BAD_REQUEST);
            }
            if (!Validator::validate($post, 'method', null, 'empty')) {
                return $this->errorResponse('Please insert method', HttpStatusHelper::HTTP_BAD_REQUEST);
            }
            if (!Validator::validate($post, 'param', 'array', 'empty')) {
                $post['param'] = array();
            }
            if (!Validator::validate($post, 'header', 'array', 'empty')) {
                $post['header'] = array();
            }

            /* @var $connectionManager \Vortexgin\LibraryBundle\Manager\ConnectionManager */
            $connectionManager = new ConnectionManager($this->container->getParameter('vortexgin.library.endpoint.bridge'));

            $response = $connectionManager->stream($post['url'], $post['method'], $post['param'], $post['header']);
            if (!$response instanceof JsonResponse) {
                return $this->errorResponse('API Executed Failed', HttpStatusHelper::HTTP_EXPECTATION_FAILED);
            }

            return $response;
        } catch (\Exception $e) {
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Bridging API failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

}
