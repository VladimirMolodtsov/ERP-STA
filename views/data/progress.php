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
 
<form action="index.php" method="GET" name="loadnext" id ="loadnext">
<input type="hidden" name="r"  value="<?= $nextForm ?>">
</form>

<div align=center><img src="img/ajax-loader.gif" onload="document.getElementById('loadnext').submit();"></div>
