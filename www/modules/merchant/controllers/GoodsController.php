<?php

namespace app\modules\merchant\controllers;

use app\models\AliyunOssApi;
use app\models\DeliverTemplate;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsAttrValue;
use app\models\GoodsCategory;
use app\models\GoodsComment;
use app\models\GoodsCommentReply;
use app\models\GoodsCouponGift;
use app\models\GoodsCouponRule;
use app\models\GoodsDeliverTemplate;
use app\models\GoodsServiceMap;
use app\models\GoodsSku;
use app\models\GoodsTraceVideo;
use app\models\GoodsViolation;
use app\models\MemQueue;
use app\models\ShopBrand;
use app\models\ShopFile;
use app\models\ShopGoodsCategory;
use app\models\ShopTheme;
use app\models\Supplier;
use app\models\UserMessage;
use app\models\Util;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * 商品管理
 * Class GoodsController
 * @package app\modules\merchant\controllers
 */
class GoodsController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array_merge(parent::actions(), [
            'ue-upload' => [
                'class' => UEditorAction::className(),
                'config' => [
                    'imagePathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/goods/{yy}/{mm}/{time}-{rand:6}',
                ],
            ],
        ]);
    }

    /**
     * 商品列表
     * @return string
     */
    public function actionList()
    {
        $type=$this->get('type');
        $url_param=Yii::$app->request->queryString;
        $video_id = $this->get('video_id');
        $queue = new MemQueue('set_shop' . $this->shop->id .'_goods_');
        $goods_cache_data=$queue->read(10);
        $data=[];
        foreach ($goods_cache_data as $k=>$value)
        {
            if($value == false)
            {
             continue;
            }
            $data[$k]= $value;
        }

        $query = Goods::find()->alias('g');
        $query->joinWith('supplier s');
        if(empty($type) && strpos($url_param,'type') === false)
        {
            $query->andWhere(['is_pack' => Goods::NO]);
            $query->andWhere(['is_score' => Goods::NO]);
            $query->andWhere(['is_coupon' => Goods::NO]);
            $query->andWhere(['is_today' => Goods::NO]);
            $query->andWhere(['is_index_best' => Goods::NO]);
        }
        if($type == 'today')
        {
            $query->andWhere(['is_today' => Goods::YES]);
            $query->andWhere(['is_pack' => Goods::NO]);
//            $query->andWhere(['is_score' => Goods::NO]);
            $query->andWhere(['is_coupon' => Goods::NO]);
        }
        //邀新优品
        if($type == 'index_best')
        {
            $query->andWhere(['is_index_best' => Goods::YES]);
            $query->andWhere(['is_pack' => Goods::NO]);
//            $query->andWhere(['is_score' => Goods::NO]);
            $query->andWhere(['is_coupon' => Goods::NO]);
        }
        if($type == 'pack')
        {
            $query->andWhere(['is_pack' => Goods::YES]);
        }
        if($type == 'score')
        {
            $query->andWhere(['is_score' => Goods::YES]);
        }
        if($type == 'coupon')
        {
            $query->andWhere(['is_coupon' => Goods::YES]);
        }

        $query->andWhere(['<>', 'g.status', Goods::STATUS_DEL]);
        $query->andFilterWhere(['type' => Goods::TYPE_ONLINE]);
        $query->andFilterWhere(['sid' => $this->shop->id]);
        $query->andFilterWhere(['scid' => $this->get('search_scid')]);
        $query->andFilterWhere(['g.status' => $this->get('search_status')]);
        $query->andFilterWhere(['g.id' => $this->get('search_id')]);
        $query->andFilterWhere(['video_id' => $video_id]);
        $query->andFilterWhere(['like', 'title', trim($this->get('search_title'))]);
        $query->andFilterWhere(['like', 's.name', trim($this->get('search_supplier'))]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('g.create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'type' => $type,
            'model_list' => $model_list,
            'goods_cache_data' => $data,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 导入淘宝商品AJAX接口
     * @return array
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public function actionImport()
    {
        $files = UploadedFile::getInstancesByName('files');
        if (empty($files)) {
            return ['message' => '没有找到上传文件。'];
        }
        $file = fopen($files[0]->tempName, 'r');
        if (!$file) {
            return ['message' => '无法读取文件。'];
        }
        $bom = fread($file, 2);
        rewind($file);
        if($bom === chr(0xff).chr(0xfe)  || $bom === chr(0xfe).chr(0xff)){
            // UTF16 Byte Order Mark present
            $encoding = 'UTF-16';
        } else {
            $file_sample = fread($file, 1000) . 'e'; // read first 1000 bytes
            // . e is a workaround for mb_string bug
            rewind($file);
            $encoding = mb_detect_encoding($file_sample , 'UTF-8, UTF-7, ASCII, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
        }
        if ($encoding){
            stream_filter_append($file, 'convert.iconv.' . $encoding . '/UTF-8');
        }
        fgetcsv($file, 0, chr(9)); // Skip version line
        fgetcsv($file, 0, chr(9)); // Skip english title line
        fgetcsv($file, 0, chr(9)); // Skip chinese title line
        while (!feof($file)) {
            $item = fgetcsv($file, 0, chr(9));
            if (empty($item) || empty($item[0])) {
                continue;
            }

            $goods = new Goods();
            $goods->type = $this->merchant->identity['type'];
            $goods->sid = $this->shop->id;
            $goods->tid = 3; // 类型
            // 根据淘宝类目计算最相似的类目编号
            $taobaoCidName = $this->taobaoCid($item[1]);
            if (!empty($taobaoCidName)) {
                $minDistance = 1;
                $similarCid = null;
                /** @var GoodsCategory $goodsCategory */
                foreach (GoodsCategory::find()->each() as $goodsCategory) {
                    $distance = Util::strEditDistance($taobaoCidName, $goodsCategory->name);
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $similarCid = $goodsCategory->id;
                    }
                }
                $goods->cid = $similarCid;
            }
            if (empty($goods->cid)) {
                $goods->cid = 116; // 食品生鲜
            }
            $goods->scid = null; // 店铺商品分类编号
            $goods->bid = null; // 商品品牌编号
            $goods->title = $item[0];
            $goods->keywords = $item[32];
            $goods->desc = $item[57];
            $goods->price = $item[7];
            $goods->share_commission_type = null; // 佣金计算方式
            $goods->share_commission_value = null; // 佣金或百分比
            $goods->stock = $item[9];
            $picture_list = explode(';', $item[28]);
            $detail_pics = [];
            foreach ($picture_list as $picture) {
                if (empty($picture)) {
                    continue;
                }
                $picture = preg_replace('/.*?\|/', '', $picture);
                $dir = 'goods';
                $relative_path = $dir . '/' . date('y/m/');
                $real_path = Yii::$app->params['upload_path'] . $relative_path;
                if (!file_exists($real_path)
                    && !FileHelper::createDirectory($real_path)) {
                    throw new ServerErrorHttpException('无法创建目录。');
                }
                $file_name = substr(uniqid(md5(rand()), true), 0, 10) . '.jpg';
                $uri = $relative_path . $file_name;
                if (!file_put_contents($real_path . $file_name, file_get_contents($picture))) {
                    throw new ServerErrorHttpException('无法保存图片。');
                }
                $shop_file = new ShopFile();
                $shop_file->sid = $this->shop->id;
                $shop_file->type = ShopFile::TYPE_IMAGE;
                $shop_file->url = $uri;
                $shop_file->status = ShopFile::STATUS_OK;
                $shop_file->create_time = time();
                $shop_file->save();
                if (empty($goods->main_pic)) {
                    $goods->main_pic = $relative_path . $file_name;
                }
                $detail_pics[] = $relative_path . $file_name;
            }
            $goods->detail_pics = json_encode($detail_pics);
            // $goods->content = $item[20]; // 电脑端详情
            $content = '<p>';
            $xml = (array) simplexml_load_string($item[53]); // 无线端详情 <wapDesc><img>xxx</img><img>yyy</img></wapDesc>
            if (isset($xml['img'])) {
                foreach ($xml['img'] as $img) {
                    $dir = 'goods';
                    $relative_path = $dir . '/' . date('y/m/');
                    $real_path = Yii::$app->params['upload_path'] . $relative_path;
                    if (!file_exists($real_path)
                        && !FileHelper::createDirectory($real_path)) {
                        throw new ServerErrorHttpException('无法创建目录。');
                    }
                    $file_name = substr(uniqid(md5(rand()), true), 0, 10) . '.jpg';
                    $uri = $relative_path . $file_name;
                    if (!file_put_contents($real_path . $file_name, file_get_contents($img))) {
                        throw new ServerErrorHttpException('无法保存图片。');
                    }
                    $shop_file = new ShopFile();
                    $shop_file->sid = $this->shop->id;
                    $shop_file->type = ShopFile::TYPE_IMAGE;
                    $shop_file->url = $uri;
                    $shop_file->status = ShopFile::STATUS_OK;
                    $shop_file->create_time = time();
                    $shop_file->save();
                    $content .= '<img src="' . Yii::$app->params['upload_url'] . $relative_path . $file_name . '"/>';
                }
            }
            $content .= '</p>';
            $goods->content = $content;
            $goods->status = Goods::STATUS_OFF;
            $goods->create_time = time();
            $goods->deliver_fee_type = null;
            $goods->weight = empty($item[50]) ? null : $item[50];
            $goods->bulk = empty($item[49]) ? null : $item[49];
            $goods->remark = json_encode($item);
            if (!$goods->save()) {
                return [
                    'message' => '无法保存商品信息。',
                    'errors' => $goods->errors,
                ];
            }
        }
        fclose($file);
        return ['result' => 'success', 'files' => [['url' => '']]];
    }

    /**
     * 添加商品时信息时时写入缓存
     * @return string|array
     */
    public function actionSetGoodsCache()
    {
        $goods = new Goods();
        $goods->type = $this->merchant->identity['type'];
        $goods->sid = $this->shop->id;
        $goods->create_time = time();
        $goods->status = Goods::STATUS_OFF;
        $goods->share_commission_type = 1;
        $set_key=$this->post('set_cache_key');
        $exist_attr_value_list=[];
        $sku_cache_list=[];
        $goods_deliver_template_cache_list=[];
        $sku_attr_value_map = []; // 规格属性值和编号对应['颜色:红' => 1, '颜色:黄' => 2, '尺码:大' => 3, '尺码:小' => 4]
        if ($goods->load($this->post())) {

            // 商品属性及规格
            $post_attr_value = $this->post('GoodsAttrValue');
            if (!empty($post_attr_value) && is_array($post_attr_value)) {

                // 规格属性
                foreach ($post_attr_value as $aid => $value_image_list) {
                    $attr = GoodsAttr::findOne($aid);
                    $value_count = count($value_image_list['value']);
                    for ($i = 0; $i < $value_count; $i++) {
                        if (empty($value_image_list['value'][$i])) {
                            continue;
                        }
                        $attr_value = new GoodsAttrValue();
                        $attr_value->aid = $aid;
                        $attr_value->value = $value_image_list['value'][$i];
                        $attr_value->image = isset($value_image_list['image'][$i]) ? $value_image_list['image'][$i] : '';
                        $exist_attr_value_list[]=$attr_value;
                      $sku_attr_value_map[$attr->name . ':' . $attr_value->value] = $attr_value->id;
                    }
                }
            }

            // 规格
            $post_sku = $this->post('GoodsSku');
            if (!empty($post_sku) && is_array($post_sku)) {
                foreach ($post_sku as $key_name => $stock_price) {
                    $key = [];
                    $key_name_list = explode('_', $key_name);
                    foreach ($key_name_list as $_name) {
                        $_key = $sku_attr_value_map[$_name];
                        $key[] = $_key;
                    }
                    $key = implode('_', $key);
                    /** @var GoodsSku $sku */
                        $sku = new GoodsSku();
                    $sku->key = $key;
                    $sku->key_name = $key_name;
                    $sku->stock = $stock_price['stock'];
                    $sku->market_price = $stock_price['market_price'];
                    $sku->price = $stock_price['price'];
                    $sku->supplier_price = $stock_price['supplier_price'];
                    $sku->commission = $stock_price['commission'];
                    $sku->img = $stock_price['image'];
                    $sku_cache_list[]=$sku;
                }
            }
            // 运费模板
            $goods_deliver_template_list = $this->post('goods_deliver_template_list');
            if (!empty($goods_deliver_template_list)) {
               // GoodsDeliverTemplate::deleteAll(['gid' => $goods->id]);
                $goods_deliver_template_list = json_decode($goods_deliver_template_list, true);
                foreach ($goods_deliver_template_list as $did) {
                    $deliver_template = DeliverTemplate::findOne($did);
                    $goods_deliver_template_cache_list[]=$deliver_template;
                }
            }


            Yii::$app->cache->set($set_key,[
                'goods' => $goods,
                'exist_attr_value_list' =>$exist_attr_value_list,
                'sku_cache_list' => $sku_cache_list,
                'goods_deliver_template_cache_list' =>$goods_deliver_template_cache_list,
            ],0);

        }
       // $aa=Yii::$app->cache->get($set_key);
        return [
            'result' => 'success'
        ];
    }
    /**
     * 清空新增商品缓存信息
     * @return string|array
     */
    public function actionClearAll()
    {
        $queue = new MemQueue('set_shop' . $this->shop->id . '_goods_');//初始化缓存对象
        if(!$queue->clear())
        {
            return ['message' => '操作失败'];
        }
        return [
            'result' => 'success'
        ];
    }
    /**
     * 添加/修改商品
     * @return string|array
     * @throws NotFoundHttpException
     */
    public function actionEdit()
    {

        if (empty($this->shop->tid)) {
            // 指定一个默认的主题
            /** @var ShopTheme $theme */
            $theme = ShopTheme::find()->orderBy('id ASC')->one();
            $this->shop->tid = $theme->id;
            $this->shop->save();
        }
        $id = $this->get('id');
        $set_cache_key = false;
        $set_goods_cache=[];
        $queue = new MemQueue('set_shop' . $this->shop->id . '_goods_');//初始化缓存对象
        if (!empty($id)) {
            $goods = Goods::findOne($id);
            if (empty($goods) || $goods->shop->mid != $this->merchant->id) {
                throw new NotFoundHttpException('没有找到商品信息。');
            }
        } else {
            $goods = new Goods();
            $goods->type = $this->merchant->identity['type'];
            $goods->sid = $this->shop->id;
            $goods->create_time = time();
            $key = $this->get('key');//获取缓存键名
            if(empty($this->get('tid'))) {
                $goods->tid = $this->get('tid');
                if (empty($key)) {
                    $set_cache_key = $queue->add([
                        'goods' => $goods,
                    ]);
                } else {
                    $set_cache_key = $key;
                    $set_goods_cache = Yii::$app->cache->get($key);//获取缓存设置商品信息
                    $goods = $set_goods_cache['goods'];
                }
            }
        }
        $goods->status = Goods::STATUS_OFF;
        $goods->share_commission_type = 1;
        $shop_goods_category_map = ArrayHelper::map(ShopGoodsCategory::find()
            ->asArray()
            ->andWhere(['sid' => $goods->sid])
            ->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL])
            ->orderBy('sort DESC, id ASC')
            ->all(),
            'id', 'name');
        $brand_map = ArrayHelper::map(ShopBrand::find()
            ->asArray()
            ->joinWith('brand')
            ->select(['bid' => 'bid', 'name'])
            ->andWhere(['sid' => $goods->sid])
            ->andWhere(['status' => ShopBrand::STATUS_VALID])
            ->all(),
            'bid', 'name');
        $deliver_template_map = ArrayHelper::map(DeliverTemplate::find()
            ->joinWith('shopExpress')
            ->joinWith('shopExpress.shop')
            ->andWhere(['{{%shop}}.mid' => $this->merchant->id])
            ->andWhere(['or',['{{%deliver_template}}.gid' => null],['{{%deliver_template}}.gid' => $id]])
            ->andWhere(['{{%deliver_template}}.status' => DeliverTemplate::STATUS_OK])
            ->all(), 'id', 'name');

        $goods_did=GoodsDeliverTemplate::find()->where(['gid'=>$id]);
        /** @var $item GoodsDeliverTemplate */
        foreach ($goods_did->each() as $item)
        {
            unset($deliver_template_map[$item->did]);
        }
        //获取缓存物流模板设置
       if(isset($set_goods_cache['goods_deliver_template_cache_list']) && !empty($set_goods_cache['goods_deliver_template_cache_list']) )
       {
           foreach ($set_goods_cache['goods_deliver_template_cache_list'] as $item)
           {
               unset($deliver_template_map[$item->id]);
           }
       }

        $supplier_map = ArrayHelper::map(Supplier::find()
            ->asArray()
            ->andWhere(['status' => Supplier::STATUS_OK])
            ->all(),
            'id', 'name');

        $video_map = ArrayHelper::map(GoodsTraceVideo::find()
            ->asArray()
            ->andWhere(['status' => GoodsTraceVideo::STATUS_OK,'sid' => $goods->sid ])
            ->orderBy('create_time desc')
            ->all(),
            'id', 'name');
        if ($goods->load($this->post()) && $goods->validate()) {

            $trans = Yii::$app->db->beginTransaction();
            try {
                if ($goods->sale_type == Goods::TYPE_SUPPLIER && empty($goods->supplier_id)) {
                    throw new Exception('请选择供货商。');
                }
                if ($goods->sale_type == Goods::TYPE_SUPPLIER && empty($goods->supplier_price)) {
                    throw new Exception('请填写结算价。');
                }
                if ($goods->is_limit == 1 && (empty($goods->limit_type) || empty($goods->limit_amount))) {
                    throw new Exception('请填写限购类型和数量。');
                }
                if($goods->is_pack != 1)
                {
                 $goods->is_pack_redeem=0;
                }
                // 商品基础数据
                $r = $goods->save(false);
                if (!$r) {
                    throw new Exception('');
                }
                $is_best = $this->post('service_map');
                // 商品服务
                GoodsServiceMap::deleteAll(['gid' => $goods->id]);
                $serviceMap = $this->post('service_map');
                if (!empty($serviceMap) && is_array($serviceMap)) {
                    foreach ($this->post('service_map') as $serviceId) {
                        $serviceMap = new GoodsServiceMap();
                        $serviceMap->gid = $goods->id;
                        $serviceMap->sid = $serviceId;
                        $serviceMap->save();
                    }
                }
                // 商品属性及规格
                $post_attr_value = $this->post('GoodsAttrValue');
                if (!empty($post_attr_value) && is_array($post_attr_value)) {
                    // 一般属性
                    $attr_list = GoodsAttr::find()
                        ->andWhere(['tid' => $goods->tid, 'is_sku' => 0])
                        ->all();
                    foreach ($attr_list as $attr) {
                        /** @var GoodsAttr $attr */
                        /** @var GoodsAttrValue $attr_value */
                        $attr_value = GoodsAttrValue::find()->andWhere(['gid' => $goods->id, 'aid' => $attr->id])->one();
                        if (empty($attr_value)) {
                            $attr_value = new GoodsAttrValue();
                            $attr_value->gid = $goods->id;
                            $attr_value->aid = $attr->id;
                        }
                        if (empty($post_attr_value[$attr->id])) {
                            try {
                                $attr_value->delete();
                            } catch (\Throwable $t) {
                            }
                        } else {
                            $attr_value->value = $post_attr_value[$attr->id]['value'];
                            $r = $attr_value->save();
                            if (!$r) {
                                throw new Exception('无法保存属性：' . $attr->name . '：' . $attr_value->value);
                            }
                            unset($post_attr_value[$attr->id]);
                        }
                    }

                    // 规格属性
                    $sku_attr_value_map = []; // 规格属性值和编号对应['颜色:红' => 1, '颜色:黄' => 2, '尺码:大' => 3, '尺码:小' => 4]
                    foreach ($post_attr_value as $aid => $value_image_list) {
                        $attr = GoodsAttr::findOne($aid);
                        GoodsAttrValue::deleteAll(['gid' => $goods->id, 'aid' => $aid]);
                        $value_count = count($value_image_list['value']);
                        for ($i = 0; $i < $value_count; $i++) {
                            if (empty($value_image_list['value'][$i])) {
                                continue;
                            }
                            $attr_value = new GoodsAttrValue();
                            $attr_value->gid = $goods->id;
                            $attr_value->aid = $aid;
                            $attr_value->value = $value_image_list['value'][$i];
                            $attr_value->image = isset($value_image_list['image'][$i]) ? $value_image_list['image'][$i] : '';
                            $r = $attr_value->save();
                            if (!$r) {
                                throw new Exception('无法保存规格属性：' . $attr->name . '：' . $attr_value->value . '：' . $attr_value->image);
                            }
                            $sku_attr_value_map[$attr->name . ':' . $attr_value->value] = $attr_value->id;
                        }
                    }

                    // 规格
                    $post_sku = $this->post('GoodsSku');
                    if (!empty($post_sku) && is_array($post_sku)) {
                        foreach ($post_sku as $key_name => $stock_price) {
                            $key = [];
                            $key_name_list = explode('_', $key_name);
                            foreach ($key_name_list as $_name) {
                                if (isset($sku_attr_value_map[$_name])) {
                                    $_key = $sku_attr_value_map[$_name];
                                    if (empty($_key)) {
                                        throw new Exception('没有找到对应的属性：' . $_name);
                                    }
                                    $key[] = $_key;
                                }
                            }
                            $key = implode('_', $key);
                            /** @var GoodsSku $sku */
                            $sku = GoodsSku::find()->andWhere(['gid' => $goods->id, 'key_name' => $key_name])->one();
                            if (empty($sku)) {
                                $sku = new GoodsSku();
                                $sku->gid = $goods->id;
                            }
                            $sku->key = $key;
                            $sku->key_name = $key_name;
                            $sku->stock = $stock_price['stock'];
                            $sku->market_price = $stock_price['market_price'];
                            $sku->price = $stock_price['price'];
                            $sku->supplier_price = $stock_price['supplier_price'];
                            $sku->commission = $stock_price['commission'];
                            $sku->img = $stock_price['image'];
                            if ($goods->sale_type == Goods::TYPE_SUPPLIER && empty($stock_price['supplier_price'])) {
                                throw new Exception('无法保存SKU信息：' . $sku->key_name . '没设置结算价');
                            }
                            $r = $sku->save();
                            if (!$r) {
                                throw new Exception('无法保存SKU信息：' . $sku->key_name);
                            }
                        }
                    }
                }
                // 运费模板
                $goods_deliver_template_list = $this->post('goods_deliver_template_list');
                if (!empty($goods_deliver_template_list)) {
                    GoodsDeliverTemplate::deleteAll(['gid' => $goods->id]);
                    $goods_deliver_template_list = json_decode($goods_deliver_template_list, true);
                    foreach ($goods_deliver_template_list as $did) {
                        $deliver_template = DeliverTemplate::findOne($did);
                        if (empty($deliver_template) || $deliver_template->shopExpress->shop->mid != $this->merchant->id) {
                            throw new Exception('您选择的运费模板编号[' . $did . ']不存在。');
                        }
                        $goods_deliver_template = new GoodsDeliverTemplate();
                        $goods_deliver_template->gid = $goods->id;
                        $goods_deliver_template->did = $deliver_template->id;
                        $r = $goods_deliver_template->save();
                        if (!$r) {
                            throw new Exception('无法保存商品运费模板关联信息。');
                        }
                    }
                }
                $trans->commit();
               // $data=$goods_cache_data=$queue->read(20);
                if($set_cache_key!=false)
                {
                Yii::$app->cache->delete($set_cache_key);//保存数据成功 清除设置商品缓存信息
                }
                //unset($data[$set_cache_key]);
               //$aa=Yii::$app->cache->get($set_cache_key);
                if ($this->isAjax()) {
                    return ['result' => 'success'];
                } else {
                    Yii::$app->session->addFlash('success', '数据已保存。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/merchant/goods/list']),
                        'txt' => '商品列表'
                    ]));
                }
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                $msg = $e->getMessage();
                if (!empty($msg)) {
                    $goods->addError('title', $msg);
                }
            }
        }
        if ($this->isAjax()) {
            $errors = $goods->errors;
            if (!empty($errors)) {
                $error = array_pop($errors);
                return ['message' => $error[0], 'errors' => $errors];
            }
        }
        return $this->render('edit', [
            'goods' => $goods,
            'shop_goods_category_map' => $shop_goods_category_map,
            'brand_map' => $brand_map,
            'video_map' => $video_map,
            'deliver_template_map' => $deliver_template_map,
            'supplier_map' => $supplier_map,
            'set_cache_key' =>$set_cache_key,
        ]);
    }

    /**
     * 删除商品AJAX接口
     * @return array
     */
    public function actionDelete()
    {
        $id = $this->get('id');
        $model = Goods::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商品信息。'];
        }
        if (empty($model->shop) || $model->shop->mid != $this->merchant->id) {
            return ['message' => '没有权限。'];
        }
        $model->status = Goods::STATUS_DEL;
        $model->save(false);
        return ['result' => 'success'];
    }

    /**
     * 设置商品类型AJAX接口(今日推荐|邀新优品等)
     * @return array
     */
    public function actionSetGoods()
    {
        $gid_array = $this->get('gid_array');
        $type=$this->get('type');
        if(empty($gid_array) || empty($type))
        {
            return ['message' => '参数错误。'];
        }

        foreach ($gid_array as $gid)
        {
            $model = Goods::findOne($gid);
            if (empty($model)) {
               continue;
            }
            if($model->is_pack == 1 || $model->is_score == 1 || $model->is_coupon == 1)
            {
                continue;
            }
            switch ($type)
            {
                case 'today':
                    $model->is_today = 1;
                 break;
                case 'index_best':
                    $model->is_index_best = 1;
                  break;
                default:
                    continue;
            }
            $model->save(false);
        }

//        if (empty($model->shop) || $model->shop->mid != $this->merchant->id) {
//            return ['message' => '没有权限。'];
//        }
//        $model->status = Goods::STATUS_DEL;
//        $model->save(false);
        return ['result' => 'success'];
    }

    /**
     * 设置商品状态AJAX接口
     * @return array
     */
    public function actionStatus()
    {
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods) || $goods->shop->mid != $this->merchant->id || $goods->status == Goods::STATUS_DEL) {
            return ['message' => '没有找到商品信息。'];
        }
        $new_status = [
            Goods::STATUS_ON => Goods::STATUS_OFF,
            Goods::STATUS_OFF => Goods::STATUS_ON
        ][$goods->status];


        if ($new_status == Goods::STATUS_ON) {
            if (GoodsAttrValue::find()
                    ->joinWith('goods_attr')
                    ->andWhere(['is_sku' => 1])
                    ->andWhere(['gid' => $goods->id])
                    ->exists()
                &&
                !GoodsSku::find()
                    ->andWhere(['gid' => $goods->id])
                    ->exists()
            ) {
                return ['message' => '没有设置任何规格，无法上架。'];
            }
            if($goods->is_pack_redeem == 1)
            {
                $pack_redeem_count = Goods::find()->where(['and', ['is_pack_redeem' => 1], ['status' => Goods::STATUS_ON]])->count();
                if ($pack_redeem_count >= 1) {
                    return ['message' => '已有卡券礼包产品，请修改或者下架商品后再操作'];
                }
            }
            if($goods->is_coupon == 1)
            {
                $goods_coupon_count = Goods::find()->where(['and', ['is_coupon' => 1], ['status' => Goods::STATUS_ON]])->count();
                if ($goods_coupon_count >= 1) {
                    return ['message' => '已有活动在进行，请结束后在设置新活动开启'];
                }

                $coupon_gift = GoodsCouponRule::find()->where(['gid' => $goods->id])->one();
                if (empty($coupon_gift)) {
                    return ['message' => '请先设置该活动商品优惠券信息'];
                }

            }

            if($goods->is_pack == 1 && $goods->is_pack_redeem == 1)
            {
                $goods_redeem_count = Goods::find()->where(['and', ['is_pack' => 1],['is_pack_redeem' => 1],['status' => Goods::STATUS_ON]])->count();
                if ($goods_redeem_count >= 1) {
                    return ['message' => '已存在礼包卡券商品，请先下架或者更改产品类型'];
                }

            }

        }

        if (empty($goods->deliver_fee_type)) {
            return ['message' => '运费计费方式必选。'];
        }

        if ($goods->deliver_fee_type == Goods::DELIVER_FEE_TYPE_BULK && (empty($goods->bulk) || $goods->bulk == 0)) {
            return ['message' => '运费模板选择体积计费，体积必填。'];
        }

        if ($goods->deliver_fee_type == Goods::DELIVER_FEE_TYPE_WEIGHT && (empty($goods->weight) || $goods->weight == 0)) {
            return ['message' => '运费模板选择重量计费，重量必填。'];
        }

        /** @var GoodsViolation $goods_violation */
        $goods_violation = GoodsViolation::find()->where(['gid' => $id])->andWhere(['<>', 'status', GoodsViolation::STATUS_DEL])->one();
        if (!empty($goods_violation)) {
            $goods_violation->status = GoodsViolation::STATUS_WAIT_MANAGER;
            $goods_violation->save();
            return ['message' => '违规处理 待管理员审核。'];
        }
        $goods->status = $new_status;
        if ($goods->status == Goods::STATUS_ON) {
            $goods->sale_time = time();
        }
        $goods->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 设置商品状态AJAX接口
     * @return array
     */
    public function actionRecommend()
    {
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods) || $goods->shop->mid != $this->merchant->id || $goods->status == Goods::STATUS_DEL) {
            return ['message' => '没有找到商品信息。'];
    }
        $type = $this->get('type');
        if(empty($type))
        {
            return ['message' => '没有获得商品类型信息。'];
        }
        $new_type= [
            0 => 1,
            1 => 0,
            'NULL' => 1
        ][$goods->$type];
        $goods->$type = $new_type;
        $goods->save();
        return [
            'result' => 'success'
        ];
    }
    /**
     * 商品视频管理
     * @return array | string
     */
    public function actionVideoList()
    {

        $query = GoodsTraceVideo::find();
        $query->andFilterWhere(['like', 'name', $this->get('search_name')]);
        $query->andFilterWhere(['<>', 'status', GoodsTraceVideo::STATUS_DEL]);
        $query->andWhere(['sid'=>$this->shop->id]);
        $query->orderBy('create_time DESC');
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->offset($pagination->offset)->limit($pagination->limit);
        $videoList = $query->all();
        return $this->render('video_list', [
            'videoList' => $videoList,
            'pagination' => $pagination,
        ]);
    }
    /**
     * 添加、修改商品视频
     * @return array|string
     * @throws Exception
     */
    public function actionVideoEdit()
    {
        $id = $this->get('id');
        if (!empty($id)) {
            $traceVideo = GoodsTraceVideo::findOne(['id' => $id]);
            if (empty($traceVideo)) {
                throw new NotFoundHttpException('没有找到视频。');
            }
        } else {
            $traceVideo = new GoodsTraceVideo();
            $traceVideo->sid=$this->shop->id;
            $traceVideo->status = GoodsTraceVideo::STATUS_OK;
            $traceVideo->create_time = time();
        }
        if ($traceVideo->load($this->post()) && $traceVideo->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/goods/video-list']),
                'txt' => '视频列表'
            ]));
        }
        $ossName = 'ytb_2_' . Util::randomStr(8);
        $ossCoverName = $ossName . '.jpg';
        $ossVideoName = $ossName . '.mp4';
        return $this->render('video_edit', [
            'traceVideo' => $traceVideo,
            'ossCoverName' => $ossCoverName,
            'ossVideoName' => $ossVideoName,
            'ossPolicy' => (new AliyunOssApi())->ossPolicy('goods_trace'),
        ]);
    }
    /**
     * 删除视频AJAX接口
     * @return array
     */
    public function actionDeleteVideo()
    {
        $id = $this->get('id');
        $model = GoodsTraceVideo::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到视频信息。'];
        }
        $model->status = GoodsTraceVideo::STATUS_DEL;
        $model->save(false);
        return [
            'result' => 'success'
        ];
    }
    /**
     * 获取三级分类MapAJAX接口
     * @return array
     * [
     *     id1 => [
     *         'name' => name1,
     *         'children' => [
     *             id11 => [
     *                 'name' => name11,
     *                 'children' => [
     *                     id111 => [
     *                         'name' => name111,
     *                     ],
     *                     id112 => [],
     *                 ],
     *             ],
     *             id12 => [],
     *         ],
     *     ],
     *     id2 => [],
     * ]
     */
    public function actionCategoryMap()
    {
        $map = [];
        $cid = [];
        foreach (GoodsCategory::find()->andWhere(['pid' => null])->andFilterWhere(['in', 'id', $cid])->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL])->all() as $level1) {
            /** @var GoodsCategory $level1 */
            $level2_list = [];
            foreach (GoodsCategory::find()->andWhere(['pid' => $level1->id])->andFilterWhere(['in', 'id', $cid])->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL])->all() as $level2) {
                /** @var GoodsCategory $level2 */
                $level3_list = [];
                foreach (GoodsCategory::find()->andWHere(['pid' => $level2->id])->andWhere(['<>', 'status', GoodsCategory::STATUS_DEL])->all() as $level3) {
                    /** @var GoodsCategory $level3 */
                    $level3_list[$level3->id] = ['name' => $level3->name];
                }
                $level2_list[$level2->id] = ['name' => $level2->name, 'children' => $level3_list];
            }
            $map[$level1->id] = ['name' => $level1->name, 'children' => $level2_list];
        }
        return [
            'result' => 'success',
            'map' => $map
        ];
    }

    /**
     * 分类列表
     * @return string
     */
    public function actionCategory()
    {
        $query = ShopGoodsCategory::find();
        $query->andWhere(['sid' => $this->shop->id]);
        $query->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('sort DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('category', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加修改分类
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEditCategory()
    {
        $id = $this->get('id');
        if (!empty($id)) {
            $model = ShopGoodsCategory::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到分类信息。');
            }
        } else {
            $model = new ShopGoodsCategory();
            $model->status = ShopGoodsCategory::STATUS_SHOW;
            $model->sid = $this->shop->id;
        }
        if ($model->load($this->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/goods/category']),
                'txt' => '商品分类列表'
            ]));
        }
        return $this->render('category_edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除商品分类AJAX接口
     * @return array
     */
    public function actionCategoryDelete()
    {
        $id = $this->get('id');
        $model = ShopGoodsCategory::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到商品分类信息。'];
        }
        if ($model->sid != $this->shop->id) {
            return ['message' => '没有权限。'];
        }
        $model->status = ShopGoodsCategory::STATUS_DEL;
        $model->save(false);
        return ['result' => 'success'];
    }

    /**
     * 设置商品分类状态AJAX接口
     * @return array
     */
    public function actionCategoryStatus()
    {
        $id = $this->get('id');
        /* @var $model Goods */
        $model = ShopGoodsCategory::find()->where(['id' => $id])->andWhere(['<>', 'status', ShopGoodsCategory::STATUS_DEL])->one();
        if (empty($model)) {
            return ['message' => '没有找到商品分类信息。'];
        }
        if ($model->sid != $this->shop->id) {
            return ['message' => '没有权限。'];
        }
        $new_status = [
            ShopGoodsCategory::STATUS_SHOW => ShopGoodsCategory::STATUS_HIDE,
            ShopGoodsCategory::STATUS_HIDE => ShopGoodsCategory::STATUS_SHOW
        ][$model->status];
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }

    /**
     * 商品评价管理
     * @return string
     */
    public function actionComment()
    {
        $query = GoodsComment::find();
        $query->joinWith('goods')->joinWith('goods.shop');
        $query->andWhere(['{{%shop}}.mid' => $this->merchant->id]);
        $query->andWhere(['{{%goods_comment}}.pid' => null]); // 不查询追加评论
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('{{%goods_comment}}.is_reply ASC, {{%goods_comment}}.create_time DESC');
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('comment', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商品评价详情
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCommentView()
    {
        $id = $this->get('id');
        $comment = GoodsComment::findOne($id);
        if (empty($comment) || $comment->goods->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到商品评价详情。');
        }
        return $this->render('comment_view', [
            'comment' => $comment,
        ]);
    }

    /**
     * 商品评论回复AJAX接口
     * @return array
     */
    public function actionReplyComment()
    {
        $reply = new GoodsCommentReply();
        if ($reply->load($this->post())) {
            $comment = GoodsComment::findOne($reply->cid);
            if (empty($comment) || $comment->goods->shop->mid != $this->merchant->id) {
                return ['message' => '没有找到回复的评论信息。'];
            }
            $reply->create_time = time();
            if ($reply->save()) {
                $comment->is_reply = 1;
                $comment->save();
                $user_message = new UserMessage();
                $user_message->uid = $comment->uid;
                $user_message->title = '您的商品评价有新的回复内容';
                $user_message->content = '您对商品[' . Html::encode($comment->goods->title) . ']的评价有回复。';
                $user_message->status = UserMessage::STATUS_NEW;
                $user_message->create_time = time();
                $user_message->save();
                return ['result' => 'success'];
            }
            $errors = $reply->errors;
            $error = array_shift($errors)[0];
            return ['message' => $error, 'errors' => $errors];
        }
        return ['message' => '参数错误。'];
    }

    /**
     * 删除SKU AJAX接口
     * @return array
     */
    public function actionDeleteGoodsSku()
    {
        $id = $this->get('id');
        if (empty($id)) {
            return ['message' => '参数错误'];
        }
        $model = GoodsSku::findOne($id);
        if ($this->shop->id != $model->goods->sid) {
            return ['message' => '没有找到该sku'];
        }
        try {
            $model->delete();
            return ['result' => 'success'];
        } catch (\Throwable $t) {
            return ['message' => '无法删除当前sku'];
        }
    }


    /**
     * 活动商品优惠券编辑/新增
     * @throws  Exception
     * @return string
     */
    public function actionCouponEdit()
    {
        $gid=$this->get('gid');
        if (empty($gid)) {

            throw new Exception('没有获取商品id');

        } else {
            $model=GoodsCouponRule::find()->where(['gid' => $gid])->one();
            if(empty($model))
            {
                $model = new GoodsCouponRule();
                $model->gid = $gid;
                $model->status = GoodsCouponRule::STATUS_NO;
                $model->create_time = time();
            }
        }
        if ($model->load($this->post())) {
            if (!$model->save()) {
                throw new Exception('无法保存数据');
            }
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/goods/list?type=coupon']),
                'txt' => '活动商品列表'
            ]));
        }

        return $this->render('coupon_edit', [
            'model' => $model,
        ]);
    }
    /**
     * 活动赠品列表
     * @return string
     */
    public function actionGiftList()
    {
        $query = GoodsCouponGift::find();
        $query->andWhere(['<>','status',GoodsCouponGift::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('gift_list', [
            'model_list' => $list,
            'pagination' => $pagination,
        ]);
    }


    /**
     * 活动赠品编辑/新增
     * @throws Exception
     * @return string
     */
    public function actionGiftEdit()
    {
        $id=$this->get('id');
        if (empty($id)) {
            $model = new GoodsCouponGift();
            $model->status = GoodsCouponGift::STATUS_OK;
            $model->create_time = time();

        } else {
            $model = GoodsCouponGift::findOne($id);
        }
        if ($model->load($this->post())) {
            if (!$model->save()) {
                throw new Exception('无法保存数据');
            }
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/goods/gift-list']),
                'txt' => '赠品列表'
            ]));
        }

        return $this->render('gift_edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除赠品 AJAX接口
     * @return array
     */
    public function actionDeleteGift()
    {
        $id = $this->get('id');
        if (empty($id)) {
            return ['message' => '参数错误'];
        }
        $model = GoodsCouponGift::findOne($id);
        if (empty($model)) {
            return ['message' => '没有找到该赠品'];
        }
        try {
            $model->delete();
            return ['result' => 'success'];
        } catch (\Throwable $t) {
            return ['message' => '无法删除当前赠品'];
        }
    }

    /**
     * 设置赠品状态AJAX接口
     * @return array
     */
    public function actionGiftStatus()
    {
        $id = $this->get('id');
        /* @var $model GoodsCouponGift */
        $model = GoodsCouponGift::find()->where(['id' => $id])->andWhere(['<>', 'status', GoodsCouponGift::STATUS_DEL])->one();
        if (empty($model)) {
            return ['message' => '没有找到赠品信息。'];
        }

        $new_status = [
            GoodsCouponGift::STATUS_OK => GoodsCouponGift::STATUS_HIDE,
            GoodsCouponGift::STATUS_HIDE => GoodsCouponGift::STATUS_OK
        ][$model->status];
        $model->status = $new_status;
        $model->save();
        return [
            'result' => 'success'
        ];
    }
    /**
     * 淘宝类目
     * @param integer $cid
     * @return string|null
     */
    private function taobaoCid($cid)
    {
        $map = [
            '50014480' => '汽车用品/内饰品',
            '50014481' => '汽车外饰品/加装装潢/防护',
            '50018708' => '汽车零配件',
            '50014482' => '汽车影音/车用电子/电器',
            '50018720' => '汽车GPS导航仪及配件',
            '50014479' => '汽车美容/保养/维修',
            '50018772' => '车用清洗用品/清洗工具',
            '2618' => '出租/培训/其它',
            '50023950' => '实体服务',
            '50020835' => '摆件',
            '50021907' => '现代装饰画',
            '50021902' => '油画',
            '50002045' => 'DIY/数字油画',
            '50020841' => '照片/照片墙',
            '50020836' => '装饰器皿',
            '50000561' => '相框/画框',
            '50020840' => '贴饰',
            '50020842' => '装饰架/装饰搁板',
            '50020843' => '装饰挂牌',
            '50020834' => '雕刻工艺',
            '50001290' => '壁饰',
            '50020845' => '装饰挂钩',
            '50020846' => '风铃及配件',
            '50020848' => '蜡烛/烛台',
            '50020856' => '创意饰品',
            '50010356' => '工艺船',
            '50022440' => '工艺扇',
            '50020854' => '香薰炉',
            '50022568' => '其他工艺饰品',
            '50024938' => '花瓶/花器/仿真花/仿真饰品',
            '50021045' => '少数民族特色工艺品',
            '50021046' => '海外工艺品',
            '50021047' => '地区特色工艺品',
            '50021048' => '宗教工艺品',
            '50025777' => '葫芦',
            '50017087' => '景点门票',
            '50012910' => '旅游卡券/服务',
            '50012917' => '巴士/地铁/交通卡券',
            '50024207' => '旅游周边商品',
            '50011153' => '背心/马甲',
            '50000436' => 'T恤',
            '50010402' => 'Polo衫',
            '50011123' => '衬衫',
            '50010167' => '牛仔裤',
            '3035' => '休闲裤',
            '50011127' => '皮裤',
            '50010158' => '夹克',
            '50010159' => '卫衣',
            '50011159' => '风衣',
            '50025883' => '呢大衣',
            '50011161' => '皮衣',
            '50011167' => '羽绒服',
            '50010160' => '西服',
            '50011129' => '西裤',
            '50011130' => '西服套装',
            '50001748' => '民族服装',
            '50005867' => '工装制服',
            '50001705' => '柜类',
            '50015200' => '床类',
            '50021837' => '床垫类',
            '50020006' => '沙发类',
            '50015455' => '坐具类',
            '50015816' => '几类',
            '50008280' => '桌类',
            '50008274' => '架类',
            '50020618' => '箱类',
            '50020617' => '镜子类',
            '50020615' => '榻榻米空间',
            '50020614' => '根雕类',
            '50015915' => '屏风/花窗',
            '50015886' => '案/台类',
            '50015230' => '户外/庭院家具',
            '50006281' => '宜家IKEA',
            '50015568' => '家具辅料',
            '50015566' => '二手/闲置专区',
            '50015771' => '成套家具',
            '50022373' => '情趣家具',
            '50022397' => '设计师家具',
            '50015518' => '货架/展柜',
            '211503' => '办公家具',
            '50020612' => '超市家具',
            '50020671' => '城市家具',
            '50015541' => '酒店家具',
            '50020672' => '餐饮/烘焙家具',
            '50020673' => '服装店家具',
            '50020674' => '娱乐/酒吧/KTV家具',
            '50020675' => '桑拿/足浴/健身家具',
            '50020677' => '发廊/美容家具',
            '50020679' => '校园教学家具',
            '50020680' => '医疗家具',
            '50020681' => '殡葬业家具',
            '50023945' => '国货精品笔记本',
            '50012081' => '国货精品手机',
            '50010815' => '香水',
            '50010793' => '隔离/妆前/打底',
            '50013794' => 'BB霜',
            '50010803' => '遮瑕',
            '50010789' => '粉底液/膏',
            '50010790' => '粉饼',
            '50010792' => '蜜粉/散粉',
            '50010798' => '眉笔/眉粉/眉膏',
            '50010797' => '眼线',
            '50010796' => '眼影',
            '50010794' => '睫毛膏/睫毛增长液',
            '50019254' => '假睫毛/假睫毛工具',
            '50010805' => '腮红/胭脂',
            '50010936' => '修颜/高光/阴影粉',
            '50010801' => '唇笔/唇线笔',
            '50010807' => '唇彩/唇蜜',
            '50010808' => '唇膏/口红',
            '50010800' => '双眼皮贴/胶水',
            '50010810' => '指甲油/美甲产品',
            '50019251' => '化妆刷/刷包',
            '50010817' => '化妆/美容工具',
            '50010812' => '彩妆套装/彩妆盘',
            '50010813' => '身体彩绘',
            '50019246' => '男士彩妆',
            '50010814' => '其它彩妆',
            '50011990' => '卸妆',
            '50011977' => '洁面',
            '50011978' => '化妆水/爽肤水',
            '50011979' => '面部精华',
            '50011980' => '乳液/面霜',
            '50011981' => '面膜/面膜粉',
            '50011982' => '防晒',
            '50011986' => '眼部护理',
            '50011983' => '身体护理',
            '50011987' => '胸部护理',
            '50011992' => '精油芳疗',
            '50011994' => '唇部护理',
            '50011995' => 'T区护理',
            '50011996' => '面部按摩霜',
            '50011997' => '面部磨砂/去角质',
            '50011993' => '面部护理套装',
            '50011988' => '男士护理',
            '50011998' => '手部保养',
            '50011991' => '其他保养',
            '50023283' => '假发/假发配件',
            '50023292' => '洗发护发',
            '50023293' => '头发造型',
            '50023294' => '染发烫发',
            '1403' => '普通数码相机',
            '50003773' => '专业数码单反',
            '1402' => '数码摄像机',
            '140116' => '单反镜头',
            '50021422' => '单电微单',
            '50003770' => '胶卷相机',
            '50003793' => 'LOMO',
            '50018323' => '一体机',
            '110308' => 'DIY兼容机',
            '50010605' => '服务器/Server',
            '50012320' => '无线鼠标',
            '50012307' => '有线鼠标',
            '110210' => '键盘',
            '110502' => '品牌液晶显示器',
            '110203' => 'CPU',
            '110202' => '内存',
            '110207' => '硬盘',
            '50013151' => '固态硬盘',
            '110201' => '主板',
            '110206' => '显卡',
            '50003848' => '台机电源',
            '110211' => '机箱',
            '110215' => '散热设备',
            '110212' => '光驱/刻录/DVD',
            '110205' => '声卡',
            '50003321' => '电脑周边',
            '50008759' => '组装液晶显示器',
            '50001810' => '多媒体音箱',
            '110508' => '摄像头',
            '50002415' => '键鼠套装',
            '110511' => '手写输入/绘图板',
            '110216' => '电视卡/盒',
            '50003213' => '硬盘盒',
            '50003850' => '电竞耳麦',
            '50013014' => '网络/高清播放器',
            '110209' => '网卡',
            '50022650' => '3G无线路由器',
            '110808' => '路由器',
            '50016213' => 'ADSL MODEM/宽带猫',
            '110805' => '交换机',
            '50016189' => '光纤设备',
            '50016195' => '网络存储设备',
            '50016203' => '电脑/网络工具',
            '50019309' => '无线网络',
            '50019318' => '网络设备',
            '50019361' => '机房布线',
            '50019494' => '视频监控',
            '50019510' => '语音视频',
            '110809' => '其它网络相关',
            '50019812' => '路由器/猫/网卡配件',
            '50020262' => '电力猫',
            '50118013' => '无线高清',
            '50018326' => '苹果专用配件',
            '50024094' => '手机配件',
            '50024095' => '笔记本电脑配件',
            '50024096' => '数码相机配件',
            '50024097' => '单反/单电相机配件',
            '50024098' => '平板电脑配件',
            '50005051' => 'MP3/MP4配件',
            '50020180' => '电子书配件',
            '50024104' => '电教产品配件',
            '50024103' => '摄像机配件',
            '50011826' => '家电影音周边配件',
            '50024099' => '电子元器件市场',
            '50018909' => 'USB电脑周边',
            '50024101' => '数码包/收纳/整理',
            '111703' => '3G无线上网卡设备',
            '50009211' => '移动电源',
            '50005050' => '蓝牙耳机',
            '50008482' => '数码相框',
            '50003312' => '干电池/充电电池/套装',
            '50024109' => '数码周边',
            '50050622' => '胶片相机配件',
            '50012165' => 'U盘',
            '50012166' => '闪存卡',
            '50012167' => '记忆棒',
            '110507' => '移动硬盘',
            '50012601' => '打印机配件',
            '110514' => '打印机',
            '111219' => '投影机',
            '50021132' => '绳索/扎带/办公线材',
            '110501' => '扫描仪',
            '211710' => '碎纸机',
            '111201' => '复合复印机',
            '50010757' => '其它办公设备',
            '50001718' => '保险箱',
            '50008551' => '传真/通信设备',
            '50008352' => '投影机配件',
            '50012600' => '办公设备配件及相关服务',
            '111409' => '其它耗材',
            '50019240' => '墨水',
            '50019250' => '办公用纸',
            '50024248' => '墨粉硒鼓耗材类',
            '50024253' => '磁盘刻录存储类',
            '50024258' => '多功能一体机及配件',
            '50024300' => '条码扫描/采集器材',
            '50024346' => '点/验钞/收款机及配件',
            '50024369' => '包装设备/标牌及耗材',
            '50024394' => '门禁考勤器材',
            '50024400' => '其它办公设备配件',
            '50008870' => '电子辞典/学习机',
            '50022538' => '点读机',
            '50022537' => '点读笔',
            '50010731' => '电子阅览器/电纸书',
            '50012716' => '笔类/书写工具',
            '50012676' => '纸张本册',
            '50005757' => '日常学习用品',
            '50005730' => '胶粘用品',
            '50005736' => '装订用品',
            '50005747' => '裁剪用品',
            '211708' => '计算器',
            '50016353' => '印刷制品',
            '50012645' => '收纳/陈列用品',
            '50005752' => '教学演示/展示告示用品',
            '50005756' => '绘图测量用品',
            '50013477' => '财会用品',
            '211707' => '其它文化用品',
            '50024641' => '画具/画材/书法用品',
            '50017905' => '游戏掌机/PSP/NDSL',
            '50017906' => '家用游戏机/PS3/Wii/XBOX',
            '50012068' => '游戏手柄',
            '50012834' => '游戏软件',
            '50012079' => '方向盘',
            '50012080' => '摇杆',
            '50012160' => 'PSP专用配件',
            '50012161' => 'WII专用配件/周边',
            '50012162' => 'PS2/PS3专用配件（包含PS1)',
            '50012163' => 'NDSI/NDSL专用配件',
            '50018082' => 'XBOX专用配件',
            '50025710' => 'PSV专用配件',
            '50012136' => '电视机',
            '50001813' => '家庭影院',
            '50003881' => '冰箱',
            '50015558' => '冷柜/便携冷热箱',
            '50013474' => '热水器',
            '50015563' => '酒柜',
            '350301' => '洗衣机',
            '350401' => '空调',
            '350511' => '油烟机',
            '50015382' => '燃气灶',
            '350503' => '消毒柜',
            '50018263' => '烟灶消套装',
            '50022734' => '大家电配件',
            '1205' => '耳机/耳麦',
            '50012142' => 'Hifi音箱/功放/器材',
            '50020192' => '舞台设备',
            '121616' => '组合/迷你/卡通音响',
            '50005174' => '网络高清播放器',
            '50005009' => '影碟机/DVD/蓝光/VCD/高清',
            '50011973' => 'CD机/卡座/黑胶音源',
            '50003318' => '麦克风/话筒',
            '50012067' => '随身听/便携视听/收音',
            '50012148' => '工程解决方案',
            '50012149' => '扩音器/录像机/世嘉',
            '50012934' => '其他音箱',
            '50011866' => '影音家电配件',
            '350402' => '空气净化/氧吧',
            '350407' => '加湿器',
            '50017072' => '抽湿器/除湿器',
            '350404' => '暖风机/取暖器',
            '50000360' => '电热毯',
            '50017589' => '空调扇',
            '50008557' => '电风扇',
            '50018327' => '吊扇',
            '50013195' => '挂烫机',
            '50012101' => '干衣机',
            '50008552' => '电熨斗',
            '50008553' => '蒸汽刷/干洗刷',
            '350310' => '毛球修剪器',
            '50002890' => '干鞋器/擦鞋器',
            '50008554' => '吸尘器',
            '50008555' => '扫地机',
            '50022648' => '蒸汽拖把',
            '50006508' => '超声波/蒸汽清洁机',
            '50008563' => '对讲机',
            '50008542' => '电话机(有绳/无绳/网络)',
            '50008544' => '其它生活家电',
            '50012135' => '生活家电配件',
            '50002894' => '电烤箱',
            '50012959' => '电锅煲类',
            '50002809' => '微波炉',
            '50015397' => '光波热波炉',
            '350502' => '电磁炉',
            '50002893' => '饮水机',
            '350504' => '净水器',
            '50008556' => '豆浆机',
            '50012097' => '搅拌/料理机',
            '50018218' => '榨汁机',
            '50000013' => '多士炉',
            '50018103' => '面包机',
            '350507' => '咖啡机',
            '50003695' => '电热水壶',
            '50002535' => '酸奶机',
            '50002898' => '煮蛋器/蒸蛋器',
            '50004363' => '电饼铛/可丽饼机',
            '50013007' => '电热杯',
            '50013021' => '商用厨电',
            '350709' => '定时器/提醒器',
            '50008543' => '其它厨房家电',
            '50012099' => '厨房家电配件',
            '50010567' => '清洁美容工具',
            '50024626' => '口腔护理',
            '50023686' => '美发工具',
            '50008548' => '美体瘦身',
            '50008545' => '美容/美体辅助工具',
            '50018398' => '按摩器材',
            '50023687' => '健康检测仪器',
            '50012083' => '家用保健器材',
            '50023690' => '家用护理辅助器材',
            '50023688' => '经络保健器材',
            '350210' => '其它个人护理',
            '50011877' => '各类配件',
            '50019935' => '灯具灯饰',
            '50013217' => '光源',
            '50013222' => '油漆',
            '50020966' => '晾衣架/晾衣杆',
            '50002409' => '厨房',
            '50013322' => '墙纸',
            '2159' => '环保/除味/保养',
            '50008696' => '配件专区',
            '50008725' => '二手/闲置专区',
            '50008321' => '其它',
            '50019835' => '集成吊顶',
            '50020007' => '卫浴用品',
            '50020573' => '浴霸',
            '50020906' => '地暖/暖气片/散热器',
            '50021794' => '智能家居系统',
            '50022263' => '楼梯',
            '50022270' => '瓷砖',
            '50022271' => '地板',
            '50022357' => '门',
            '50024852' => '涂料（乳胶漆）',
            '50020421' => '窗',
            '50020333' => '板材',
            '50020341' => '砖',
            '50020348' => '隔断墙',
            '50020362' => '水管管材',
            '50020369' => '线条',
            '50013517' => '人造大理石',
            '50020372' => '木方',
            '50020392' => '其它基础建材',
            '50020397' => '隔音材料',
            '50020400' => '隔热材料',
            '50020412' => '铝型材',
            '50020417' => '石膏板',
            '50020442' => '门窗密封条',
            '50020445' => '雕花件系列',
            '50020449' => '玻璃',
            '50020459' => '新型装饰材料',
            '50020472' => '天然大理石',
            '50020480' => '砂岩',
            '50013226' => '施工保护',
            '50020608' => '阳光房',
            '50020486' => '家用五金',
            '50020487' => '手动工具',
            '50020519' => '仪器仪表',
            '50020646' => '电动工具',
            '50020489' => '气动工具',
            '50020490' => '机械五金',
            '50020491' => '机电五金',
            '50020492' => '紧固件',
            '50020493' => '刃具',
            '50020494' => '液压/起重工具',
            '50020585' => '插座',
            '50020596' => '底盒',
            '50020599' => '转换器',
            '50020602' => '太阳能电池',
            '50020606' => '节电器',
            '50020975' => '变压器',
            '50020978' => '继电器',
            '50020985' => '蓄电池',
            '50020995' => '布线箱',
            '50020998' => '电工配件',
            '50021011' => '开关',
            '50021027' => '断路器',
            '50021033' => '电线',
            '50021057' => '监控器材及系统',
            '50021105' => '防盗报警器材及系统',
            '50021120' => '消防报警设备',
            '50021153' => '安全检查设备',
            '50022516' => '接线板/插头',
            '50013796' => '其它',
            '50022651' => 'LED设备',
            '50050477' => '儿童摄影',
            '50050480' => '婚纱摄影',
            '50050481' => '写真摄影',
            '50006258' => '商品摄影服务',
            '50016161' => '酒店客栈',
            '50019784' => '酒店客栈套餐',
            '2801' => '节日/派对庆典用品',
            '50009206' => '家用五金工具',
            '50010099' => '伞/雨具/防雨/防潮',
            '50010464' => '扇/迷你风扇/配件/冰垫/冰贴',
            '50012512' => '保暖贴/怀炉/保暖用品',
            '50008275' => '钟',
            '50003948' => '竹炭包',
            '50016434' => '创意礼品',
            '50012514' => '防护用品',
            '50006528' => '鞋用品',
            '50023068' => '美体/减肥/塑型/增高用具',
            '50025838' => '驱虫用品',
            '50025839' => '香薰用品',
            '50006885' => '杯子/水杯/水壶',
            '50002796' => '餐具',
            '2107' => '茶具',
            '50004438' => '咖啡器具',
            '215206' => '酒壶/酒杯/酒具',
            '50014236' => '保鲜容器/保鲜器皿',
            '50010101' => '烹饪用具',
            '50008281' => '厨用小工具/厨房储物',
            '50002258' => '烧烤/烘焙用具',
            '50022523' => '一次性餐桌用品',
            '50009146' => '个人洗护清洁用具',
            '50003949' => '家务/地板清洁用具',
            '50000569' => '衣物洗/晒/护理用具',
            '50018683' => '家庭收纳用具',
            '50022707' => '浴洗工具/配件',
            '50023189' => '家庭整理用具',
            '50023243' => '家庭防尘用具',
            '2132' => '卫浴用具/卫浴配件',
            '50001871' => '休闲毯/毛毯/绒毯',
            '50000583' => '地垫',
            '50000582' => '地毯',
            '50000584' => '挂毯/壁毯',
            '290209' => '十字绣/刺绣工具配件',
            '50024797' => '坐垫/椅垫/沙发垫',
            '50005494' => '防尘罩/沙发套/空调罩',
            '50024918' => '餐桌布艺',
            '50024922' => '十字绣/刺绣',
            '50024923' => '窗帘/窗纱',
            '50024924' => '其他帘类',
            '50024925' => '窗帘/门帘配件',
            '50024947' => '背景墙软包/床头套/工艺软包',
            '50012791' => '床上用品',
            '50010103' => '毛巾/浴巾/浴袍',
            '50012051' => '家居拖鞋/凉拖/棉拖/居家鞋',
            '213002' => '靠垫/抱枕',
            '50005033' => '布料/面料/手工diy布料面料',
            '50010041' => '布艺蛋糕/蛋糕毛巾',
            '50017143' => '缝纫DIY材料、工具及成品',
            '50006101' => '其它',
            '211104' => '婴幼儿牛奶粉',
            '50016094' => '特殊配方奶粉',
            '50014813' => '其它',
            '50018596' => '婴幼儿调味品',
            '50018801' => '婴幼儿辅食',
            '50018808' => '婴幼儿营养品',
            '50018807' => '婴幼儿零食',
            '50018184' => '婴幼儿羊奶粉',
            '50018813' => '纸尿裤/拉拉裤/纸尿片',
            '50009522' => '奶瓶',
            '50012546' => '湿巾',
            '50013866' => '童床/婴儿床/摇篮/餐椅',
            '50012711' => '布尿裤/尿垫',
            '50014248' => '宝宝洗浴护肤品',
            '50012412' => '睡袋/凉席/枕头/床品',
            '50009521' => '水杯/餐具/研磨/附件',
            '50012436' => '理发器/指甲钳/体温计等日常护理小用品',
            '50006020' => '背带/学步带/出行用品',
            '50005952' => '防撞/提醒/安全/保护',
            '50012448' => '牙胶/牙刷/牙膏',
            '50012466' => '清洁液/洗衣液/柔顺剂',
            '50018391' => '消毒/吸奶器/小家电',
            '50018394' => '驱蚊/退烧/感冒贴',
            '211112' => '其它婴童用品',
            '50022520' => '婴儿手推车/学步车',
            '50148003' => '儿童房/桌椅/家具',
            '50228003' => '奶嘴/安抚奶嘴',
            '50012374' => '防辐射',
            '50012354' => '孕妇装',
            '50023573' => '孕妇裤/托腹裤',
            '50012314' => '产妇帽/孕妇袜/孕妇鞋',
            '50023613' => '家居服/哺乳装/秋衣裤',
            '50016687' => '哺乳文胸/内裤/产检裤',
            '50023660' => '束缚带/产妇瘦身塑体衣/盆骨矫正带',
            '50005961' => '妈咪包/袋',
            '50011864' => '早孕检测',
            '50006000' => '妈妈产前产后用品',
            '50010392' => '孕产妇奶粉',
            '50026457' => '孕产妇护肤/洗护/祛纹',
            '50026460' => '孕产妇营养品',
            '50026471' => '月子营养',
            '50014512' => '婴儿礼盒',
            '50010537' => '连身衣/爬服/哈衣',
            '50012431' => '肚围/护脐带/肚兜',
            '50010530' => '披风/斗篷',
            '50010524' => '马甲',
            '50013693' => '裙子',
            '50013618' => '裤子',
            '50012433' => '儿童内衣裤/睡衣(0-16岁)',
            '50013189' => 'T恤',
            '50010527' => '衬衫',
            '50010518' => '卫衣/绒衫',
            '50010539' => '毛衣/针织衫',
            '50012308' => '外套/夹克/大衣',
            '50010531' => '棉袄/棉服',
            '50010526' => '羽绒服/羽绒内胆',
            '50010540' => '套装',
            '50012424' => '亲子装/亲子时装',
            '50012340' => '童鞋/婴儿鞋',
            '50016012' => '儿童舞蹈服/演出服/礼服',
            '50016450' => '校服/校服定制',
            '50023868' => '儿童泳衣/裤/帽',
            '50024824' => '儿童配饰/发饰',
            '50006584' => '儿童袜子(0-16岁)',
            '50006583' => '帽子/围巾/口罩/手套/耳套/脚套',
            '50006217' => '其它',
            '50156002' => '背心/吊带衫',
            '50152002' => '儿童旗袍/唐装/民族服装',
            '50146004' => '反穿衣/罩衣',
            '50020280' => '其他品牌保健品',
            '50015218' => '药食同源食品',
            '50008044' => '燕窝',
            '50005945' => '参类保健品',
            '50005773' => '蜂蜜/蜂产品',
            '50008046' => '冬虫夏草',
            '50015134' => '鹿茸',
            '50015194' => '灵芝',
            '50015207' => '枸杞及其制品',
            '50015211' => '雪蛤/林蛙油',
            '50015219' => '阿胶膏方',
            '50009980' => '三七',
            '50009979' => '山药',
            '50012186' => '石斛/枫斗',
            '50020296' => '其他传统滋补品',
            '50050143' => '新资源食品',
            '50013061' => '蜜饯/枣类/梅/果干',
            '50012981' => '山核桃/坚果/炒货',
            '50008613' => '牛肉干/猪肉脯/肉类熟食',
            '50010550' => '饼干/膨化',
            '50008055' => '巧克力/DIY巧克力',
            '50016091' => '糖果零食/果冻/布丁',
            '50009556' => '鱿鱼丝/鱼干/海味即食',
            '50008430' => '奶酪/乳制品/',
            '50552001' => '糕点/点心',
            '50009821' => '调味品/果酱/沙拉',
            '50025682' => '南北干货/肉类干货',
            '50010696' => '烘焙原料/辅料/食品添加剂',
            '50016443' => '其他食品',
            '50025689' => '方便速食',
            '50050378' => '食用油/调味油',
            '50005462' => 'QQ币/QQ卡',
            '50007212' => 'QQ增值服务',
            '50011556' => '羽毛球',
            '50012937' => '乒乓球',
            '50013823' => '足球',
            '50013202' => '篮球',
            '50017077' => '网球',
            '50017616' => '排球',
            '50017776' => '高尔夫',
            '50017859' => '棒球',
            '50017625' => '壁球',
            '50016729' => '游泳',
            '50010828' => '跳舞毯',
            '2612' => '山地/公路/便携自行车',
            '50019782' => '电动车/电动车配件',
            '50016689' => '轮滑/滑板/极限运动',
            '50016663' => '瑜伽',
            '50016472' => '舞蹈/健美操/体操',
            '50017913' => '跆拳道/武术/搏击',
            '50017085' => '踏步机/中小型健身器材',
            '50017117' => '跑步机/大型健身器械',
            '50018096' => '橄榄球',
            '50017722' => '台球',
            '50017871' => '麻将/棋牌/益智类',
            '50018005' => '飞镖/桌上足球/室内休闲',
            '50017269' => '田径运动器材',
            '50018025' => '毽子/空竹/民间运动',
            '50013253' => '游乐场/体育场馆设施',
            '50023363' => '马术运动',
            '50018189' => 'F1/赛车',
            '50018194' => '冰球/速滑/冰上运动',
            '50019503' => '慢跑(有氧运动)',
            '50019502' => '运动护具/急救用品',
            '50019501' => '运动书籍/教材',
            '50019500' => '运动健身卡/会员卡',
            '50010749' => '其它运动用品',
            '50014023' => '垂钓装备',
            '50013888' => '户外服装',
            '50013908' => '睡袋',
            '50014756' => '防潮垫/地席/枕头',
            '50019269' => '户外鞋袜',
            '50013891' => '专项户外运动装备',
            '50014759' => '刀具/多用工具',
            '50013892' => '户外休闲家具',
            '50016119' => '旅行便携装备',
            '50019007' => '军迷服饰/军迷用品',
            '50018158' => '防护/救生装备',
            '50014766' => '干粮/户外食品',
            '50014767' => '地图/旅行指南/影像资料',
            '50019539' => '帐篷/天幕/帐篷配件',
            '50019592' => '望远镜/夜视仪/户外眼镜',
            '50019601' => '户外照明',
            '50019712' => '饮水用具/盛水容器',
            '50014763' => '通讯/导航/户外表类',
            '50014764' => '洗漱清洁/护理用品',
            '50014762' => '登山杖/手杖',
            '50014757' => '炉具/餐具/野餐烧烤用品',
            '50016382' => '活动/培训',
            '2203' => '其它',
            '50013228' => '运动T恤',
            '50011717' => '运动卫衣/套头衫',
            '50011718' => '运动风衣',
            '50011739' => '运动茄克/外套',
            '50011720' => '运动棉衣',
            '50011721' => '运动羽绒服',
            '50011704' => '运动毛衣/线衫',
            '50022728' => '运动套装',
            '50022889' => '运动POLO衫',
            '50022891' => '健身服装',
            '50023105' => '运动裤',
            '50023109' => '运动裙',
            '50023415' => '运动球服',
            '50023110' => '运动马甲',
            '50011975' => '毛绒布艺类玩具',
            '50012770' => '娃娃/配件',
            '50024128' => '静态模型',
            '50013198' => '童车/儿童轮滑',
            '50008876' => '早教/音乐/智能玩具',
            '2512' => '户外运动/休闲/传统玩具',
            '50012455' => '游泳池/戏水玩具',
            '50008737' => '玩具模型零件/工具/耗材/辅件',
            '50016058' => '高达/BJD/手办/机器人',
            '50003682' => '卡通/动漫/游戏周边',
            '50015988' => '网游周边(实物)',
            '50012404' => '儿童包/背包/箱包',
            '50015994' => '聚会/魔术/cosplay用具',
            '50000813' => '宝宝纪念品/个性产品',
            '50008528' => '棋牌/桌面游戏',
            '50023498' => '解锁/迷宫/魔方/悠悠球',
            '50007116' => '电动/遥控/惯性/发条玩具',
            '50023502' => '彩泥/手工制作/仿真/过家家玩具',
            '50023504' => '积木/拆装/串珠/拼图/配对玩具',
            '50015127' => '学习/实验/绘画文具',
            '50023508' => '游乐/教学设备/大型设施',
            '50024048' => '幼儿响铃/布书手偶/爬行健身',
            '50024050' => '电子/发光/充气/整蛊玩具',
            '50024060' => '油动电动模型',
            '50000802' => '其它玩具',
            '150404' => '网络电话卡',
            '50005109' => 'Skype充值专区',
            '50006853' => '3G无线上网资费卡',
            '50016361' => '有线宽带缴费',
            '50024820' => '合约购机业务',
            '50025114' => '新入网手机号套餐',
            '50025151' => 'WIFI热点/无线套餐',
            '50026336' => '老用户预存优惠',
            '50256001' => '手机流量充值',
            '50019293' => '宗教用品',
            '2309' => '邮品',
            '2310' => '钱币',
            '50024158' => '玉石',
            '50462018' => '观赏石/奇石/矿物晶体',
            '50019296' => '票证/标牌章',
            '50012880' => '书法/绘画',
            '2301' => '古玩杂项',
            '50446020' => '烟具/酒具',
            '50426004' => '陶/瓷/瓷片',
            '50003583' => '紫砂',
            '50019273' => '金属/搪瓷器具',
            '50450016' => '手工艺/民俗',
            '50001931' => '趣味收藏',
            '50019288' => '红色收藏',
            '2305' => '金石篆刻',
            '50005060' => '收藏品保养/鉴定工具',
            '2311' => '其它收藏品',
            '50019289' => '文房用品/文具',
            '50019306' => '连环画/古籍善本',
            '290501' => '鲜花速递(同城)',
            '50003023' => '卡通花/巧克力花',
            '50004417' => '鲜果篮(预定与速递)',
            '50009339' => '婚礼鲜花布置',
            '50015210' => '商务用花',
            '50015193' => '仿真花/绿植/蔬果成品',
            '290503' => 'DIY仿真花材料',
            '50009361' => '花瓶/花器/花盆/花架',
            '50024878' => '花卉/绿植盆栽',
            '50024881' => '创意迷你植物',
            '50007010' => '园艺用品',
            '50024879' => '花卉/蔬果/草坪种子',
            '50024880' => '庭院植物/行道树木/果树',
            '50011745' => '凉鞋',
            '50011746' => '拖鞋',
            '50011743' => '靴子',
            '50011744' => '帆布鞋',
            '50012906' => '低帮鞋',
            '50012907' => '高帮鞋',
            '50012908' => '雨鞋',
            '50010850' => '连衣裙',
            '50000671' => 'T恤',
            '162104' => '衬衫',
            '1622' => '裤子',
            '162205' => '牛仔裤',
            '1623' => '半身裙',
            '162105' => '小背心/小吊带',
            '50013196' => '马夹',
            '162116' => '蕾丝衫/雪纺衫',
            '50000697' => '毛针织衫',
            '50011277' => '短外套',
            '50008897' => '西装',
            '50008898' => '卫衣/绒衫',
            '162103' => '毛衣',
            '50008901' => '风衣',
            '50013194' => '毛呢外套',
            '50008900' => '棉衣/棉服',
            '50008899' => '羽绒服',
            '50008904' => '皮衣',
            '50008905' => '皮草',
            '50000852' => '中老年女装',
            '1629' => '大码女装',
            '1624' => '职业套装/学生校服/工作制服',
            '50011404' => '婚纱/旗袍/礼服',
            '50008906' => '唐装/民族服装/舞台服装',
            '50012027' => '低帮鞋',
            '50012825' => '高帮鞋',
            '50012028' => '靴子',
            '50012032' => '凉鞋',
            '50012033' => '拖鞋',
            '50012042' => '帆布鞋',
            '50012047' => '雨鞋',
            '50012010' => '包袋',
            '50012018' => '钱包卡套',
            '50012019' => '旅行箱',
            '50026617' => '箱包相关配件',
            '50050199' => '旅行袋',
            '50008881' => '文胸',
            '50008883' => '文胸套装',
            '50008882' => '内裤',
            '50008884' => '塑身美体衣',
            '50012774' => '塑身美体裤',
            '50012775' => '塑身腰封/腰夹',
            '50012776' => '塑身分体套装',
            '50012781' => '塑身连体衣',
            '50008886' => '睡衣上装',
            '50012766' => '睡裤/家居裤',
            '50012771' => '睡裙',
            '50012772' => '睡衣/家居服套装',
            '50012773' => '睡袍/浴袍',
            '50008885' => '保暖上装',
            '50012777' => '保暖裤',
            '50012778' => '保暖套装',
            '50006846' => '短袜/打底袜/丝袜/美腿袜',
            '50010394' => '吊带/背心/T恤',
            '50008888' => '抹胸',
            '50008890' => '肚兜',
            '50008889' => '乳贴',
            '50012784' => '肩带',
            '50012785' => '吊袜带',
            '50012786' => '插片/胸垫',
            '50012787' => '搭扣',
            '50009032' => '腰带/皮带/腰链',
            '302910' => '帽子',
            '50007003' => '围巾/丝巾/披肩',
            '50009578' => '围巾/手套/帽子套件',
            '50011729' => '运动颈环/手环/指环',
            '302902' => '领带/领结',
            '50001248' => '领带夹',
            '302909' => '袖扣',
            '164206' => '婚纱礼服配件',
            '50009037' => '耳套',
            '50010410' => '手套',
            '50009035' => '手帕',
            '50010406' => '鞋包/皮带配件',
            '50009033' => '制衣面料',
            '50009047' => '其他配件',
            '50011398' => '钻石',
            '50011399' => '翡翠',
            '50011400' => '黄金K金',
            '50011401' => '铂金/PT',
            '50013964' => '天然珍珠',
            '50013957' => '天然玉石',
            '50011663' => '专柜swarovski水晶',
            '50013963' => '天然琥珀',
            '50011402' => '红蓝宝石/贵重宝石',
            '2908' => 'ZIPPO/芝宝',
            '50000467' => '品牌打火机/其它打火机',
            '290601' => '瑞士军刀',
            '50011894' => '眼镜架',
            '50011895' => '眼镜片',
            '50011892' => '框架眼镜',
            '50010368' => '太阳眼镜',
            '50011893' => '功能眼镜',
            '50011888' => '眼镜配件、护理剂',
            '50011896' => '滴眼液、护眼用品',
            '2909' => '烟具',
            '50012709' => '酒具',
            '50004788' => '工业/农业技术',
            '50004658' => '历史',
            '50004725' => '旅游',
            '50003112' => '生活',
            '3306' => '计算机/网络',
            '3331' => '外语/语言文字',
            '50004687' => '经济',
            '50004870' => '国外原版书/台版、港版书',
            '50004925' => '传记',
            '50004767' => '医学卫生',
            '50004835' => '地图/地理',
            '50004645' => '娱乐时尚',
            '50004621' => '报纸',
            '50010485' => '期刊杂志',
            '50004816' => '法律',
            '50005715' => '淘宝网开店书籍专区',
            '50004707' => '科普读物',
            '50004960' => '培训课程',
            '50000072' => '考试/教材/论文',
            '50004674' => '小说',
            '50004806' => '文化',
            '50000063' => '管理',
            '50000049' => '自我实现/励志',
            '50001965' => '漫画/动漫小说',
            '50000177' => '自然科学',
            '3334' => '体育运动',
            '50000141' => '文学',
            '50001378' => '报刊订阅',
            '50004743' => '保健/心理类书籍',
            '3332' => '工具书/百科全书',
            '50000054' => '艺术',
            '3338' => '哲学和宗教',
            '50010689' => '低于5元专区',
            '3314' => '儿童读物/教辅',
            '50004849' => '育儿书籍',
            '50013002' => '人文社科',
            '3415' => '音乐CD/DVD',
            '50000201' => '电影',
            '50003291' => '电视剧',
            '50005271' => '成人教育音像',
            '50003679' => '动画碟',
            '50005272' => '生活百科',
            '3412' => '其它',
            '50011257' => '育儿/儿童教育音像',
            '50017311' => 'MIDI乐器/电脑音乐',
            '50017318' => '乐器音箱',
            '50017319' => '乐器配件',
            '50017305' => '乐器教材/曲谱',
            '50532001' => '民族乐器',
            '50530002' => '西洋乐器',
            '50015380' => '犬主粮',
            '50015262' => '狗零食',
            '50023066' => '猫主粮',
            '50023067' => '猫零食',
            '50015285' => '猫/狗日用品',
            '50023206' => '猫/狗美容清洁用品',
            '50015288' => '猫/狗保健品',
            '50015289' => '猫/狗医疗用品',
            '50001739' => '宠物服饰及配件',
            '217311' => '猫/狗玩具',
            '217312' => '水族世界',
            '50015293' => '仓鼠类及其它小宠',
            '50015292' => '兔类及其用品',
            '50008622' => '爬虫/鸣虫及其用品',
            '50008604' => '鸟类及用品',
            '217302' => '其它宠物',
            '50023357' => '畜牧用品',
            '50012829' => '计生用品',
            '50019617' => '男用器具',
            '50019630' => '女用器具',
            '50019641' => '情趣用品',
            '50019651' => '情趣内衣',
            '50020206' => '情趣家具',
            '50012031' => '篮球鞋',
            '50012037' => '网球鞋',
            '50012038' => '足球鞋',
            '50012036' => '跑步鞋',
            '50012043' => '板鞋/休闲鞋',
            '50012044' => '帆布鞋',
            '50026312' => '童鞋/青少年鞋',
            '50012041' => '综合训练鞋/室内健身鞋',
            '50012048' => '运动沙滩鞋/凉鞋',
            '50012049' => '运动拖鞋',
            '50012064' => '其它运动鞋',
            '50012331' => '羽毛球鞋',
            '50012946' => '乒乓球鞋',
            '50013865' => '项链',
            '50013868' => '项坠/吊坠',
            '50014227' => '耳饰',
            '50013869' => '手链',
            '50013870' => '手镯',
            '50013871' => '脚链',
            '50013875' => '戒指/指环',
            '50013876' => '胸针',
            '50013877' => '摆件',
            '50013878' => '发饰',
            '50013879' => 'DIY饰品配件',
            '50013880' => '首饰保养鉴定',
            '50013881' => '首饰盒/展示架',
            '50013882' => '其它首饰',
            '50014850' => '网店服务',
            '50014851' => '网络服务',
            '50014852' => '程序/软件开发',
            '50014853' => '多媒体/摄影',
            '50014855' => '物流服务',
            '50003853' => '其它服务',
            '50014924' => '翻译/文字服务',
            '50010686' => '电脑软件',
            '50019286' => '充值平台软件/加款卡',
            '50019287' => '网络会员卡/付费卡',
            '50015312' => '商旅服务',
            '50007280' => '商标注册/咨询',
            '50015313' => '咨询服务',
            '50015307' => '管理咨询',
            '50015059' => '财务咨询',
            '50016893' => '地下城与勇士',
            '50017023' => '问道',
            '50023720' => 'OTC药品',
            '50023721' => '医疗器械',
            '50023722' => '隐形眼镜/护理液',
            '50024153' => '计生用品',
            '50024467' => '烧烤/烤肉/烤串',
            '50025007' => '日用/装饰定制',
            '50388003' => '办公/文具定制',
            '50025008' => '数码配件定制',
            '50025009' => '服饰箱包定制',
            '50014854' => '设计服务',
            '50025010' => '饰品定制',
            '50025012' => '其它定制',
            '140701' => '照片冲印',
            '50510011' => '包装用品定制',
            '50019086' => '体育赛事',
            '50019084' => '演出话剧',
            '50012482' => '洗发沐浴/个人清洁',
            '50012487' => '家庭环境清洁剂',
            '50018971' => '家私/皮具护理品',
            '50018975' => '衣物清洁剂/护理剂',
            '50018960' => '室内除臭/芳香用品',
            '2165' => '香熏用品',
            '50016889' => '卫生巾/护垫/成人尿裤',
            '50012473' => '纸品/湿巾',
            '210207' => '驱虫用品',
            '50026397' => '茶叶',
            '50026398' => '饮料/乳品',
            '50003860' => '天然粉粉食品',
            '50009857' => '藕粉/麦片/冲饮品',
            '210605' => '速溶咖啡/咖啡豆/粉',
            '50023809' => '装修设计/室内设计',
            '50056001' => '其他',
            '50026802' => '维生素/矿物质',
            '50026803' => '海洋生物类',
            '50026804' => '菌/菇/微生物发酵',
            '50026805' => '蛋白质/氨基酸',
            '50026806' => '膳食纤维/碳水化合物',
            '50026807' => '植物精华/提取物',
            '50026808' => '动物精华/提取物',
            '50026809' => '保健饮品',
            '50026810' => '功能型膳食营养补充剂',
            '50050227' => '脂肪酸/脂类',
            '50050237' => '其他',
            '50050371' => '海鲜/水产品/制品',
            '50050372' => '生肉/肉制品',
            '50050643' => '熟食/凉菜/私房菜',
            '50010566' => '新鲜蔬菜/蔬菜制品',
            '50025680' => '腌制蔬菜/泡菜/酱菜/脱水蔬菜',
            '50012382' => '蛋/蛋制品',
            '50050725' => '新鲜水果/水果制品',
            '50024607' => '新鲜蛋糕',
            '50003794' => '摩托车整车',
            '50078001' => '摩托车配件',
            '50070004' => '摩托车骑士装备',
            '50078002' => '摩托车装饰养护',
            '261407' => '其他摩托车用品',
            '50005700' => '腕表',
            '50023096' => '单肩背包',
            '50023100' => '运动鼓包/旅行包',
            '50014494' => '腰包/手包/配件包',
            '50014493' => '双肩背包',
            '50015943' => '防雨罩/背包配件',
            '50014502' => '防水包/防水箱',
            '50014500' => '钱包/卡包/证件包',
            '50014503' => '挎包/拎包/休闲包',
            '50014496' => '户外摄影包',
            '50014495' => '旅行包/旅行箱',
            '50015374' => '运动袜',
            '50019690' => '鞋垫',
            '50015371' => '头巾/遮耳',
            '50015372' => '围巾/围脖',
            '50015373' => '雪套/套脚',
            '50015370' => '手套',
            '50018244' => '腰带',
            '50018245' => '贴章/魔术贴章',
            '50015376' => '其他服饰配件',
            '50015377' => '雨衣/雨裤/雨披',
            '50015369' => '运动帽',
            '50023103' => '颈环/腕环',
            '50008142' => '威士忌/进口烈酒',
            '50013003' => '葡萄酒',
            '50008144' => '国产白酒',
            '50008147' => '黄酒',
            '50008145' => '药酒',
            '50008146' => '啤酒',
            '50008148' => '其他酒类',
            '50514003' => '进口烈酒',
            '26' => '汽车/用品/配件/改装',
            '50020808' => '家居饰品',
            '50020857' => '特色手工艺',
            '50025707' => '景点门票/度假线路/旅游服务',
            '30' => '男装',
            '50008164' => '住宅家具',
            '50020611' => '商业/办公家具',
            '50023904' => '国货精品数码',
            '50010788' => '彩妆/香水/美妆工具',
            '1801' => '美容护肤/美体/精油',
            '50023282' => '美发护发/假发',
            '14' => '数码相机/单反相机/摄像机',
            '50018222' => '台式机/一体机/服务器',
            '11' => '电脑硬件/显示器/电脑周边',
            '50018264' => '网络设备/网络相关',
            '50008090' => '3C数码配件',
            '50012164' => '闪存卡/U盘/存储/移动硬盘',
            '50007218' => '办公设备/耗材/相关服务',
            '50018004' => '电子词典/电纸书/文化用品',
            '20' => '电玩/配件/游戏/攻略',
            '50022703' => '大家电',
            '50011972' => '影音电器',
            '50012100' => '生活电器',
            '50012082' => '厨房电器',
            '50002768' => '个人护理/保健/按摩器材',
            '27' => '家装主材',
            '50020332' => '基础建材',
            '50020485' => '五金/工具',
            '50020579' => '电子/电工',
            '50050471' => '摄影/摄像服务',
            '50011949' => '特价酒店/特色客栈/公寓旅馆',
            '21' => '居家日用/婚庆/创意礼品',
            '50016349' => '厨房/餐饮用具',
            '50016348' => '清洁/卫浴/收纳/整理用具',
            '50008163' => '床上用品/布艺软饰',
            '35' => '奶粉/辅食/营养品/零食',
            '50014812' => '尿片/洗护/喂哺/推车床',
            '50022517' => '孕妇装/孕产妇用品/营养',
            '50008165' => '童装/童鞋/亲子装',
            '50020275' => '传统滋补营养品',
            '50002766' => '零食/坚果/特产',
            '50016422' => '粮油米面/南北干货/调味品',
            '40' => '腾讯QQ专区',
            '50010728' => '运动/瑜伽/健身/球迷用品',
            '50013886' => '户外/登山/野营/旅行用品',
            '50011699' => '运动服/休闲服装',
            '25' => '玩具/模型/动漫/早教/益智',
            '50008907' => '手机号码/套餐/增值业务',
            '1512' => '手机',
            '23' => '古董/邮币/字画/收藏',
            '50007216' => '鲜花速递/花卉仿真/绿植园艺',
            '50011740' => '流行男鞋',
            '16' => '女装/女士精品',
            '50006843' => '女鞋',
            '50006842' => '箱包皮具/热销女包/男包',
            '1625' => '女士内衣/男士内衣/家居服',
            '50010404' => '服饰配件/皮带/帽子/围巾',
            '50011397' => '珠宝/钻石/翡翠/黄金',
            '28' => 'ZIPPO/瑞士军刀/眼镜',
            '33' => '书籍/杂志/报纸',
            '34' => '音乐/影视/明星/音像',
            '50017300' => '乐器/吉他/钢琴/配件',
            '29' => '宠物/宠物食品及用品',
            '2813' => '成人用品/避孕/计生用品',
            '50012029' => '运动鞋',
            '50013864' => '饰品/流行首饰/时尚饰品新',
            '50014811' => '网店/网络服务/软件',
            '50016891' => '网游垂直市场根类目',
            '50023717' => 'OTC药品/医疗器械/隐形眼镜/计生用品',
            '50024451' => '外卖/外送/订餐服务',
            '50025004' => '个性定制/设计服务/DIY',
            '50025110' => '电影/演出/体育赛事',
            '50025705' => '洗护清洁剂/卫生巾/纸/香薰',
            '50026316' => '茶/咖啡/冲饮',
            '50023804' => '装修设计/施工/监理',
            '50026800' => '保健品/膳食营养补充剂',
            '50050359' => '水产肉类/新鲜蔬果/熟食',
            '50074001' => '摩托车/配件/骑士装备',
            '50468001' => '手表',
            '50510002' => '运动包/户外包/配件',
            '50008141' => '酒类',
            '50011165' => '棉衣',
            '50025885' => '棉裤',
            '50025884' => '羽绒裤',
        ];
        if (isset($map[$cid])) {
            return $map[$cid];
        }
        return null;
    }
}
