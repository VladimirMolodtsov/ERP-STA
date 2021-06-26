<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;


$this->title = 'Настройка Контроля за банковским счетами. Управление';



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
  openSwitchWin('head/switch-control-bank-real&id='+id);  
}

function switchUsedAll(id)
{
  openSwitchWin('head/switch-control-bank-all&id='+id);  
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
                'attribute' => 'bankAccount',
                'label'     => 'Банк (счет)',
                'format' => 'raw',
            ],        
                        
            [
                'attribute' => 'accountNumber',
                'label'     => 'Номер счета',
                'format' => 'raw',
            ],        
                        

                        
            [
                'attribute' => 'inUseReal',
                'label'     => 'Реальные',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['inUseReal'] == 1 ){ $isOp = true;}
                    else                               { $isOp = false;}
                    
                    return "<a href='#' onclick='switchUsedReal(".$model['id'].")'".\yii\helpers\Html::tag('span',
                        $isOp ? 'Yes' : 'No',['class' => 'label label-' . ($isOp ? 'success' : 'danger'),]
                        )."</a>";
                },
            ],        
                        
            
            [
                'attribute' => 'inUseAll',
                'label'     => 'Все',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['inUseAll'] == 1 ){ $isOp = true;}
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

