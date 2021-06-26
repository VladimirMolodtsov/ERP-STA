<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Счета - контроль оплаты';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>

<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		    
            'title:raw:Организация',
            'have_phone:raw:Телефонов',
            'razdel:raw:Раздел',
            'area:raw:Область',			
			'city:raw:Город',			
			'schetCount:raw:Число счетов',			
            [
                'attribute' => 'schetLastDate',
				'label'     => 'Дата последнего заказа ',
                'format' => ['datetime', 'php:d-m-Y'],
            ],

/*			[
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
*/
			
        ],
    ]
);
?>