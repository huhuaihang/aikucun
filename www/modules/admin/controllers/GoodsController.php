<?php

namespace app\modules\admin\controllers;

use app\models\AliyunOssApi;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsBarrageRules;
use app\models\GoodsCategory;
use app\models\GoodsComment;
use app\models\GoodsService;
use app\models\GoodsServiceMap;
use app\models\GoodsSource;
use app\models\GoodsTraceVideo;
use app\models\GoodsType;
use app\models\GoodsViolation;
use app\models\ManagerLog;
use app\models\MerchantMessage;
use app\models\Package;
use app\models\System;
use app\models\SystemMessage;
use app\models\Util;
use app\models\ViolationType;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 商品管理
 * Class GoodsController
 * @package app\modules\admin\controllers
 */
class GoodsController extends BaseController
{
    use UploadControllerTrait;




    public function actions() {
        return [

            'uploadsouce'=>[
                'class' => 'app\widgets\batchupload\UploadAction'
            ]
        ];
    }


    /**
     * 商品类型管理
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionType()
    {
        if (!$this->manager->can('goods/type')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsType::find();
        $model_list = $query->all();
        return $this->render('type', [
            'model_list' => $model_list
        ]);
    }

    /**
     * 添加修改商品类型
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditType()
    {
        if (!$this->manager->can('goods/type')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = GoodsType::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到类型信息。');
            }
        } else {
            $model = new GoodsType();
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存商品类型', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/goods/type']),
                'txt' => '商品类型列表'
            ]));
        }
        return $this->render('type_edit', [
            'model' => $model
        ]);
    }

    /**
     * 商品属性列表
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAttr()
    {
        if (!$this->manager->can('goods/attr')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $search_tid = $this->get('search_tid');
        if (empty($search_tid)) {
            throw new BadRequestHttpException('参数错误。');
        }
        $query = GoodsAttr::find();
        $query->andWhere(['tid' => $search_tid]);
        $model_list = $query->all();
        $tid_map = ArrayHelper::map(GoodsType::find()->all(), 'id', 'name');
        return $this->render('attr', [
            'search_tid' => $search_tid,
            'model_list' => $model_list,
            'tid_map' => $tid_map,
        ]);
    }

    /**
     * 添加修改属性
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditAttr()
    {
        if (!$this->manager->can('goods/attr')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = GoodsAttr::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到属性信息。');
            }
        } else {
            $tid = $this->get('tid');
            if (empty($tid)) {
                throw new BadRequestHttpException('参数错误。');
            }
            $model = new GoodsAttr();
            $model->tid = $tid;
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存商品属性', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/goods/attr', 'search_tid' => $model->tid]),
                'txt' => '商品类型列表'
            ]));
        }
        return $this->render('attr_edit', [
            'model' => $model
        ]);
    }

    /**
     * 分类列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionCategory()
    {
        if (!$this->manager->can('goods/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsCategory::find();
        $query->andWhere(['pid' => null]); // 顶层分类
        $query->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL]);
        $category_list = $query->all();
        return $this->render('category', [
            'category_list' => $category_list
        ]);
    }

    /**
     * 获取分类列表AJAX接口
     * @return array
     */
    public function actionCategoryList()
    {
        if (!$this->manager->can('goods/category')) {
            return ['message' => '没有权限。'];
        }
        $pid = $this->get('pid');
        $query = GoodsCategory::find()
            ->asArray()
            ->select([
                'id',
                'name',
                'url',
                'image',
            ]);
        if (!empty($pid)) {
            $query->andWhere(['pid' => $pid]);
        } else {
            $query->andWhere(['pid' => null]);
        }
        $query->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL]);
        $category_list = $query->all();
        return [
            'result' => 'success',
            'category_list' => $category_list
        ];
    }

    /**
     * 添加修改分类
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditCategory()
    {
        if (!$this->manager->can('goods/category')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = GoodsCategory::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到分类信息。');
            }
            /** @var array $cate_list */
            $cate_lists = $model->familyTree([], $model->pid);
            /** @var string $cate_list */
            $cate_list = implode(',', ArrayHelper::getColumn($cate_lists, 'id'));
        } else {
            $model = new GoodsCategory();
            $model->status = GoodsCategory::STATUS_SHOW;
            /** @var string $cate_list */
            $cate_list = '';
        }
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存商品分类', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            return $this->redirect(['/admin/goods/category']);
//            Yii::$app->session->setFlash('redirect', json_encode([
//                'url' => Url::to(['/admin/goods/category']),
//                'txt' => '商品分类列表'
//            ]));
        }
        return $this->render('category_edit', [
            'model' => $model,
            'cate_list' => $cate_list,
        ]);
    }

    /**
     * 删除商品分类AJAX接口
     */
    public function actionCategoryDelete()
    {
        if (!$this->manager->can('goods/category')) {
            return ['message' => '没有权限'];
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = GoodsCategory::findOne($id);
            if (empty($model)) {
                return ['message' => '没有找到分类信息。'];
            }
            $model_child = GoodsCategory::find()->where(['pid' => $id])->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL])->all();
            if (!empty($model_child)) {
                return ['message' => '该分类有子分类请先删除子分类'];
            }
            $goods = new Goods();
            $is_goods = $goods->find()->andwhere(['cid' => $id])
                ->andWhere(['status' => [Goods::STATUS_ON,  Goods::STATUS_OFF]])
                ->count();
            if ($is_goods > 0) {
                return ['message' => '该分类下面还有商品不能删除'];
            }
            $model->status = GoodsCategory::STATUS_DEL;
            if (!$model->save()) {
                return ['message' => $model->errors[0]];
            }
        }
        return ['result' => 'success'];
    }

    /**
     * 商品列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        if (!$this->manager->can('goods/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Goods::find();
        $query->joinWith('shop')->andFilterWhere(['like', '{{%shop}}.name', $this->get('search_shop')]);
        $query->joinWith('goods_type')->andFilterWhere(['like', '{{%goods_type}}.name', $this->get('search_type')]);
        $query->joinWith('goods_brand')->andFilterWhere(['like', '{{%goods_brand}}.name', $this->get('search_brand')]);
        $query->joinWith('goods_category')->andFilterWhere(['like', '{{%goods_category}}.name', $this->get('search_category')]);
        $query->andFilterWhere(['{{%goods}}.cid' => $this->get('search_cid')]);
        $query->andFilterWhere(['{{%goods}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['like', 'title', $this->get('search_title')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商品详细
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->manager->can('goods/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到产品信息。');
        }
        return $this->render('view', [
            'goods' => $goods
        ]);
    }

    /**
     * 订单商品评论列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionComment()
    {
        if (!$this->manager->can('goods/comment')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsComment::find();
        $query->andWhere(['<>', 'status', GoodsComment::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC , status ASC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('comment', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 删除商品评论AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteComment()
    {
        if (!$this->manager->can('goods/comment')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = GoodsComment::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商品评论信息。'];
        }
        $model->status = GoodsComment::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除商品评论', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 设置商品评论状态AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionStatusComment()
    {
        if (!$this->manager->can('goods/comment')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        /* @var $model GoodsComment */
        $model = GoodsComment::find()->where(['id' => $id])->andWhere(['<>', 'status', GoodsComment::STATUS_DEL])->one();
        if (empty($model)) {
            return ['message' => '没有找到商品评论信息。'];
        }
        $new_status = [
            GoodsComment::STATUS_SHOW => GoodsComment::STATUS_HIDE,
            GoodsComment::STATUS_HIDE => GoodsComment::STATUS_SHOW
        ][$model->status];
        $model->status = $new_status;
        ManagerLog::info($this->manager->id, '设置商品评论状态', $model->id . ':' . $model->status . '->' . $new_status);
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 设置商品违规
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionViolation()
    {
        if (!$this->manager->can('goods/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }
        $model = new GoodsViolation();
        $model->gid = $id;
        $model->status = GoodsViolation::STATUS_WAIT_MERCHANT;
        $model->create_time = time();

        if ($model->load($this->post())) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) {
                    throw new Exception('无法保存商品违规。');
                }
                $goods->status = Goods::STATUS_OFF;
                if (!$goods->save(false)) {
                    throw new Exception('商品下架失败。');
                }
                $violation_type = ViolationType::findOne($model->vid);
                /** @var  $merchant_message MerchantMessage */
                $merchant_message = new MerchantMessage();
                $merchant_message->mid = $goods->shop->mid;
                $merchant_message->title = '商品违规下架';
                $merchant_message->content = '违规商品ID:' . $goods->id . " 商品名称：" . $goods->title . ' 违规类型：' . $violation_type->name;
                $merchant_message->time = time();
                $merchant_message->status = SystemMessage::STATUS_UNREAD;
                if (!$merchant_message->save()) {
                    throw new Exception('商户消息发送失败。');
                }
                ManagerLog::info($this->manager->id, '设置商品违规', print_r($model->attributes, true));
                $trans->commit();
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/goods/list']),
                    'txt' => '商品列表'
                ]));
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('violation', [
            'model' => $model,
        ]);
    }

    /**
     * 违规商品审核
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionVerifyViolation()
    {
        if (!$this->manager->can('goods/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsViolation::find();
        $query->andWhere(['{{%goods_violation}}.status' => GoodsViolation::STATUS_WAIT_MANAGER]);
        $query->joinWith(['goods', 'violationType', 'goods.shop']);
        $query->andFilterWhere(['vid' => $this->get('search_vid')]);
        $query->andFilterWhere(['like', 'title', $this->get('search_title')]);
        $query->andFilterWhere(['like', '{{%shop}}.name', $this->get('search_shop')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('verify_violation', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 违规商品审核AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionStatusViolation()
    {
        if (!$this->manager->can('goods/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $status = $this->get('status');
        $remark = $this->get('remark');
        /** @var GoodsViolation $goods_violation */
        $goods_violation = GoodsViolation::findOne($id);
        if (empty($goods_violation)) {
            throw new NotFoundHttpException('没有找到违规商品信息。');
        }
        if ($status == 'accept') {
            $goods_violation->status = GoodsViolation::STATUS_DEL;
            ManagerLog::info($this->manager->id, '设置商品违规', print_r($goods_violation->attributes, true));
            if (!$goods_violation->save()) {
                return ['message' => '违规商品审核通过失败。'];
            }
            $goods = Goods::findOne($goods_violation->gid);
            $goods->status = Goods::STATUS_ON;
            if (!$goods->save()) {
                return ['message' => '商品商家失败。'];
            } else {
                return ['result' => 'success'];
            }
        }
        if ($status == 'refuse') {
            $goods_violation->status = GoodsViolation::STATUS_WAIT_MERCHANT;
            $goods_violation->remark = $remark;
            /** @var  $merchant_message MerchantMessage */
            $merchant_message = new MerchantMessage();
            $merchant_message->mid = $goods_violation->goods->shop->mid;
            $merchant_message->title = '商品违规下架';
            $merchant_message->content = '违规商品ID:' . $goods_violation->gid . ' 违规处理被拒绝 拒绝理由：' . $remark;
            $merchant_message->time = time();
            $merchant_message->status = SystemMessage::STATUS_UNREAD;
            if (!$merchant_message->save()) {
                return ['message' => '商户消息发送失败。'];
            }
            ManagerLog::info($this->manager->id, '设置商品违规', print_r($goods_violation->attributes, true));
            if (!$goods_violation->save()) {
                return ['message' => '违规商品审核通过失败。'];
            } else {
                return ['result' => 'success'];
            }
        }
        return ['message' => '参数错误。'];
    }

    /**
     * 设置商品状态AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionRecommend()
    {
        if (!$this->manager->can('goods/violation')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods) || $goods->status == Goods::STATUS_DEL) {
            return ['message' => '没有找到商品信息。'];
        }
        $new_recommend = [
            0 => 1,
            1 => 0,
            'NULL' => 1
        ][$goods->is_index];
        $goods->is_index = $new_recommend;
        $goods->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 商品服务列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionService()
    {
        if (!$this->manager->can('goods/service')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsService::find();
        $serviceList = $query->all();
        return $this->render('service', [
            'serviceList' => $serviceList
        ]);
    }

    /**
     * 添加修改商品服务
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditService()
    {
        if (!$this->manager->can('goods/service')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $service = GoodsService::findOne(['id' => $id]);
            if (empty($service)) {
                throw new NotFoundHttpException('没有找到服务信息。');
            }
        } else {
            $service = new GoodsService();
        }
        if ($service->load($this->post()) && $service->save()) {
            ManagerLog::info($this->manager->id, '保存商品服务', print_r($service->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/goods/service']),
                'txt' => '商品服务列表'
            ]));
        }
        return $this->render('service_edit', [
            'service' => $service,
        ]);
    }

    /**
     * 删除商品服务AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDeleteService()
    {
        if (!$this->manager->can('goods/service')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $service = GoodsService::findOne(['id' => $id]);
        if (empty($service)) {
            return ['result' => 'failure', 'message' => '没有找到服务信息。'];
        }
        ManagerLog::info($this->manager->id, '删除商品服务', print_r($service->attributes, true));
        GoodsServiceMap::deleteAll(['sid' => $service->id]);
        try {
            $service->delete();
        } catch (\Throwable $e) {
        }
        return ['result' => 'success'];
    }

//    /**
//     * 溯源视频管理
//     * @return array | string
//     */
//    public function actionTraceVideo()
//    {
//
//        $query = GoodsTraceVideo::find();
//        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
//        $query->andWhere(['<>', 'status', GoodsTraceVideo::STATUS_DEL]);
//        $pagination = new Pagination(['totalCount' => $query->count()]);
//        $query->andWhere(['sid'=>null]);
//        $query->orderBy('id DESC');
//        $query->offset($pagination->offset)->limit($pagination->limit);
//        $videoList = $query->all();
//        return $this->render('trace_video', [
//            'videoList' => $videoList,
//            'pagination' => $pagination,
//        ]);
//    }
//
//    /**
//     * 添加、修改溯源视频
//     * @return array|string
//     * @throws Exception
//     */
//    public function actionTraceVideoEdit()
//    {
//        $id = $this->get('id');
//        if (!empty($id)) {
//            $traceVideo = GoodsTraceVideo::findOne(['id' => $id]);
//            if (empty($traceVideo)) {
//                throw new NotFoundHttpException('没有找到视频。');
//            }
//        } else {
//            $traceVideo = new GoodsTraceVideo();
//            $traceVideo->status = GoodsTraceVideo::STATUS_OK;
//            $traceVideo->create_time = time();
//        }
//        if ($traceVideo->load($this->post()) && $traceVideo->save()) {
//            Yii::$app->session->addFlash('success', '数据已保存。');
//            Yii::$app->session->setFlash('redirect', json_encode([
//                'url' => Url::to(['/admin/goods/trace-video']),
//                'txt' => '视频列表'
//            ]));
//        }
//        $ossName = 'ytb_1_' . Util::randomStr(8);
//        $ossCoverName = $ossName . '.jpg';
//        $ossVideoName = $ossName . '.mp4';
//        return $this->render('trace_video_edit', [
//            'traceVideo' => $traceVideo,
//            'ossCoverName' => $ossCoverName,
//            'ossVideoName' => $ossVideoName,
//            'ossPolicy' => (new AliyunOssApi())->ossPolicy('goods_trace'),
//        ]);
//    }
//
//    /**
//     * 删除视频AJAX接口
//     * @return array
//     * @throws ForbiddenHttpException
//     */
//    public function actionDeleteVideo()
//    {
//        $id = $this->get('id');
//        $model = GoodsTraceVideo::findOne($id);
//        if (empty($model)) {
//            return ['message' => '没有找到视频信息。'];
//        }
//        $model->status = GoodsTraceVideo::STATUS_DEL;
//        ManagerLog::info($this->manager->id, '删除视频', $model->id);
//        $model->save(false);
//        return [
//            'result' => 'success'
//        ];
//    }
//
//    /**
//     * 素材图片管理
//     * @return array|string
//     */
//    public function actionSource()
//    {
//        $query = GoodsSource::find();
//        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
//        $query->andFilterWhere(['<>', 'status', GoodsSource::STATUS_DEL]);
//        $pagination = new Pagination(['totalCount' => $query->count()]);
//        $query->andWhere(['<>', 'status', GoodsSource::STATUS_DEL]);
//        $query->orderBy('id DESC');
//        $query->offset($pagination->offset)->limit($pagination->limit);
//        $sourceList = $query->all();
//
//        return $this->render('source', [
//            'sourceList' => $sourceList,
//            'pagination' => $pagination,
//        ]);
//    }
//
//    /**
//     * 添加、修改素材图片
//     * @return array|string
//     * @throws Exception
//     */
//    public function actionSourceEdit()
//    {
//
//        $id = $this->get('id');
//        if (!empty($id)) {
//            $goodSource = GoodsSource::findOne(['id' => $id]);
//            if (empty( $goodSource )) {
//                throw new NotFoundHttpException('没有找到视频。');
//            }
//        } else {
//            $goodSource = new GoodsSource();
//            $goodSource ->create_time = time();
//        }
//        if($this->post())
//        {
//            if($goodSource->load($this->post()) && is_array($goodSource->img_list))
//            {
//                $goodSource->img_list=json_encode($goodSource->img_list);
//            }
//            else
//            {
//                $goodSource->img_list='';
//            }
//
//            if ($goodSource->save()) {
//                Yii::$app->session->addFlash('success', '数据已保存。');
//                Yii::$app->session->setFlash('redirect', json_encode([
//                    'url' => Url::to(['/admin/goods/source']),
//                    'txt' => '素材列表'
//                ]));
//            }
//        }
//
//        return $this->render('source_edit', [
//            'goodSource' =>  $goodSource ,
//        ]);
//    }
//
//    /**
//     * 删除图文素材AJAX接口
//     * @return array
//     * @throws ForbiddenHttpException
//     */
//    public function actionDeleteSource()
//    {
//        $id = $this->get('id');
//        $model = GoodsSource::findOne($id);
//        if (empty($model)) {
//            return ['message' => '没有找到图文素材信息。'];
//        }
//        $model->status = GoodsSource::STATUS_DEL;
//        ManagerLog::info($this->manager->id, '删除图文素材', $model->id);
//        $model->save(false);
//        return [
//            'result' => 'success'
//        ];
//    }

    /**
     * 套餐卡管理
     * @return array|string
     */
    public function actionPackage()
    {
        $query = Package::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['<>', 'status', Package::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->andWhere(['<>', 'status', Package::STATUS_DEL]);
        $query->orderBy('id DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $list = $query->all();

        return $this->render('package', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加、修改套餐卡
     * @return array|string
     * @throws Exception
     */
    public function actionPackageEdit()
    {

        $id = $this->get('id');
        if (!empty($id)) {
            $model = Package::findOne(['id' => $id]);
            if (empty( $model )) {
                throw new NotFoundHttpException('没有找到套餐。');
            }
        } else {
            $model = new Package();
            $model->status = Package::STATUS_SHOW;
            $model ->create_time = time();
        }
        if($this->post())
        {
            if($model->load($this->post()) && $model->save())
            {
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/admin/goods/package']),
                    'txt' => '套餐列表'
                ]));
            }
        }

        return $this->render('package_edit', [
            'model' =>  $model ,
        ]);
    }

    /**
     * 删除套餐卡AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeletePackage()
    {
        $id = $this->get('id');
        $model = Package::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到套餐卡信息。'];
        }
        $model->status = Package::STATUS_DEL;
        ManagerLog::info($this->manager->id, '删除套餐卡', $model->id);
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }

    /**
     * 商品弹幕规则
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionBarrageRules()
    {
        if (!$this->manager->can('goods/barrage-rules')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = GoodsBarrageRules::find();
        $query->andWhere(['status' => GoodsBarrageRules::STATUS_OK]);
        $query->orderBy('id desc');
        $rule_list = $query->all();
        return $this->render('barrage_rules', [
            'rule_list' => $rule_list
        ]);
    }
    /**
     * 添加修改商品弹幕规则
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEditBarrage()
    {
        if (!$this->manager->can('goods/barrage-rules')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $barrage = GoodsBarrageRules::findOne(['id' => $id]);
            if (empty($barrage)) {
                throw new NotFoundHttpException('没有找到弹幕信息。');
            }
        } else {
            $barrage = new GoodsBarrageRules();
            $barrage->create_time=time();
            $barrage->status =GoodsBarrageRules::STATUS_OK;
        }
        if ($barrage->load($this->post()) && $barrage->save()) {
            ManagerLog::info($this->manager->id, '保存商品弹幕', print_r($barrage->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/goods/barrage-rules']),
                'txt' => '商品服务列表'
            ]));
        }
        return $this->render('barrage_rules_edit', [
            'barrage' => $barrage,
        ]);
    }
    /**
     * 删除商品弹幕AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteBarrage()
    {
        if (!$this->manager->can('goods/barrage-rules')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $barrage = GoodsBarrageRules::findOne(['id' => $id]);
        if (empty($barrage)) {
            return ['result' => 'failure', 'message' => '没有找到弹幕信息。'];
        }
        ManagerLog::info($this->manager->id, '删除商品服务', print_r($barrage->attributes, true));
        $barrage->status =GoodsBarrageRules::STATUS_DELETE;
        try {
            $barrage->save();
        } catch (\Throwable $e) {
        }
        return ['result' => 'success'];
    }




}
