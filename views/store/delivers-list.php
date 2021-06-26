<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Задания экспедитору';
$curUser=Yii::$app->user->identity;

?>
<style>

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 120px;
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


<h3><?= Html::encode($this->title) ?></h3>


<div style='text-align:right;'>  
	<input class="btn btn-primary"  style="width: 150px;" type="button" value="Создать заявку" onclick="javascript:openWin('store/deliver-zakaz&action=create','deliverZakazWin');"/>
</div>  
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
                'attribute' => 'id',
				'label' => 'Доставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   $val= "<a href='#' onclick='openWin(\"store/deliver-zakaz&id=".$model['id']."\", \"deliverZakazWin\");' >";
				   if (!empty($model['requestNum']))   $val.="Номер: ".$model['requestNum']."<br>";
												else  $val.="Номер: ".$model['id']."<br>";
				   if  (strtotime($model['requestDatePlanned']) > strtotime ('01.01.2001'))
						$val.="Дата доставки:".date("d.m.Y", strtotime($model['requestDatePlanned'])); 
				   	
				   $val.="</a>";
				   return $val;
                },
            ],		
			
			[
                'attribute' => 'requestExecutor',
				'label' => 'Исполнитель',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'title',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },
            ],		
			
			[
                'attribute' => 'id',
				'label' => 'По счету',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{				
     			if (empty($model['schetNum'])) {return;}	
				   $val= "Номер: ".$model['schetNum']." ";
				   $val.="<br> от:".date("d.m.Y", strtotime($model['schetDate'])); 
				   $val.="<br> на сумму:".number_format($model['schetSumm'], 2, '.', ' ') ;
				   return $val;
                },
            ],		
			
            [
                'attribute' => 'deliverSum',
				'label'     => 'Товаров на сумму',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				 return number_format($model['deliverSum'], 2, '.', ' ') ;
                }
            ],


            [
                'attribute' => 'requestStatus',
				'label'     => 'Исполнено  - Ожидается',                
				'filter'=>array(
				"0" => "Создано",
				"1" => "Подгот.  к отгр.",				
				"2" => "Выдано  эксп.",				
				"3" => "В доставке",
				"4" => "Доставлено",
				"5" => "Отчет сдан",
				"6" => "Завершено",
				),

                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
					$val ="";
					switch ($model['requestStatus']) 
					{
					case 0:
						$val = "<nobr> <div class='local_lbl'>Создано</div> <div class='arrow-left'></div> <input class='btn btn-info local_btn'  type=button value='Подгот.  к отгр.' onclick='javascript:openSwitchWin(\"store/deliver-status&status=1&id=".$model['id']."\");'> </nobr>";
				        break;
					case 1:
						$val = "<nobr> <div class='local_lbl' style='border-color:#5bc0de;' >Подгот.  к отгр.</div> <div class='arrow-left'></div> <input class='btn btn-info local_btn' style='background:LightSeaGreen ' type=button value='Выдано  эксп.' onclick='javascript:openSwitchWin(\"store/deliver-status&status=2&id=".$model['id']."\");'> </nobr>";
				        break;
					case 2:
						$val = "<nobr> <div class='local_lbl' style='border-color:LightSeaGreen;'>Выдано  эксп.</div> <div class='arrow-left'></div> <input class='btn btn-info local_btn' style='background:LimeGreen' type=button value='В доставке' onclick='javascript:openSwitchWin(\"store/deliver-status&status=3&id=".$model['id']."\");'> </nobr>";
				        break;

					case 3:
						$val = "<nobr> <div class='local_lbl'  style='border-color:LimeGreen;'>В доставке</div> <div class='arrow-left'></div> <input class='btn btn-success local_btn'  type=button value='Доставлено' onclick='javascript:openSwitchWin(\"store/deliver-status&status=4&id=".$model['id']."\");'> </nobr>";
				        break;

					case 4: 
						$val = "<nobr> <div class='local_lbl' style='border-color:#449d44;'>Доставлено</div> <div class='arrow-left'></div> <input class='btn btn-info local_btn' style='background:Green' type=button value='Отчет сдан' onclick='javascript:openSwitchWin(\"store/deliver-status&status=5&id=".$model['id']."\");'> </nobr>";
				        break;

					case 5:
						$val = "<nobr> <div class='local_lbl' style='border-color:Green;'>Отчет сдан</div> <div class='arrow-left'></div> <input class='btn btn-info local_btn' style='background:ForestGreen' type=button value='Завершено' onclick='javascript:openEditWin(\"store/deliver-finalize&id=".$model['id']."\");'> </nobr>";
				        break;

						
					}	
						

                return $val;
				}
				
            ],

	         [
                'attribute' => 'Действие',
				'label'     => 'Печать',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
							return " <a href='#' class='btn btn-default' onclick='openWin(\"store/deliver-zakaz-print&noframe=1&id=".$model['id']."\", \"deliverZakazWin\");' ><img src='img/printer.png' alt='Печать'></a>";					
					}	

            ],

	

            [
                'attribute' => 'Действие',
				'label'     => 'Удалить',                
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
						return "<input class='btn btn-default ' style='margin-top:5px' type=button value=' X ' onclick='javascript:openSwitchWin(\"store/deliver-delete&id=".$model['id']."\", \"deliverWin\");'>";
					}	

            ],
			
            
        ],
    ]
);

?>