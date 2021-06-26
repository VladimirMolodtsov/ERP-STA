<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Привязка заказа на закупку';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setRequest(id) {
	window.parent.closeRequestList(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $this,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                        
			[
                'attribute' => 'goodTitle',
				'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                return "<a href='#' onclick='javascript:setRequest(\"".$model['id']."\");' >".$model['goodTitle']."</a>";
                },
                
            ],	

			[
                'attribute' => 'count',
				'label' => 'К-во',
                'format' => 'raw',
            ],	
     
			[
                'attribute' => 'sklad',
				'label' => 'Склад',
                'format' => 'raw',
            ],	

			
			[
                'attribute' => 'requestNum',
				'label' => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                return "<a href='#' onclick='javascript:setRequest(\"".$model['id']."\");' >".$model['requestNum']." от ".$model['formDate']."</a>";
                },
                
            ],
			

            [
                'attribute' => 'orgTitle',
				'label' => 'Клиент',
                'format' => 'raw',                
            ],	
            
        ],
    ]
);

?>
