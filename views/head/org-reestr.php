<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Реестр клиентов';
//if (Yii::$app->user->isGuest == true){ return;}
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">


</script> 
 
<style>



</style>


<?php 
echo $model->printSavedClientReestr($provider, $model);

?>

