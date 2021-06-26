<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Данные по оплате';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0020)) {return;}

 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

 
<style>



</style>

<?php 

$reestrRecord = $model->getReestrData();
//if ($reestrRecord == false) return;
?>

<p> <b>Детализация по запросу на оплату № <?= $reestrRecord->id ?>  на сумму <?= $reestrRecord->summRequest ?>. </b></p>
<p> 
<p>
Получатель <?= $reestrRecord->orgTitle ?>.  
</p>

<?php if(!empty($model->reestExtData['manager'])) {echo "<p>Запрос создан: ".$model->reestExtData['manager'] ." ";}
echo "Дата формирования запроса: ".$reestrRecord->formDate."</p>"; ?>  



<?php if(!empty($reestrRecord->note)) echo "<p> Коментарий:".$reestrRecord->note."</p>"; ?>  

<?php if(!empty($reestrRecord->refSchet)) { 
$schetRecord = $model->getReestrSchetData($reestrRecord->refSchet);

$schetProvider = $model->getReestrSchetProvider($reestrRecord->refSchet);
?>
<p> Счет № <?= $schetRecord->schetNum  ?> от <?= $schetRecord->schetDate ?> </p>

 <?php
 
    $content = \yii\grid\GridView::widget(
    [
        'dataProvider' => $schetProvider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
                'attribute' => 'goodTitle',
				'label'     => 'Номенклатура',
                'format' => 'raw',
            ],
      
            [
                'attribute' => 'goodCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],
            
            [
                'attribute' => 'goodEd',
				'label'     => 'Ед. изм.',
                'format' => 'raw',
            ],           
            
            [
                'attribute' => 'goodSumm',
				'label'     => 'сумма',
                'format' => 'raw',
            ],
            
      ]//columns            
    ]
	);

    echo Collapse::widget([
    'items' => [
        [
        
        
            'label' => "Товары по счету: ▼ ",                        
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 



} 
else "<p>Счет не задан. </p>";
?>  


<?php 

    if(!empty($reestrRecord->refZakupka)) { 
    $purchaseRecord = $model->getReestrPurchaseData($reestrRecord->refZakupka);
    $purchaseProvider = $model->getReestrPurchaseProvider($reestrRecord->refZakupka);

    echo "<p> Закупка № ".$purchaseRecord->id." от ".$purchaseRecord->dateCreation."</p>"; 
    
    $content = \yii\grid\GridView::widget(
    [
        'dataProvider' => $purchaseProvider ,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],


            
            [
                'attribute' => 'id',
				'label'     => 'Запрос цены',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['id']." от ".$model['zakazDate'];
                },                
            ],

            [
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
            ],
      
            [
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],
            
            [
                'attribute' => 'zakazNote',
				'label'     => 'Примечание',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                    return  mb_substr($model['zakazNote'],0,'50') ;
                },                
            ],
                        
            [
                'attribute' => 'refZakaz',
				'label'     => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {  
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                return  "<a href=# onclick='openWin(\"market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['refZakaz']."\",\"detailWin\");'>".$model['refZakaz']." от ".$model['formDate']."</a>";
                },  
            ],           
            
            [
                'attribute' => 'title',
				'label'     => 'Клиент',
                'format' => 'raw',
            ],           

            [
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
            ],
                        
            
            [
                'attribute' => 'schetNum',
				'label'     => 'Счет клиенту',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) { 
                    if (empty($model['refSchet'])) return "Не указан";
                    return  $model['schetNum']." от ".$model['schetDate'];
                },                

                 
            ],           
            
      ]//columns            
    ]
	);

    echo Collapse::widget([
    'items' => [
        [
            'label' => "Заявки связанные с закупкой: ▼ ",                        
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 


    
    
    
}else "<p>Закупка не привязана. </p>";
?>


