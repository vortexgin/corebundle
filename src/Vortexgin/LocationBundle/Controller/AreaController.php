<?php

namespace Vortexgin\LocationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vortexgin\CoreBundle\Controller\BaseController;
use Vortexgin\CoreBundle\Util\HttpStatusHelper;
use Vortexgin\CoreBundle\Util\Validator;
use Vortexgin\LocationBundle\Util\AreaFilterGenerator;

class AreaController extends BaseController{
    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Area",
     *      description="Create area",
     *      parameters={
     *          {"name"="kota_id",  "dataType"="integer", "required"=true, "description"="id of kota"},
     *          {"name"="name",     "dataType"="string", "required"=true, "description"="name of area"},
     *          {"name"="zipcode",  "dataType"="string", "required"=true, "description"="zipcode of area"},
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

            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');
            /** @var $areaManager \Vortexgin\LocationBundle\Manager\AreaManager */
            $areaManager = $this->container->get('vortexgin.location.manager.area');

            // request validation
            if(!Validator::validate($post, 'kota_id', null, 'empty'))
                return $this->errorResponse('Please insert kota', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'name', null, 'empty'))
                return $this->errorResponse('Please insert name', HttpStatusHelper::HTTP_BAD_REQUEST);
            $detailKota = $kotaManager->get(array(array('id', $post['kota_id'])));
            if(count($detailKota) <= 0)
                return $this->errorResponse('Kota not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $param = array(
                'kota'      => $detailKota[0],
                'name'      => $post['name'],
                'zipcode'   => Validator::validate($post, 'zipcode', null, 'empty')?$post['zipcode']:null,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $area = $areaManager->insert($param);
            if(!$area)
                return $this->errorResponse('Create area failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($areaManager->serialize($area), HttpStatusHelper::HTTP_CREATED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Create area failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Area",
     *      description="Read area",
     *      parameters={
     *          {"name"="limit",        "dataType"="integer", "required"=false, "description"="data limit, default 20"},
     *          {"name"="page",         "dataType"="integer", "required"=false, "description"="data offset, default 0"},
     *          {"name"="order_by",     "dataType"="string", "required"=false, "format"="id|expired_date", "description"="data order by, default id"},
     *          {"name"="order_type",   "dataType"="string", "required"=false, "format"="ASC|DESC", "description"="data order type, default DESC"},
     *          {"name"="id",           "dataType"="string", "required"=false, "description"="id of area"},
     *          {"name"="kota_id",      "dataType"="integer", "required"=false, "description"="id of kota"},
     *          {"name"="name",         "dataType"="string", "required"=false, "description"="name of area"},
     *          {"name"="zipcode",         "dataType"="string", "required"=false, "description"="zipcode of area"},
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

            /** @var $areaManager \Vortexgin\LocationBundle\Manager\AreaManager */
            $areaManager = $this->container->get('vortexgin.location.manager.area');

            $filter = AreaFilterGenerator::generateFilter($get);

            list($orderBy, $orderSort, $limit, $page) = $this->extractDefaultParameter($areaManager, $get);

            $listArea   = $areaManager->get($filter, $orderBy, $orderSort, $page, $limit);
            $totalArea  = $areaManager->count($filter);

            if(count($listArea) <= 0)
                return $this->errorResponse('Area not found', HttpStatusHelper::HTTP_NOT_FOUND);

            foreach($listArea as $key=>$value){
                $data[] = $areaManager->serialize($value);
            }

            return $this->successResponse(array(
                'area'    => $data,
                'count'     => array(
                    'total' => count($listArea),
                    'all'   => (int) $totalArea[1],
                )
            ));
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Read area failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Area",
     *      description="Update area",
     *      parameters={
     *          {"name"="kota_id",  "dataType"="integer", "required"=false, "description"="id of kota"},
     *          {"name"="name",     "dataType"="string", "required"=false, "description"="name of area"},
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

            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');
            /** @var $areaManager \Vortexgin\LocationBundle\Manager\AreaManager */
            $areaManager = $this->container->get('vortexgin.location.manager.area');

            $detail = $areaManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Area not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $area = $detail[0];
            // request validation
            $detailKota = null;
            if(Validator::validate($post, 'kota_id', null, 'empty')){
                $detailKota = $kotaManager->get(array(array('id', $post['kota_id'])));
                if(count($detailKota) <= 0)
                    return $this->errorResponse('Kota not found', HttpStatusHelper::HTTP_NOT_FOUND);
            }

            $param = array(
                'kota'      => $detailKota[0],
                'name'      => Validator::validate($post, 'name', null, 'empty')?$post['name']:null,
                'zipcode'   => Validator::validate($post, 'zipcode', null, 'empty')?$post['zipcode']:null,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $newArea = $areaManager->update($area, $param);
            if(!$newArea)
                return $this->errorResponse('Update area failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($areaManager->serialize($newArea), HttpStatusHelper::HTTP_ACCEPTED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Update area failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Vortexgin",
     *      resource="Area",
     *      description="Delete area",
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

            /** @var $areaManager \Vortexgin\LocationBundle\Manager\AreaManager */
            $areaManager = $this->container->get('vortexgin.location.manager.area');

            $detail = $areaManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Area not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $areaManager->setUser($this->user->getUsername()?:'ANONYMOUS');
            $areaManager->delete($detail[0]);

            return $this->successResponse(array(), HttpStatusHelper::HTTP_NO_CONTENT);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Delete area failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}
