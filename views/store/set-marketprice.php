<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Среднерыночная цена';
//$this->params['breadcrumbs'][] = $this->title;

$marketPriceRecord = $model->loadStoreRecord();
//print_r ($otvesData);
//return;

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

  <h2><?= Html::encode($this->title) ?></h2>
   <p>		
		Товар: <?= Html::encode($marketPriceRecord->title) ?> <br>
		Закупочная цена: <?= Html::encode($marketPriceRecord->price) ?> за <?= Html::encode($marketPriceRecord->ed) ?> <br>
	</p>
   <?php $form = ActiveForm::begin(); ?>
	
	<?= $form->field($model, 'marketPrice')->label('Среднерыночная цена')?>   
  
   <input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:window.close();"/>    
   <?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?> 
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'id')->hiddenInput()->label(false)?>   
   </div>

   <?php ActiveForm::end(); ?>
   
   