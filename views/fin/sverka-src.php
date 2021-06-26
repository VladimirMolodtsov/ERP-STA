<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Сверка долга';
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

</script>


<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=fin/profit-src&strDate=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-8' style='text-align:center'><h4><?= date("d.F.Y", $now) ?></h4></div>
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=fin/profit-src&strDate=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
 
  <div class ='col-md-1' style='text-align:center'><div class ='col-md-1' style='text-align:center'><?= $model->syncDateTime ?></div></div>
  <div class='col-md-1' style='text-align:right;'><a href='index.php?r=data/sync-profit&syncTime=<?= $now ?>'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>

<p>Суммарная прибыль: <?=  number_format($model->sumValue,2,'.','&nbsp;'); ?>  </p>

<?php

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
                'attribute' => 'ownerOrgTitle',
                'label' => 'Организация',
                'format' => 'raw',
            ],
       
            [
                'attribute' => 'regRecord',
                'label' => 'Документ',
                'format' => 'raw',
            ],

  
            [
                'attribute' => 'recordDate',
                'label' => 'Дата',
                'format' => ['date', 'php:Y-m-d H:i:s'],
            ],
       
            [
                'attribute' => 'recordNumber',
                'label' => 'Номер',
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
                    return number_format($model['goodAmount'],2,'.','&nbsp;');
                }                
                
            ],

            [
                'attribute' => 'sellPrice',
                'label' => 'Цена продажи',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['sellPrice'],2,'.','&nbsp;');
                }                
                
            ],

            
            [
                'attribute' => 'initPrice',
                'label' => 'Себестоимость',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['initPrice'],2,'.','&nbsp;');
                }                
                
            ],

            [
                'attribute' => 'profit',
                'label' => 'Прибыль',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['profit'],2,'.','&nbsp;');
                }                
                
            ],
            [
                'attribute' => 'profitability',
                'label' => 'Рентабельность',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['profitability'],2,'.','&nbsp;');
                }                
                
            ],
                        
            
  
        ],
    ]
);
?>
