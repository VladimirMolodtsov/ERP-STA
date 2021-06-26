<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - выписки';
$this->params['breadcrumbs'][] = $this->title;
 
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function syncExtract(){
    
openSwitchWin("/bank/aurora/aurora-load-extract");
}

</script> 

<hr>
<div class="item-header">Загруженные выписки:</div> 

<div class ='row'>

   
   <div class ='col-md-10'>   
   </div>
   
   
 
  <div class='col-md-1' style='text-align:center'>  </div>
  <div class='col-md-1' style='text-align:right;'><a href='#' onClick='syncExtract();'><span class='glyphicon glyphicon-refresh'></span></a></div>  
  
</div>

<div class='spacer'></div>



<?php 
echo
 \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  

            [
                'attribute' => 'uploadTime',
                'label'     => 'Загружена',
                //'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                   return "<a href ='#' onclick=\"openWin('bank/aurora/show-extract&id=".$model['id']."','childWin');\" >".date("d.m.Y H:i:s", strtotime($model['uploadTime'])+4*3600)."</a>";
                     
               }
               
            ],            
           
           [
                'attribute' => 'inputRemain',
                'label'     => 'Входящая сумма',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['inputRemain'],2,',','&nbsp;');
               }
                
            ],            
            [
                'attribute' => 'outputRemain',
                'label'     => 'Исходящая сумма',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['outputRemain'],2,',','&nbsp;');
               }
                
            ],            
                         
            [
                'attribute' => 'srcFile',
                'label'     => 'Заголовок',
                'format' => 'raw',            
            ],            

        
        ],
    ]
); 

?>


