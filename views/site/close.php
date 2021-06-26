<?php


use yii\helpers\Html;
$this->title = "Операция прошла успешно";
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Операция успешно завершена. 
    </p>
	
[js]<script type="text/javascript">
window.opener.location.reload(false); 
window.parent.location.reload();
window.close();
</script>[/js] 
	