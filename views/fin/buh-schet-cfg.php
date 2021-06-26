<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Параметр контроля';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>

</style>
  
<script>
function switchActive(rowRef)
{  
     openSwitchWin('fin/switch-schet-use&reportRef=<?= $model->id ?>&rowRef='+rowRef);
     $.pjax.reload({container:"#rowsList"});
}

</script>


<?php Pjax::begin(['id' => 'formEdit']);  $form = ActiveForm::begin(); ?>

<div class='row'>
 <div class ='col-md-3'></div> 
 <div class ='col-md-4'><?= $form->field($model, 'reportTitle')->label(false) ?></div>
 <div class ='col-md-5'><?=  Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>  </div>
</div>
<div class='spacer'></div>

<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>


<?php ActiveForm::end(); Pjax::end(); ?>

<div class='spacer'></div>

<?php
Pjax::begin(['id' => 'rowsList']); 
//number_format($model->sumValue,2,'.','&nbsp;');
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
       
           [
                'attribute' => 'schet',
                'label' => 'Счет',
                'format' => 'raw',
            ],
                            
            [
                'attribute' => 'subSchet',
                'label' => 'Субсчет',
                'format' => 'raw',
            ],

            [
                'attribute' => 'subSubSchet',
                'label' => 'Субсубсчет',
                'format' => 'raw',
            ],
            
            [
                'attribute' => '-',
                'label' => 'Активен',
                'filter' => ['1' => 'Все','2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['lnkRef'] != 0 ){ $isUse = true;}
                   else                       { $isUse = false;}
                    return "<a href='#' onclick='switchActive(".$model['id'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                        ['class' => 'label label-' . ($isUse ? 'success' : 'danger'),])."</a>";
                }                
                
            ],

        ],
    ]
);
Pjax::end();    
?>
