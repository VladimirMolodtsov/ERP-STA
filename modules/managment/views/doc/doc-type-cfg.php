<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\tabs\TabsX;

$this->title = 'Классификация документов';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/doc-type.js');

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />

<style>
</style>
  
<script>
</script>

<div class='row'>
<div class='col-md-4'>
<?php

echo  GridView::widget(
    [
        'dataProvider' => $typeProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
  //      'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
 /*   'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],*/        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'typeTitle',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;'],
                'value' => function ($model, $key, $index, $column) {     
                $id = 'typeTitleEdit'.$model['id'];     
                $c = "";
                $action = "saveField(".$model['id'].",'typeTitleEdit')";
                if ($model['typeTitle'] == 'Новый тип') $c = 'color:Crimson';
                return Html::textInput($id , $model['typeTitle'], 
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:100%;'.$c,
                'onChange' => $action,                
                ]);
                }               
            ],                            
            [
                'attribute' => '-',
                'label' => '',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;'],
                'value' => function ($model, $key, $index, $column) {                     
                $action = "removeType(".$model['id'].");";
                return Html::tag('span', '', 
                [
                'class' => 'glyphicon glyphicon-remove-circle',
                'style' => 'color:Crimson',
                'onclick' => $action,
                ]);
                }               
            ],                            
            
        ],
    ]
);
?>
<div class='spacer'></div>
<div style='text-align:right;'><a href='#' onclick='addNewTypeRow();'><span class='glyphicon glyphicon-plus'></span></a></div> 
</div>
<div class='col-md-8'>
<?php
$typeArray = $model->getTypeArray();
$typeArray[0]='не задан'; 
echo  GridView::widget(
    [
        'dataProvider' => $operationProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
  //      'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*   'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        */
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'refDocType',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;width:200px;'],

                'value' => function ($model, $key, $index, $column) use ($typeArray) {     
                $id = 'typeTitleSelect'.$model['id'];     
                $c = "";
                if ($model['refDocType'] == 0) $c = 'color:Crimson';
                $action = "saveField(".$model['id'].",'typeTitleSelect')";                
                return Html::dropDownList($id , $model['refDocType'], $typeArray,
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:200px;'.$c,
                'onChange' => $action,                
                ]);
                }                               
                
            ],                            

            [
                'attribute' => 'operationTitle',
                'label' => 'Операция',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;'],
                'value' => function ($model, $key, $index, $column) {     
                $id = 'operationTitleEdit'.$model['id'];     
                $c = "";
                if ($model['operationTitle'] == 'Новая операция') $c = 'color:Crimson';
                $action = "saveField(".$model['id'].",'operationTitleEdit')";                
                return Html::textInput($id , $model['operationTitle'], 
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:100%;'.$c,
                'onChange' => $action,
                ]);
                }                               
            ],                            
            
            [
                'attribute' => '-',
                'label' => '',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;'],
                'value' => function ($model, $key, $index, $column) {                     
                $action = "removeOperation(".$model['id'].");";
                return Html::tag('span', '', 
                [
                'class' => 'glyphicon glyphicon-remove-circle',
                'style' => 'color:Crimson',
                'onclick' => $action,
                ]);
                }               
            ],                            
            

       ],
    ]
);
?>

<div class='spacer'></div>
<div style='text-align:right;'><a href='#' onclick='addNewOperationRow();'><span class='glyphicon glyphicon-plus'></span></a></div> 


</div>



</div>
<?php
$form = ActiveForm::begin(['id' => 'saveDataForm']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>