<?php

namespace app\modules\api\controllers;

use app\models\Discount;
use app\models\DiscountGoods;
use app\models\Goods;
use app\models\GoodsSku;
use app\models\Marketing;
use app\models\ShopConfig;
use app\models\UserCart;
use app\models\Util;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;
use yii\web\Cookie;

/**
 * 购物车控制器
 * Class CartController
 * @package app\modules\h5\controllers
 */
class CartController extends BaseController
{
    /**
     * 购物车列表
     * GET
     */
    public function actionList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return [
                'shop_cart_list' => $this->cookieList(),
            ];
        }
        $this->mergeCookie($user->id);
        $query = UserCart::find();
        $query->andWhere(['uid' => $user->id]);
        $query->orderBy('sid ASC, create_time DESC');
        $shop_cart_list = [];
        foreach ($query->each() as $cart) {
            /** @var UserCart $cart */
            if (!isset($shop_cart_list[$cart->sid])) {
                $shop_cart_list[$cart->sid] = [
                    'shop' => [
                        'id' => $cart->shop->id,
                        'name' => $cart->shop->name,
                        'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($cart->sid, 'logo'),
                    ],
                    'cart_list' => [],
                ];
            }
            $shop_cart_list[$cart->sid]['cart_list'][] = [
                'gid' => $cart->gid,
                'sku_key_name' => $cart->sku_key_name,
                'amount' => $cart->amount,
                'price' => $cart->price,
                'goods' => [
                    'title' => $cart->goods->title,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $cart->goods->main_pic,
                    'is_supplier' => ($cart->goods->supplier_id && $cart->goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                ],
            ];
        }
        return [
            'shop_cart_list' => array_values($shop_cart_list),
        ];
    }

    /**
     * 加入到购物车
     * GET
     * gid 商品编号
     * sku_id 规格编号
     * amount 增加或减少的数量
     * set_amount 需要设置的最终数量，会覆盖amount
     */
    public function actionAdd()
    {
        $gid = $this->get('gid');
        $sku_key_name = $this->get('sku_key_name');
        $amount = $this->get('amount', 1);
        $set_amount = $this->get('set_amount');
        if (empty($gid)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }
        $goods = Goods::findOne($gid);
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有找到商品信息。',
            ];
        }
        if ($goods->status != Goods::STATUS_ON) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '商品暂时无法加入购物车。',
            ];
        }
        if ($goods->is_pack == 1) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '亲，礼包商品无法加入购物车，请直接购买！',
            ];
        }

        //限时抢购商品
        $item_list[0] = [
            'goods' => [
                'id' => $goods->id,
                'title' => $goods->title,
                'price' => $goods->price,
                'self_price' => 0,
            ],
            'amount' =>1,
        ];

        $price = $goods->price;
        if (!empty($sku_key_name)) {
            /** @var GoodsSku $sku */
            $sku = GoodsSku::find()->andWhere(['gid' => $goods->id, 'key_name' => $sku_key_name])->one();
            if (empty($sku)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '您选择的型号不存在。',
                ];
            }
            //限时抢购商品
            $item_list[0]['sku'] = [
                'id' => $sku->id,
                'key' => $sku->key,
                'key_name' => $sku->key_name,
                'price' => $sku->price,
                'self_price' => 0,
            ];

            $price = $sku->price;
            $stock = $sku->stock;
        } else {
            if (GoodsSku::find()->andWhere(['gid' => $goods->id])->exists()) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '必须选择一个商品规格。',
                ];
            }
            $stock = $goods->stock;
        }
        $res=Marketing::calcDiscount($item_list,'',false);//限时抢购
        if (!empty($res['message'])) {
            return $res;
        }
        $discountPrice = $res['discountMoney'];//减价金额
        $price=Util::money($price-$discountPrice);
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $this->cookieAdd($gid, $amount, $sku_key_name);
        }
        $uid = $user->id;
        $cart = UserCart::find()
            ->andWhere(['uid' => $uid])
            ->andWhere(['gid' => $gid])
            ->andWhere(['sku_key_name' => $sku_key_name])
            ->one();
        if (empty($cart)) {
            $cart = new UserCart();
            $cart->uid = $uid;
            $cart->sid = $goods->sid;
            $cart->gid = $goods->id;
            $cart->sku_key_name = $sku_key_name;
            $cart->amount = 0;
            $cart->create_time = time();
        }
        if (!empty($set_amount)) {
            $cart->amount = $set_amount;
        } else {
            $cart->amount = $cart->amount + $amount;
        }
        if ($cart->amount <= 0) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '数量错误。'
            ];
        }
        if ($cart->amount > $stock) {
            return [
                'error_code' => ErrorCode:: CART_ADD_FAIL,
                'message' => '库存不足。'
            ];
        }
        $cart->price = $price;
        if ($cart->save()) {
            return [
                'amount' => $cart->amount,
            ];
        }
        return [
            'error_code' => ErrorCode::CART_ADD_FAIL,
            'message' => '无法加入购物车。',
            'errors' => $cart->errors
        ];
    }

    /**
     * 删除购物车单个商品
     * GET
     * id 单个购物车编号
     * ids 多个编号，半角逗号隔开
     */
    public function actionDelete()
    {
        $gid = $this->get('gid');
        $sku_key_name = $this->get('sku_key_name');
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            $this->cookieDelete($gid, $sku_key_name);
            return [];
        }
        UserCart::deleteAll(['uid' => $user->id, 'gid' => $gid, 'sku_key_name' => $sku_key_name]);
        return [];
    }

    /**
     * Cookie中的购物车信息
     * @return array
     */
    private function cookieList()
    {
        $cookie = Yii::$app->request->cookies->get('cart');
        if (empty($cookie)) {
            return [];
        }
        $cart = $cookie->value; // gid.amount.sku|
        if (empty($cart)) {
            return [];
        }
        $shop_cart_list = [];
        foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
            $item = explode('^^', $item);
            $gid = $item[0];
            $amount = $item[1];
            $sku_key_name = count($item) > 2 ? $item[2] : '';
            $goods = Goods::findOne($gid);
            if (empty($goods)) {
                continue;
            }
            if (!isset($shop_cart_list[$goods->sid])) {
                $shop_cart_list[$goods->sid] = [
                    'shop' => [
                        'id' => $goods->shop->id,
                        'name' => $goods->shop->name,
                        'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($goods->sid, 'logo'),
                    ],
                    'cart_list' => [],
                ];
            }
            $shop_cart_list[$goods->sid]['cart_list'][] = [
                'gid' => $gid,
                'sku_key_name' => $sku_key_name,
                'amount' => $amount,
                'price' => $goods->price,
                'goods' => [
                    'title' => $goods->title,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                ],
            ];
        }
        return array_values($shop_cart_list);
    }

    /**
     * 将Cookie中的购物车合并到用户购物车中
     * @param $uid integer 用户编号
     * @return boolean
     */
    private function mergeCookie($uid)
    {
        $cookie = Yii::$app->request->cookies->get('cart');
        if (empty($cookie)) {
            return true;
        }
        $cart = $cookie->value; // gid.amount.sku|
        if (empty($cart)) {
            return true;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
                $item = explode('^^', $item);
                $gid = $item[0];
                $amount = $item[1];
                $sku_key_name = count($item) > 2 ? $item[2] : '';
                $goods = Goods::findOne($gid);
                if (empty($goods)) {
                    continue;
                }
                $cart = UserCart::find()
                    ->andWhere(['uid' => $uid, 'gid' => $goods->id, 'sku_key_name' => $sku_key_name])
                    ->one();
                if (empty($cart)) {
                    $cart = new UserCart();
                    $cart->uid = $uid;
                    $cart->amount = 0;
                    $cart->create_time = time();
                }
                $cart->sid = $goods->sid;
                $cart->gid = $goods->id;
                $cart->sku_key_name = $sku_key_name;
                $cart->amount = $cart->amount + $amount;
                $cart->price = $goods->price;
                if (!$cart->save()) {
                    throw new Exception('无法合并购物车。');
                }
            }
            $trans->commit();
            Yii::$app->response->cookies->remove('cart');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 加入到Cookie
     * @param $gid integer 商品编号
     * @param $amount integer 数量
     * @param $sku_key_name string 规格
     * @return array
     */
    private function cookieAdd($gid, $amount, $sku_key_name)
    {
        $cart = '';
        $cookie = Yii::$app->request->cookies->get('cart');
        if (!empty($cookie)) {
            $cart = $cookie->value;
        }
        $cart_list = [];
        foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
            if (preg_match('/^' . $gid . '\^\^\d+\^\^' . str_replace('/', '\/', $sku_key_name) . '/', $item)) {
                $item = explode('^^', $item);
                $item[1] = $item[1] + $amount;
                $amount = $item[1];
                $item = implode('^^', $item);
                $gid = 0;
            }
            $cart_list[] = $item;
        }
        if ($gid > 0) {
            $cart_list[] = $gid . '^^' . $amount . '^^' . $sku_key_name;
        }
        $cookie = new Cookie([
            'name' => 'cart',
            'value' => implode('$$$', $cart_list),
        ]);
        Yii::$app->response->cookies->add($cookie);
        return [
            'amount' => $amount,
        ];
    }

    /**
     * Cookie删除
     * @param $gid integer 商品编号
     * @param $sku_key_name string 规格
     */
    private function cookieDelete($gid, $sku_key_name)
    {
        $cookie = Yii::$app->request->cookies->get('cart');
        if (empty($cookie)) {
            return;
        }
        $cart = $cookie->value;
        $cart_list = [];
        foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
            if (preg_match('/^' . $gid . '\^\^\d+\^\^' . $sku_key_name . '/', $item)) {
                continue;
            }
            $cart_list[] = $item;
        }
        $cookie = new Cookie([
            'name' => 'cart',
            'value' => implode('$$$', $cart_list),
        ]);
        Yii::$app->response->cookies->add($cookie);
    }
}
