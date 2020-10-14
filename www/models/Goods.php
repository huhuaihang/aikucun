<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 商品
 * Class Goods
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $type 类型：线上 线下
 * @property integer $sid 店铺编号
 * @property integer $tid 商品类型编号
 * @property integer $cid 商品分类编号
 * @property integer $scid 店铺商品分类编号
 * @property integer $bid 商品品牌编号
 * @property string $title 标题
 * @property string $keywords 关键字
 * @property string $desc 关键字
 * @property string $bill 海报文案
 * @property string $video_id 视频编号
 * @property float $price 价格
 * @property float $supplier_price 结算价格
 * @property integer $share_commission_type 佣金计算方式
 * @property float $share_commission_value 佣金或百分比
 * @property integer $stock 库存
 * @property string $main_pic 主图
 * @property string $pack_pic 礼包图
 * @property string $detail_pics 详情图列表JSON
 * @property string $content 详情
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property integer $deliver_fee_type 运费计费方式
 * @property float $weight 重量
 * @property float $bulk 体积
 * @property string $remark 备注
 * @property string $sale_time 上架时间
 * @property integer $is_recommend 是否推荐
 * @property integer $is_index 是否首页推荐
 * @property integer $is_pack 是否礼包产品
 * @property integer $is_score 是否积分产品
 * @property integer $is_best 是否爆款
 * @property integer $is_today 是否今日抢购
 * @property integer $is_coupon 是否优惠券活动产品
 * @property integer $is_pack_redeem 是否礼包兑换券产品
 * @property integer $is_index_best 是否特邀优品
 * @property integer $is_height_commission 是否高佣金
 * @property integer $score 积分数量
 * @property integer $sale_type 发货类型 自营 一键代发
 * @property integer $supplier_id 供货商编号
 * @property integer $is_limit 是否限购
 * @property integer $limit_type 限购类型
 * @property integer $limit_amount 限购数量
 * @property integer $limit_start_time 限购开始时间
 * @property integer $limit_end_time 限购结束时间
 *
 * @property Shop $shop 关联店铺
 * @property GoodsTraceVideo $video 关联商品视频
 * @property GoodsType $goods_type 关联商品类型
 * @property GoodsBrand $goods_brand 关联商品品牌
 * @property GoodsCategory $goods_category 关联商品分类
 * @property Supplier $supplier 关联供货商
 * @property GoodsViolation $goods_violation 关联违规商品表 单商品 (违规状态 待商家处理/管理员处理)
 * @property DeliverTemplate[] $deliverTemplateList 关联运费模板列表
 * @property GoodsSku[] $skuList 关联规格列表
 * @property OrderItem[] $orderItemList 关联订单详情列表
 * @property ShopGoodsCategory $shopGoodsCategory 店铺商品分类
 * @property GoodsService[] $serviceList 关联服务列表
 */
class Goods extends ActiveRecord
{
    const TYPE_ONLINE = 1; // 线上商品
    const TYPE_OFFLINE = 2; // 线下商品

    const YES = 1; //是
    const NO = 0; //否

    const TYPE_0WN = 1; // 自营
    const TYPE_SUPPLIER = 2; // 一键代发

    const SHARE_COMMISSION_TYPE_NONE = 0;
    const SHARE_COMMISSION_TYPE_MONEY = 1;
    const SHARE_COMMISSION_TYPE_RATIO = 2;

    const STATUS_ON = 1;
    const STATUS_OFF = 9;
    const STATUS_DEL = 0;

    const DELIVER_FEE_TYPE_WEIGHT = 1;
    const DELIVER_FEE_TYPE_BULK = 2;
    const DELIVER_FEE_TYPE_COUNT = 3;

