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
 
 <p> Файл: <?= Html::encode($fname) ?></p>
 <p> Загружено организаций: <?= Html::encode($from) ?></p>

<form action="index.php" method="GET" name="loadnext" id ="loadnext">
<input type="hidden" name="r"  value="site/csv-parse"> 
<input type="hidden" name="from"  value="<?= Html::encode($from) ?>"> 
<input type="hidden" name="fname" value="<?= Html::encode($fname) ?>"> 
</form>

<div align=center><img src="img/ajax-loader.gif" ></div>

 <script>
document.getElementById('loadnext').submit();
</script> 

<div id="loader" onload="document.getElementById('loadnext').submit();">
</div>