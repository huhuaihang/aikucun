<?php

namespace app\modules\admin\controllers;

use app\models\City;
use app\models\KeyMap;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderLog;
use PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 订单管理
 * Class OrderController
 * @package app\modules\admin\controllers
 */
class OrderController extends BaseController
{
    /**
     * 订单列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PHPExcel_Exception
     * @throws Exception
     */
    public function actionList()
    {
        if (!$this->manager->can('order/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Order::find()->groupBy('{{%order}}.id');
        $query->andFilterWhere(['{{%order}}.uid' => $this->get('uid')]);
        $query->andFilterWhere(['{{%order}}.no' => $this->get('search_no')]);
        if (!empty($this->get('search_order_type'))) {
            $order_type = $this->get('search_order_type');
            switch ($order_type) {
                case  1://礼包订单
                    $query->andWhere(['{{%order}}.is_pack' => 1 , '{{%order}}.pack_coupon_status' => 0]);
                    break;
                case  2://卡券礼包订单
                    $query->andWhere(['<>', '{{%order}}.pack_coupon_status', 0]);
                    break;
                case  3://积分兑换订单
                    $query->andWhere(['{{%order}}.is_score' => 1]);
                    break;
                case  4://优惠券活动订单
                    $query->andWhere(['{{%order}}.is_coupon' => 1]);
                    break;
                case  5://限时抢购活动订单
                    $query->andWhere(['>', '{{%order}}.discount_ids', 0]);
                    break;
            }

        }
        $query->joinWith(['itemList', 'itemList.goods','shop']);
        $query->andFilterWhere(['like', '{{%goods}}.title',$this->get('search_goods_name')]);
        $query->andFilterWhere(['{{%order}}.status' => $this->get('search_status')]);
        $query->andFilterWhere(['like', '{{%shop}}.name', $this->get('search_shop_name')]);
        if (!empty($this->get('search_start_date'))) {
            $query->andFilterWhere(['>=', '{{%order}}.create_time', strtotime($this->get('search_start_date'))]);
        }
        if (!empty($this->get('search_end_date'))) {
            $query->andFilterWhere(['<', '{{%order}}.create_time', strtotime($this->get('search_end_date')) + 86400]);
        }
        $query->joinWith('financeLog');
        $query->andFilterWhere(['{{%finance_log}}.pay_method' => $this->get('search_pay_method')]);
        $query->andFilterWhere(['{{%finance_log}}.status' => $this->get('search_pay_status')]);
        $query->andFilterWhere(['{{%finance_log}}.trade_no' => $this->get('search_trade_no')]);
        $search_deliver_info = $this->get('search_deliver_info');
        if (!empty($search_deliver_info)) {
            $query->andWhere(['like', '{{%order}}.deliver_info', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_deliver_info))) . '%', false]);
        }
        $query->joinWith('user');
        $search_user_info = $this->get('search_user_info');
        if (!empty($search_user_info)) {
            $query->andWhere(['like', '{{%user}}.mobile', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($search_user_info))) . '%', false]);
        }
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '订单号',
                         '卖家',
                         '买家',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                         '订单总金额',
                         '物流费用',
                         '商品金额',
                         '支付交易号',
                         '支付金额',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商户备注',
                         '状态',
                         '创建时间',
                         '商品标题',
                         '商品规格',
                         '商品单价',
                         '商品数量',
                         '操作记录',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $order) {
                /** @var Order $order */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $order->no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $order->shop->name);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, emoji_unified_to_docomo($order->user->nickname));
                if (!empty($order->deliver_info)) {
                    if (strstr($order->getDeliverInfoJson('address'), '省')
                        || strstr($order->getDeliverInfoJson('address'), '上海')
                        || strstr($order->getDeliverInfoJson('address'), '重庆')
                        || strstr($order->getDeliverInfoJson('address'), '天津')
                        || strstr($order->getDeliverInfoJson('address'), '自治区')) {
                        $sheet->setCellValueExplicitByColumnAndRow(3, $r, $order->getDeliverInfoJson('address'));
                    } else {
                        $area = $order->getDeliverInfoJson('area');
                        $city = City::findByCode($area);
                        $sheet->setCellValueExplicitByColumnAndRow(3, $r, implode(' ', $city->address()) . ' ' . emoji_unified_to_docomo($order->getDeliverInfoJson('address')));
                    }

                    $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->getDeliverInfoJson('name')));
                    $sheet->setCellValueExplicitByColumnAndRow(5, $r, $order->getDeliverInfoJson('mobile'));
                }
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $order->amount_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $order->deliver_fee, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $order->goods_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r, $order->financeLog->trade_no);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, $order->financeLog->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method));
                    $sheet->setCellValueExplicitByColumnAndRow(12, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status));
                }
                $sheet->setCellValueExplicitByColumnAndRow(13, $r, emoji_unified_to_docomo($order->user_remark));
                $sheet->setCellValueExplicitByColumnAndRow(14, $r, $order->merchant_remark);
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, KeyMap::getValue('order_status', $order->status));
                $sheet->setCellValueExplicitByColumnAndRow(16, $r, Yii::$app->formatter->asDatetime($order->create_time));
                $_r1 = 0;
                foreach ($order->itemList as $item) {
                    $sheet->setCellValueExplicitByColumnAndRow(17, $r + $_r1, $item->title);
                    $sheet->setCellValueExplicitByColumnAndRow(18, $r + $_r1, $item->sku_key_name);
                    $sheet->setCellValueExplicitByColumnAndRow(19, $r + $_r1, $item->price, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(20, $r + $_r1, $item->amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                $op_log = '';
                foreach (OrderLog::find()->andWhere(['oid' => $order->id])->each() as $log) {/** @var OrderLog $log */
                    $op_log .= Yii::$app->formatter->asDatetime($log->time) . chr(9);
                    $op_log .= KeyMap::getValue('order_log_u_type', $log->u_type) . chr(9);
                    $op_log .= $log->content . chr(10);
                }
                $sheet->setCellValueExplicitByColumnAndRow(21, $r, $op_log);
                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }

        if ($this->get('export1') == 'excel1') {
            $query->andWhere(['IN', '{{%order}}.id', [18926  ,19745  ,19178  ,19265  ,19173  ,19419  ,18786  ,18762  ,19596  ,18618  ,19109  ,19047  ,19233  ,18850  ,19719  ,18909  ,18793  ,18989  ,18759  ,18617  ,18885  ,19700  ,19210  ,19645  ,18843  ,19556  ,19227  ,19607  ,19695  ,19206  ,18994  ,19183  ,18755  ,19837  ,19796  ,19774  ,18712  ,19684  ,19127  ,19372  ,18864  ,19851  ,19287  ,19153  ,18879  ,19020  ,19471  ,19099  ,19829  ,19859  ,19557  ,19552  ,18774  ,18643  ,19580  ,19482  ,18789  ,19665  ,19512  ,18967  ,18717  ,18768  ,19203  ,18964  ,18752  ,18990  ,18961  ,19456  ,18681  ,19167  ,18787  ,19387  ,19064  ,18827  ,18878  ,19845  ,19104  ,19510  ,19817  ,19643  ,19385  ,19642  ,18686  ,19560  ,19532  ,19166  ,19111  ,18685  ,18805  ,18831  ,18784  ,19821  ,19682  ,19786  ,19375  ,18969  ,19431  ,19037  ,19544  ,19108  ,19263  ,19602  ,18857  ,19103  ,19039  ,19146  ,19250  ,19053  ,19505  ,19322  ,19710  ,18798  ,19801  ,18913  ,19819  ,19823  ,18888  ,19223  ,18684  ,19089  ,19771  ,18894  ,19402  ,18756  ,19818  ,19513  ,19253  ,19473  ,19149  ,18993  ,19515  ,19399  ,19026  ,19792  ,18845  ,19833  ,19215  ,18731  ,18906  ,19835  ,19857  ,19050  ,18910  ,19035  ,19150  ,18728  ,18813  ,18887  ,19144  ,18855  ,18797  ,19858  ,18628  ,19247  ,18921  ,18877  ,19425  ,19778  ,18818  ,18709  ,19043  ,18811  ,18794  ,19016  ,18780  ,18812  ,19393  ,19063  ,19480  ,19400  ,18783  ,19277  ,19723  ,19551  ,19084  ,18946  ,19716  ,19098  ,19688  ,18740  ,19683  ,19121  ,18624  ,19802  ,18741  ,18835  ,19589  ,18860  ,19069  ,19401  ,19726  ,18817  ,18943  ,19640  ,19795  ,18852  ,18814  ,18695  ,19470  ,19048  ,19254  ,19067  ,19279  ,18651  ,19501  ,18723  ,18750  ,19579  ,18972  ,19022  ,19696  ,19662  ,19126  ,19475  ,19787  ,19455  ,19266  ,18976  ,19739  ,19584  ,18690  ,19595  ,19678  ,18868  ,18829  ,18808  ,19180  ,18719  ,19655  ,18678  ,18899  ,19003  ,19001  ,18826  ,19273  ,18703  ,19620  ,19034  ,18849  ,19246  ,19232  ,19569  ,19717  ,18687  ,18996  ,19663  ,18727  ,19058  ,19165  ,19090  ,18984  ,18907  ,18744  ,19591  ,18823  ,18766  ,19181  ,19348  ,18998  ,18886  ,19038  ,19559  ,18995  ,19548  ,19614  ,18884  ,18880  ,19052  ,19061  ,18614  ,19368  ,18725  ,19019  ,18858  ,19145  ,18917  ,19008  ,19018  ,19029  ,19186  ,18923  ,18882  ,18696  ,18680  ,18737  ,18674  ,19188  ,18612  ,19243  ,18611  ,19283  ,19295  ,19304  ,19504  ,18683  ,18960  ,18705  ,19346  ,19214  ,18871  ,19244  ,19172  ,19525  ,19083  ,18736  ,18739  ,18747  ,18765  ,19430  ,18757  ,18775  ,18767  ,19139  ,18937  ,18770  ,18773  ,18822  ,18799  ,18963  ,19129  ,19059  ,18801  ,18802  ,18821  ,18904  ,18824  ,18889  ,18830  ,19762  ,18836  ,18839  ,18862  ,18842  ,18846  ,18883  ,18875  ,18856  ,18869  ,18870  ,18874  ,19114  ,19781  ,18881  ,18908  ,18898  ,19110  ,18956  ,18920  ,19793  ,18933  ,18935  ,18938  ,18953  ,18959  ,18948  ,18954  ,19055  ,18975  ,19045  ,19011  ,19575  ,18974  ,18985  ,18973  ,19023  ,18979  ,18980  ,18991  ,18999  ,19143  ,18997  ,19017  ,19012  ,19080  ,19538  ,19060  ,19046  ,19033  ,19041  ,19056  ,19706  ,19044  ,19062  ,19177  ,19518  ,19065  ,19068  ,19073  ,19628  ,19078  ,19217  ,19718  ,19780  ,19537  ,19137  ,19132  ,19135  ,19122  ,19130  ,19187  ,19131  ,19134  ,19157  ,19592  ,19490  ,19148  ,19164  ,19155  ,19162  ,19154  ,19163  ,19169  ,19171  ,19207  ,19529  ,19191  ,19197  ,19200  ,19192  ,19201  ,19371  ,19208  ,19226  ,19496  ,19799  ,19238  ,19740  ,19248  ,19411  ,19309  ,19807  ,19654  ,19565  ,19689  ,19382  ,19500  ,19391  ,19467  ,19797  ,19394  ,19427  ,19395  ,19403  ,19417  ,19424  ,19423  ,19511  ,19561  ,19440  ,19444  ,19446  ,19442  ,19466  ,19582  ,19476  ,19454  ,19462  ,19463  ,19469  ,19497  ,19474  ,19478  ,19484  ,19486  ,19495  ,19547  ,19506  ,19542  ,19499  ,19519  ,19514  ,19526  ,19522  ,19527  ,19597  ,19528  ,19558  ,19531  ,19533  ,19648  ,19543  ,19535  ,19545  ,19546  ,19578  ,19828  ,19604  ,19563  ,19603  ,19618  ,19594  ,19593  ,19606  ,19673  ,19686  ,19730  ,19664  ,19610  ,19634  ,19609  ,19608  ,19625  ,19622  ,19637  ,19638  ,19636  ,19644  ,19646  ,19734  ,19650  ,19652  ,19707  ,19657  ,19658  ,19677  ,19661  ,19675  ,19681  ,19788  ,19692  ,19713  ,19733  ,19832  ,19714  ,19721  ,19750  ,19748  ,19754  ,19746  ,19760  ,19758  ,19789  ,19775  ,19776  ,19785  ,19783  ,19803  ,19800  ,19827  ,19843  ,19842  ,19856]]);
            // 导出Excel文件
            $excel = new PHPExcel();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '订单号',
                         '卖家',
                         '买家',
                         '收货地址',
                         '收货人',
                         '收货人手机',
                         '订单总金额',
                         '物流费用',
                         '商品金额',
                         '支付交易号',
                         '支付金额',
                         '支付方式',
                         '支付状态',
                         '用户备注',
                         '商户备注',
                         '状态',
                         '创建时间',
                         '商品标题',
                         '商品规格',
                         '商品单价',
                         '商品数量',
                         '操作记录',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font'=>['bold'=>true], 'alignment'=>['horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            foreach ($query->each() as $order) {
                /** @var Order $order */
                $sheet->setCellValueExplicitByColumnAndRow(0, $r, $order->no, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $order->shop->name);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, emoji_unified_to_docomo($order->user->nickname));
                if (!empty($order->deliver_info)) {
                    if (strstr($order->getDeliverInfoJson('address'), '省')
                        || strstr($order->getDeliverInfoJson('address'), '上海')
                        || strstr($order->getDeliverInfoJson('address'), '重庆')
                        || strstr($order->getDeliverInfoJson('address'), '天津')
                        || strstr($order->getDeliverInfoJson('address'), '自治区')) {
                        $sheet->setCellValueExplicitByColumnAndRow(3, $r, $order->getDeliverInfoJson('address'));
                    } else {
                        $area = $order->getDeliverInfoJson('area');
                        $city = City::findByCode($area);
                        $sheet->setCellValueExplicitByColumnAndRow(3, $r, implode(' ', $city->address()) . ' ' . emoji_unified_to_docomo($order->getDeliverInfoJson('address')));
                    }

                    $sheet->setCellValueExplicitByColumnAndRow(4, $r, emoji_unified_to_docomo($order->getDeliverInfoJson('name')));
                    $sheet->setCellValueExplicitByColumnAndRow(5, $r, $order->getDeliverInfoJson('mobile'));
                }
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, $order->amount_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, $order->deliver_fee, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, $order->goods_money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                if (!empty($order->fid)) {
                    $sheet->setCellValueExplicitByColumnAndRow(9, $r, $order->financeLog->trade_no);
                    $sheet->setCellValueExplicitByColumnAndRow(10, $r, $order->financeLog->money, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(11, $r, KeyMap::getValue('finance_log_pay_method', $order->financeLog->pay_method));
                    $sheet->setCellValueExplicitByColumnAndRow(12, $r, KeyMap::getValue('finance_log_status', $order->financeLog->status));
                }
                $sheet->setCellValueExplicitByColumnAndRow(13, $r, emoji_unified_to_docomo($order->user_remark));
                $sheet->setCellValueExplicitByColumnAndRow(14, $r, $order->merchant_remark);
                $sheet->setCellValueExplicitByColumnAndRow(15, $r, KeyMap::getValue('order_status', $order->status));
                $sheet->setCellValueExplicitByColumnAndRow(16, $r, Yii::$app->formatter->asDatetime($order->create_time));
                $_r1 = 0;
                foreach ($order->itemList as $item) {
                    $sheet->setCellValueExplicitByColumnAndRow(17, $r + $_r1, $item->title);
                    $sheet->setCellValueExplicitByColumnAndRow(18, $r + $_r1, $item->sku_key_name);
                    $sheet->setCellValueExplicitByColumnAndRow(19, $r + $_r1, $item->price, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicitByColumnAndRow(20, $r + $_r1, $item->amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $_r1++;
                }
                $op_log = '';
                foreach (OrderLog::find()->andWhere(['oid' => $order->id])->each() as $log) {/** @var OrderLog $log */
                    $op_log .= Yii::$app->formatter->asDatetime($log->time) . chr(9);
                    $op_log .= KeyMap::getValue('order_log_u_type', $log->u_type) . chr(9);
                    $op_log .= $log->content . chr(10);
                }
                $sheet->setCellValueExplicitByColumnAndRow(21, $r, $op_log);
                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="订单列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $excelWriter->save('php://output');
            return null;
        }
        $sum = $query->orderBy('create_time DESC')->sum("goods_money");
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();

        return $this->render('list', [
            'model_list' => $model_list,
            'sum' => $sum,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 订单详细查看
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->manager->can('order/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order)) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }
    /**
     * 优惠券活动订单详细查看
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionOrderCouponView()
    {
        if (!$this->manager->can('order/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $coupon_id = $this->get('id');
        $order = Order::find()->where(['coupon_id'=>$coupon_id])->one();
        if (empty($order)) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }

    /**
     * 兑换券活动订单详细查看
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionOrderPackCouponView()
    {
        if (!$this->manager->can('order/list')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $coupon_id = $this->get('id');
        $order = Order::find()->where(['id'=> $coupon_id])->one();
        if (empty($order)) {
            throw new NotFoundHttpException('没有找到订单信息。');
        }
        return $this->render('view', [
            'order' => $order,
        ]);
    }

    /**
     * 取消订单审核列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionVerifyCancel()
    {
        if (!$this->manager->can('order/verify-cancel')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Order::find();
        $query->andWhere(['status' => Order::STATUS_CANCEL_WAIT_MANAGER]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('cancel_verify', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 通过订单取消申请AJAX接口
     * @return array
     */
    public function actionAcceptCancel()
    {
        if (!$this->manager->can('order/verify-cancel')) {
            return ['message' => '没有权限。'];
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order)) {
            return ['message' => '没有找到订单信息。'];
        }
        if ($order->status != Order::STATUS_CANCEL_WAIT_MANAGER) {
            return ['message' => '订单状态错误。'];
        }
        try {
            $order->doCancel();
            OrderLog::info($this->manager->id, OrderLog::U_TYPE_MANAGER, $order->id, '通过订单取消申请。');
            return ['result' => 'success'];
        } catch (Exception $e) {
            Yii::error('订单取消时出现错误：' . $e->getMessage());
            return ['message' => $e->getMessage()];
        }
    }

    /**
     * 拒绝订单取消申请AJAX接口
     * @return array
     */
    public function actionRejectCancel()
    {
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order)) {
            return ['message' => '没有找到订单信息。'];
        }
        if ($order->status != Order::STATUS_CANCEL_WAIT_MANAGER) {
            return ['message' => '订单状态错误。'];
        }
        if (OrderDeliver::find()->where(['oid' => $order->id])->exists()) {
            $order->status = Order::STATUS_PACKED;
        } else {
            $order->status = Order::STATUS_PAID;
        }

        $order->save(false);
        OrderLog::info($this->manager->id, OrderLog::U_TYPE_MANAGER, $order->id, '拒绝订单取消申请。');
        return ['result' => 'success'];
    }
}
