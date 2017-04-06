<?php

namespace Vortexgin\LocationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vortexgin\CoreBundle\Controller\BaseController;
use Vortexgin\CoreBundle\Util\HttpStatusHelper;
use Vortexgin\CoreBundle\Util\Validator;
use Vortexgin\LocationBundle\Util\ProvinsiFilterGenerator;

class ProvinsiController extends BaseController{
    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Provinsi",
     *      description="Create provinsi",
     *      parameters={
     *          {"name"="code",   "dataType"="string", "required"=true, "description"="code of provinsi"},
     *          {"name"="name",   "dataType"="string", "required"=true, "description"="name of provinsi"},
     *      },
     *      statusCodes={
     *          201="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function createAction(Request $request){
        try{
            $this->init();
            $post = $request->request->all();

            /** @var $provinsiManager \Vortexgin\LocationBundle\Manager\ProvinsiManager */
            $provinsiManager = $this->container->get('vortexgin.location.manager.provinsi');

            // request validation
            if(!Validator::validate($post, 'code', null, 'empty'))
                return $this->errorResponse('Please insert code', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'name', null, 'empty'))
                return $this->errorResponse('Please insert name', HttpStatusHelper::HTTP_BAD_REQUEST);
            $duplicateCode = $provinsiManager->get(array(array('code', $post['code'])));
            if(count($duplicateCode) > 0)
                return $this->errorResponse('Code has been used', HttpStatusHelper::HTTP_CONFLICT);

            $param = array(
                'code'      => $post['code'],
                'name'      => $post['name'],
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $provinsi = $provinsiManager->insert($param);
            if(!$provinsi)
                return $this->errorResponse('Create provinsi failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($provinsiManager->serialize($provinsi), HttpStatusHelper::HTTP_CREATED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Create provinsi failed, Please try again later. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Provinsi",
     *      description="Read provinsi",
     *      parameters={
     *          {"name"="limit",      "dataType"="integer", "required"=false, "description"="data limit, default 20"},
     *          {"name"="page",       "dataType"="integer", "required"=false, "description"="data offset, default 0"},
     *          {"name"="order_by",   "dataType"="string", "required"=false, "format"="id|expired_date", "description"="data order by, default id"},
     *          {"name"="order_type", "dataType"="string", "required"=false, "format"="ASC|DESC", "description"="data order type, default DESC"},
     *          {"name"="id",         "dataType"="string", "required"=false, "description"="id of provinsi"},
     *          {"name"="code",       "dataType"="string", "required"=false, "description"="code of provinsi"},
     *          {"name"="name",       "dataType"="string", "required"=false, "description"="name of provinsi"},
     *      },
     *      statusCodes={
     *          200="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function readAction(Request $request){
        try{
            $this->init();
            $get = $request->query->all();

            /** @var $provinsiManager \Vortexgin\LocationBundle\Manager\ProvinsiManager */
            $provinsiManager = $this->container->get('vortexgin.location.manager.provinsi');

            $filter = ProvinsiFilterGenerator::generateFilter($get);

            list($orderBy, $orderSort, $limit, $page) = $this->extractDefaultParameter($provinsiManager, $get);

            $listProvinsi   = $provinsiManager->get($filter, $orderBy, $orderSort, $page, $limit);
            $totalProvinsi  = $provinsiManager->count($filter);

            if(count($listProvinsi) <= 0)
                return $this->errorResponse('Provinsi not found', HttpStatusHelper::HTTP_NOT_FOUND);

            foreach($listProvinsi as $key=>$value){
                $data[] = $provinsiManager->serialize($value);
            }

            return $this->successResponse(array(
                'provinsi'  => $data,
                'count'     => array(
                    'total' => count($listProvinsi),
                    'all'   => (int) $totalProvinsi[1],
                )
            ));
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Read provinsi failed, Please try again later. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Provinsi",
     *      description="Update provinsi",
     *      parameters={
     *          {"name"="code",   "dataType"="string", "required"=false, "description"="code of provinsi"},
     *          {"name"="name",   "dataType"="string", "required"=false, "description"="name of provinsi"},
     *      },
     *      statusCodes={
     *          202="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function updateAction(Request $request, $id){
        try{
            $this->init();
            $post = $request->request->all();

            /** @var $provinsiManager \Vortexgin\LocationBundle\Manager\ProvinsiManager */
            $provinsiManager = $this->container->get('vortexgin.location.manager.provinsi');

            $detail = $provinsiManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Provinsi not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $provinsi = $detail[0];
            // request validation
            if(Validator::validate($post, 'code', null, 'empty')){
                $duplicateCode = $provinsiManager->get(array(array('code', $post['code'])));
                if(count($duplicateCode) > 0){
                    if($duplicateCode[0]->getId() != $id)
                        return $this->errorResponse('Code has been used', HttpStatusHelper::HTTP_CONFLICT);
                }
            }

            $param = array(
                'code'      => Validator::validate($post, 'code', null, 'empty')?$post['code']:null,
                'name'      => Validator::validate($post, 'name', null, 'empty')?$post['name']:null,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $newProvinsi = $provinsiManager->update($provinsi, $param);
            if(!$newProvinsi)
                return $this->errorResponse('Update provinsi failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($provinsiManager->serialize($newProvinsi), HttpStatusHelper::HTTP_ACCEPTED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Update provinsi failed, Please try again later. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Provinsi",
     *      description="Delete provinsi",
     *      statusCodes={
     *          204="Returned when successful",
     *          400="Bad request",
     *          500="System error",
     *      }
     * )
     */
    public function deleteAction(Request $request, $id){
        try{
            $this->init();

            /** @var $provinsiManager \Vortexgin\LocationBundle\Manager\ProvinsiManager */
            $provinsiManager = $this->container->get('vortexgin.location.manager.provinsi');

            $detail = $provinsiManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Provinsi not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $provinsiManager->setUser($this->user->getUsername()?:'ANONYMOUS');
            $provinsiManager->delete($detail[0]);

            return $this->successResponse(array(), HttpStatusHelper::HTTP_NO_CONTENT);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Delete provinsi failed, Please try again later. '.$e->getMessage(), HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}
