<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;

/**
 * 权限初始化
 * Class RbacController
 * @package app\commands
 */
class TestController extends Controller
{
    public function actionIndex()
    {
        $source = Yii::$app->redis->set('var1','asdasd');
        $source1 = Yii::$app->redis->get('var1');
        $list = Yii::$app->redis->rpush('list', json_encode(['gid' => 3, 'uid' => 4, 'sku_key_name' => '紫色']));
        $data_list = Yii::$app->redis->lrange('list', 1, 1);
        var_dump($source,$source1);
        var_dump($data_list, json_decode($data_list[0]));
        exit;
        $query = User::find();
        $query->andWhere(['BETWEEN', 'id', '3000', '3010']);
        echo $query->createCommand()->getRawSql();
    }
}