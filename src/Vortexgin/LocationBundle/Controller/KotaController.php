<?php

namespace Vortexgin\LocationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vortexgin\CoreBundle\Controller\BaseController;
use Vortexgin\CoreBundle\Util\HttpStatusHelper;
use Vortexgin\CoreBundle\Util\Validator;
use Vortexgin\LocationBundle\Util\KotaFilterGenerator;

class KotaController extends BaseController{
    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="Kota",
     *      description="Create kota",
     *      parameters={
     *          {"name"="provinsi_id",  "dataType"="integer", "required"=true, "description"="id of provinsi"},
     *          {"name"="name",         "dataType"="string", "required"=true, "description"="name of kota"},
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
            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');

            // request validation
            if(!Validator::validate($post, 'provinsi_id', null, 'empty'))
                return $this->errorResponse('Please insert provinsi', HttpStatusHelper::HTTP_BAD_REQUEST);
            if(!Validator::validate($post, 'name', null, 'empty'))
                return $this->errorResponse('Please insert name', HttpStatusHelper::HTTP_BAD_REQUEST);
            $detailProvinsi = $provinsiManager->get(array(array('id', $post['provinsi_id'])));
            if(count($detailProvinsi) <= 0)
                return $this->errorResponse('Provinsi not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $param = array(
                'provinsi'  => $detailProvinsi[0],
                'name'      => $post['name'],
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $kota = $kotaManager->insert($param);
            if(!$kota)
                return $this->errorResponse('Create kota failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($kotaManager->serialize($kota), HttpStatusHelper::HTTP_CREATED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Create kota failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="Kota",
     *      description="Read kota",
     *      parameters={
     *          {"name"="limit",        "dataType"="integer", "required"=false, "description"="data limit, default 20"},
     *          {"name"="page",         "dataType"="integer", "required"=false, "description"="data offset, default 0"},
     *          {"name"="order_by",     "dataType"="string", "required"=false, "format"="id|expired_date", "description"="data order by, default id"},
     *          {"name"="order_type",   "dataType"="string", "required"=false, "format"="ASC|DESC", "description"="data order type, default DESC"},
     *          {"name"="id",           "dataType"="string", "required"=false, "description"="id of kota"},
     *          {"name"="provinsi_id",  "dataType"="integer", "required"=false, "description"="id of provinsi"},
     *          {"name"="name",         "dataType"="string", "required"=false, "description"="name of kota"},
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

            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');

            $filter = KotaFilterGenerator::generateFilter($get);

            list($orderBy, $orderSort, $limit, $page) = $this->extractDefaultParameter($kotaManager, $get);

            $listKota   = $kotaManager->get($filter, $orderBy, $orderSort, $page, $limit);
            $totalKota  = $kotaManager->count($filter);

            if(count($listKota) <= 0)
                return $this->errorResponse('Kota not found', HttpStatusHelper::HTTP_NOT_FOUND);

            foreach($listKota as $key=>$value){
                $data[] = $kotaManager->serialize($value);
            }

            return $this->successResponse(array(
                'kota'    => $data,
                'count'     => array(
                    'total' => count($listKota),
                    'all'   => (int) $totalKota[1],
                )
            ));
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Read kota failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="Kota",
     *      description="Update kota",
     *      parameters={
     *          {"name"="provinsi_id",  "dataType"="integer", "required"=false, "description"="id of provinsi"},
     *          {"name"="name",         "dataType"="string", "required"=false, "description"="name of kota"},
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
            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');

            $detail = $kotaManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Kota not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $kota = $detail[0];
            // request validation
            $detailProvinsi = null;
            if(Validator::validate($post, 'provinsi_id', null, 'empty')){
                $detailProvinsi = $provinsiManager->get(array(array('id', $post['provinsi_id'])));
                if(count($detailProvinsi) <= 0)
                    return $this->errorResponse('Provinsi not found', HttpStatusHelper::HTTP_NOT_FOUND);
            }

            $param = array(
                'provinsi'  => $detailProvinsi[0],
                'name'      => Validator::validate($post, 'name', null, 'empty')?$post['name']:null,
                'user_log'  => $this->user->getUsername()?:'ANONYMOUS',
            );
            $newKota = $kotaManager->update($kota, $param);
            if(!$newKota)
                return $this->errorResponse('Update kota failed, Please try again later', HttpStatusHelper::HTTP_EXPECTATION_FAILED);

            return $this->successResponse($kotaManager->serialize($newKota), HttpStatusHelper::HTTP_ACCEPTED);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Update kota failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * @ApiDoc(
     *      section="Master",
     *      resource="Kota",
     *      description="Delete kota",
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

            /** @var $kotaManager \Vortexgin\LocationBundle\Manager\KotaManager */
            $kotaManager = $this->container->get('vortexgin.location.manager.kota');

            $detail = $kotaManager->get(array(array('id', $id)));
            if(!$detail)
                return $this->errorResponse('Kota not found', HttpStatusHelper::HTTP_NOT_FOUND);

            $kotaManager->setUser($this->user->getUsername()?:'ANONYMOUS');
            $kotaManager->delete($detail[0]);

            return $this->successResponse(array(), HttpStatusHelper::HTTP_NO_CONTENT);
        }catch(\Exception $e){
            $this->container->get('logger')->error(sprintf($e->getMessage()));
            return $this->errorResponse('Delete kota failed, Please try again later', HttpStatusHelper::HTTP_PRECONDITION_FAILED);
        }
    }
}
