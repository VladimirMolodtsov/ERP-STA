<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\tabs\TabsX;

$this->title = 'Использование отчета по прибыли';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/monitor-save-row-cfg.js');

$rowRef = $model->rowRef;
?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>



  <h4>Учитываемые организации собственника</h4>
<?php                    
echo  GridView::widget(
    [
        'dataProvider' => $ownprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],*/        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'owerOrgTitle',
                'label' => 'Организация',
                'format' => 'raw',
            ],
            [
                'attribute' => 'multOwn',
                'label' => '+/-',
                'filter' => [1 =>'Все', 2 =>'<> 0', 3 =>'=0', 4 =>'> 0', 5 => '< 0'  ],
                'contentOptions'   =>   ['style' => 'padding:2px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($rowRef) {
     
                 $val=$model['multOwn'];                 

                 $out = "";
                 $id = '1minus'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].", -1, 1 )";
                 if ( $val == -1 ) $style = "color:White;background-color:Crimson;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' -1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               $out .= "&nbsp;";
                 $id = '1zero'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].",0, 1)";
                 if ( $val == 0 )  $style = "color:Black;background-color:LightGray;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' 0 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
            $out .= "&nbsp;";
                 $id = '1plus'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].",1, 1)";
                 if ( $val == 1 )  $style = "color:White;background-color:Green;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' +1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               
                 return $out;        
                },               
            ],

                     
        ],
    ]
);

?>

  <h4>Параметры отчета по прибыли</h3>
<?php                    
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
//        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],*/        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'typeTitle',
                'label' => 'Параметр отчета',
                'format' => 'raw',
            ],
            [
                'attribute' => '-',
                'label' => '+/-',
                //'filter' => [0 =>'Все', 1 =>'<> 0', 2 =>'=0', 3 =>'> 0', 4 => '< 0'  ],
                'contentOptions'   =>   ['style' => 'padding:2px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($rowRef) {
     
                 $val=$model['mult'];                 

                 $out = "";
                 $id = '7minus'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].", -1, 7 )";
                 if ( $val == -1 ) $style = "color:White;background-color:Crimson;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' -1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               $out .= "&nbsp;";
                 $id = '7zero'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].",0, 7)";
                 if ( $val == 0 )  $style = "color:Black;background-color:LightGray;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' 0 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
            $out .= "&nbsp;";
                 $id = '7plus'.$model['id'];
                 $action="setDiv(".$rowRef.",".$model['id'].",1, 7)";
                 if ( $val == 1 )  $style = "color:White;background-color:Green;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' +1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               
                 return $out;        
                },               
            ],

                     
        ],
    ]
);

?>


<input type='button' class='btn btn-primary' onclick='window.opener.location.reload(false); window.close();' value='Завершить'>
    
<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/head/save-row-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>


