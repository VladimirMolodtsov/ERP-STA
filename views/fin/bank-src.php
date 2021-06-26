<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Состояние счета';
$curUser=Yii::$app->user->identity;


$now =strtotime($model->strDate);
$prev=$now-24*3600;
$next=$now+24*3600;

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.otves {
    background-color: Green ;
    //width: 50px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

.small_btn {
    //background-color: Green ;
    //width: 50px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

.inuse {
    background-color:  Brown;
    //width: 30px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

</style>
  
<script>
function switchActive(id)
{  
     openSwitchWin('fin/switch-bank-use&id='+id);
}

</script>


<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=fin/bank-src&strDate=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-6' style='text-align:center'><h4><?= date("d.F.Y", $now) ?></h4></div>
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=fin/bank-src&strDate=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
 
  <div class ='col-md-3' style='text-align:center'><?= $model->syncDateTime ?></div>
  <div class='col-md-1' style='text-align:right;'><a href='index.php?r=data/sync-bank&syncTime=<?= $now ?>&parentForm=self'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>

    <p>Сумма по счетам:  <?= number_format($model->sumValue,2,'.','&nbsp;') ?> </p>    

<hr>

<?php
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
                'attribute' => 'usedOrgTitle',
                'label' => 'Организация',
                'format' => 'raw',
            ],
                            
            [
                'attribute' => 'bankAccount',
                'label' => 'Счет в банке',
                'format' => 'raw',
            ],

            [
                'attribute' => 'accountNumber',
                'label' => 'Номер счета',
                'format' => 'raw',
            ],
            
            [
                'attribute' => 'cashType',
                'label' => 'Тип',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['cashType'] == 1) return "нал";
                                                return "б/н";
                }                
                
            ],
                                                
            [
                'attribute' => 'cashSum',
                'label' => 'Сумма ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['cashSum'],2,'.','&nbsp;');
                }                
                
            ],

            [
                'attribute' => 'cashEd',
                'label' => 'Валюта',
                'format' => 'raw',
            ],
            

            [
                'attribute' => 'inUseReal',
                'label' => 'Активен',
                'filter' => ['1' => 'Все','2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['inUseReal'] == 1 ){ $isUse = true;}
                   else                         { $isUse = false;}
                    return "<a href='#' onclick='switchActive(".$model['useRef'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                        ['class' => 'label label-' . ($isUse ? 'success' : 'danger'),])."</a>";
                }                
                
            ],

        ],
    ]
);
?>
