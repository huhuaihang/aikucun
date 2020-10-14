<?php

namespace app\models;

use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 系统版本号
 * Class SystemVersion
 * @package app\models
 *
 * @property integer $id PK
 * @property string $api_version 接口版本号
 * @property string $ios_version IOS版本号
 * @property string $android_version Android版本号
 * @property string $aes_key AES加密key，base64编码
 * @property string $aes_iv AES加密iv，base64编码
 * @property integer $android_download_source 安卓下载来源
 * @property string $android_download_url 安卓下载地址
 * @property string $update_info 更新信息
 * @property integer $is_support 是否支持
 * @property integer $create_time 创建时间
 */
class SystemVersion extends ActiveRecord
{
    const ANDROID_DOWNLOAD_SOURCE_PGY = 1;
    const ANDROID_DOWNLOAD_SOURCE_YYB = 2;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['api_version', 'ios_version', 'android_version', 'aes_key', 'aes_iv', 'android_download_source'], 'required'],
            [['api_version', 'ios_version', 'android_version'], 'string', 'max' => 16],
            ['is_support', 'integer'],
            [['aes_key', 'aes_iv'], 'string', 'max' => 128],
            [['android_download_url'], 'string', 'max' => 256],
            ['update_info', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'api_version' => '接口版本号',
            'ios_version' => '苹果版本号',
            'android_version' => '安卓版本号',
            'aes_key' => 'AES加密密钥',
            'aes_iv' => 'AES加密初始化向量',
            'android_download_source' => '安卓下载来源',
            'android_download_url' => '安卓下载地址',
            'update_info' => '更新信息',
            'is_support' => '是否支持',
        ];
    }

    /**
     * 接口内容加密
     * @param $api_version string 接口版本号
     * @param $data string 明文
     * @return string
     * @throws Exception
     */
    public static function aesEncode($api_version, $data)
    {
        /** @var SystemVersion $version */
        $version = SystemVersion::find()->andWhere(['api_version' => $api_version])->one();
        if (empty($version)) {
            throw new Exception('没有找到版本信息：' . $api_version);
        }
        $key = $version->aes_key;
        $iv = $version->aes_iv;

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $encrypted;
    }

    /**
     * 接口密文解密
     * @param $api_version string 接口版本号
     * @param $encrypted string 密文
     * @return string 明文
     * @throws Exception
     */
    public static function aesDecode($api_version, $encrypted)
    {
        /** @var SystemVersion $version */
        $version = SystemVersion::find()->andWhere(['api_version' => $api_version])->one();
        if (empty($version)) {
            throw new Exception('没有找到版本信息：' . $api_version);
        }
        $key = $version->aes_key;
        $iv = $version->aes_iv;
        $encrypted = base64_decode($encrypted);
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $decrypted;
    }
}
