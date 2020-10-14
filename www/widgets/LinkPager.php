<?php

namespace app\widgets;

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * 分页代码
 * Class LinkPager
 * @package app\widgets
 */
class LinkPager extends \yii\widgets\LinkPager
{
    /**
     * @var array HTML attributes for the go num input tag.
     */
    public $inputOptions = ['style' => 'max-width:50px; text-align:center; float:left;'];
    /**
     * @var array HTML attributes for the go button tag.
     */
    public $goOptions = ['class' => 'btn btn-default btn-xs', 'style' => 'float:left;'];
    /**
     * @var boolean Show statistics data.
     */
    public $showStatistics = true;
    /**
     * @var array HTML attributes for the statistics label tag.
     */
    public $statisticsLabelOptions = ['style' => 'float:left; line-height:30px;'];
    /**
     * @var boolean Show goto pagenum button.
     */
    public $showGoBtn = true;
    /**
     * @var string|boolean the text label for the "first" page button. Note that this will NOT be HTML-encoded.
     * If it's specified as true, page number will be used as label.
     * Default is false that means the "first" page button will not be displayed.
     */
    public $firstPageLabel = '首页';
    /**
     * @var string|boolean the text label for the "last" page button. Note that this will NOT be HTML-encoded.
     * If it's specified as true, page number will be used as label.
     * Default is false that means the "last" page button will not be displayed.
     */
    public $lastPageLabel = '尾页';

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run()
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }

        echo $this->renderPageButtons();
    }

    /**
     * Renders the page buttons.
     *
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list ($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // Go button
        if ($this->showGoBtn) {
            $inputOptions = array_merge($this->inputOptions, [
                'class' => 'go_txt'
            ]);
            $buttons[] = Html::tag('li', Html::textInput('page_num', $this->pagination->page + 1, $inputOptions), ['class' => 'go_txt']);
            $goOptions = array_merge($this->goOptions, [
                'onclick' => 'window.location=\'' . Url::current([
                        'page' => 'A761208'
                    ]) . '\'.replace(\'A761208\', $(\'input[name=page_num]\').val())'
            ]);
            $buttons[] = Html::tag('li', Html::button('Go', $goOptions), ['class' => 'go_btn']);
        }

        if ($this->showStatistics) {
            $buttons[] = Html::tag('li', Html::label('总数：' . $this->pagination->totalCount . '；总页数：' . $this->pagination->pageCount . '；', '', $this->statisticsLabelOptions), ['class' => 'statistics']);
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}
