<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Холодные звонки - заявка на счет';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>

  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		    
            'title:raw:Организация',
			'schetNum:raw:Номер счета',
            [
                'attribute' => 'schetDate',
				'label'     => 'Дата ',
                'format' => ['datetime', 'php:d-m-Y'],
            ],

			[
                'attribute' => 'isOplata',
				'label'     => 'Оплачен ',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isOplata'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
			
			
			[
                'attribute' => 'id',
				'label' => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=market/market-schet&id=".$model['id']."'>Взять</a>";
                },
            ],		
			
        ],
    ]
);
?>