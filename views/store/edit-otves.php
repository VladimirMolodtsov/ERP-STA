<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Работа с отвесом';
//$this->params['breadcrumbs'][] = $this->title;

$otvesData = $model->loadOtvesData();
//print_r ($otvesData);
//return;

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

</style>

  <h2><?= Html::encode($this->title) ?></h2>
   <p>		
		Товар: <?= Html::encode($otvesData[0]['title']) ?> <br>
		Размер отвеса: <?= Html::encode($otvesData[0]['size']) ?> <?= Html::encode($otvesData[0]['ed']) ?> <br>
		Цена: <?= Html::encode($otvesData[0]['price']) ?> <?= Html::encode($otvesData[0]['edPrice']) ?> <br>
		<?php if ($otvesData[0]['inUse'] == 1) 
		{
		 echo "Отвес зарезервирован: <br>";		
		 echo "&nbsp;&nbsp;&nbsp;&nbsp;Менеджер ".$otvesData[0]['userFIO']."<br>";		
		 echo "&nbsp;&nbsp;&nbsp;&nbsp;Дата резервирования ".date ("d.m.Y h:i",strtotime($otvesData[0]['reservDate']))."<br>";		
		} 
		else
		{
		 echo "Отвес свободен<br>";		
		}
		?>

	</p>
  <?php $form = ActiveForm::begin(); ?>
	
	<?php if ($otvesData[0]['inUse'] == 1) {?>
	<?= $form->field($model, 'actionType')->radio(['label' => 'Освободить отвес', 'value' => 1, 'uncheck' => null]) ?> 
	<?php }?>
	<?= $form->field($model, 'actionType')->radio(['label' => 'Удалить отвес', 'value' => 2, 'uncheck' => null]) ?> 
  
   <input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:window.close();"/>    
   <?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?> 
   <div style='visibility:hidden'>   
   <?= $form->field($model, 'otvesId')->hiddenInput()->label(false)?>   
   </div>

   <?php ActiveForm::end(); ?>
   
   