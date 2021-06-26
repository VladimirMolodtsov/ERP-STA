<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;


$this->title = 'Настройка Контроля за остатками на складе. Управление';



?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

<script type="text/javascript">

function switchUsedReal(id)
{
  openSwitchWin('head/switch-control-remain-real&id='+id);  
}

function switchUsedAll(id)
{
  openSwitchWin('head/switch-control-remain-all&id='+id);  
}

</script>


<h3><?= Html::encode($this->title) ?></h3>

<?php
     
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
 
            [
                'attribute' => 'usedOrgTitle',
                'label'     => 'Организация',
                'format' => 'raw',
            ],        

            [
                'attribute' => 'scladTitle',
                'label'     => 'Склад',
                'format' => 'raw',
            ],        
                        

            [
                'attribute' => 'scladIsUsedReal',
                'label'     => 'Реальные',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['scladIsUsedReal'] == 1 ){ $isOp = true;}
                    else                               { $isOp = false;}
                    
                    return "<a href='#' onclick='switchUsedReal(".$model['id'].")'".\yii\helpers\Html::tag('span',
                        $isOp ? 'Yes' : 'No',['class' => 'label label-' . ($isOp ? 'success' : 'danger'),]
                        )."</a>";
                },
            ],        
                        
            
            [
                'attribute' => 'scladIsUsedAll',
                'label'     => 'Все',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['scladIsUsedAll'] == 1 ){ $isOp = true;}
                    else                               { $isOp = false;}
                    
                    return "<a href='#' onclick='switchUsedAll(".$model['id'].")'".\yii\helpers\Html::tag('span',
                        $isOp ? 'Yes' : 'No',['class' => 'label label-' . ($isOp ? 'success' : 'danger'),]
                        )."</a>";
                },
            ],        
 

 
            
        ],
    ]
    );
?>

