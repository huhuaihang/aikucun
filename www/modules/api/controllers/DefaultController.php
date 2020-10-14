<?php

namespace app\modules\api\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\City;
use app\models\Goods;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\models\System;
use app\models\SystemVersion;
use app\models\Util;
use app\models\WeixinMpApi;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;
use yii\data\Pagination;

/**
 * 默认控制器
 * Class DefaultController
 * @package app\modules\api\controllers
 */
class DefaultController extends BaseController
{
    /**
     * 服务器时间戳
     * GET
     */
    public function actionServerTime()
    {
        return [
            'timestamp' => time(),
        ];
    }

    /**
     * 服务器版本
     * GET
     */
    public function actionVersion()
    {
        /** @var SystemVersion $version */
        $version = SystemVersion::find()->orderBy('create_time DESC')->one();
        if (empty($version)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '系统没有定义版本号信息。',
            ];
        }
        return [
            'api_version' => $version->api_version,
            'ios_version' => $version->ios_version,
            'android_version' => $version->android_version,
            'android_download_source' => $version->android_download_source,
            'android_download_url' => $version->android_download_url,
            'update_info' => $version->update_info,
        ];
    }

    /**
     * 服务器配置
     * GET
     */
    public function actionServerConfig()
    {
        return [
            'upload_url' => Yii::$app->params['upload_url'],
        ];
    }

    /**
     * 地址列表
     * GET
     * format 返回格式 flat 编码对应名称 tree 上下级组织好
     * level  返回级别 1 省 2 省市 3 省市区
     */
    public function actionCity()
    {
        $format = $this->get('format');
        $level = $this->get('level');
        if ($format == 'flat') {
            $query = City::find();
            if ($level == 1) {
                $query->andWhere(['like', 'code', '%0000', false]);
            } elseif ($level == 2) {
                $query->andWhere(['like', 'code', '%00', false]);
            }
            return [
                'city_list' => ArrayHelper::map($query->orderBy('code ASC')->all(), 'code', 'name')
            ];
        } elseif ($format == 'tree') {
            $p_list = [];
            foreach (City::getMap($level) as $p_code => $p) {
                $c_list = [];
                if ($level > 1) {
                    foreach ($p['c_list'] as $c_code => $c) {
                        $a_list = [];
                        if ($level > 2) {
                            foreach ($c['a_list'] as $a_code => $a) {
                                $a_list[] = [
                                    'code' => $a_code,
                                    'name' => $a['name'],
                                ];
                            }
                        }
                        $c_list[] = [
                            'code' => $c_code,
                            'name' => $c['name'],
                            'a_list' => $a_list,
                        ];
                    }
                }
                $p_list[] = [
                    'code' => $p_code,
                    'name' => $p['name'],
                    'c_list' => $c_list,
                ];
            }
            return [
                'p_list' => $p_list,
            ];
        }
        return [
            'error_code' => ErrorCode::PARAM,
            'message' => '没有找到类型参数。',
        ];
    }

    /**
     * 商品分类（一级分类）
     * GET
     */

    public  function  actionCategory()
    {

        if ($this->client_api_version > '1.0.3') {
            $choicest = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW,'pid'=>null])->orderBy('sort desc');

            $category=[];
            foreach ($choicest->each()  as $cat)
            {

                $category[]=[
                    'id'=>$cat->id,
                    'name'=>$cat->name,

                ];
            }
            return [
                'category' => $category,

            ];
        }
        else
        {
        $sub_tree_cate = GoodsCategory::subtree('', 0, 1);
//        $choicest = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW, 'is_choicest' => 1])->asArray()->all();
//        $best = array('id' => '0', 'pid' => '', 'name' => '精选','image' => '', 'lev' => 1, 'menu' => [0 => ['id' => '0', 'pid' => '', 'image' => '', 'name' => '云约精选', 'lev' => 2, 'menu' => $choicest]]);
//        array_unshift($sub_tree_cate, $best);
        foreach ($sub_tree_cate as $k1 => $sub_cate) {
            $sub_tree_cate[$k1]['pid'] = '';
            empty($sub_tree_cate[$k1]['image']) ? $sub_tree_cate[$k1]['image'] = '' : $sub_tree_cate[$k1]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $sub_cate['image'];
            foreach ($sub_cate['menu'] as $k2 => $sub) {
                empty($sub['image']) ? $sub_tree_cate[$k1]['menu'][$k2]['image'] = '' : $sub_tree_cate[$k1]['menu'][$k2]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $sub['image'];
                if (!empty($sub['menu'])) {
                    foreach ($sub['menu'] as $k3 => $cate) {
                        empty($sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image']) ? $sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image'] = '' : $sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $cate['image'];
                    }
                }
            }
        }
        return ['sub_tree_cate' => $sub_tree_cate];
        }
    }
    /**
     * 商品分类（一级分类关联商品）
     * GET
     */
    public function actionCatgoods()
    {

        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $user = null;
        }
        $cid = $this->get('cid');
        if($cid=='' || $cid=='undefined')
        {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '分类id不存在。',
            ];
