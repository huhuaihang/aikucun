<?php

namespace app\widgets;

use app\models\Ad;
use app\models\AdLocation;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * 广告内容展示组件
 * Class AdWidget
 * @package app\widgets
 *
 * @property integer $lid 广告位代码，必需
 *
 * 使用方法：
 * <?php AdWidget::begin(['lid'=>广告位编号]);?>
 * Smarty模板代码
 * {foreach $ad_list as $ad}
 *   <a href="/site/da?id={$ad['id']}">{$ad['id']}:{$ad['txt']}</a>
 * {/foreach}
 * <?php AdWidget::end();?>
 *
 * 广告模板内容将按以下顺序查找并渲染：
 * 1.@runtime/ad/{$lid}.tpl Smarty模板文件
 * 2.广告位数据库中保存的模板内容ad_location.code
 * 3.小部件渲染时页面中嵌入的Smarty模板内容
 * 4.使用Html::a(Html::encode($ad->txt), [$ad->url]);循环输出所有广告链接
 * 如果上面1中的模板文件不存在，将根据获取到的模板内容创建文件
 */
class AdWidget extends Widget
{
    /**
     * @var int 广告位置编号
     */
    public $lid = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        ob_start();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $tpl_content = ob_get_clean();
        if (empty($this->lid)) {
            return null;
        }
        $loc = AdLocation::findOne($this->lid);
        if (empty($loc)) {
            return '<h1 style="color:#F00;">此处代码中设置的广告位编号为[' . $this->lid . ']，需要到管理后台添加相应的广告位定义。</h1>';
        }
        $ad_list = $loc->getActiveAdList()->all();
        Ad::updateAllCounters(['show' => 1], ['id' => ArrayHelper::getColumn($ad_list, 'id')]);
        // 查找广告模板
        $path = Yii::getAlias('@runtime/ad');
        $file = $path . '/' . $this->lid . '.tpl';
        if (file_exists($file)) {
            // 使用模板文件渲染
            return $this->renderFile($file, [
                'ad_list' => $ad_list
            ]);
        } else {
            if (!empty($loc->code)) {
                $tpl_content = $loc->code;
            }
            if (!empty($tpl_content)) {
                // 保存备用模板内容
                if (!file_exists($path)) {
                    FileHelper::createDirectory($path);
                }
                if (file_put_contents($file, $tpl_content)) {
                    return $this->renderFile($file, [
                        'ad_list' => $ad_list
                    ]);
                }
            }
        }
        return $this->render('ad_widget', [
            'lid' => $this->lid,
            'ad_list' => $ad_list
        ]);
    }
}
