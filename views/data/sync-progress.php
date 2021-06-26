<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Сканирование данных из 1C';
?>
  
<h2><?= Html::encode($this->title) ?></h2>
 
<?php
if (!empty ($syncSubtitle)){
echo "<p>".Html::encode($syncSubtitle)."</p>";
}
?>
 
<p> Загружено записей: <?= Html::encode($startRow) ?> 
<?php 
if ($allRecords != 0){
echo "из ";
echo Html::encode($allRecords); 
}
?>
</p>


<form action="index.php" method="GET" name="loadnext" id ="loadnext">
<input type="hidden" name="r"  value="data/sync-progress">
<input type="hidden" name="startRow"  value="<?= Html::encode($startRow) ?>"> 
<input type="hidden" name="allRecords"  value="<?= Html::encode($allRecords) ?>"> 
<input type="hidden" name="mode"  value="2"> 
</form>

<div align=center><img src="img/ajax-loader.gif" onload="document.getElementById('loadnext').submit();"></div>


<?php
/*
echo "<pre> \n";
print_r ($_GET);
		$session = Yii::$app->session;      
		$session->open();		
		$syncSubtitle  = $session->get('syncSubtitle','');                          
		$actionName=$session->get('actionName');
		$retSync = $session->get('syncOplataResult' );		
		$lastOplataTime = $session->get('lastOplataTime');
	    $oplataRefArray = $session->get('oplataRefArray');
	
		print_r ($syncSubtitle);
		echo "\n actionName: \n";
		print_r ($actionName);
		echo "\n retSync: \n";
		print_r ($retSync);
		echo "\n lastOplataTime: \n";
		print_r ($lastOplataTime);
		echo "\n oplataRefArray: \n";
		print_r ($oplataRefArray);
echo "</pre>";		
phpinfo();		

*/
?>


<script>
//document.getElementById('loadnext').submit();
</script> 

