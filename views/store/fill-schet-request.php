<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Счет на закупку товара';
//$this->params['breadcrumbs'][] = $this->title;


/*print_r ($requestData);
return;*/

?>
<style>

</style>

  <h2><?= Html::encode($this->title) ?></h2>
   <p>		
   Товар в заявках: <?= Html::encode($requestData['good']) ?> <br>
   Общее к-во: <?= Html::encode($requestData['sumCount']) ?> <br>
   Ближайшая дата поставки: <?= Html::encode($requestData['minDate']) ?> <br>
  
   <?php $form = ActiveForm::begin(); ?>
	
	<?= $form->field($model, 'goodSchet')->label('Реквезиты счета')?>  
	<?= $form->field($model, 'goodSclad')->label('Закупаемое наименование')?>  
	<?= $form->field($model, 'goodValue')->label('Цена закупки')?>  
  
   <input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:window.close();"/>    
   <?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?> 
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'good')->hiddenInput()->label(false)?>   
   </div>

   <?php ActiveForm::end(); ?>
   
   