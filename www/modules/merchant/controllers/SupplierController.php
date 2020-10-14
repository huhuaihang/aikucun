<?php
namespace app\modules\merchant\controllers;

use app\models\Goods;
use app\models\GoodsSku;
use app\models\KeyMap;
use app\models\ManagerLog;
use app\models\Order;
use app\models\Supplier;
use app\models\SupplierConfigForm;
use app\models\UCenterApi;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 供货商
 * Class SupplierController
 * @package app\modules\admin\controllers
 */
class SupplierController extends BaseController
{
    /**
     * 供货商列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionList()
    {
        $query = Supplier::find();
        $query->andFilterWhere(['mobile' => $this->get('search_mobile')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 设置供货商状态AJAX接口
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionStatus()
    {
        $id = $this->get('id');
        $supplier = Supplier::findOne(['id' => $id]);
        if (empty($supplier)) {
            throw new NotFoundHttpException('没有找到供货商信息。');
        }
        $supplier->status = [Supplier::STATUS_STOP => Supplier::STATUS_OK, Supplier::STATUS_OK => Supplier::STATUS_STOP][$supplier->status];
        if (!$supplier->save(false)) {
            return [
                'result' => 'failure',
                'message' => '无法保存状态。',
                'errors' => $supplier->errors
            ];
        }
        ManagerLog::info(1, '保存供货商【' . $supplier->id . '】状态【' . $supplier->status . '】。');
        return ['result' => 'success'];
    }

    /**
     * 编辑供货商
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \app\modules\api\models\ApiException
     * @throws \yii\base\Exception
     */
    public function actionEdit()
    {

        $id = $this->get('id');
        if (!empty($id)) {
            $supplier = Supplier::findOne(['id' => $id]);
            if (empty($supplier)) {
                throw new NotFoundHttpException('没有找到供货商。');
            }
        } else {
            $supplier = new Supplier();
            $supplier->setScenario('add');
            $supplier->status = Supplier::STATUS_OK;
            $supplier->create_time = time();
        }
        $configForm = new SupplierConfigForm($supplier->id);

        if ($supplier->load($this->post()) && $configForm->load($this->post())) {
            $postSupplier = $this->post('Supplier');

            if (!empty($postSupplier['password'])) {
                $supplier->password = Yii::$app->security->generatePasswordHash($postSupplier['password']);
            } else {
                $supplier->password = $supplier->oldAttributes['password'];
            }

            if ($supplier->save()) {
                $configForm->sid = $supplier->id;
                if (!$configForm->save()) {
                    Yii::$app->session->addFlash('warning', '无法保存供货商配置信息。');
                }
                ManagerLog::info(1, '保存供货商', print_r($supplier->attributes, true));
                Yii::$app->session->addFlash('success', '数据已保存。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/merchant/supplier/list']),
                    'txt' => '供货商列表'
                ]));
            }
        }
        return $this->render('edit', [
            'supplier' => $supplier,
            'configForm' => $configForm,
        ]);
    }

    /**
     * 商品列表
     * @return string
     * @throws ForbiddenHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionGoodsList()
    {
        $query = Goods::find();
        $query->joinWith('shop');
        $query->joinWith('supplier');
        $query->joinWith('goods_type');
        $query->joinWith('goods_category');
        $query->andWhere('goods.supplier_id IS NOT NULL');
        $query->andFilterWhere(['like', 'supplier.name', $this->get('search_supplier')]);
        $query->andFilterWhere(['like', 'title', $this->get('search_name')]);
        $query->andFilterWhere(['goods.id' => $this->get('search_id')]);
        $query->andFilterWhere(['goods_type.id' => $this->get('search_type')]);
        $query->andFilterWhere(['goods_category.id' => $this->get('search_category')]);
        if ($this->get('export') == 'excel') {
            // 导出Excel文件
            $excel = new Spreadsheet();
            $sheet = $excel->setActiveSheetIndex(0);
            foreach ([
                         '编号',
                         '商品名称',
                         '商户名称',
                         '品牌',
                         '供货商',
                         '分类',
                         '状态',
                         '新品推荐',
                         '类型',
                         '添加时间',
                         '结算价',
                         '零售价',
                     ] as $index => $title) {
                $sheet->setCellValue(chr(65 + $index) . '1', $title);
            }
            $sheet->getStyle('A1:Z1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $r = 2;
            /** @var Goods $goods */
            foreach ($query->each() as $goods) {
                $sheet->setCellValueExplicitByColumnAndRow(1, $r, $goods->id, DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicitByColumnAndRow(2, $r, $goods->title, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(3, $r, !empty($goods->hid) ? $goods->house->name : $goods->shop->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(4, $r, empty($goods->bid) ? '' : $goods->goods_brand->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(5, $r, $goods->supplier->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(6, $r, empty($goods->cid) ? '' : $goods->goods_category->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(7, $r, KeyMap::getValue('goods_status', $goods->status), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(8, $r, empty($goods->is_new_recommend) ? '否' : '是', DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(9, $r, $goods->goods_type->name, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(10, $r, date('Y-m-d H:i:s', $goods->create_time), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(11, $r, $goods->supplier_price, DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(12, $r, $goods->price, DataType::TYPE_STRING);
                $_r1 = 0;
                $_r1 = max(1, $_r1);
                $r += $_r1;
            }
            $sheet->freezePane('A2');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="商品列表导出_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            $excelWriter = IOFactory::createWriter($excel, 'Xls');
            $excelWriter->save('php://output');
            die();
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $model_list = $query->all();
        return $this->render('goods_list', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 商品编辑
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionGoodsEdit()
    {
        $goods = Goods::findOne(['id' => $this->get('id')]);
        if (empty($goods)) {
            throw new NotFoundHttpException('没有找到商品信息。');
        }

        if ($this->isPost()) {
            if (empty($this->post('Goods')['supplier_price'])) {
                throw new NotFoundHttpException('没有找到商品供货商结算价。');
            }
            $goods->supplier_price = $this->post('Goods')['supplier_price'];
            if (!$goods->save()) {
                throw new ServerErrorHttpException('无法保存商品信息。');
            }
            ManagerLog::info(1, '保存商品【' . $goods->id . '】结算价【' . $goods->supplier_price . '】。');
            $skuSupplierPrice = $this->post('SkuSupplierPrice');
            if (!empty($skuSupplierPrice) && is_array($skuSupplierPrice)) {
                foreach ($skuSupplierPrice as $skuId => $supplierPrice) {
                    if (empty($supplierPrice)) {
                        throw new NotFoundHttpException('结算价必填。');
                    }
                    $sku = GoodsSku::findOne(['id' => $skuId, 'gid' => $goods->id]);
                    if (empty($sku)) {
                        throw new NotFoundHttpException('没有找到规格信息。');
                    }
                    $sku->supplier_price = $supplierPrice;
                    if ($sku->save(false)) {
                        ManagerLog::info(1, '保存商品【' . $goods->id . '】规格【' . $sku->key_name . '】结算价【' . $sku->supplier_price . '】。');
                    }
                }
            }
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/supplier/goods-list']),
                'txt' => '商品列表'
            ]));
        }
        return $this->render('goods_edit', [
            'goods' => $goods,
        ]);
    }

    /**
     * 供货商订单列表
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionOrderList()
    {
        $query = Order::find()->alias('order');
        $query->joinWith(['itemList order_item', 'itemList.goods goods']);
        $query->andWhere('goods.supplier_id > 0');
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $query->orderBy('order.create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $orderList = $query->all();
        return $this->render('order_list', [
            'orderList' => $orderList,
            'pagination' => $pagination,
        ]);
    }
}
