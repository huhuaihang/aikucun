<?php
/**
 * @var $this yii\web\View
 * @var $items array
 */

use yii\helpers\Html;

?>
<div class="hidden-sm hidden-xs btn-group">
    <?php foreach ($items as $item) {?>
        <?php if (!is_array($item)) {
            continue;
        }?>
        <?php if (!empty($item['onclick'])) {?>
            <button class="<?php echo $item['btn_class'];?>" onclick="<?php echo $item['onclick'];?>" title="<?php echo $item['tip'];?>">
                <?php if (isset($item['icon'])) {?><i class="ace-icon <?php echo $item['icon'];?> bigger-120"></i><?php }?>
                <?php if (isset($item['text'])) {echo Html::encode($item['text']);}?>
            </button>
        <?php } elseif (!empty($item['href'])) {?>
            <a class="<?php echo $item['btn_class'];?>" href="<?php echo $item['href'];?>" title="<?php echo $item['tip'];?>"   <?php if (isset($item['target'])) {?> target="<?php echo $item['target'];?>" <?php }?> >
                <?php if (isset($item['icon'])) {?><i class="ace-icon <?php echo $item['icon'];?> bigger-120"></i><?php }?>
                <?php if (isset($item['text'])) {echo Html::encode($item['text']);}?>
            </a>
        <?php }?>
    <?php }?>
</div>
<div class="hidden-md hidden-lg">
    <div class="inline pos-rel">
        <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown" data-position="auto">
            <i class="ace-icon fa fa-cog icon-only bigger-110"></i>
        </button>

        <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">
            <?php foreach ($items as $item) {?>
                <?php if (!is_array($item)) {
                    continue;
                }?>
                <li>
                    <a href="<?php echo isset($item['href']) ? $item['href'] : 'javascript:void(0)';?>" onclick="<?php echo isset($item['onclick']) ? $item['onclick'] : 'javascript:void(0)';?>" class="tooltip-info" data-rel="tooltip" title="<?php echo $item['tip'];?>">
                        <span class="<?php echo isset($item['color']) ? $item['color'] : '';?>">
                            <?php if (isset($item['icon'])) {?><i class="ace-icon <?php echo $item['icon'];?> bigger-120"></i><?php }?>
                            <?php if (isset($item['text'])) {echo Html::encode($item['text']);}?>
                        </span>
                    </a>
                </li>
            <?php }?>
        </ul>
    </div>
</div>
