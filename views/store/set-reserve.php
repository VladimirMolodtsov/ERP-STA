<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Резервирование товара';
//$this->params['breadcrumbs'][] = $this->title;

$storeRecord = $model->loadStoreRecord();
//$model->size = $model->noOtves;

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

  <h2><?= Html::encode($this->title) ?></h2>
   <p>		
		Товар: <?= Html::encode($storeRecord->title) ?> <br>
		Цена: <?= 1.2*floatval($storeRecord->price)  ?> <br>
		Всего остаток на складе: <?= Html::encode($storeRecord->amount) ?> <?= Html::encode($storeRecord->ed) ?> <br>
		Доступно: <?= Html::encode($model->noOtves) ?> <?= Html::encode($storeRecord->ed) ?> <br>
  </p>
  <?php $form = ActiveForm::begin(); ?>
  
	<?= $form->field($model, 'size')->label('Обьем резерва')?>  
	
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'id')->hiddenInput()->label(false)?>   
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>   
   </div>
   
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?> 
   <?php ActiveForm::end(); ?>
   
   