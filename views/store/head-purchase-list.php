<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;

$this->title = 'Реестр закупок';
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
 
.gridcell {
	width: 100%;		
	height: 100%;
	/*background:DarkSlateGrey;*/
}	

.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.gridcell:hover{
	background:DarkSlateGrey;
	color:#FFFFFF;
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
                'attribute' => 'dateCreation',
				'label'     => 'Закупка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                  return "<nobr><a href='#' onclick='openWin(\"\store/head-purchase&noframe=1&id=".$model['id']."\",\"storeWin\");'>".$model['id']." от ".date('d.m.Y', strtotime($model['dateCreation']))."</a></nobr>";     
                }
            ],		

            [
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',

            ],		
            
            [
                'attribute' => 'requestStatus',
				'label'     => 'Согласование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =1 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Формирование";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Cформирован ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="<div class='gridcell' style='background-color:Yellow;' >На согласов ".$statusList[0]['execDate'] ."</div>";
                            break;
                        case 2: 
                            $retVal ="Согласован ".$statusList[0]['execDate'];                            
                            break;
                        case 3: 
                            $retVal ="Отправлен ".$statusList[0]['execDate'];                            
                            break;
                        case 4: 
                            $retVal ="Получен ответ ".$statusList[0]['execDate'];                               
                            break;
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                
			],


            [
                'attribute' => 'schetStatus',
				'label'     => 'Статус счета',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =2 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Не запрошен";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Запрошен ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="Счет получен".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="<div class='gridcell' style='background-color:Yellow;' >На согласов ".$statusList[0]['execDate'] ."</div>";
                            break;
                        case 3: 
                            $retVal ="Согласован ".$statusList[0]['execDate'];                            
                            break;
                        case 4: 
                            $retVal ="Подтверждено ".$statusList[0]['execDate'];                               
                            break;
                        case 5: 
                            $retVal ="В бухгалтер. ".$statusList[0]['execDate'];                               
                            break;
                        case 6: 
                            $retVal ="В реестре ".$statusList[0]['execDate'];                               
                            break;
                        case 7: 
                            $retVal ="Оплачен ".$statusList[0]['execDate'];                               
                            break;
                            
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                
                
			],
         
			[
                'attribute' => 'supplierShetRef',
				'label'     => 'Счет от поставщика',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                    if ($model['supplierShetRef'] == 0) return "<span class='label label-danger'>Нет счета</span>";
                   $strSql = "SELECT schetNum, schetDate   FROM {{%supplier_schet_header}} where id =:refSchet order by id DESC LIMIT 1";                   
                   $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['supplierShetRef'],])->queryAll();                                        
                    return "<span class='label label-success'>".$schetData[0]['schetNum']." от ".$schetData[0]['schetDate']."</span>"; 
                }
            ],		
            
		
            
            [
                'attribute' => 'transportStatus',
				'label'     => 'Доставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =3 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Неизвестно";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="В производстве ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="Готов к отгр. ".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="Трансп. заказан ".$statusList[0]['execDate'];                            
                            break;
                        case 3: 
                            $retVal ="Трансп. на загр. ".$statusList[0]['execDate'];                            
                            break;
                        case 4: 
                            $retVal ="Загрузка начата ".$statusList[0]['execDate'];                               
                            break;
                        case 5: 
                            $retVal ="Загружено ".$statusList[0]['execDate'];                               
                            break;
                        case 6: 
                            $retVal ="Отправлен ".$statusList[0]['execDate'];                               
                            break;
                        case 7: 
                            $retVal ="На разгрузке ".$statusList[0]['execDate'];                               
                            break;
                        case 8: 
                            $retVal ="На складе ".$statusList[0]['execDate'];                               
                            break;
                            
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                
                
			],
            
           [
                'attribute' => 'docStatus',
				'label'     => 'Документы',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
               
                $strSql = 'SELECT execDate, etap FROM {{%purchase_etap}} where stage =4 AND purchaseRef = :purchaseRef ORDER BY etap DESC';
                  
                $statusList = Yii::$app->db->createCommand($strSql, [':purchaseRef' => $model['id'],])->queryAll();                                        
		        if (count($statusList) == 0) return "Неизвестно";
        
        
                    switch ($statusList[0]['etap'])
                    {
                        
                        case 0: 
                            $retVal ="Получены транспортные док ".$statusList[0]['execDate'];
                            break;
                        case 1: 
                            $retVal ="Документы зарегестрированы ".$statusList[0]['execDate'];
                            break;
                        case 2: 
                            $retVal ="Получены документы на товар ".$statusList[0]['execDate'];                            
                            break;
                        case 3: 
                            $retVal ="Поставка закрыта ".$statusList[0]['execDate'];                            
                            break;
                            
                    }
                    
                    return "<div style='font-size:12px;'>".$retVal."</div>"; 
                 }                

			],
            
        ],
    ]
	);
?>   
<br>   
<div class="row">  
   
</div>      
	
