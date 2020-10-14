<?php

use app\assets\EchartsAsset;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $user \app\models\User
 * @var $parent_list []
 * @var $child_list []
 * @var $count integer
 */

EchartsAsset::register($this);

$this->title = '用户关系详情';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table>
    <thead></thead>
    <tr>上级链
        <?php
        echo "总数:" . count($parent_list) . "<br>";
//        echo Html::a('返回上级', Url::to(['/admin/user/relation-view', 'id' => $user->pid])) . "<br>";
        foreach ($parent_list as $key => $parent) {
            echo Html::a('===>>>>' . $parent['real_name'] . ' ' . $parent['mobile'], Url::to(['/admin/user/relation-view', 'id' => $parent['id']]), ['style' => 'margin-right:5px']);
            if ($key % 20 == 0 && $key != 0) echo "<br>";
        }
        echo chr(10) . chr(13). "<br>";
        ?>
    </tr>
    <tr>
        <?php
        echo "总数:" . $count . "<br>";
        echo Html::a('返回上级', Url::to(['/admin/user/relation-view', 'id' => $user->pid])) . "<br>";
        foreach ($child_list as $key => $child) {
            echo Html::a($child['real_name'] . $child['mobile'], Url::to(['/admin/user/relation-view', 'id' => $child['id']]), ['style' => 'margin-right:5px']);
            if ($key % 20 == 0 && $key != 0) echo "<br>";
        }
        ?>
    </tr>
</table>
<div class="row">
    <div class="col-md-12" id="child" style="width: 100%;height:600px;">

    </div>
</div>
<script type="text/javascript">
    function page_init() {
        child();
    }
    function child() {
        <?php
        $query = User::find()->where(['id' => $user->id]);
        $list = $list2 = [];
        /** @var User $user */
        foreach ($query->each() as $user) {
            $child_list =  [];
            $child = new stdClass();
            /** @var User $child_user */
            foreach ($user->getChildList() as $child_user) {
                $child_list[] = $child_user->id . $child_user->real_name;
                $child_list2 =  [];
                $child2 = new stdClass();
                if (!empty($child_user->getChildList())) {
                    /** @var User $child_user2 */
                    foreach ($child_user->getChildList() as $child_user2) {
                        $child_list2[] = $child_user2->id . $child_user2->real_name;
                    }
                    $child2->parentNode = $child_user->id . $child_user->real_name;
                    $child2->childNodes = $child_list2;
                    $list2[] = $child2;
                }
            }
            $child->parentNode = $user->id. $user->real_name;
            $child->childNodes = $child_list;
            $list[] = $child;
        }
        $list = array_merge($list, $list2);
        Yii::$app->cache->set('member_list_' . $user->id, $list, 5);
            if (Yii::$app->cache->exists('member_list_' . $user->id)) {
                $list = Yii::$app->cache->get('member_list_'. $user->id);
            } else {
                $query = User::find()->where(['id' => $user->id]);
                $list = $list2 = [];
                /** @var User $user */
                foreach ($query->each() as $user) {
                    $child_list =  [];
                    $child = new stdClass();
                    /** @var User $child_user */
                    foreach ($user->getChildList() as $child_user) {
                        $child_list[] = $child_user->id . $child_user->real_name;
                        $child_list2 =  [];
                        $child2 = new stdClass();
                        if (!empty($child_user->getChildList())) {
                            /** @var User $child_user2 */
                            foreach ($child_user->getChildList() as $child_user2) {
                                $child_list2[] = $child_user2->id . $child_user2->real_name;
                            }
                            $child2->parentNode = $child_user->id . $child_user->real_name;
                            $child2->childNodes = $child_list2;
                            $list2[] = $child2;
                        }
                    }
                    $child->parentNode = $user->id. $user->real_name;
                    $child->childNodes = $child_list;
                    $list[] = $child;
                }
                $list = array_merge($list, $list2);
                Yii::$app->cache->set('member_list_' . $user->id, $list, 5);
            }

        ?>
        var myGraphData = <?php echo json_encode($list, JSON_UNESCAPED_UNICODE);?>;

        var listdata = [];
        var linksdata = [];
        var nodes =[{
            "nodename": "<?php echo $user->id.$user->real_name?>",
            "nodelevel":0,
            "parentnode":0
        }];
        for(var i=0; i < myGraphData.length; i++){
            getNodes(myGraphData[i].parentNode,myGraphData[i].childNodes,nodes);
            setLinkData( myGraphData[i].childNodes, myGraphData[i].parentNode, linksdata);
        }
        setNodeData(nodes,listdata);

        var levels = 0;
        var legend_data = [];
        var series_categories = [];
        var temp = ["一","二","三","四","五"];
        for(var i=0; i < nodes.length; i++){
            levels = Math.max(levels, nodes[i].nodelevel);
        }
        for(var i=0; i<=levels; i++){

            legend_data.push({
                name : i===0?'父节点':'层级'+temp[i],
                icon : 'rect'
            });

            series_categories.push({
                name : i===0?'父节点':'层级'+temp[i],
                symbol : 'rect'
            });

        }
        var myChart = echarts.init(document.getElementById('child'), 'macarons');
        var option = {
            title: {
                text: "<?php echo $user->real_name?>朋友们进阶",
                top: "top",
                left: "left",
                textStyle: {
                    color: '#292421'
                }
            },
            tooltip: {
                formatter: '{b}'
            },
            backgroundColor: '#FFFFFF',
            legend: {
                show : true,
                data : legend_data,
                textStyle: {
                    color: '#292421'
                },
                icon: 'circle',
                type: 'scroll',
                orient: 'horizontal',
                left: 10,
                top: 20,
                bottom: 20,
                itemWidth: 10,
                itemHeight: 10
            },
            animationDuration: 0,
            animationEasingUpdate: 'quinticInOut',
            series: [{
                name: '关系图',
                type: 'graph',
                layout: 'force',
                force: {
                    repulsion: 300,
                    gravity: 0.1,
                    edgeLength: 15,
                    layoutAnimation: true,
                },
                data: listdata,
                links: linksdata,
                categories: series_categories,
                roam: true,
                label: {
                    normal: {
                        show: true,
                        position: 'bottom',
                        formatter: '{b}',
                        fontSize: 10,
                        fontStyle: '600',
                    }
                },
                lineStyle: {
                    normal: {
                        opacity: 0.9,
                        width: 0.5,
                        curveness: 0
                    }
                }
            }]
        };
        myChart.setOption(option);
    }

    function getNodes(parentNode,childNodes,nodes){
        var pnode;
        console.log(nodes.length);
        for(var i=0; i<nodes.length; i++){
            if(parentNode == nodes[i].nodename){
                pnode = nodes[i];
            }
        }
        for(i=0; i<childNodes.length; i++){
            nodes.push({
                nodename : childNodes[i],
                nodelevel: pnode.nodelevel+1,
                parentnode: parentNode,
            });
        }
    }

    function setNodeData(nodes,listdata) {
        var size = 33;
        for(var i=0; i<nodes.length; i++){
            listdata.push({
                category: nodes[i].nodelevel,
                name: nodes[i].nodename,
                symbolSize: size,
                draggable: "true"
            });
        }
    }

    function setLinkData(childList, parentnode, links) {
        console.log(childList.length);
        for(var i=0; i<childList.length; i++){
            links.push({
                // links根据节点名称建立，只适用于节点名称不一样的情况
                "source": childList[i],
                "target": parentnode,
                lineStyle: {
                    normal: {
                        color: 'source',
                    }
                }
            });
        }
    }
</script>
