<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Синхронизация с 1с';
?>
 
 <h2><?= Html::encode($this->title) ?></h2>
 
  
  <?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        //'filterModel' => $model,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],		    
            'title:raw:Заголовок',
            [
                'attribute' => 'schetNum',
                'format' => 'raw',
				'label' => 'Номер счета',
                'value' => function ($model, $key, $index, $column) {
				return "<a href='index.php?r=market/market-schet&id=".$model['id']."'>".$model['id']."</a>";  	
				},				
            ],		
		    'schetDate:raw:Дата',
			'schetSumm:raw:Сумма',
		    'ref1C:raw:Ссылка в 1С',
            [
                'attribute' => 'refManager',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				return  Yii::$app->db->createCommand('SELECT userFIO from {{%user}} where id=:refManager  ', 
				[':refManager' => $model['refManager'],])->queryScalar();
                },
            ],		
			
        ],
		
    ]
);

?>
  
  
 