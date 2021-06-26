<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Проведенные закупки';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
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
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}
</style>
  
<script>
function syncByDate(syncTime)
{
  openWin('/data/sync-purch&parentForm=/site/success&syncTime='+syncTime,'syncWin');  
}
</script>

<div class ='row'>

   <div class ='col-md-8'>   
 
   </div>
   
   <div class ='col-md-2'>   
     <a href='index.php?r=managment/head/show-purch&noframe=1'>Сбросить фильтры</a>
   </div>
     
  <div class='col-md-2' style='text-align:right;'>
  <a href='index.php?r=managment/head/purch-classify-cfg&noframe=1'><span class='glyphicon  glyphicon-cog'></span></a></div> 
</div>

<h4><?= $model->rowTitle ?> </h4>
<div class ='spacer'></div>
<div style='width:800px; overflow: auto;'><?php
for ($i=0;$i<count($model->existDates);$i++)
{
 $action = "syncByDate(".$model->existDates[$i]['time'].")";    
 if ($model->existDates[$i]['exist']>0)  echo "<span onclick='".$action."' class='clickable' style='color:blue;'>".$model->existDates[$i]['date']."</span>&nbsp;&nbsp;";     
                                    else echo "<span onclick='".$action."' class='clickable' style='color:LightGray;'>".$model->existDates[$i]['date']."</span>&nbsp;&nbsp;";     
}
?></div>

<?php
echo  GridView::widget(
    [
        'dataProvider' => $sumprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
                
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],


        
        'columns' => [       
            [
                'attribute' => 'ownerOrgTitle',
                'label' => 'Собственник',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],        
            
            [
                'attribute' => 'typeTitle',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            
            [
                'attribute' => 'purchSum',
                'label' => 'Сумма',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column) {
                  return number_format($model['purchSum'] ,2,'.','&nbsp;');
                }

            ],

            
        ],
    ]
);
?>

<div class ='spacer'></div>


<?php
echo  GridView::widget(
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
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],


        
        'columns' => [       
            [
                'attribute' => 'ownerOrgTitle',
                'label' => 'Собственник',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],        
            [
                'attribute' => 'typeTitle',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            [
                'attribute' => 'purchDate',
                'label' => 'Дата',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            [
                'attribute' => 'purchTitle',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            [
                'attribute' => 'purchCount',
                'label' => 'К-во',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
            [
                'attribute' => 'purchEd',
                'label' => 'Ед.',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],
        
            [
                'attribute' => 'purchSum',
                'label' => 'Сумма',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],

            
        ],
    ]
);


?>

<!--
    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewMask();'><span class='glyphicon glyphicon-plus'></span></a></div>  
    </div>
-->


<input type='button' class='btn btn-primary' onclick='window.opener.location.reload(false); window.close();' value='Завершить'>


<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/head/save-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
  //echo "<input type='submit'>";
ActiveForm::end(); 
?>

<?php
 //echo "<pre>\n";
 //print_r ($model->existDates);
  //print_r ($model->debug);
  //print_r($data);
//echo "</pre>\n";
?>