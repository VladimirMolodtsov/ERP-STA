<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Просмотр заявки';
//$this->params['breadcrumbs'][] = $this->title;
$zakazRecord=$model->getZakazRecord();
		 
?>
<style>
 .table-small{
	font-size: 10pt;
	 }
	
.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	
	

 
</style>


  <h2><?= Html::encode($this->title) ?></h2>
  
  Наименование компании <u><strong><?= Html::encode($zakazRecord['title'])?></strong></u> <br>
  Заявка номер  <?= Html::encode($zakazRecord['id'])?>  от <?= Html::encode($zakazRecord['formDate'])?>
	
    <div class="part-header"> Содержание заявки </div> 
    <br>	
    <?php	
	/* <a href='#' id='edit_zakaz'> </a>*/
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
	   
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Вернутся" onclick="javascript:history.back();"/>   