    const LIMIT_TYPE_PEOPLE_DAY = 1; // 每人每天
    const LIMIT_TYPE_DAY = 2; // 每天

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'sid', 'tid', 'cid', 'title','desc', 'price', 'stock', 'main_pic', 'status', 'create_time',
                'sale_type', 'share_commission_value'], 'required'],
            ['title', 'string', 'max' => 128],
            [['keywords', 'desc'], 'string', 'max' => 512],
            [['content', 'bid'], 'safe'],
            [['stock','is_recommend', 'is_index', 'score', 'is_score', 'is_pack' ,'is_coupon','is_pack_redeem', 'is_limit', 'limit_amount'], 'default', 'value' => 0],
            [['share_commission_type', 'stock', 'deliver_fee_type', 'sort'], 'integer'],
            [['price', 'supplier_price', 'share_commission_value', 'weight', 'bulk'], 'double'],
            [['main_pic', 'pack_pic'], 'string', 'max' => 128],
            [['detail_pics', 'sale_time', 'is_recommend', 'is_index', 'is_pack', 'is_score', 'is_best', 'is_index_best'
                , 'is_height_commission', 'is_today', 'pack_pic', 'bill', 'limit_type', 'limit_start_time', 'limit_end_time'], 'safe'],
            [['scid', 'supplier_id','video_id'], 'integer'],
            [['limit_start_time', 'limit_end_time'], function ($attribute) {
                if (!is_int($this[$attribute]) && preg_match('/^[\d- :]+$/', $this[$attribute])) {
                    $this[$attribute] = strtotime($this[$attribute]);
                }
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'sid' => '商品所属店铺',
            'tid' => '商品类型',
            'cid' => '商品分类',
            'scid' => '店铺内商品分类',
            'bid' => '品牌',
            'title' => '商品名称',
            'keywords' => '关键字',
            'desc' => '描述',
            'bill' => '海报文案',
            'video_id' => '商品视频',
            'price' => '商品价格',
            'supplier_price' => '商品结算价格',
            'share_commission_type' => '佣金计算方式',
            'share_commission_value' => '设置佣金金额',
            'stock' => '库存',
            'main_pic' => '商品主图',
            'pack_pic' => '礼包封面图',
            'detail_pics' => '轮播图',
            'content' => '商品描述',
            'status' => '商品状态',
            'create_time' => '创建时间',
            'deliver_fee_type' => '运费计费方式',
            'weight' => '重量',
            'bulk' => '体积',
            'remark' => '备注',
            'sale_time' => '上架时间',
            'is_recommend' => '是否推荐',
            'is_index' => '是否首页推荐',
            'is_pack' => '是否礼包商品',
            'is_score' => '是否积分商品',
            'is_best' => '是否爆款推荐',
            'is_today' => '是否今日推荐',
            'is_index_best' => '是否邀新优品',
            'is_height_commission' => '是否地理标产品',
            'is_coupon' => '是否优惠券活动商品',
            'is_pack_redeem' => '是否礼包兑换券商品',
            'sort' => '排序',
            'score' => '积分',
            'sale_type' => '商品类型',
            'supplier_id' => '供货商',
            'is_limit' => '是否限购',
            'limit_type' => '限购类型',
            'limit_amount' => '限购数量',
            'limit_start_time' => '限购开始时间',
            'limit_end_time' => '限购结束时间',
        ];
    }

    /**
     * 获取商品销量
     * @return int
     */
    public function getSaleAmount()
    {
        return (int)((substr(time(),5,3) * $this->id )/100  + intval(OrderItem::find()->alias('order_item')
            ->joinWith('order order')
            ->andWhere(['order.status' => [
                Order::STATUS_PAID,
                Order::STATUS_PACKING,
                Order::STATUS_PACKED,
                Order::STATUS_DELIVERED,
                Order::STATUS_RECEIVED,
                Order::STATUS_COMPLETE
            ]])
            ->andWhere(['order_item.gid' => $this->id])
            ->sum('amount')));
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }

    /**
     * 关联商品类型
     * @return \yii\db\ActiveQuery
     */
    public function getGoods_type()
    {
        return $this->hasOne(GoodsType::className(), ['id' => 'tid']);
    }

    /**
     * 关联商品视频
     * @return \yii\db\ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(GoodsTraceVideo::className(), ['id' => 'video_id']);
    }

    /**
     * 关联商品品牌
     * @return \yii\db\ActiveQuery
     */
    public function getGoods_brand()
    {
        return $this->hasOne(GoodsBrand::className(), ['id' => 'bid']);
    }

    /**
     * 关联商品分类
     * @return \yii\db\ActiveQuery
     */
    public function getGoods_category()
    {
        return $this->hasOne(GoodsCategory::className(), ['id' => 'cid']);
    }

    /**
     * 关联供货商
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    /**
     * 详情图列表
     * @return array
     */
    public function getDetailPicList()
    {
        if (empty($this->detail_pics)) {
            return [];
        }
        return json_decode($this->detail_pics, true);
    }

    /**
     * 获取关联违规
     * @return GoodsViolation
     */
    public function getGoods_violation()
    {
        /** @var GoodsViolation $model */
        $model = GoodsViolation::find()
            ->where(['gid' => $this->id])
            ->andWhere(['<>', 'status', GoodsViolation::STATUS_DEL])
            ->one();
        return $model;
    }

    /**
     * 关联运费模板列表
     * @return \yii\db\ActiveQuery
     */
    public function getDeliverTemplateList()
    {
        return $this->hasMany(DeliverTemplate::className(), ['id' => 'did'])->viaTable(GoodsDeliverTemplate::tableName(), ['gid' => 'id']);
    }

    /**
     * 返回商品最低价格
     * @return float
     */
    public function getMinPrice()
    {
        $price = $this->price;
        foreach (GoodsSku::find()->andWhere(['gid' => $this->id])->each() as $sku) {/** @var GoodsSku $sku */
            if (Util::comp($sku->price, $price, 2) < 0) {
                $price = $sku->price;
            }
        }
        return $price;
    }

    /**
     * 关联规格列表
     * @return \yii\db\ActiveQuery
     */
    public function getSkuList()
    {
        return $this->hasMany(GoodsSku::className(), ['gid' => 'id']);
    }

    /**
     * 关联订单详情列表
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItemList()
    {
        return $this->hasMany(OrderItem::className(), ['gid' => 'id']);
    }

    /**
     * 关联店铺商品分类
     * @return \yii\db\ActiveQuery
     */
    public function getShopGoodsCategory()
    {
        return $this->hasOne(ShopGoodsCategory::className(), ['id' => 'scid']);
    }

    /**
     * 关联服务映射列表
     * @return \yii\db\ActiveQuery
     */
    public function getServiceMapList()
    {
        return $this->hasMany(GoodsServiceMap::class, ['gid' => 'id']);
    }

    /**
     * 关联服务列表
     * @return \yii\db\ActiveQuery
     */
    public function getServiceList()
    {
        return $this->hasMany(GoodsService::class, ['id' => 'sid'])->via('serviceMapList');
    }

    /**
     * 返回总库存
     * @param boolean $calcNewOrder 是否计算未发货订单
     * @param integer $sid 查询店铺
     * @return int
     */
    public function getAllStock_bak($calcNewOrder = true, $sid = null)
    {
        $stock = 0;
        foreach ($this->skuList as $sku) {
            $stock += $sku->getStock($calcNewOrder, $sid);
        }
        if ($stock < 0) {
            $stock = 0;
        }
        return $stock;
    }

    /**
     * 获取商品剩余库存
     * @return int
     */
    public function getAllStock()
    {
        $query = GoodsSku::find();
        $query->andWhere(['gid' => $this->id]);
        $amount = intval($query->sum('stock'));
        if (empty($amount)) {
            $amount = $this->stock;
        }
        $sell = intval(OrderItem::find()
            ->alias('order_item')
            ->joinWith('order order')
            ->andWhere(['order.status' => Order::STATUS_CREATED])
            ->andWhere(['order_item.gid' => $this->id])
            ->sum('amount')
        );
        $amount -= $sell;
        if ($amount < 0) {
            $amount = 0;
        }
        return $amount;
    }

    /**
     * 获取商品总库存
     * @return int
     */
    public function getGoodsStock()
    {
        $query = GoodsSku::find();
        $query->andWhere(['gid' => $this->id]);
        $amount = intval($query->sum('stock'));
        if (empty($amount)) {
            $amount = $this->stock;
        }
        return $amount;
    }

    /**
     * 商品判断每天的限购
     * @param $gid int 商品编号
     * @param $amount int 商品数量
     * @return  bool
     */
    public static function checkTodayGoodsLimit($gid, $amount)
    {
        $goods = Goods::findOne($gid);
        if ($goods->is_limit != 1) {
            return true;
        }
        if ($amount > $goods->getAllStock()) {
            return false;
        }
        return true;
//        if ($goods->limit_type == 1) {
//            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
//            if (Yii::$app->cache->exists('today_goods_' . $beginToday. '_'. $gid)
//                && Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) > $goods->limit_amount
//            ) {
//                return false;
//            }
//
//            if ((Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) + $amount) > $goods->limit_amount
//            ) {
//                return false;
//            }
//        } elseif($goods->limit_type == 2) {
//            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
//            if (Yii::$app->cache->exists('today_goods_' . $beginToday. '_'. $gid)
//                && Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) > $goods->limit_amount
//            ) {
//                return false;
//            }
//            if ((Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) + $amount) > $goods->limit_amount
//            ) {
//                return false;
//            }
//        } else {
//            return true;
//        }
//        $goods_arr = [6, 7, 10, 31, 32, 33, 35, 38];
//        if (in_array($gid, $goods_arr)) {
//            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
//            if (Yii::$app->cache->exists('today_goods_' . $beginToday. '_'. $gid)
//                && Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) > 3
//            ) {
//                return false;
//            }
//
//            if ((Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid) + $amount) > 3
//            ) {
//                return false;
//            }
//        }
        return true;
    }

    /**
     * 商品判断每天的限购
     * @param $gid int 商品编号
     * @param $amount int 商品数量
     */
    public static function setTodayGoodsLimit($gid, $amount)
    {
        $goods_arr = [6, 7, 10, 31, 32, 33, 35, 38];
        if (in_array($gid, $goods_arr)) {
//        if ($gid == 6) {
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            if (Yii::$app->cache->exists('today_goods_' . $beginToday. '_'. $gid)) {
                $old_amount = Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid);
                Yii::$app->cache->set('today_goods_' . $beginToday. '_'. $gid, $old_amount + $amount);
            } else {
                Yii::$app->cache->set('today_goods_' . $beginToday. '_'. $gid, $amount);
            }
        }
    }
}
