<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Маркетинг - анализ продаж по городам';
$this->params['breadcrumbs'][] = $this->title;

$zakazSummary = $model->getSchetSummary();
?>
  <h2><?= Html::encode($this->title) ?></h2>
  
  <p>
   Суммарно по базе:&nbsp;&nbsp;&nbsp;
   <u>Есть заказ:</u> <b><?= Html::encode($zakazSummary['haveZakaz']) ?> </b> &nbsp;&nbsp;&nbsp;
   <u>Есть счет:</u> <b><?= Html::encode($zakazSummary['haveSchet']) ?> </b> &nbsp;&nbsp;&nbsp;
   <u>Конверсия:</u> <b><?= Html::encode($zakazSummary['conversion']) ?> </b>
  </p>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,		
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		    
            'areas:raw:Область',
			'citys:raw:Город',
			[
                'attribute' =>'haveZakaz',
                'label' => 'Есть заказы',
				'format' =>['decimal', 0]                 
	        ],

			[
                'attribute' =>'haveSchet',
                'label' => 'Есть счет',
				'format' =>['decimal', 0]                 
	
	        ],					
			[
                'attribute' =>'conv',
                'label' => 'Конверсия',
				'format' =>['decimal', 2]                 
	        ]		
						
			
	 ],
    ]
);
?>