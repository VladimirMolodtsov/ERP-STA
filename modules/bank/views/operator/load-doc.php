<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - регистрация документов';
$this->params['breadcrumbs'][] = $this->title;

$now = $model->showDate;
$prev= $model->showDate-24*3600;
$next= $model->showDate+24*3600;
 
$zero=strtotime (date("Y-m-d", $model->showDate)." 00:00:00"); //на начало дня
$shift = intval((time() - $zero)/3600)+4; // сколько прошло с начала дня


 
 
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.action_ref {    
    color:Green;
}
</style>


<script type="text/javascript">

function loadDocFinalize()
{
  openWin("bank/operator/start-sync-reg-doc","progressWin");
}

</script> 
<?php /*
<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=bank/operator/load-doc&showDate=<?= $prev ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-10' style='text-align:center'><?= date("d.F.Y", $now) ?></div>
   <div class ='col-md-1' style='text-align:right'>
    <?php if ($next < time()) { ?>  
       <a href="index.php?r=bank/operator/load-doc&showDate=<?= $next ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   <?php } ?>  
   </div>
</div>
*/ ?> 

<div class ='row'>
   <div class ='col-md-8' style='text-align:center'></div>
   <div class ='col-md-2' style='text-align:right'>
   </div>
   <div class='col-md-2'>
    <input type='button' class='btn btn-primary' value='Загрузить документы' onclick='loadDocFinalize();'>
   </div>

</div>

<hr>
<div class="item-header">Загруженные документы:</div> 
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
                'attribute' => 'regDateTime',
                'label'     => 'Загружен',
                //'format' => ['datetime', 'php:d.m.Y H:i'],
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['regDateTime']));
               }
               
            ],            
            [
                'attribute' => 'docIntNum',
                'label'     => 'Номер',
                'format' => 'raw',                            
            ],              
            [
                'attribute' => 'docTitle',
                'label'     => 'Документ',
                'format' => 'raw',                            
            ],            
            [
                'attribute' => 'Документ',
                'label'     => 'Номер/дата',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return "<a href ='#' onclick=\"openWin('bank/operator/reg-doc&id=".$model['id']."','childWin');\" 
                    >".$model['docOrigNum']." от ".date("d.m.Y", strtotime($model['docOrigDate']))."</a>";
               }
                       
            ],            
            
           [
                'attribute' => 'docSum',
                'label'     => 'На сумму',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['docSum'],2,',','&nbsp;');
               }
                
            ],            
            
                         
            [
                'attribute' => 'userFIO',
                'label'     => 'Оператор',
                'format' => 'raw',            
            ],            

        
        ],
    ]
); 

?>


