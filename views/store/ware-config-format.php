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
            alert('Error while saving data!');
        }
    });	
}

</script>

<p>Тип товара</p>
<?php
$typePovider= $model->getWareFormatsProvider(Yii::$app->request->get());
echo GridView::widget(
    [
        'dataProvider' => $typePovider,
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
                'attribute' => 'formatString',
                'label' => 'Наименование',
                'format' => 'raw',
                'contentOptions' =>['style' =>'padding:0px'],
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $id = 'formatString'.$model['id'];
                   $action = "saveData(".$model['id'].",'formatString')";
                   return  Html::textInput( 
                         $id, 
                          $model['formatString'],                                
                        [
                           'class' => 'form-control',
                           'style' => 'width:100%; font-size:11px;padding:1px;', 
                           'id' => $id, 
                           'onchange' => $action,
                           'placeholder' => 'Заполните название',
                        ]); 
                }                                
           ],
           
            [
                'attribute' => 'width',
                'label' => 'Ширина',
                'format' => 'raw',
                'contentOptions' =>['style' =>'padding:0px'],
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $id = 'formatWidth'.$model['id'];
                   $action = "saveData(".$model['id'].",'formatWidth')";
                   return  Html::textInput( 
                         $id, 
                          $model['width'],                                
                        [
                           'class' => 'form-control',
                           'style' => 'width:100%; font-size:11px;padding:1px;', 
                           'id' => $id, 
                           'onchange' => $action,
                           'placeholder' => 'ширина',
                        ]); 
                }                                
           ],
            [
                'attribute' => 'length',
                'label' => 'Наименование',
                'format' => 'raw',
                'contentOptions' =>['style' =>'padding:0px'],
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $id = 'formatLength'.$model['id'];
                   $action = "saveData(".$model['id'].",'formatLength')";
                   return  Html::textInput( 
                         $id, 
                          $model['length'],                                
                        [
                           'class' => 'form-control',
                           'style' => 'width:100%; font-size:11px;padding:1px;', 
                           'id' => $id, 
                           'onchange' => $action,
                           'placeholder' => 'длинна',
                        ]); 
                }                                
           ],
            [
                'attribute' => 'intSize',
                'label' => 'Диаметр',
                'format' => 'raw',
                'contentOptions' =>['style' =>'padding:0px'],
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $id = 'formatIntSize'.$model['id'];
                   $action = "saveData(".$model['id'].",'formatIntSize')";
                   return  Html::textInput( 
                         $id, 
                          $model['intSize'],                                
                        [
                           'class' => 'form-control',
                           'style' => 'width:100%; font-size:11px;padding:1px;', 
                           'id' => $id, 
                           'onchange' => $action,
                           'placeholder' => 'Внутренний диаметр',
                        ]); 
                }                                
           ],
           
           
          [
                'attribute' => 'isProduct',
                'label' => 'Продукция',
                'filter' => [0 => 'Все', 1=> 'Да', 2 => 'Нет'],
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                 if ($model['isProduct'] == 1) $style = 'background:DarkBlue';
                                         else $style = 'background:White';
                   
                 $action = "switchProduct(".$model['id'].")";
                   
                   $id = 'isProduct_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Продукция',
                     'style'   => $style,
                   ]);
                   
                   return $val;
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
          'onclick' => "saveData(0,'wareFormatAdd')",          
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

