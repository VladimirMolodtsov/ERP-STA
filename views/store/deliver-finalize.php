<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Отчет экспедитора';
//$this->params['breadcrumbs'][] = $this->title;

$deliverRecord = $model->prepareDeliver();
?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

  <h2><?= Html::encode($this->title) ?></h2>
   <p>		
  
  </p>
  <?php $form = ActiveForm::begin(); ?>
  
    <div class='row'>
		<div class="col-lg-4 col-md-4">
		<p><b>Дата фактического выполнения </b></p>
			<?= $form->field($model, 'requestDateReal')->textInput(['class' => 'tcal','style'=>'width:200px;'])->label(false)?> 
		</div>
		
		<div class="col-lg-4 col-md-4">
			<?= $form->field($model, 'requestRealSize')->textInput(['style'=>'width:300px; margin:0px; padding:0px; left:0px'])->label('Фактический вес')?>  
		</div>

		<div class="col-lg-4 col-md-4">
			<?= $form->field($model, 'requestCashReal')->textInput(['style'=>'width:300px; margin:0px; padding:0px; left:0px'])->label('Сумма затрат')?>  
		</div>
		
		<div class="col-lg-4 col-md-4">
			<?= $form->field($model, 'requestNote')->textarea(['rows' => 3, 'cols' => 36, 'style'=>'width:300px; margin:0px; padding:0px; left:0px'])->label('Дополнения:')?>    
		</div>
				
	</div>
	
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'id')->hiddenInput()->label(false)?>   
   </div>
   
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?> 
   <?php ActiveForm::end(); ?>
   
