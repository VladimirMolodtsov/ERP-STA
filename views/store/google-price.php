<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Товарный прайс';
$curUser=Yii::$app->user->identity;

$zakazRecord = $model->preparePrice();
?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
</style>
    
  
<script>
function setVal(id, val, ed)
{
 openSwitchWin('store/add-gprice-zakaz&zakazId=<?= $model->zakazId ?>&priceid='+id+'&val='+val+'&ed='+ed);
 //document.location.href = 'index.php?r=market/market-zakaz&zakazId=<? $zakazId ?>&orgId=<? $orgId ?>'; 
}
</script>

<p>Состав заказа № <?= $zakazRecord->id ?> от  <?= date("d.m.Y", strtotime($zakazRecord->formDate)) ?>. Клиент <b><?= $model->orgTitle ?> </b> </p>

<?php
Pjax::begin(); 
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $zakazProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'Товар',
				'label' => 'Товар',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
                   if (empty($model['good'])) return $model['initialZakaz'];
				   return $model['good'];
                },
            ],		



            
			[
                'attribute' => 'count',
				'label' => 'К-во',
                'format' => 'raw',
            ],		

    		[
                'attribute' => 'ed',
				'label' => 'Ед. изм.',
                'format' => 'raw',
            ],		
        
    		[
                'attribute' => 'value',
				'label' => 'Цена',
                'format' => 'raw',
            ],		
        ],
    ]
);
Pjax::end();
?>
<a  class='btn btn-primary button' style="width: 125px;" href="index.php?r=market/market-zakaz&noframe=1&zakazId=<?=$model->zakazId?>&orgId=<?=$orgId?>" >В заявку</a>
<hr>

<p>Выберите товар щелкнув на цену. Выбранная позиция вставится в заказ. </p>

<?php
Pjax::begin(); 
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

           
			[
                'attribute' => 'wareType',
				'label' => 'Тип Сырья',
                'format' => 'raw',
            ],		

            
           [
                'attribute' => 'wareTitle',
				'label' => 'Продукция',
                'format' => 'raw',
            ],		
			
            [
                'attribute' => 'wareWeight',
				'label' => 'Вес пачки <br>(1000 л), кг',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return $model['wareType'];
                },
            ],		


            
            [
                'attribute' => 'cntVal2',
				'label' => 'Партнерам, <br> пачка 1000л',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return "<a href='#' onclick='setVal(".$model['id'].",".$model['cntVal2'].",\"руб/пач.\" );'>".$model['cntVal2']."</a>";
                },
            ],		

            [
                'attribute' => 'cntVal3',
				'label' => 'Мелкий опт, <br> пачка 1000л',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{									   
                   return "<a href='#' onclick='setVal(".$model['id'].",".$model['cntVal3'].",\"руб/пач.\");'>".$model['cntVal3']."</a>";
                },
            ],		

            [
                'attribute' => 'cntVal4',
				'label' => 'Розница, <br> пачка 1000л',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
                   return "<a href='#' onclick='setVal(".$model['id'].",".$model['cntVal4'].",\"руб/пач.\");'>".$model['cntVal4']."</a>";
				},
            ],		
        
            

            [
                'attribute' => 'weightVal2',
				'label' => 'Партнерам, <br> руб/кг',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
                   return "<a href='#' onclick='setVal(".$model['id'].",".$model['weightVal2'].",\"руб/кг\");'>".$model['weightVal2']."</a>";
                },
            ],		

            [
                'attribute' => 'weightVal3',
				'label' => 'Мелкий опт, <br> руб/кг',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return "<a href='#' onclick='setVal(".$model['id'].",".$model['weightVal3'].",\"руб/кг\");'>".$model['weightVal3']."</a>";
                },
            ],		

            [
                'attribute' => 'weightVal4',
				'label' => 'Розница, <br> руб/кг',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return "<a href='#' onclick='setVal(".$model['id'].",".$model['weightVal4'].",\"руб/кг\");'>".$model['weightVal4']."</a>";
                },
            ],		
            
            
            
        ],
    ]
);
Pjax::end();
?>