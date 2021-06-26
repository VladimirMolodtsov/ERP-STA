<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Список клиентов по которым нет активной работы';
//$this->params['breadcrumbs'][] = $this->title;
$curUser=Yii::$app->user->identity;
if ($curUser->roleFlg & 0x0080)
{
	
 echo "Помошник менеджера";
}

?>
<p>Менеджер <b><?= Html::encode($curUser->userFIO)?></b></p>
<h3><?= Html::encode($this->title) ?></h3>
<style>
.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 20px;
}
</style>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


 <script>
 </script>  
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
                'attribute' => 'title',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\")' >".$model['title']."</a>";
                },
            ],		
				
            [
                'attribute' => 'contactDate',
				'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= date("d-m-Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

			[
	
                'attribute' => 'nextContactDate',
				'label'     => 'Назначеная дата',
                //'format' => ['datetime', 'php:d.m.Y'],
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
				
				 if(strtotime($model['nextContactDate']) < time()-8*60*60*24) return "";
                 return	date ('d.m.Y', strtotime($model['nextContactDate']));
					
				}
				
            ],

			[
	            'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
            ],

			[
	            'attribute' => 'operator',
				'label'     => 'Оператор',                
            ],
			
			[
                'attribute' => 'Сдвинуть',
				'label' => 'Запланировать через:',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				$val = "";
				$val .="<input class='btn btn-primary local_btn'  type=button value=' + ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=0&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn' style='background:ForestGreen' type=button value=' 1 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=1&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn' style='background:Green' type=button value=' 3 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=3&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn' style='background:LimeGreen' type=button value=' 7 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=7&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				$val .="<input class='btn btn-primary local_btn' style='background:Maroon' type=button value=' 15 ' onclick='javascript:openSwitchWin(\"market/event-shift&shift=15&noframe=1&id=".$model['id']."\", \"shiftWin\");'>&nbsp;";
				return  $val;
                },
            ],		
			
        ],
    ]
);
?>