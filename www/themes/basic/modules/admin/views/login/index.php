<?php

use cornernote\ace\web\AceAsset;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $model app\models\ManagerLoginForm
 */

AceAsset::register($this);

$this->title = '管理员登录';
?>
<?php $this->beginPage();?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="<?= Yii::$app->charset ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <meta name="description" content="管理员登录" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
</head>
<?php $this->beginBody() ?>
<body class="login-layout light-login">
<div class="main-container">
    <div class="main-content">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="login-container">
                    <div class="center">
                        <h1>
                            <i class="ace-icon fa fa-leaf green"></i>
                            <span class="red">管理后台</span>
                            <span class="white" id="id-text2"><?php echo Yii::$app->params['appName'];?></span>
                        </h1>
                        <h4 class="blue" id="id-company-text">&copy; <?php echo Yii::$app->params['companyName'];?></h4>
                    </div>

                    <div class="space-6"></div>

                    <div class="position-relative">
                        <div id="login-box" class="login-box visible widget-box no-border">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <h4 class="header red lighter bigger">
                                        <i class="ace-icon fa fa-coffee green"></i>
                                        <?php if (!empty($model->errors)) {
                                            $errors = $model->errors;
                                            $error = array_pop($errors);
                                            echo $error[0];
                                        } else {
                                            echo '请输入您的登录信息';
                                        }?>
                                    </h4>

                                    <div class="space-6"></div>

                                    <?php echo Html::beginForm(['/admin/login'], 'post', ['id'=>'login-form']);?>
                                        <fieldset>
                                            <label class="block clearfix">
                                                <span class="block input-icon input-icon-right">
                                                    <?php echo Html::activeTextInput($model, 'username', ['class' => 'form-control', 'placeholder' => '登录账号']);?>
                                                    <i class="ace-icon fa fa-user"></i>
                                                </span>
                                            </label>

                                            <label class="block clearfix">
                                                <span class="block input-icon input-icon-right">
                                                    <?php echo Html::activePasswordInput($model, 'password', ['class' => 'form-control', 'placeholder' => '登录密码']);?>
                                                    <i class="ace-icon fa fa-lock"></i>
                                                </span>
                                            </label>

                                            <div class="space"></div>

                                            <div class="clearfix">
                                                <label class="inline">
                                                    <?php echo Html::checkbox(Html::getInputName($model, 'rememberMe'), $model->rememberMe == 1, ['class' => 'ace']);?>
                                                    <span class="lbl"> 记住登录状态</span>
                                                </label>

                                                <button class="width-35 pull-right btn btn-sm btn-primary">
                                                    <i class="ace-icon fa fa-key"></i>
                                                    <span class="bigger-110">登录</span>
                                                </button>
                                            </div>

                                            <div class="space-4"></div>
                                        </fieldset>
                                    <?php echo Html::endForm();?>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                        </div><!-- /.login-box -->
                    </div><!-- /.position-relative -->
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
</body>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>
