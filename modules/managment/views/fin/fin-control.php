<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\date\DatePicker;

use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Финансовый анализ';
$curUser=Yii::$app->user->identity;

$now =$model->controlTime;
$prev=$now-24*3600;
$next=$now+24*3600;
 //echo date("d.m.Y", $now);
 
$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/fin-control.js');
 
?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />

<style>

</style>
  
<script>
function syncFinControl()
{ 
 document.location.href='index.php?r=/managment/fin/sync-control&controlTime=<?= $model->controlTime ?>';
}

function addNewRow()
{
   openSwitchWin('/managment/fin/control-row-add'); 
}

function openForEdit(id)
{
  openWin('/managment/fin/fin-control-cfg&controlTime=<?= $model->controlTime ?>&id='+id,'editWin'); 
}

function changeShowDate()
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=/managment/fin/fin-control&showDate='+showDate ;
}

</script>


<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=/managment/fin/fin-control&controlTime=<?= $prev ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
<div class ='col-md-1' style='text-align:center'></div>
 <div class ='col-md-3' style='text-align:center'>
<?php   
   echo DatePicker::widget([
    'name' => 'show_date',
    'id' => 'show_date',
    'value' => date("d.m.Y",$now),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
]);
?>      
   </div>   
<div class ='col-md-2' style='text-align:center'></div>   
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=/managment/fin/fin-control&controlTime=<?= $next ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
 
  <div class ='col-md-3' style='text-align:center'><?= $model->syncDateTime ?></div>
  <div class='col-md-1' style='text-align:right;'><a href='#' onClick='syncFinControl();'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>



<div class='spacer'></div>





 <?php 
     
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        //'filterModel' => $model,
        //'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
     

            [
                'attribute' => 'rowTitle',
                'label'     => 'Параметр',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                 return "<a href='#' onclick='openForEdit(".$model['id'].")'>".$model['rowTitle']."</a>";
                }                                
                
            ],        

            [
                'attribute' => 'valDoc',
                'label'     => 'по Документам',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:150px;'],
                'value' => function ($model, $key, $index, $column) {                              
                
                                
                if ($model['dataType']== 0) 
                {
                 $id = 'docEdit'.$model['contentRef'];
                 $action = "saveField(".$model['contentRef'].",'docEdit')";             
                 if ($model['contentRef'] == 0) $disabled = true;
                                           else $disabled = false;
                 
                    return Html::textInput($id , $model['valDoc'], 
                        [
                        'id'    => $id,
                        'class' => 'form-control',
                        'style' => 'width:150px;',
                        'onChange' => $action,                
                        'disabled' => $disabled
                        ]);     
                
                 }
                 else  return number_format($model['valDoc'],2,".","&nbsp;");
                }                                
            ],        

    /*        [
                'attribute' => 'valUTm',
                'label'     => 'УТ (вручную)',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                 return number_format($model['valUTm'],2,".","&nbsp;");
                }                                
                
            ], */       
            [
                'attribute' => 'valUTa',
                'label'     => 'УТ (авто)',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                 return number_format($model['valUTa'],2,".","&nbsp;");
                }                                
                
            ],        
/*            [
                'attribute' => 'valBuh_m',
                'label'     => 'Бух (вручную)',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                 return number_format($model['valBuh_m'],2,".","&nbsp;");
                }                                
                
            ],   */     
            [
                'attribute' => 'valBuh_a',
                'label'     => 'Бух (авто)',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                 return number_format($model['valBuh_a'],2,".","&nbsp;");
                }                                
                
            ],        

      ]//columns            
    ]
    );
?>




<div class ='row'>
  <div class ='col-md-10'></div>
  <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewRow();'><span class='glyphicon glyphicon-plus'></span></a></div>  
</div>



<?php 
//echo "<pre>";
//print_r ($model->debug);
//    print_r($data);
//echo "</pre>";
 ?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/fin/save-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//  echo "<input type='submit'>";
ActiveForm::end(); 
?>
