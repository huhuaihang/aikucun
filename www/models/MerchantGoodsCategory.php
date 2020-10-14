<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户经营类目
 * Class MerchantGoodsCategory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property string $cid_list 商品分类列表JSON
 * @property string $quality_inspection_report 质检报告文件列表JSON
 * @property string $authorization_certificate 销售授权书/进货发票文件列表JSON
 * @property string $industry_qualification 行业资质文件列表JSON
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 */
class MerchantGoodsCategory extends ActiveRecord
{
    const STATUS_WAIT = 1;
    const STATUS_OK = 2;
    const STATUS_REJECT = 9;
    const STATUS_DEL = 0;
}
