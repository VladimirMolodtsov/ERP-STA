<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $this->title;

$model->loadData();
?>

    <h1><?= Html::encode($this->title) ?></h1>
	
<div class="form-group">


<?php	$form = ActiveForm::begin(); ?>

<div class="row">  
	<div class="col-lg-4">
		<?= $form->field($model, 'emailOP')->label('Почта отдел продаж')?>		
   </div>   

</div>      
<div class="row">  
   	<div class="col-lg-4">
		<?= $form->field($model, 'emailSUP')->label('Почта отдел поставок')?>		
   </div>   
</div>   
<div class="row">  
   <div class="col-lg-4">
		<?= $form->field($model, 'emailCTRL')->label('Почта  контроль')?>		
   </div>      
</div>   
    
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'save-button']) ?>
            </div>
   <?php ActiveForm::end(); ?>
</div>
