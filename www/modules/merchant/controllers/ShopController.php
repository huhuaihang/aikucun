<?php

namespace app\modules\merchant\controllers;

use app\models\AlipayApi;
use app\models\AllInPayApi;
use app\models\FinanceLog;
use app\models\GoodsBrand;
use app\models\GoodsCategory;
use app\models\GoodsTypeBrand;
use app\models\Merchant;
use app\models\MerchantConfig;
use app\models\MerchantConfigBankForm;
use app\models\MerchantConfigCompanyForm;
use app\models\MerchantConfigForm;
use app\models\MerchantConfigPersonBasicForm;
use app\models\MerchantConfigPersonForm;
use app\models\MerchantConfigPersonProductForm;
use app\models\MerchantConfigProductForm;
use app\models\MerchantFee;
use app\models\PinganApi;
use app\models\Shop;
use app\models\ShopBrand;
use app\models\ShopConfig;
use app\models\ShopDecoration;
use app\models\ShopDecorationItem;
use app\models\ShopFile;
use app\models\ShopFileCategory;
use app\models\ShopProfileForm;
use app\models\ShopTheme;
use app\models\System;
use app\models\User;
use app\models\WeixinAppApi;
use kucha\ueditor\UEditorAction;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * 店铺管理
 * Class ShopController
 * @package app\modules\merchant\controllers
 */
class ShopController extends BaseController
{
    /**
     * 上传文件AJAX接口
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
                    'imagePathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}', //上传保存路径
                    'imageRoot' => Yii::getAlias('@webroot'),
                    'scrawlPathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}',
                    'snapscreenPathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}',
                    'catcherPathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}',
                    'videoPathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}',
                    'filePathFormat' => '/uploads/shop/{yy}/{mm}/{time}-{rand:6}',
                    'imageManagerListPath' => '/uploads/shop',
                    'fileManagerListPath' => '/uploads/shop',
                ],
            ],
        ]);
    }

    /**
     * 店铺设置
     * @return string
     */
    public function actionProfile()
    {
        $model = new ShopProfileForm();
        $model->mid = $this->merchant->id;
        $model->loadDefault();
        $shop_theme = ShopTheme::findOne($this->shop->tid);
        if ($model->load($this->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', '店铺信息已保存。');
        }
        return $this->render('profile', [
            'model' => $model,
            'shop_theme' => $shop_theme,
        ]);
    }

    /**
     * 店铺详细资料
     * @return string
     */
    public function actionMerchantConfig()
    {
        $step  = $this->get('step');
        $url = '';
        if (!empty($step)) {
            switch ($step) {
                case 'person_basic':
                    $model = new MerchantConfigPersonBasicForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/merchant-config', 'step' => 'person_basic'];
                    break;
                case 'person':
                    $model = new MerchantConfigPersonForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/merchant-config', 'step' => 'person_product'];
                    break;
                case 'company':
                    $model = new MerchantConfigCompanyForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/merchant-config', 'step' => 'bank'];
                    break;
                case 'bank':
                    $model = new MerchantConfigBankForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/merchant-config', 'step' => 'product'];
                    break;
                case 'product':
                    $model = new MerchantConfigProductForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/edit-brand', 'status' => 'first'];
                    break;
                case 'person_product':
                    $model = new MerchantConfigPersonProductForm();
                    $view = 'merchant_config_' . $step;
                    $url = ['/merchant/shop/edit-brand', 'status' => 'first'];
                    break;
                default:
                    $model = new MerchantConfigForm();
                    $view = 'merchant_config';
                    $url = ['/merchant'];
                    break;
            }

        } else {
            $model = new MerchantConfigForm();
            $view = 'merchant_config';
        }
        $model->mid = $this->merchant->id;
        $model->sid = $this->shop->id;
        $model->loadDefault();
        if ($model->load($this->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', '店铺信息已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to($url),
                'txt' => '店铺入驻申请'
            ]));
        }

