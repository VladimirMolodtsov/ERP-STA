<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */


use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

</div>

<input class="btn btn-primary"  style="width: 150px;" type="button" value="Закрыть" onclick="window.close(); history.back();"/>

<script>

window.resizeTo(800,600);

</script>