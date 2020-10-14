<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class GoodsExpress
 * @package models
 * @property $goods_list 商品列表[] [['gid' => 3, 'amount' => 5 ], ['gid' => 10, 'amount' => 3]] gid商品ID  amount商品数量
 * @property $p_area 省AREA_ID
 * @property $c_area 市AREA_ID
 */
class GoodsExpress extends Model
{
    /**
     * @param $goods_list array
     * @param $p_area integer
     * @param $c_area integer
     * @return array
     */
    public static function multiGoodsExpress($goods_list, $p_area, $c_area)
    {
        /** @var $temp_deliver [] */
        $temp_deliver = [];
        $fee = 0;
        foreach ($goods_list as $goods) {
            $fee = 0;
            $temp_deliver[$goods['gid']] = GoodsExpress::getGoodsExpress($goods['gid'], $goods['amount'], $p_area, $c_area);
            if (!empty($temp_deliver[$goods['gid']]['message'])) {
                return ['message' => $temp_deliver[$goods['gid']]['message']];
            }
            if (!empty($temp_deliver[$goods['gid']]['result'])) {
                /** @var $min_fee [] */
                $min_fee = ArrayHelper::getColumn($temp_deliver[$goods['gid']]['deliver_list'], 'fee');
                $temp_deliver[$goods['gid']]['deliver_list']['min_fee'] = min($min_fee);
            }
            foreach ($temp_deliver as $tk => $tv) {
                $fee += (int)$tv['deliver_list']['min_fee'];
            }
        }
        return ['result' => 'success', 'fee' => $fee];
    }

    /**
     * 获取单个 商品 的运费模板价格
     * @param $gid integer 商品ID
     * @param $amount integer 数量
     * @param $p_area string 省编码
     * @param $c_area string 市编码
     * @return array
     */
    public static function getGoodsExpress($gid, $amount, $p_area, $c_area)
    {
        $goods = Goods::findOne($gid);
        if (empty($goods)) {
            return ['message' => '没有找到商品信息。'];
        }
        if ($goods->status != Goods::STATUS_ON) {
            return ['message' => '商品已下架。'];
        }

        /** @var GoodsDeliverTemplate[] $goods_deliver_template_list */
        $goods_deliver_template_list = GoodsDeliverTemplate::find()->where(['gid' => $gid])->all();
        if (!empty($goods_deliver_template_list)) {
            $did = ArrayHelper::getColumn($goods_deliver_template_list, 'did');
            $deliver_list = ShopExpress::find()
                ->select(['{{%deliver_template}}.*', '{{%shop_express}}.*'])
                ->joinWith(['deliver_templates', 'express'])->where(['sid' => $goods->sid])
                ->andWhere(['in', '{{%deliver_template}}.id', $did])
                ->andWhere(['<>', '{{%deliver_template}}.status', DeliverTemplate::STATUS_STOP])
                ->andFilterWhere(['or', ['like', 'pid_list', $p_area], ['like', 'cid_list', $c_area]])
                ->asArray()->all();
        } else {
            /** @var $deliver_list ShopExpress[] */
            $deliver_list = ShopExpress::find()->select(['{{%deliver_template}}.*', '{{%shop_express}}.*'])
                ->joinWith(['deliver_templates', 'express'])
                ->where(['sid' => $goods->sid])
                ->andFilterWhere(['or', ['like', 'pid_list', $p_area], ['like', 'cid_list', $c_area]])
                ->andWhere(['<>', '{{%deliver_template}}.status', DeliverTemplate::STATUS_STOP])
                ->asArray()->all();
        }

        if (empty($deliver_list)) {
            return ['message' => '此地区物流可能不能达到请联系客服。'];
        } else {
            foreach ($deliver_list as $key => $deliver) {
                $deliver_list[$key]['fee'] = '0';
                $deliver_list[$key]['express_name'] = $deliver['express']['name'];
                $deliver_fee = [];
                if ($goods->deliver_fee_type == Goods::TYPE_ONLINE && $deliver['use_weight'] == 1) {
                    //按照重量计算运费
                    if (($goods->weight * $amount) > $deliver['weight_start']) {
                        //续重 几单位
                        if ($deliver['weight_extra'] == 0) {
                            $extra_weight = 0;
                        } else {
                            $extra_weight = ceil((($goods->weight * $amount) - $deliver['weight_start']) / $deliver['weight_extra']);
                        }
                        $extra_fee = $extra_weight * $deliver['weight_extra_fee'];
                        $deliver_fee[] = $deliver['weight_start_fee'] + $extra_fee;
                        $deliver_list[$key]['w_fee'] = $deliver['weight_start_fee'] + $extra_fee;
                    } else {
                        //首重
                        $deliver_fee[] = $deliver['weight_start_fee'];
                        $deliver_list[$key]['w_fee'] = $deliver['weight_start_fee'];
                    }
                }
                if ($goods->deliver_fee_type == Goods::DELIVER_FEE_TYPE_BULK && $deliver['use_bulk'] == 1) {
                    //按照体积计算
                    if (($goods->bulk * $amount) > $deliver['bulk_start']) {
                        //续 几单位
                        if ($deliver['bulk_extra'] == 0) {
                            $extra_bulk = 0;
                        } else {
                            $extra_bulk = ceil((($goods->bulk * $amount) - $deliver['bulk_start']) / $deliver['bulk_extra']);
                        }
                        $extra_fee = $extra_bulk * $deliver['bulk_extra_fee'];
                        $deliver_fee[] = $deliver['bulk_start_fee'] + $extra_fee;
                        $deliver_list[$key]['b_fee'] = $deliver['bulk_start_fee'] + $extra_fee;
                    } else {
                        $deliver_fee[] = $deliver['bulk_start_fee'];
                        $deliver_list[$key]['b_fee'] = $deliver['bulk_start_fee'];
                    }
                }
                if ($goods->deliver_fee_type == Goods::DELIVER_FEE_TYPE_COUNT && $deliver['use_count'] == 1) {
                    //计件计算
                    if ($amount > $deliver['count_start']) {
                        //续件 几单位
                        if ($deliver['count_extra'] == 0) {
                            $extra_count = 0;
                        } else {
                            $extra_count = ceil(($amount - $deliver['count_start']) / $deliver['count_extra']);
                        }
                        $extra_fee = $extra_count * $deliver['count_extra_fee'];
                        $deliver_fee[] = $deliver['count_start_fee'] + $extra_fee;
                        $deliver_list[$key]['c_fee'] = $deliver['count_start_fee'] + $extra_fee;
                    } else {
                        $deliver_fee[] = $deliver['count_start_fee'];
                        $deliver_list[$key]['c_fee'] = $deliver['count_start_fee'];
                    }
                }
                if (empty($deliver_fee)) {
                    $deliver_fee = [0];
                }
                $deliver_list[$key]['deliver_templates']['fees'] = $deliver_fee;

                $deliver_list[$key]['fee'] = min($deliver_list[$key]['deliver_templates']['fees']);
                if (empty($deliver_list[$key]['deliver_templates'])) {
                    unset($deliver_list[$key]);
                }
                unset($deliver_list[$key]['deliver_templates'], $deliver_list[$key]['express']);
            }
            $fee = ArrayHelper::getColumn($deliver_list, 'fee');
            array_multisort($fee, SORT_ASC, $deliver_list);
            if (empty($deliver_list)) {
                return ['message' => '此地区物流可能不能达到请联系客服。'];
            } else {
                return ['result' => 'success', 'deliver_list' => [$deliver_list[0]]];
            }
        }
    }
}