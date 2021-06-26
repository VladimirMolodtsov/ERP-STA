<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Загрузка данных из cvs';
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

p {
    margin: 5px;
}

 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: rgb(157, 214, 45);
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
 <p>
<p>Файл должен быть в формате csv. Кодировка файла "utf-8".</p>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'csvFile')->fileInput() ?>

    <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>


 <?php
   
 /* $data = $model->getData();
  print_r ($data);
  */

 ?>



</p>