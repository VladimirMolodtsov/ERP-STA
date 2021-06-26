<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Заявки на отгрузку';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>
<style>

.local_btn {
	font-size: 12px;
	margin:4px;
	padding:4px;
	width:100px;
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
 
<script type="text/javascript" src="phone.js"></script>  

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
                'attribute' => 'requestId',
				'label' => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				 $val ="<b>".$model['requestId']."</b>"." от ".date("d.m.y",strtotime($model['requestDate']));
				 if (!empty ($model['execNum'])) $val.="<br>".$model['execNum'];
				 return $val;
                }
            ],	
			
			[
                'attribute' => 'schetNum',
				'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				 return $model['schetNum']." от ".date("d.m.Y",strtotime($model['schetDate']))."<br> на сумму: ".number_format($model['schetSumm'], 2, '.', ' ');
                }
            ],	
			
			
            [
                'attribute' => 'supplyDate',
				'label'     => 'Дата отгрузки',
                'format' => ['datetime', 'php:d.m.Y'],
            ],			
			[
                'attribute' => 'title',
				'label' => 'Клиент',
                'format' => 'raw',
            ],	
			[
                'attribute' => 'userFIO',
				'label' => 'Менеджер',
                'format' => 'raw',
            ],	
            
			
            [
                'attribute' => 'supplyState',
				'label'     => 'Статус',                
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
					$val ="";
					if ($model['supplyState'] == 0) 
					{
						return "<nobr><div class='local_lbl' style='border-color:Black;' >Ожидает</div><div class='arrow-left'></div><input class='btn btn-warning local_btn'  type=button value='Принято' onclick='javascript:openWin(\"store/supply-request&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>";
				    }  
	
/*	
  0x00001 - Принята к исполнению
  0x00002 - Передана экспедитору
  0x00004 - Отказ
*/  

					if ($model['supplyState'] & 0x00004) 
					{
						return "<nobr><input class='btn btn-danger local_btn'  type=button value='Отказ' onclick='javascript:openWin(\"store/supply-request&actor=2&id=".$model['requestId']."\", \"supplyWin\");'></nobr>";
				    }  

					if ($model['supplyState'] & 0x00008) 
					{
						return "<nobr><div class='local_lbl' style='border-color:ForestGreen;' >Доставлен </div><div class='arrow-left'></div><input class='btn btn-success local_btn' style='background-color: ForestGreen;' type=button value='Завершить' onclick='javascript:openWin(\"store/supply-request&viewMode=deliver&id=".$model['requestId']."\", \"supplyWin\");'></nobr><br>
						        Работа будет завершена после закрытия счета.";
				    }  
					
					if ($model['supplyState'] & 0x00002) 
					{
						return "<nobr><div class='local_lbl' style='border-color:#5bc0de;' >Доставляется </div><div class='arrow-left'></div><input class='btn btn-success local_btn'  type=button value='Доставлен' onclick='javascript:openWin(\"store/supply-request&viewMode=deliver&id=".$model['requestId']."\", \"supplyWin\");'></
						nobr>";
				    }  
					
					if ($model['supplyState'] & 0x00001) 
					{
					   return "<nobr><div class='local_lbl' style='border-color:Green;' >Принято </div><div class='arrow-left'></div><input class='btn btn-success local_btn'  type=button value='Доставляется' onclick='javascript:openWin(\"store/supply-request&viewMode=accepted&id=".$model['requestId']."\", \"supplyWin\");'></
					   nobr>";
				    }  
																

                return "";
				}
				
            ],

 
            [
                'attribute' => 'supplyState',
				'label'     => 'Доставка',                
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
					$val ="";

					if ($model['supplyState'] & 0x00008) return "";
					if ($model['supplyState'] & 0x00004) return "";
					
					$inDeliver = Yii::$app->db->createCommand(
					'Select sum(requestGoodValue*requestCount) from {{%request_deliver}}, {{%request_deliver_content}} where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef and refSchet = :refSchet')
					->bindValue(':refSchet', $model['refSchet'])										
					->queryScalar();

					if ($inDeliver >= $model['schetSumm']) {$color ="ForestGreen";}
													else   {$color ="Crimson";}
					$val .="Доставляется товаров на сумму:  <font color =".$color.">". number_format($inDeliver, 2, '.', ' ')."</font> ";
					
					
					if ($model['supplyState'] & 0x00002) 
					{
						return $val ."<div style='text-align:right'><input class='btn btn-primary local_btn' style='width:175px;' type=button value='Создать доставку' onclick='javascript:openWin(\"store/deliver-zakaz&action=create&schetId=".$model['refSchet']."\", \"deliverWin\");'></div>";
				    }  

                return "";
				}
				
            ],

 
			
            
        ],
    ]
);

?>