<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Добавление нового контакта';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;


?>
<style>
 
</style>



  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  
   <?= $form->field($model, 'orgTitle')->label('Название организации')?>

   <div class="part-header"> Информация о контакте </div>	
   
   <?= $form->field($model, 'contactPhone')->label('Телефон')?>
   <?= $form->field($model, 'contactEmail')->label('e-mail')?>   
   <?= $form->field($model, 'contactFIO')->label('Контактное лицо')?>

   <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
   
   <div class="part-header"> Адрес</div>	
   
   <?= $form->field($model, 'area')->label('Область')?>   
   <?= $form->field($model, 'city')->label('Город')?>   
   <?= $form->field($model, 'adress')->label('Адрес')?>   
   
   <div class="part-header"> Дополнительная информация</div>	
   
   <?= $form->field($model, 'phoneList')->label('Другие телефоны (через запятую)')?>   
   <?= $form->field($model, 'urlList')->label('Адреса сайтов (через запятую)')?>
   <?= $form->field($model, 'razdelList')->label('Разделы (через запятую)')?>
   
      
   <div class="item-header"> Заполнил<div>  
   <p><?= Html::encode($curUser->userFIO)?></p> 
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
   
   <?php ActiveForm::end(); ?>
   
   