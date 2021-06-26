<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Счета - контроль оплаты';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>


<div class="part-header"> Обобщенная статистика</div> 
<br>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getMarketPersonalProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            'userFIO:raw:Менеджер',
            'zakazAll:raw:Заказов всего',
            'zakazMonth:raw:Заказов за месяц',
            'schetAll:raw:Счетов всего',
            'schetMonth:raw:Счетов за месяц',			
        ],
    ]
);
?>

<br>
<div class="part-header"> Заказы</div> 
<br>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getZakazStateProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			
			'id:raw:Номер заказа',					
			[
                'attribute' => 'formDate',
				'label'     => 'Дата заказа',
                'format' => ['datetime', 'php:d-m-Y G:i'],
            ],			
			'title:raw:Клиент',
			'userFIO:raw:Менеджер',

			[
                'attribute' => 'isActive',
				'label'     => 'Обработан',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isActive'] >0 ){ $isFlg = true;}
					else                       { $isFlg = false;}
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
                'attribute' => 'Последний контакт',
                'format' => ['datetime', 'php:d-m-Y'],
                'value' => function ($model, $key, $index, $column) {
				
				$res = Yii::$app->db->createCommand('SELECT MAX(contactDate) from {{%contact}} where ref_org=:ref_org', 
				[':ref_org' => $model['orgId'],])->queryScalar();
                return $res;
                },
            ],		
			
			
	         [
                'attribute' => 'nextContactDate,',
				'label'     => 'Cледующий контакт',
                'format' => ['datetime', 'php:d-m-Y'],
				
            ],
	

	
        ],
    ]
);
?>

<br>
<div class="part-header"> Счета</div> 
<br>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getSchetStateProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
	
			'schetNum:raw:Номер счета',					
			[
                'attribute' => 'schetDate',
				'label'     => 'Дата счета ',
                'format' => ['datetime', 'php:d-m-Y G:i'],
            ],			
			'title:raw:Клиент',
			'userFIO:raw:Менеджер',

			[
                'attribute' => 'isSchetActive',
				'label'     => 'Завершен',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isSchetActive'] ==0 ){ $isFlg = true;}
					else                       { $isFlg = false;}
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
                'attribute' => 'Последний контакт',
                'format' => ['datetime', 'php:d-m-Y'],
                'value' => function ($model, $key, $index, $column) {
				
				$res = Yii::$app->db->createCommand('SELECT MAX(contactDate) from {{%contact}} where ref_org=:ref_org', 
				[':ref_org' => $model['orgId'],])->queryScalar();
                return $res;
                },
            ],		
			
			
	         [
                'attribute' => 'nextContactDate,',
				'label'     => 'Cледующий контакт',
                'format' => ['datetime', 'php:d-m-Y'],
				
            ],
	

			
        	[
                'attribute' => 'id',
				'label'     => 'Текущий статус',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$lastOp = Yii::$app->db->createCommand(
					'SELECT max(refOp) as max_refOp from {{%schet_status}}
					  where refSchet=:refSchet', 
					[':refSchet' => $model['id'] ])->queryOne();


				 $titleOpList = Yii::$app->db->createCommand(
				'SELECT id, opTitle from {{%schetop}} where id>=:refOp  ORDER BY id LIMIT 2', 
					[':refOp' => $lastOp['max_refOp'] ])->queryAll();
                 				
				if (empty ($titleOpList))  {return "&nbsp;";}
							 
				 $retVal = "Выполнено: ".$titleOpList[0]['opTitle'];
				 if (count ($titleOpList) > 1)
				 {
				 $retVal.="<br> Ожидается:  ".$titleOpList[1]['opTitle'];
				 }	 				
				 return $retVal;
                },
            ],					
	
	
        ],
    ]
);
?>
