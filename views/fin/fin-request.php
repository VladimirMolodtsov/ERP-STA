<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Документы по счету';
$this->params['breadcrumbs'][] = $this->title;

?>

<style>

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 100px;
}

.local_lbl
{
	
	padding: 2px;
	font-size: 10pt;
	background: white;
	color: black;
	border:1px solid;
	width: 120px;
	border-radius: 4px;
	display:inline-block;
	position:relative;
	top:2px;
	
}

.arrow-left {
  border: 10px solid transparent; 
  border-left-color: steelblue;  
  border-right: 0;
  display:inline-block;  
  margin: -5px 10px;
  }

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 



  <h2><?= Html::encode($this->title) ?></h2>


<br>
<div class="part-header"> Запрос о поступлении денег на счет</div> 
<br>
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

		    [
                'attribute' => 'schetDate',
				'label'     => 'Счет:',
                'format' => 'raw',			
                'value' => function ($model, $key, $index, $column) {
					return "<nobr>№ ".$model['schetNum']." от ".date("d.m.Y",strtotime($model['schetDate']))."</nobr><br> На сумму: ".$model['schetSumm']."<br>"; 				
                },
            ],			
						
			
			'title:raw:Клиент',
			'userFIO:raw:Менеджер',

		    [
                'attribute' => 'isOplata1',
				'filter'=>array(
				"0" => "Нет",
				"1" => "Да",				
				),

				'label'     => 'Оплата:',
                'format' => 'raw',			
				
                'value' => function ($model, $key, $index, $column) {
				
				$list = Yii::$app->db->createCommand('SELECT oplateSumm, oplateDate, oplateNum  from {{%oplata}} where refSchet=:refSchet', 
				[':refSchet' => $model['schetId'],])->queryAll();
				$ret="";
							
				if (count($list) == 0 ) {return "N/A";}							
				
				for ($i=0; $i<count($list); $i++ )
				{															
					$ret = "<nobr>№ ".$list[0]['oplateNum']." от ".date("d.m.Y",strtotime($list[0]['oplateDate']))."</nobr><br> На сумму: ".$list[0]['oplateSumm']."<br>"; 				
				}
				return $ret;
                },

            ],			
		
		    [
                'attribute' => 'isSupply1',
				'filter'=>array(
				"0" => "Нет",
				"1" => "Да",				
				),

				'label'     => 'Поставка:',
                'format' => 'raw',			
				
                'value' => function ($model, $key, $index, $column) {
				
				$list = Yii::$app->db->createCommand('SELECT supplySumm, supplyDate, supplyNum  from {{%supply}} where refSchet=:refSchet', 
				[':refSchet' => $model['schetId'],])->queryAll();
				$ret="";
							
				if (count($list) == 0 ) {return "N/A";}							
				
				for ($i=0; $i<count($list); $i++ )
				{								
					$ret = "<nobr>№ ".$list[0]['supplyNum']." от ".date("d.m.Y",strtotime($list[0]['supplyDate']))."</nobr><br> На сумму: ".$list[0]['supplySumm']."<br>"; 				
				}
				return $ret;
                },

            ],			

			
			
			[
                'attribute' => 'isConfirmed',
				'filter'=>array(
				"0" => "Нет",
				"1" => "Да",				
				),

				'label' => 'Подтверждено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				if ($model['isConfirmed'] == 0) 
				{
				return "<input class='btn btn-success local_btn'  type=button value='Подтвердить' 
						onclick='javascript:openSwitchWin(\"fin/fin-confirm&status=1&schetId=".$model['schetId']."\");'>"; 
				}
				
				return "<input class='btn btn-default local_btn'   type=button value='Отменить' 
						onclick='javascript:openSwitchWin(\"fin/fin-confirm&status=0&schetId=".$model['schetId']."\");'> "; 

                },
            ],		
			
	
        ],
    ]
);
?>
