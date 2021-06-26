<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$model->loadLeadData($id); 
?>

<script>
</script>

<p> <?= $model->contactFIO ?> </p>
<p> <?= $model->contactPhone ?>  <?= $model->contactEmail ?> </p>
<p> <?= $model->note ?> </p>
