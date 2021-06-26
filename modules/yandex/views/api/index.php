<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Ip- телефония';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<pre>
<?PHP 

$balance= $model->getBalance() ;

//$answer= $model->getDirectNumbersStatus();
//$answer= $model->getPbxInternal();
$answer= $model->getPbxStatus(101);

print_r($answer);

?>

</pre>

<p>Текущий баланс <?= $balance->balance ?> <?= $balance->currency ?></p>
