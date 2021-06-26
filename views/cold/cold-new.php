<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Добавление нового контакта';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;


?>
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
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 5px;
}
 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: rgb(216, 96, 73);
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
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
   
   