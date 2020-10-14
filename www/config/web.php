<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'aikucun',
    'name' => '爱库存',
    'version' => '1.0.0',
    'basePath' => dirname(__DIR__),
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'aikucun',
        ],
        'db' => $db,
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'keyPrefix' => 'aikucun_',
            'useMemcached' => true,
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/h5/login'],
        ],
        'manager' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Manager',
            'enableAutoLogin' => true,
            'loginUrl' => ['/admin/login'],
            'identityCookie' => ['name' => '_manager_identity', 'httpOnly' => true],
            'idParam' => '_mid',
            'returnUrlParam' => '__manager_returnUrl',
        ],
        'agent' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Agent',
            'enableAutoLogin' => true,
            'loginUrl' => ['/agent/login'],
            'identityCookie' => ['name' => '_agent_identity', 'httpOnly' => true],
            'idParam' => '_agent_id',
            'returnUrlParam' => '__agent_returnUrl',
        ],
        'merchant' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Merchant',
            'enableAutoLogin' => true,
            'loginUrl' => ['/merchant/login'],
            'identityCookie' => ['name' => '_merchant_identity', 'httpOnly' => true],
            'idParam' => '_merchant_id',
            'returnUrlParam' => '__merchant_returnUrl',
        ],
        'supplier' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Supplier',
            'enableAutoLogin' => true,
            'loginUrl' => ['/supplier/login'],
            'identityCookie' => ['name' => '_supplier_identity', 'httpOnly' => true],
            'idParam' => '_supplier_id',
            'returnUrlParam' => '__supplier_returnUrl',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'authManager' => [
            'class' => 'app\models\RbacManager',
            'itemTable' => '{{%auth_item}}',
            'assignmentTable' => '{{%auth_assignment}}',
            'itemChildTable' => '{{%auth_item_child}}',
        ],
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                    '@app/modules' => '@app/themes/basic/modules',
                    '@app/widgets' => '@app/themes/basic/widgets',
                ],
            ],
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                ],
            ],
        ],
        'formatter' => [
            'nullDisplay' => '',
            'dateFormat' => 'php:Y.m.d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
        ],
        'assetManager' => [
            'linkAssets' => true,
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [
                        'jquery.min.js',
                    ]
                ],
            ],
        ],
        'image' => [
            'class' => 'yii\image\ImageDriver',
            'driver' => 'GD', // GD or Imagick
        ],
        'qr' => [
            'class' => '\Da\QrCode\Component\QrCodeComponent',
        ],
        'redis' =>[
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost', //你的redis地址
            'port' => 6379, //端口
            'database' => 0,
        ],
    ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'agent' => [
            'class' => 'app\modules\agent\Module',
        ],
        'merchant' => [
            'class' => 'app\modules\merchant\Module',
        ],
        'h5' => [
            'class' => 'app\modules\h5\Module',
        ],
        'supplier' => [
            'class' => 'app\modules\supplier\Module',
        ],
    ],
    'params' => $params,
];

return $config;
