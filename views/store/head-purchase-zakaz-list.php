<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;

$this->title = 'Перечень запросов на закупку товара - управление';
?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

.btn-small {	
	padding: 2px;	 
	font-size: 10pt;	
} 
 
.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}
</style>

<script type="text/javascript">
</script>	

<h3><?= Html::encode($this->title) ?></h3>

<?php  
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'zakazDate',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                  return "<nobr><a href='#' onclick='openWin(\"\store/head-purchase-zakaz&noframe=1&id=".$model['id']."\",\"storeWin\");'>".$model['id']." от ".date('d.m.Y', strtotime($model['zakazDate']))."</a></nobr>";     
                }
            ],		
            
			[
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'filter' => $model->getWareTitleList(),
                'value' => function ($model, $key, $index, $column) {
                    return "<div style='font-size:12px;'>".$model['wareTitle']."</div>";
                }
            ],		
            
            [
                'attribute' => 'wareEd',
				'label'     => 'Ед.изм.',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		


            [
                'attribute' => 'status',
				'label'     => 'Статус',
                'format' => 'raw',
                //'options' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {
                    
                    $retVal="N/A";
                    switch ($model['status'])
                    {
                        
                        case 0: 
                            $retVal ="<div class ='gridcell' style=''>В работе</div>";
                            break;
                        case 1: 
                            $retVal ="<div class ='gridcell' style='background:Yellow'>На согласов.</div>";
                            break;
                        case 2: 
                            $retVal ="<div class ='gridcell' style=''><b>Согласован</b></div>";
                            break;
                        case 4: 
                            $retVal ="<div class ='gridcell' style='background:DarkOrange;'>На доработке</div>";
                            break;
                        case 8: 
                            $retVal ="<div class ='gridcell' style='background:DarkGreen; color:white;'>В закупке</div>";
                            break;
                    }
                    
                    return "".$retVal."</div>"; 
                 }                
			],

            [
                'attribute' => 'Note',
				'label'     => 'Коментарий',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return mb_substr($model['zakazNote'],0,32,'UTF-8');
                }
            ],		

            [
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
                'filter' => $model->getOrgTitleList(),
            ],		

            [
                'attribute' => 'curentValue',
				'label'     => 'Цена',
                'format' => 'raw',
            ],		
                       
        ],
    ]
	);
?>   
<br>   
<div class="row">  

    <div class="col-md-3 button_menu">
		<input class="btn btn-primary"  style="width:220px;" type="button" value="Новый заказ" onclick="javascript:openWin('store/purchase-zakaz&noframe=1','storeWin');"/>
   </div>   
   
</div>      
	
