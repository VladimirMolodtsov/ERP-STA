<?php


use yii\helpers\Html;
$this->title = "Операция прошла успешно";
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Операция успешно завершена. 
    </p>
	
<script type="text/javascript">
window.opener.location.reload(false); 
//window.parent.location.reload(false); 
window.opener.focus();
window.close();
</script> 
	