<?php

namespace app\modules\api\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\Goods;
use app\models\Util;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 广告
 * Class DaController
 * @package app\modules\api\controllers
 */
class DaController extends BaseController
{
    /**
     * 广告列表
     * GET
     * lid 广告位编号
     */
    public function actionList()
    {
        $lid = $this->get('lid');
        $loc = AdLocation::findOne($lid);
        if (empty($loc)) {
            return [
                'error_code' => ErrorCode::AD_LOC,
                'message' => '没有找到广告位。',
            ];
        }
        $ad_list = [];
        foreach ($loc->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
            $_ad = [
                'id' => $ad->id,
                'name' => $ad->name,
                'txt' => $ad->txt,
                'img' => Util::fileUrl($ad->img),
                'url' => $ad->url,
                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
            ];
            if ($loc->type = AdLocation::TYPE_GOODS) {
                $goods = Goods::findOne($ad['txt']);
                if (!empty($goods) && $goods->status == Goods::STATUS_ON) {
                    $_ad['goods'] = [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'main_pic' => Util::fileUrl($goods->main_pic, true, '_300x300'),
                        'min_price' => $goods->getMinPrice(),
                    ];
                }
            }
            $ad_list[] = $_ad;
        }
        Ad::updateAllCounters(['show' => 1], ['id' => ArrayHelper::getColumn($ad_list, 'id')]);
        return [
            'da_list' => $ad_list,
        ];
    }

    /**
     * 广告列表
     * GET
     * id 广告位编号
     */
    public function actionPackDetail()
    {
        $id = $this->get('id');
        $id = Yii::$app->params['site_host'] == 'http://yuntaobang.ysjjmall.com' ? 20: 24;
        $ad = Ad::findOne($id);
        if (empty($ad)) {
            return [
                'error_code' => ErrorCode::AD_LOC,
                'message' => '没有找到广告。',
            ];
        }
        $loc = AdLocation::findOne($ad->lid);
        if (empty($loc)) {
            return [
                'error_code' => ErrorCode::AD_LOC,
                'message' => '没有找到广告位。',
            ];
        }


        return [
            'url' => $ad->url,
        ];
    }

    /**
     * 广告列表
     * GET
     * id 广告位编号
     */
    public function actionDetail()
    {
        $id = $this->get('id');

        $ad = Ad::findOne($id);
        if (empty($ad)) {
            return [
                'error_code' => ErrorCode::AD_LOC,
                'message' => '没有找到广告。',
            ];
        }
        $loc = AdLocation::findOne($ad->lid);
        if (empty($loc)) {
            return [
                'error_code' => ErrorCode::AD_LOC,
                'message' => '没有找到广告位。',
            ];
        }
        $ad_list = [];
        $_ad = [
            'id' => $ad->id,
            'name' => $ad->name,
            'txt' => $ad->txt,
            'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
            'url' => $ad->url,
            'location' => $ad->location->getAttributes(['id', 'height', 'width']),
        ];
        if ($loc->type = AdLocation::TYPE_GOODS) {
            $goods = Goods::findOne($ad['txt']);
            if (!empty($goods) && $goods->status == Goods::STATUS_ON) {
                $_ad['goods'] = [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    'min_price' => $goods->getMinPrice(),
                ];
            }
        }
        $ad_list[] = $_ad;

        Ad::updateAllCounters(['show' => 1], ['id' => ArrayHelper::getColumn($ad_list, 'id')]);
        return [
            'da_list' => $ad_list,
        ];
    }

    /**
     * 广告点击
     * GET
     * id 广告编号
     */
    public function actionClick()
    {
        $id = $this->get('id');
        $ad = Ad::findOne($id);
        if (empty($ad)) {
            return [
                'error_code' => ErrorCode::AD_NOT_FOUND,
                'message' => '没有找到广告信息。',
            ];
        }
        Ad::updateAllCounters(['click' => 1], ['id' => $id]);
        return [
            'url' => $ad->url,
        ];
    }
}
