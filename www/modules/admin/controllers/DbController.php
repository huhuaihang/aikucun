<?php

namespace app\modules\admin\controllers;

use app\models\ManagerLog;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 数据库管理
 * Class DbController
 * @package app\modules\admin\controllers
 */
class DbController extends BaseController
{
    /**
     * 执行SQL查询
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionSql()
    {
        if (!$this->manager->can('db/sql')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $sql = $this->post('sql');
        $affected = 0;
        $table = [];
        if (!empty($sql)) {
            try {
                if (preg_match('/^SELECT/i', $sql)) {
                    $table = Yii::$app->db->createCommand($sql)->queryAll();
                } else {
                    $affected = Yii::$app->db->createCommand($sql)->execute();
                }
                ManagerLog::info($this->manager->id, '执行SQL', $sql);
            } catch (Exception $error) {
                Yii::$app->session->addFlash('error', implode(' ', $error->errorInfo));
            }
        }
        return $this->render('sql', [
            'sql' => $sql,
            'affected' => $affected,
            'table' => $table
        ]);
    }

    /**
     * 数据库备份
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @return string
     */
    public function actionBackup()
    {
        if (!$this->manager->can('db/backup')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $table_list = Yii::$app->db->schema->getTableNames();
        if ($this->isPost()) {
            switch ($this->post('backup_type')) {
                case 'all':
                    $backup_table_list = $table_list;
                    break;
                case 'standard':
                    $backup_table_list = [
                        'agent', // 代理商
                        'deliver_template', // 运费模板
                        'goods', // 商品
                        'goods_attr', // 商品属性
                        'goods_attr_value', // 商品属性值
                        'goods_brand', // 商品品牌
                        'goods_category', // 商品分类
                        'goods_comment', // 商品评论
                        'goods_config', // 商品设置
                        'goods_sku', // 商品SKU
                        'goods_type', // 商品类型
                        'goods_type_brand', // 商品类型品牌
                        'goods_violation', // 商品违规
                        'manager', // 管理员
                        'manager_role', // 管理员角色
                        'merchant', // 商户
                        'order', // 订单
                        'order_item', // 订单详情
                        'shop', // 店铺
                        'shop_brand', // 店铺品牌
                        'shop_config', // 店铺设置
                        'shop_express', // 店铺快递
                        'system', // 系统设置
                        'user', // 用户
                        'user_account', // 用户账户
                        'user_account_log', // 用户账户记录
                        'user_address', // 用户收货地址
                        'user_cart', // 用户购物车
                        'user_commission', // 用户佣金
                        'user_faq', // 用户常见问题结果
                        'user_fav_goods', // 用户收藏商品
                        'user_fav_shop', // 用户收藏店铺
                        'user_recharge', // 用户充值记录
                    ];
                    break;
                case 'min':
                    $backup_table_list = [
                        'agent', // 代理商
                        'deliver_template', // 运费模板
                        'goods', // 商品
                        'goods_attr_value', // 商品属性值
                        'goods_config', // 商品设置
                        'goods_sku', // 商品SKU
                        'manager', // 管理员
                        'merchant', // 商户
                        'order', // 订单
                        'order_item', // 订单详情
                        'shop', // 店铺
                        'system', // 系统设置
                        'user', // 用户
                        'user_account', // 用户账户
                        'user_commission', // 用户佣金
                    ];
                    break;
                case 'custom':
                    $backup_table_list = array_intersect($table_list, $this->post('backup_table'));
                    break;
                default:
                    throw new BadRequestHttpException('参数错误。');
            }
            if (!file_exists(Yii::$app->params['upload_path'] . '/database/')) {
                mkdir(Yii::$app->params['upload_path'] . '/database', 0777, true);
            }
            $backup_file = 'database/' . date('YmdHis') . '.sql';
            switch (Yii::$app->db->driverName) {
                case 'sqlite':
                    $db_file = substr(Yii::$app->db->dsn, 7);
                    foreach ($backup_table_list as $table) {
                        system('sqlite3 ' . $db_file . ' ".dump ' . $table . '" >> ' . Yii::$app->params['upload_path'] . $backup_file);
                    }
                    break;
                case 'mysql':
                    $db_name = preg_replace('/.*dbname=(.*)/', '$1', Yii::$app->db->dsn);
                    foreach ($backup_table_list as $table) {
                        system('mysqldump -u' . Yii::$app->db->username . ' -p' . Yii::$app->db->password . ' ' . $db_name . ' ' . $table . ' >> ' . Yii::$app->params['upload_path'] . $backup_file);
                    }
                    break;
                default:
                    throw new ServerErrorHttpException('无法识别的数据库格式。');
            }
            ManagerLog::info($this->manager->id, '备份数据库', $backup_file);
            Yii::$app->session->addFlash('success', '备份成功。<br /><ul><li><a href="' . Yii::$app->params['upload_url'] . $backup_file . '">' . $backup_file . '</a></li></ul>');
        }
        return $this->render('backup', [
            'table_list' => $table_list
        ]);
    }
}
