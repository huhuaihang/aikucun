<?php

use app\models\City;
use app\models\KeyMap;
use app\models\UserAddress;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $user \app\models\User
 */

$this->title = '用户详情';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $user->id;?></td>
    </tr>
    <tr>
        <th>手机号码</th>
        <td><?php echo $user->mobile;?></td>
    </tr>
    <tr>
        <th>昵称</th>
        <td><?php echo Html::encode($user->nickname);?></td>
    </tr>
    <tr>
        <th>头像</th>
        <td><?php echo Html::img($user->getRealAvatar(), ['width' => 200, 'height' => 200]);?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('user_status', $user->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($user->create_time);?></td>
    </tr>
    <tr>
        <th colspan="2">账户</th>
    </tr>
    <tr>
        <th>消费积分</th>
        <td><?php echo $user->account->score;?></td>
    </tr>
    <tr>
        <th colspan="2">收货地址</th>
    </tr>
    <tr>
        <td colspan="2">
            <table class="table">
                <thead>
                <tr>
                    <th>地区</th>
                    <th>详细地址</th>
                    <th>收货人</th>
                    <th>电话</th>
                    <th>默认</th>
                    <th>状态</th>
                    <th>创建时间</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (UserAddress::find()->andWhere(['uid' => $user->id])->each() as $address) {/** @var UserAddress $address */?>
                    <tr>
                        <td><?php $city = City::findByCode($address->area);echo implode(' ', $city->address());?></td>
                        <td><?php echo Html::encode($address->address);?></td>
                        <td><?php echo Html::encode($address->name);?></td>
                        <td><?php echo Html::encode($address->mobile);?></td>
                        <td><?php echo $address->is_default == 1 ? '是' : '否';?></td>
                        <td><?php echo KeyMap::getValue('user_address_status', $address->status);?></td>
                        <td><?php echo Yii::$app->formatter->asDatetime($address->create_time);?></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <th colspan="2">账户信息</th>
    </tr>
    <tr>
        <th>账户记录</th>
        <td><a href="<?php echo Url::to(['/admin/user/account-list', 'uid' => $user->id]);?>">记录列表</a></td>
    </tr>
    <tr>
        <th>补贴记录</th>
        <td><a href="<?php echo Url::to(['/admin/user/account-list', 'uid' => $user->id]);?>">记录列表</a></td>
    </tr>
</table>
