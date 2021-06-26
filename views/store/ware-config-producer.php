<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Настройка';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>
  
<script>

/*************/
function saveData(id, type)
{
    if (id >0) {
    var idx = type+id;
    document.getElementById('dataVal').value=document.getElementById(idx).value;    
    }
    document.getElementById('recordId').value=id;    
    document.getElementById('dataType').value=type;    
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-warehouse-cfg',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.location.reload(true); 
        },
        error: function(){
              document.location.reload(true);
        }
    });	
}

</script>

<p> Производители </p>
<?php
$producerProvider= $model->getWareProducerProvider(Yii::$app->request->get());
echo GridView::widget(
    [
        'dataProvider' => $producerProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
//        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,               
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
        
            [
                'attribute' => 'wareProdTitle',
                'label' => 'Наименование',
                'contentOptions' =>['style' =>'padding:0px'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $action = "setSelected(".$model['id'].")";
                   $id = 'wareProdTitle'.$model['id'];
                   
                   $action = "saveData(".$model['id'].",'wareProdTitle')";
                   return  Html::textInput( 
                         $id, 
                         $model['wareProdTitle'],                                
                        [
                           'class' => 'form-control',
                           'style' => 'width:100%; font-size:11px;padding:1px;', 
                           'id' => $id, 
                           'onchange' => $action,
                           'placeholder' => 'Заполните название',
                        ]); 
                }                                
           ],
       ]
    ]
);
?>

<div align='right'>
<?php
     echo \yii\helpers\Html::tag( 'span', '', 
          [
          'class'   => 'clickable glyphicon glyphicon-plus',
          'id'      => 'config',
          'onclick' => "saveData(0,'wareProdAdd')",          
           ]);
?>
</div>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-warehouse-cfg']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