//            $choicest = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW,'pid'=>null])->all();
//            $cid=reset($choicest)['id'];
        }
        //$cid = $this->get('cid', 12);
        //分享佣金
        $share_commission = 30;
        $commission_ratio = 0;
        if ($user) {
            $share_commission = $user->childBuyRatio;
            $commission_ratio = $user->buyRatio;
        }
        $query = Goods::find();
        $query->andWhere(['cid' => $cid]);
        $query->andWhere(['is_pack' => 0]);
        $query->andWhere(['status' => Goods::STATUS_ON]);
        $query->andWhere(['is_coupon' => 0]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('create_time desc, sort asc');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $goods_item=[];
        /** @var Goods $v */
        foreach ($query->each() as $v) {
            $limit = [
                'is_limit' => empty($v->is_limit) ? 0 : $v->is_limit,
                'limit_type' => empty($v->limit_type) ? '' : $v->limit_type,
                'limit_type_str' => empty($v->limit_type) ? '' : KeyMap::getValue('goods_limit_type', $v->limit_type),
                'limit_amount' => empty($v->limit_amount) ? '' : $v->limit_amount,
            ];
            $json = (object)$limit;
            $goods_item[] = [
                'id' => $v->id,
                'title' => $v->title,
                'desc' => $v->desc,
                'price' => $v->price,
                'share_commission' => Util::convertPrice($v->share_commission_value * $share_commission / 100),
                'self_price' => Util::convertPrice($v->share_commission_value * $commission_ratio / 100),
                'main_pic' => $this->client_api_version > '1.0.6' ? Util::fileUrl($v->main_pic, true, '_300x300') : Util::fileUrl($v->main_pic),
                'sale_amount' => $v->getSaleAmount(),
                'limit' => $json
            ];
        }

        // 商品分类轮播广告

        $banner_list = [];
        /** @var AdLocation $query1 */
        $query1 = AdLocation::find()->andwhere(['remark' => $cid])->one();
        if (!empty($query)) {
            foreach ($query1->getActiveAdList()->each() as $ad) {
                /** @var Ad $ad */
                $banner_list[] = [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'txt' => $ad->txt,
                    'img' => Util::fileUrl($ad->img),
                    'url' => $ad->url,
                    'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                ];
            }
        }

        return [
            'goods_item' => $goods_item,
            'banner_list'=>$banner_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 商品分类
     * GET
     */
//    public function actionCategory()
//    {
//        $sub_tree_cate = GoodsCategory::subtree('', 0, 1);
////        $choicest = GoodsCategory::find()->where(['status' => GoodsCategory::STATUS_SHOW, 'is_choicest' => 1])->asArray()->all();
////        $best = array('id' => '0', 'pid' => '', 'name' => '精选','image' => '', 'lev' => 1, 'menu' => [0 => ['id' => '0', 'pid' => '', 'image' => '', 'name' => '云约精选', 'lev' => 2, 'menu' => $choicest]]);
////        array_unshift($sub_tree_cate, $best);
//        foreach ($sub_tree_cate as $k1 => $sub_cate) {
//            $sub_tree_cate[$k1]['pid'] = '';
//            empty($sub_tree_cate[$k1]['image']) ?  $sub_tree_cate[$k1]['image'] = '' : $sub_tree_cate[$k1]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $sub_cate['image'];
//            foreach($sub_cate['menu'] as $k2 => $sub){
//                empty($sub['image']) ? $sub_tree_cate[$k1]['menu'][$k2]['image'] = '' : $sub_tree_cate[$k1]['menu'][$k2]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $sub['image'];
//                if (!empty($sub['menu'])) {
//                    foreach($sub['menu'] as $k3 => $cate){
//                        empty($sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image']) ? $sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image'] = '' :  $sub_tree_cate[$k1]['menu'][$k2]['menu'][$k3]['image'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $cate['image'];
//                    }
//                }
//            }
//        }
//        return ['sub_tree_cate' => $sub_tree_cate];
//    }

    /**
     * 上传单文件
     * GET
     * dir 保存目录
     * POST
     * file 上传文件
     */
    public function actionUpload()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $dir = $this->get('dir');
        if (empty($dir)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到保存位置参数。',
            ];
        }
        if (!in_array($dir, [
            'brand',
            'da',
            'goods',
            'goods_category',
            'goods_comment',
            'merchant',
            'shop',
            'user_avatar',
            'order_refund',
        ])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '保存位置参数错误。',
            ];
        }
        $file = UploadedFile::getInstanceByName('file');
        if (empty($file)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到上传文件。',
            ];
        }
        $relative_path = $dir . '/' . date('y/m/');
        $real_path = Yii::$app->params['upload_path'] . $relative_path;
        try {
            if (!file_exists($real_path)
                && !FileHelper::createDirectory($real_path)) {
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '无法创建目录。',
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法创建目录。',
            ];
        }
        $file_name = substr(uniqid(md5(rand()), true), 0, 10);
        $file_name .= '-' . Inflector::slug($file->baseName);
        $file_name .= '.' . $file->extension;
        $uri = $relative_path . $file_name;
        if (!$file->saveAs($real_path . $file_name)) {
            Yii::error('无法保存上传文件：' . print_r($file->error, true));
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法保存文件。',
                'errors' => ['file' => [$file->error]],
            ];
        }
        return [
            'uri' => $uri,
            'url' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $uri,
        ];
    }

    /**
     * 上传多文件
     * GET
     * dir 保存目录
     * POST
     * file[] 上传文件列表
     */
    public function actionUploadMulti()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $dir = $this->get('dir');
        if (empty($dir)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到保存位置参数。',
            ];
        }
        if (!in_array($dir, [
            'brand',
            'da',
            'goods',
            'goods_category',
            'goods_comment',
            'merchant',
            'shop',
            'user_avatar',
            'order_refund',
        ])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '保存位置参数错误。',
            ];
        }
        $files = UploadedFile::getInstancesByName('files');
        if (empty($files)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到上传文件。',
            ];
        }
        $relative_path = $dir . '/' . date('y/m/');
        $real_path = Yii::$app->params['upload_path'] . $relative_path;
        try {
            if (!file_exists($real_path)
                && !FileHelper::createDirectory($real_path)) {
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '无法创建目录。',
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法创建目录。',
            ];
        }
        $file_list = [];
        foreach ($files as $file) {
            $file_name = substr(uniqid(md5(rand()), true), 0, 10);
            $file_name .= '-' . Inflector::slug($file->baseName);
            $file_name .= '.' . $file->extension;
            $uri = $relative_path . $file_name;
            if (!$file->saveAs($real_path . $file_name)) {
                Yii::error('无法保存上传文件：' . print_r($file->error, true));
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '无法保存文件。',
                    'errors' => ['file' => [$file->error]],
                ];
            }
            $file_list[] = [
                'uri' => $uri,
                'url' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $uri,
            ];
        }
        return [
            'file_list' => $file_list,
        ];
    }

    /**
     * 获取字典
     */
    public function actionKeyMap()
    {
        $type = $this->get('type');
        $query = KeyMap::find();
        if (!empty($type)) {
            $query->andWhere(['t' => $type]);
        }
        $query->orderBy('t');
        $key_map = [];
        foreach ($query->each() as $key) {
            $key_map[] = [
                't' => $key->t,
                'k' => $key->k,
                'v' => $key->v,
            ];
        }
        // 用户中心的部分字典
        $key_map[] = ['t' => 'gender', 'k' => 0, 'v' => '保密'];
        $key_map[] = ['t' => 'gender', 'k' => 1, 'v' => '男'];
        $key_map[] = ['t' => 'gender', 'k' => 2, 'v' => '女'];
        return [
            'key_map' => $key_map
        ];
    }

    /**
     * 微信公众号JS配置
     * @throws \yii\base\Exception
     */
    public function actionWeixinMpJsConfig()
    {
        $config = (new WeixinMpApi())->jsWxConfig($this->get('url'));
        return [
            'wxConfig' => $config,
        ];
    }

    /**
     * 获取说明文字
     * @return array
     */
    public function actionDescription()
    {
        $name = $this->get('name', 'commission');
        $des = '';
        if ($name == 'commission') {
            $des = System::getConfig('commission_description');
        }
        if ($name == 'shop') {
            $des = System::getConfig('shop_description');
        }
        return [
            'des' => $des
        ];
    }

    /**
     * 获取系统设置
     * @return array
     */
    public function actionGetSystem()
    {
        $config = $this->get('config');
        if (empty($config)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数必填。',
            ];
        }
        $system = System::getConfig($config);
        return [
            'system' => $system
        ];
    }
}