        return $this->render($view, [
            'model' => $model,
        ]);
    }

    /**
     * 返回修改入驻资料 设置状态AJAX接口
     * @return array
     */
    public function actionAjaxMerchantStatus()
    {
        $merchant = Merchant::findOne($this->merchant->id);
        $merchant->status = Merchant::STATUS_DATA1_OK;
        $merchant->save();
        return ['result' => 'success'];
    }

    /**
     * 准备支付保证金页面
     * @return string
     */
    public function actionReadyPay()
    {

        $merchant = Merchant::findOne($this->merchant->id);
        $fid = '';
        $earnest_money = 0;
        $shop_config = ShopConfig::getConfig($this->shop->id, 'cid_list');
        $cid_list = json_decode($shop_config, true);
        if (!empty($cid_list) && empty($merchant->shop->earnest_money_fid)){
            $merchant_fee = MerchantFee::find()->where(['in', 'cid', $cid_list])->max('earnest_money');
        }
        if (!empty($merchant->shop->earnest_money_fid)) {
            $finance = FinanceLog::findOne($merchant->shop->earnest_money_fid);
            $fid = $merchant->shop->earnest_money_fid;
            $earnest_money = $finance->money;
        }
        if (!empty($merchant_fee)) {
            $earnest_money = $merchant_fee;
        }
        return $this->render('ready_pay', [
            'earnest_money' => $earnest_money,
            'merchant_id' => $this->merchant->id,
            'fid' => $fid,
        ]);
    }

    /**
     * 入驻申请支付AJAX接口
     * @return array
     * @throws Exception
     */
    public function actionPay()
    {
        $type = $this->get('type');
        $pay_method = $this->get('pay_method');
        $merchant_fee = $this->get('merchant_fee');
        if ($type != 'merchant') {
            return ['message' => '参数错误。'];
        }
        $user_id = MerchantConfig::getConfig($this->merchant->id, 'register_from_uid');
        /** @var User $user */
        $user = '';
        if (!empty($user_id)) {
            $user = User::findOne($user_id);
        }
        if ($type == 'merchant') {
            $merchant = Merchant::findOne($this->merchant->id);
            if (empty($merchant) || $merchant->status != Merchant::STATUS_DATA2_OK) {
                return ['message' => '入驻申请状态错误。'];
            }
            if (empty($merchant_fee)) {
                return ['message' => '无法确定支付金额，请联系客服解决。'];
            }
            if (empty($merchant->shop->earnest_money_fid)) {
                $finance_log = new FinanceLog();
                $finance_log->type = FinanceLog::TYPE_MERCHANT_EARNEST_MONEY;
                $finance_log->pay_method = FinanceLog::PAY_METHOD_YHK;
                $finance_log->create_time = time();
            } else {
                $finance_log = FinanceLog::findOne($merchant->shop->earnest_money_fid);
            }
            $finance_log->money = $merchant_fee;
            $finance_log->status = FinanceLog::STATUS_WAIT;
            $finance_log->save();
            $shop = $merchant->shop;
            $shop->earnest_money_fid = $finance_log->id;
            $shop->save();
        } else {
            return ['message' => '参数错误。'];
        }

        $result = [];
        switch ($pay_method) {
            case FinanceLog::PAY_METHOD_YHK: // 银行卡
                if (System::getConfig('pingan_open') != 1) {
                    return ['message' => '系统没有开通银行卡支付。'];
                }
                $pingan_api = new PinganApi();
                $finance_log->pay_method = FinanceLog::PAY_METHOD_YHK;
                if (empty($finance_log->trade_no)) {
                    $finance_log->trade_no = $pingan_api->generateOrderNo(rand(10000000, 99999999));
                }
                $finance_log->save();
                $result['trade_no'] = $finance_log->trade_no;
                $result['money'] = $finance_log->money;
                break;
            case FinanceLog::PAY_METHOD_WX_SCAN: // 微信扫码
                if (System::getConfig('weixin_scan_pay_open') != 1) {
                    return ['message' => '系统没有开通微信扫码支付。'];
                }
                $finance_log->pay_method = FinanceLog::PAY_METHOD_WX_SCAN;
                $finance_log->trade_no = 'Y' . date('YmdHis') . $user_id;
                $finance_log->save();
                $weixin_api = new WeixinAppApi();
                list($prepay_id, $code_url) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '商户保证金', $finance_log->trade_no, $finance_log->money);
                $result['weixin'] = [
                    'prepay_id' => $prepay_id,
                    'code_url' => $code_url,
                ];
                break;
            case FinanceLog::PAY_METHOD_ZFB: // 支付宝
                if (System::getConfig('alipay_open') != 1) {
                    return ['message' => '系统没有开通支付宝支付。'];
                }
                $finance_log->pay_method = FinanceLog::PAY_METHOD_ZFB;
                if (empty($finance_log->trade_no)) {
                    $finance_log->trade_no = 'Y' . date('YmdHis') . $user_id;
                }
                $finance_log->save();
                $alipay_api = new AlipayApi();
                $form = $alipay_api->AlipayTradeWapPay(System::getConfig('site_name') . '订单', $finance_log->trade_no, $finance_log->money, Url::to(['/merchant/shop/ready-pay/'], true));
                $result['form'] = $form;
                break;
            case FinanceLog::PAY_METHOD_ALLINPAY: // 通联支付
                if (System::getConfig('allinpay_open') != 1) {
                    return ['message' => '系统没有开通通联支付。'];
                }
                $finance_log->pay_method = FinanceLog::PAY_METHOD_ALLINPAY;
                if (empty($finance_log->trade_no)) {
                    $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                }
                $finance_log->save();
                $allinpay_api = new AllInPayApi();
                list($url, $data) = $allinpay_api->submit($finance_log->trade_no, $finance_log->money, $finance_log->create_time, System::getConfig('site_name') . '订单');
                $form = '<form id="allinpay_form" method="post" action="' . $url . '">';
                foreach ($data as $k => $v) {
                    $form .= '<input type="hidden" name="' . $k . '" value ="' . $v . '" />';
                }
                $form .= '</form>';
                $form .= '<script>document.getElementById("allinpay_form").submit();</script>';
                $result['form'] = $form;
                break;
            default:
                return ['message' => '无法确定支付方式。'];
        }
        $result['result'] = 'success';
        $result['fid'] = $finance_log->id;
        return $result;
    }

    /**
     * 检测支付状态AJAX接口
     * @return array
     */
    public function actionCheckPayResult()
    {
        $fid = $this->get('fid');
        /** @var Shop $shop */
        $shop = Shop::find()->where(['earnest_money_fid' => $fid])->one();
        if ($shop->mid != $this->merchant->id) {
            return ['message' => '不存在缴费记录'];
        }
        $finance_log = FinanceLog::findOne($fid);
        if (empty($finance_log)) {
            return ['message' => '没有找到支付记录。'];
        }
        return [
            'result' => 'success',
            'pay_status' => $finance_log->status,
            'pay_money' => $finance_log->money,
        ];
    }

    /**
     * 设置店铺使用主题AJAX接口
     * @return array
     */
    public function actionSetTemplate()
    {
        $code = $this->get('code');
        $id = $this->get('id');
        if (empty($id)) {
            return ['message' => '参数错误。'];
        }
        /** @var ShopTheme $theme */
        $theme = ShopTheme::find()->where(['code' => $code, 'id' => $id])->one();
        if (empty($theme)) {
            return ['message' => '没有找到该主题。'];
        }
        $this->shop->tid = $theme->id;
        $this->shop->save();
        return ['result' => 'success'];
    }

    /**
     * 店铺装修
     * @return string|array
     */
    public function actionDecoration()
    {
        $decoration = ShopDecoration::findBySid($this->shop->id);
        if ($this->isPost()) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $decoration->header_background_image = $this->post('header_background_image');
                $r = $decoration->save();
                if (!$r) {
                    $errors = $decoration->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception($error);
                }
                $delete_ids = $this->post('delete_ids');
                if (!empty($delete_ids)) {
                    $delete_ids = preg_split('/\D/', $delete_ids, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($delete_ids as $id) {
                        $item = ShopDecorationItem::findOne($id);
                        if (empty($item) || $item->shop->mid != $this->merchant->id) {
                            throw new Exception('没有找到要删除的编号为[' . $id . ']的店铺装修块。');
                        }
                        try {
                            $item->delete();
                        } catch (\Throwable $t) {
                        }
                    }
                }
                $post_item = $this->post('ShopDecorationItem');
                if (!empty($post_item)) {
                    if (!isset($post_item['id'], $post_item['type'], $post_item['sort'], $post_item['data'])) {
                        throw new Exception('参数错误。');
                    }
                    $id_list = $post_item['id'];
                    $type_list = $post_item['type'];
                    $sort_list = $post_item['sort'];
                    $data_list = $post_item['data'];
                    foreach ($id_list as $idx => $id) {
                        if (!empty($id)) {
                            $item = ShopDecorationItem::findOne($id);
                            if (empty($item) || $item->shop->mid != $this->merchant->id) {
                                throw new Exception('没有找到编号为[' . $id . ']的店铺装修块。');
                            }
                        } else {
                            $item = new ShopDecorationItem();
                            $item->sid = $this->shop->id;
                        }
                        $item->type = $type_list[$idx];
                        $item->sort = $sort_list[$idx];
                        $item->data = $data_list[$idx];
                        $r = $item->save();
                        if (!$r) {
                            $errors = $item->errors;
                            $error = array_shift($errors)[0];
                            throw new Exception($error);
                        }
                    }
                }
                $trans->commit();
                if ($this->isAjax()) {
                    return ['result' => 'success'];
                } else {
                    Yii::$app->session->addFlash('success', '数据已保存。');
                }
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                if ($this->isAjax()) {
                    return ['message' => $e->getMessage()];
                } else {
                    Yii::$app->session->addFlash('error', '错误：' . $e->getMessage());
                }
            }
        }
        return $this->render('decoration', [
            'shop' => $this->shop,
            'decoration' => $decoration,
        ]);
    }

    /**
     * 店铺品牌列表
     * @return string
     */
    public function actionBrand()
    {
        $query = ShopBrand::find();
        $query->where(['sid' => $this->shop->id]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->all();
        return $this->render('brand', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 修改店铺品牌
     * @return string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionEditBrand()
    {
        $id = $this->get('id'); // ShopBrand.id
        $status = $this->get('status');
        if (!empty($id)) {
            $shop_brand = ShopBrand::findOne($id);
            if (empty($shop_brand) || $shop_brand->shop->mid != $this->merchant->id) {
                throw new NotFoundHttpException('没有找到品牌信息。');
            }
            if ($shop_brand->status == ShopBrand::STATUS_VALID) {
                throw new NotFoundHttpException('品牌暂时无法修改。');
            }
            $goods_brand = GoodsBrand::findOne($shop_brand->bid);
        } else {
            $shop_brand = new ShopBrand();
            $shop_brand->sid = $this->shop->id;
            $goods_brand = null;
            $search_name = $this->get('search_name');
            if (!empty($search_name)) {
                $goods_brand = GoodsBrand::find()->andWhere(['name' => $search_name])->one();
            }
            if (empty($goods_brand)) {
                $goods_brand = new GoodsBrand();
            }
            $goods_brand->name = $search_name;
        }
        if ($shop_brand->load($this->post())) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $r = $goods_brand->load($this->post());
                if (!$r) {
                    throw new Exception('无法加载商品品牌信息。');
                }
                /** @var GoodsBrand $exist_goods_brand */
                $exist_goods_brand = GoodsBrand::find()->andWhere(['name' => $goods_brand->name])->one();
                if (!empty($exist_goods_brand)) {
                    $goods_brand = $exist_goods_brand;
                } else {
                    $r = $goods_brand->load($this->post());
                    if (!$r) {
                        throw new Exception('无法加载商品品牌提交数据。');
                    }
                    $goods_brand->create_time = time();
                    $r = $goods_brand->save(false);
                    if (!$r) {
                        $errors = $goods_brand->errors;
                        $error = array_shift($errors)[0];
                        throw new Exception($error);
                    }
                    $brand_tid = $this->post('brand_tid');
                    if (empty($brand_tid)) {
                        throw new Exception('没有选择任何品牌品类。');
                    }
                    $brand_tid = json_decode($brand_tid, true);
                    if (empty($brand_tid)) {
                        throw new Exception('品牌品类数据错误。');
                    }
                    GoodsTypeBrand::deleteAll(['bid' => $goods_brand->id]);
                    foreach ($brand_tid as $tid) {
                        $goods_type_brand = new GoodsTypeBrand();
                        $goods_type_brand->bid = $goods_brand->id;
                        $goods_type_brand->tid = $tid;
                        if (!$goods_type_brand->save()) {
                            throw new Exception('无法保存品牌品类。');
                        }
                    }
                }
                $shop_brand->bid = $goods_brand->id;
                $shop_brand->status = ShopBrand::STATUS_WAIT;
                $r = $shop_brand->save();
                if (!$r) {
                    $errors = $shop_brand->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception($error);
                }
                $url = '';
                if (!empty($status)) {
                    //入驻时 添加的 品牌  设置 入驻状态
                    $merchant = Merchant::findOne($this->merchant->id);
                    $merchant->status = Merchant::STATUS_WAIT_DATA2;
                    $url = ['/merchant'];
                    $merchant->save();
                }
                $trans->commit();
                Yii::$app->session->addFlash('success', '添加成功，请等待审核。');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(empty($url) ? ['/merchant/shop/brand'] : $url),
                    'txt' => '品牌列表'
                ]));
            } catch (Exception $e) {
                $trans->rollBack();
                Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }
        return $this->render('brand_edit', [
            'goods_brand' => $goods_brand,
            'shop_brand' => $shop_brand,
        ]);
    }

    /**
     * 查看品牌详情
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewBrand()
    {
        $id = $this->get('id');
        $shop_brand = ShopBrand::findOne($id);
        if (empty($shop_brand) || $shop_brand->shop->mid != $this->merchant->id) {
            throw new NotFoundHttpException('没有找到品牌信息。');
        }
        $goods_brand = GoodsBrand::findOne($shop_brand->bid);
        return $this->render('brand_view', [
            'shop_brand' => $shop_brand,
            'goods_brand' => $goods_brand,
        ]);
    }

    /**
     * 删除品牌AJAX 接口
     * @return array
     */
    public function actionDeleteBrand()
    {
        $id = $this->get('id');
        $shop_brand = ShopBrand::findOne($id);
        if (empty($shop_brand) || $shop_brand->shop->mid != $this->merchant->id) {
            return ['message' => '没有找到品牌信息。'];
        }
        try {
            $shop_brand->delete();
        } catch (\Throwable $t) {
        }
        return ['result' => 'success'];
    }

    /**
     * 店铺经营类目
     */
    public function actionCategory()
    {
        $map = [];
        $shop_goods_cid = ShopConfig::getConfig($this->shop->id, 'cid_list');
        $shop_goods_cid = json_decode($shop_goods_cid, true);
        foreach (GoodsCategory::find()->andWhere(['pid' => null])->andFilterWhere(['in', 'id', $shop_goods_cid])->all() as $level1) {
            /** @var GoodsCategory $level1 */
            $level2_list = [];
            foreach (GoodsCategory::find()->andWhere(['pid' => $level1->id])->andFilterWhere(['in', 'id', $shop_goods_cid])->all() as $level2) {
                /** @var GoodsCategory $level2 */
                $level3_list = [];
                foreach (GoodsCategory::find()->andWHere(['pid' => $level2->id])->all() as $level3) {
                    /** @var GoodsCategory $level3 */
                    $level3_list[$level3->id] = ['name' => $level3->name];
                }
                $level2_list[$level2->id] = ['name' => $level2->name, 'children' => $level3_list];
            }
            $map[$level1->id] = ['name' => $level1->name, 'children' => $level2_list];
        }
        return $this->render('category', [
            'category_map' => $map,
        ]);
    }

    /**
     * 店铺文件列表
     * @return string
     */
    public function actionFile()
    {
        $query = ShopFile::find();
        $query->where(['sid' => $this->shop->id]);
        $query->andWhere(['status' => ShopFile::STATUS_OK]);
        $model_list = $query->all();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        return $this->render('shop_file', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 店铺文件列表
     * @return string
     */
    public function actionFileCategory()
    {
        $query = ShopFileCategory::find();
        $query->where(['sid' => $this->shop->id]);
        $model_list = $query->all();
        $pagination = new Pagination(['totalCount' => $query->count()]);
        return $this->render('shop_file_category', [
            'model_list' => $model_list,
            'pagination' => $pagination,
        ]);
    }

    /**
     * 添加文件
     * @return string
     */
    public function actionAddFile()
    {
        $model = new ShopFile();
        $model->sid = $this->shop->id;
        $model->create_time = time();
        $file_type = ShopFileCategory::find()->where(['sid' => $this->merchant->id])->all();
        $file_category = [];
        foreach ($file_type as $type) { /** @var ShopFileCategory $type**/
            $file_category[$type->id] = $type->name;
        }
        if ($model->load($this->post())) {
            /** @var ShopFile $file **/
            $file = ShopFile::find()->where(['url' => $model->url])->one();
            $file->cid = $model->cid;
            $file->status = $model->status;
            if ($file->save()) {
                Yii::$app->session->addFlash('success', '添加成功');
                Yii::$app->session->setFlash('redirect', json_encode([
                    'url' => Url::to(['/merchant/shop/file']),
                    'txt' => '文件管理'
                ]));
            }
        }
        return $this->render('edit_shop_file',[
            'model' => $model,
            'file_type' => $file_category,
        ]);
    }

    /**
     * 添加/编辑 文件分类
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEditFileCategory()
    {
        $model = new ShopFileCategory();
        $model->sid = $this->shop->id;
        $id = $this->get('id');
        if (!empty($id)) {
            $model = ShopFileCategory::findOne($id);
        }
        if (empty($model) || $model->sid !== $this->shop->id) {
            throw new NotFoundHttpException('没有找到文件分类信息。');
        }
        if ($model->load($this->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', '添加成功');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/merchant/shop/file-category']),
                'txt' => '文件分类管理'
            ]));
        }
        return $this->render('edit_file_category', [
            'model' => $model
        ]);
    }

    /**
     * AJAX 删除文件分类接口
     * @return array
     */
    public function actionDeleteFileCategory()
    {
        $id = $this->get('id');
        $model = ShopFileCategory::findOne($id);
        if (empty($model) || $model->sid !== $this->shop->id) {
            return ['message' => '没有找到文件分类'];
        }
        ShopFile::updateAll(['cid' => NULL], ['sid' => $this->shop->id, 'cid' => $id]);
        try {
            $model->delete();
        } catch (\Throwable $e) {
        }
        return ['result' => 'success'];
    }

    /**
     * AJAX 删除文件接口
     * @return array
     */
    public function actionDeleteFile()
    {
        $id = $this->get('id');
        $model = ShopFile::findOne($id);
        if (empty($model) || $model->sid !== $this->shop->id) {
            return ['message' => '没有找到文件'];
        }
        $model->status = ShopFile::STATUS_DEL;
        if ($model->save()) {
            return ['result' => 'success'];
        } else {
            return ['message' => '无法删除当前文件'];
        }
    }
}
