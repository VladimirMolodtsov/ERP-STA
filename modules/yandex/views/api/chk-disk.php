<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Проверка загрузки';
$this->params['breadcrumbs'][] = $this->title;

//$res = $model->chkUpload();
?>

<pre>
<?php
    $res = $model->chkUpload();

    print_r($res);
?>
</pre>
