<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Результат синхронизации с 1С';
?>
 
 <h2><?= Html::encode($this->title) ?></h2>
 
 
 
<?php if (!empty($resultArray['syncClientResult'])) {?>  
 <p> <b>Клиенты:</b></p>
 <ul>
	<li> Сканировано записей <?= $resultArray['syncClientResult']['allRecords'] ?>
	<li> Изменено/добавлено клиентов  <?= Html::encode($resultArray['syncClientResult']['updatedClients']) ?>
 </ul>
 <?php }?>
 
 
 <?php if (!empty($resultArray['syncSchetResult'])) {?>
 <p> <b>Счета:</b></p>
 <ul>
	<li> Сканировано записей <?= Html::encode($resultArray['syncSchetResult']['allRecords']) ?>
	<li> Изменено/добавлено счетов  <?= Html::encode($resultArray['syncSchetResult']['updatedSchet']) ?>
 </ul>
 <?php }?>
 
 <?php if (!empty($resultArray['syncOplataResult'])) {?>
 <p> <b>Оплаты:</b></p>
 <ul>
	<li> Сканировано записей <?= Html::encode($resultArray['syncOplataResult']['allRecords']) ?>
	<li> Изменено/добавлено oплат  <?= Html::encode($resultArray['syncOplataResult']['updatedOplata']) ?>
 </ul>
 <?php }?>
 
 
 <?php if (!empty($resultArray['syncSupplyResult'])) {?>
 <p> <b>Отгрузки:</b></p>
 <ul>
	<li> Сканировано записей <?= Html::encode($resultArray['syncSupplyResult']['allRecords']) ?>
	<li> Изменено/добавлено oплат  <?= Html::encode($resultArray['syncSupplyResult']['updatedSupply']) ?>
 </ul>
 <?php }?>
 
 

<?php if (!empty($resultArray['syncSupplierResult'])) {?>  
 <p> <b>Поставщики:</b></p>
 <ul>
	<li> Сканировано записей <?= $resultArray['syncSupplierResult']['allRecords'] ?>
	<li> Изменено/добавлено клиентов  <?= Html::encode($resultArray['syncSupplierResult']['updatedClients']) ?>
 </ul>
 <?php }?>

 
 <?php if (!empty($resultArray['syncSupplierSchetResult'])) {?>  
 <p> <b>Счета поставщиков:</b></p>
 <ul>
	<li> Сканировано записей <?= $resultArray['syncSupplierSchetResult']['allRecords'] ?>
	<li> Изменено/добавлено записей  <?= Html::encode($resultArray['syncSupplierSchetResult']['updatedRecord']) ?>
 </ul>
 <?php }?>

 <?php if (!empty($resultArray['syncSupplierOplataResult'])) {?>  
 <p> <b>Оплаты поставщикам:</b></p>
 <ul>
	<li> Сканировано записей <?= $resultArray['syncSupplierOplataResult']['allRecords'] ?>
	<li> Изменено/добавлено записей  <?= Html::encode($resultArray['syncSupplierOplataResult']['updatedRecord']) ?>
 </ul>
 <?php }?>
 
 <?php if (!empty($resultArray['syncSupplierWaresResult'])) {?>  
 <p> <b>Поступление товара:</b></p>
 <ul>
	<li> Сканировано записей <?= $resultArray['syncSupplierWaresResult']['allRecords'] ?>
	<li> Изменено/добавлено записей  <?= Html::encode($resultArray['syncSupplierWaresResult']['updatedRecord']) ?>
 </ul>
 <?php }?>
 
<p> Синхронизация завершена</p> 
 
