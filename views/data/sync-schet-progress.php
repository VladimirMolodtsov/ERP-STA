<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Сканирование данных из 1C - Список счетов';
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
 
<p> Загружено записей: <?= Html::encode($startRow) ?> из <?= Html::encode($allRecords) ?></p>


<form action="index.php" method="GET" name="loadnext" id ="loadnext">
<input type="hidden" name="r"  value="<?PHP 
if (empty($mode)) {$mode =1;}
if ($mode == 1) {echo "data/import-schet";}
if ($mode == 2) {echo "data/market-schet-sync";}
if ($mode == 3) {echo "data/load-schet-activity";}
?>"> 

<input type="hidden" name="startRow"  value="<?= Html::encode($startRow) ?>"> 
<input type="hidden" name="mode"  value="<?= Html::encode($mode) ?>"> 
</form>

<div align=center><img src="img/ajax-loader.gif" ></div>

 <script>
document.getElementById('loadnext').submit();
</script> 

<div id="loader" onload="document.getElementById('loadnext').submit();">
</div>