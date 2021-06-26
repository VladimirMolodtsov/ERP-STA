<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Сканирование данных в Google';
?>
  
<h2><?= Html::encode($this->title) ?></h2>
 
</p>

<script type="text/javascript">

function startSync()
{

  document.getElementById('loadnext').submit();
}

</script> 

<form action="index.php" method="GET" name="loadnext" id ="loadnext">
<input type="hidden" name="r"  value="<?= $syncUrl ?>">
</form>

<div align=center><img src="img/ajax-loader.gif" onload="startSync();"></div>


<script>
//document.getElementById('loadnext').submit();
</script> 

