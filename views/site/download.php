<?php


use yii\helpers\Html;
$this->title = "Выгрузка файла";
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
		<a href='<?= $url ?>'> Файл сформирован. Для выгрузки нажмите на эту ссылку.</a>
    </p>

<?php if($redirect ==1){ ?>	
<script type="text/javascript">
document.location.href="<?= $url ?>"; 
</script>
<?php } ?>

	