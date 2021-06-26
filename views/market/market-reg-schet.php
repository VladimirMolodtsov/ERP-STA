<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Регистрация счёта';
$this->params['breadcrumbs'][] = $this->title;

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>
.button {
    background-color: #e7e7e7;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px px;
	width: 150px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 



 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: ForestGreen;
	font-size: 14pt;
 }
 
 .item-header{
	 
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
.table-small{
	font-size: 10pt;
	 }
 
  .contact_title {
    margin:5px 0px;
    padding:10px;
	font-size: 10pt;    
}
  .contact_text {
    margin:5px 0px;
    padding:10px;
    border:1px solid #ffbc80;
    background: Beige;
	font-size: 10pt;    
}

 
 .phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
	font-size: 10pt;    
}
/* кликабельный текст */
.phones {
    color:#f70;
    cursor: help
	font-size: 10pt;    
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;
}
 
.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	
	 
</style>

  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  <p>Цель: регистрация счета.</p>
  
  <div class="item-header"> Наименование компании <u><strong><?= Html::encode($record->title)?></strong></u></div>
   <br>
   <div class="part-header"> Данные счета </div>    
   <br>
	<?= $form->field($model, 'schetINN')->label('ИНН')?>  
	<?= $form->field($model, 'schetNumber')->label('Номер выставленного счета')?>  	
	<?= $form->field($model, 'schetDate')->textInput(['class' => 'tcal',])->label('Дата выставленного счета ')?>


   <br>
   <div class="part-header"> Согласованная заявка</div>    
	
    <?php		
	echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getZakazDetailProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			
   			[
                'attribute' => 'initialZakaz',
				'label'     => 'Начальный заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if (empty(trim($model['initialZakaz']))){$val="-";}
					else {$val=$model['initialZakaz'];}
					if ($model['isActive'] == 1) 
					{
                    return $val;
					}
					return "<div class='nonActiveCell'>".$val." </div>";
					
                },
            ],		

					
			[
                'attribute' => 'good',
				'label'     => 'Предложенный товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if (empty(trim($model['good']))){$val="-";}
					else {$val=$model['good'];}
					if ($model['isActive'] == 1) 
					{
                    return $val;
					}
					return "<div class='nonActiveCell'>".$val." </div>";
					
                },
	
            ],		

			[
                'attribute' => 'spec',
				'label'     => 'Спецификация',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'count',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
			
			[
                'attribute' => 'ed',
				'label'     => 'Ед.изм',
                'format' => 'raw',
            ],		
			
			[
                'attribute' => 'value',
				'label'     => 'Цена',
                'format' => 'raw',
            ],		
							
			[
                'attribute' => 'dopRequest',
				'label'     => 'Доп. условия',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'dostavka',
				'label'     => 'Доставка',
                'format' => 'raw',
            ],		
			
							
        ],
    ]
	);
	?>

	
   <div class="item-header"> Заполнил<div>  
   <p><?= Html::encode($curUser->userFIO)?></p> 
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>   
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'orgId')->hiddenInput()->label(false)?>   
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'eventId')->hiddenInput()->label(false)?> 
   </div>
   
   <?php ActiveForm::end(); ?>
   
   