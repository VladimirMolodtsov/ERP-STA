<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;
use yii\widgets\Pjax;


$this->title = 'Настройка Контроля по сверке долга. Управление';



?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

<script type="text/javascript">

function switchInUse(id)
{
  openSwitchWin('head/switch-control-sverka-use&id='+id);  
}

function switchInBlack(id)
{
  openSwitchWin('head/switch-control-sverka-black&id='+id);  
}

</script>


<h3><?= Html::encode($this->title) ?></h3>


<p> Отслеживаемые организации (юр. лица)</p>


<?php
Pjax::begin();     
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $providerInUse,
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
                'attribute' => 'inUse',
                'label'     => 'Учитывать',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['inUse'] == 1 ){ $isOp = true;}
                    else                               { $isOp = false;}
                    
                    return "<a href='#' onclick='switchInUse(".$model['id'].")'".\yii\helpers\Html::tag('span',
                        $isOp ? 'Yes' : 'No',['class' => 'label label-' . ($isOp ? 'success' : 'danger'),]
                        )."</a>";
                },
            ],        
                       
        ],
    ]
    );
Pjax::end();     
?>

<p> Черный список контрагентов</p>
<?php
Pjax::begin();     
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $providerIsBlack,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

             
            [
                'attribute' => 'orgTitle',
                'label'     => 'Организация',
                'format' => 'raw',
            ],        

            [
                'attribute' => 'S',
                'label'     => 'Сумма сверки',
                'format' => 'raw',
            ],        
 
            
            [
                'attribute' => 'isBlack',
                'label'     => 'Не учитывать',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['isBlack'] == 1 ){ $isOp = true;}
                    else                               { $isOp = false;}
                    
                    return "<a href='#' onclick='switchInBlack(".$model['id'].")'".\yii\helpers\Html::tag('span',
                        $isOp ? 'Yes' : 'No',['class' => 'label label-' . ($isOp ?  'danger' : 'success'),]
                        )."</a>";
                },
            ],        
                       
        ],
    ]
    );
Pjax::end();     
?>

