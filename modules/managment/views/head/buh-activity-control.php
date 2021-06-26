<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Контроль активности бухгалтерии';
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

.suspicious {
  color:Crimson;  
}  


.notsuspicious {
  color:DarkGreen;  
}  
</style>
  
<script>
  function openPeriod()
  {
    stDate = document.getElementById('stDate').value;
    enDate = document.getElementById('enDate').value;
    document.location.href='index.php?r=managment/head/buh-activity-control&stDate='+stDate+"&enDate="+enDate;
  } 
</script>

</script>

<div class ='row'>

   <div class ='col-md-2'>   
    <input class="form-control" type='date' name='stDate' id='stDate' value='<?= $model->stDate ?>'>   
   </div> 
    <div class ='col-md-2'> 
    <input class="form-control" type='date' name='enDate' id='enDate' value='<?= $model->enDate ?>'>
    </div> 
    <div class ='col-md-1'>
       <a href='#' onclick='openPeriod();' ><span class='glyphicon glyphicon-ok'></span></a>
   </div>
     
  <div class='col-md-7' style='text-align:right;'>
  
  </div>
</div>

<?php
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
  */      
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       

            [
                'attribute' => 'cn',
                'label' => 'Дата',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column) {
                  return date ("d.m.Y", $model['cn']);
                }
            ],

            [
                'attribute' => 'manual',
                'label' => 'Ручной ввод',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['manual'] == 1 ) { $isOp = true;}
                    else                        { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
                        );

                }                
            ],        

            [
                'attribute' => 'auto',
                'label' => 'Авто',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['auto'] == 1 ) { $isOp = true;}
                    else                        { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
                        );

                }                
            ],        

            [
                'attribute' => 'syncDateTime',
                'label' => 'Дата синхр.',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
            ],        

            [
                'attribute' => 'isFinished',
                'label' => 'Завершено',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['isFinished'] == 1 ) { $isOp = true;}
                    else                        { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
                        );

                }

            ],        

        
                                        
        ],
    ]
);


?>
