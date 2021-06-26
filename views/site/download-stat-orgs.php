<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Выгрузка данных по сводной статистике контактов с клиентами по организациям';
?>
<style>
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

 <p>
     Файл подготовлен, вы можете скачать его по ссылке:
	  <a href="<?php 	echo $fname; ?>"> <?php echo $fname; ?></a>
</p>