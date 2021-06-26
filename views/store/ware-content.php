<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Наполнение склада';
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
     openSwitchWin('store/switch-ware-active&id='+id);
}
</script>


<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=store/ware-content&strDate=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-8' style='text-align:center'><h4><?= date("d.F.Y", $now) ?></h4></div>
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=store/ware-content&strDate=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
 
  <div class ='col-md-1' style='text-align:center'><div class ='col-md-1' style='text-align:center'><?= $model->syncDateTime ?></div></div>
  <div class='col-md-1' style='text-align:right;'><a href='index.php?r=data/sync-sclad&syncTime=<?= $now ?>'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>

<p>Сумма по себестоимости: <?=  number_format($model->sumValue,2,'.','&nbsp;'); ?>  <a href="index.php?r=store/ware-use&strDate=<?= date('Y-m-d',$now) ?>">по складам ...</a></p>



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
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'orgTitle',
                'label' => 'Организация',
                'format' => 'raw',
            ],
       
            [
                'attribute' => 'scladTitle',
                'label' => 'Склад',
                'format' => 'raw',
            ],

            [
                'attribute' => 'articul',
                'label' => 'Артикул',
                'format' => 'raw',
            ],
       
            [
                'attribute' => 'grpGood',
                'label' => 'Группа товара',
                'format' => 'raw',
            ],

            [
                'attribute' => 'goodTitle',
                'label' => 'Номенклатура',
                'format' => 'raw',
            ],

            [
                'attribute' => 'goodEd',
                'label' => 'Ед.изм.',
                'format' => 'raw',
            ],

            
            [
                'attribute' => 'goodAmount',
                'label' => 'К-во.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {  
                if ($model['goodAmount'] <0)  return "<font color='Crimson'>".number_format($model['goodAmount'],2,'.','&nbsp;')."</font>";             
                    return number_format($model['goodAmount'],2,'.','&nbsp;');
                }                
                
            ],
            
            [
                'attribute' => 'initPrice',
                'label' => 'Себестоимость',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                if ($model['initPrice'] <0)  return "<font color='Crimson'>".number_format($model['initPrice'],2,'.','&nbsp;')."</font>";             
                    return number_format($model['initPrice'],2,'.','&nbsp;');
                }                
                
            ],

            [
                'attribute' => 'isActive',
                'label' => 'Активен',
                'filter' => ['1' => 'Да', '2' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['isActive'] == 1 ){ $isUse = true;}
                   else                        { $isUse = false;}
                    return "<a href='#' onclick='switchActive(".$model['id'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                        ['class' => 'label label-' . ($isUse ? 'success' : 'danger'),])."</a>";
                }                
                
            ],

        ],
    ]
);
?>
