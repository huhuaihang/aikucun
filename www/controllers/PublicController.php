<?php

namespace app\controllers;
use app\models\Goods;
use app\models\User;
use app\models\Util;
use yii\helpers\Url;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * 公开内容
 * Class PublicController
 * @package app\controllers
 */
class PublicController extends BaseController
{


    /**
     * 商品详情页面
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionGoodsContent()
    {
        $share_commission = $self_buy_ratio =   30;

        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            $share_commission = $user->childBuyRatio;

        }

        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }
        if ($goods->status != Goods::STATUS_ON) {
            throw new BadRequestHttpException('商品没有上架。');
        }

        /** $var $similar_list[] 相似产品列表   */
        $similar_list = [];

        $query = Goods::find();
        $query->andWhere(['cid' => $goods->cid]);
        $query->andWhere(['<>', 'id', $goods->id]);
        $query->andWhere(['is_pack' => Goods::NO]);
        $query->andWhere(['is_coupon' => Goods::NO]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->limit(6);
        /** @var Goods $item */
        foreach ($query->each() as $item) {
            $similar_list[] = [
                'id' => $item->id,
                'title' => $item->title,
                'desc' => $item->desc,
                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item->main_pic,
                'price' => $item->price,
                'share_commission' => round($item->share_commission_value * $share_commission / 100, 2),
                'share_url' => Url::to(['/h5/goods/view', 'id' => $goods->id], true),
            ];
        }
        $similar_list = Util::array_even($similar_list);

        return $this->render('goods_content', [
            'goods' => $goods,
            'similar_list' => $similar_list,
        ]);
    }
}
