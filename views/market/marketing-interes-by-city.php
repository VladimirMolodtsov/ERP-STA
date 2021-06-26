<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Маркетинг - интерес клиентов по городам';
$this->params['breadcrumbs'][] = $this->title;

$interesSummary = $model->getInteresSummary();
?>
  <h2><?= Html::encode($this->title) ?></h2>
  
  <p>
   Суммарно по базе:&nbsp;&nbsp;&nbsp;
   <u>Обработано организаций:</u> <b><?= Html::encode($interesSummary['haveContact']) ?> </b> &nbsp;&nbsp;&nbsp;
   <u>Есть интерес:</u> <b><?= Html::encode($interesSummary['haveInteres']) ?> </b> &nbsp;&nbsp;&nbsp;
   <u>Отказ:</u> <b><?= Html::encode($interesSummary['haveReject']) ?> </b> &nbsp;&nbsp;&nbsp;
   <u>Конверсия:</u> <b><?= Html::encode($interesSummary['conversion']) ?> </b>
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
                'attribute' =>'haveContact',
                'label' => 'Всего обработано организаций',
				'format' =>['decimal', 0]                 
	
	        ],		
			[
                'attribute' =>'haveInteres',
                'label' => 'Есть интерес',
				'format' =>['decimal', 0]                 
	
	        ],					
			[
                'attribute' =>'haveReject',
                'label' => 'Отказ',
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