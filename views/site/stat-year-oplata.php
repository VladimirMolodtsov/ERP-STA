<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = 'Сводная статистика поступления оплат за год';

?>
  <h2><?= Html::encode($this->title) ?></h2>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 75px;	
	float:right;
}
		
 
</style>


 <script>
 </script>  

<a class="btn btn-primary"  href='index.php?r=site/stat-year-sales'>По отгрузкам</a>&nbsp;&nbsp;
<a class="btn btn-primary"  style='background: Silver;'  href='index.php?r=site/stat-year-oplata'>По оплатам</a>&nbsp;&nbsp;
<a class="btn btn-primary"  href='index.php?r=site/stat-year-goods'>По товару</a>&nbsp;&nbsp;
<a class="btn btn-primary"  href='index.php?r=site/stat-year-contacts'>По контактам</a>&nbsp;&nbsp;
 

&nbsp;&nbsp;<a href="#" onclick="openEditWin('index.php?r=site/stat-year-sales&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 
<div>
<?php
  $monthTitles = array(
	"1" => "январь",
	"2" => "февраль",
	"3" => "март",
	"4" => "апрель",
	"5" => "май",
	"6" => "июнь",
	"7" => "июль",
	"8" => "август",
	"9" => "сентябрь",
	"10" => "октябрь",
	"11" => "ноябрь",
	"12" => "декабрь"); 

/*Предыдущий год*/    
$cur_m = date('n');
$cur_y = date('Y');
$j=1;
for($i=$cur_m+1;$i<=12;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y-1);
$j++;
}

/*Этот год*/        
for($i=1;$i<=$cur_m;$i++)
{
$atr[$j] = 'm_'.$i;
$lbl[$j] = $monthTitles[$i]." ".($cur_y);
$j++;
}


$grid =  \yii\grid\GridView::widget(
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
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\",\"orgList\")' >".$model['title']."</a>";
                },
            ],		

			
   	        [
                'attribute' => 'S',
				'label'     => 'Всего',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {	                    
                    return number_format($model['S'],0,'.','&nbsp;');
                },
                
                
			],	

            
   	        [
                'attribute' => $atr[1],
				'label'     => $lbl[1],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[2],
				'label'     => $lbl[2],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[3],
				'label'     => $lbl[3],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[4],
				'label'     => $lbl[4],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[5],
				'label'     => $lbl[5],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[6],
				'label'     => $lbl[6],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[7],
				'label'     => $lbl[7],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[8],
				'label'     => $lbl[8],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[9],
				'label'     => $lbl[9],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[10],
				'label'     => $lbl[10],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[11],
				'label'     => $lbl[11],
                'format' => 'raw',
			],	
            
   	        [
                'attribute' => $atr[12],
				'label'     => $lbl[12],
                'format' => 'raw',
			],	
            

		],
    ]
);

echo $grid;
?>
</div>


