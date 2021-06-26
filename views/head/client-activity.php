<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Активность клиентов';
//if (Yii::$app->user->isGuest == true){ return;}
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">


</script> 
 
<style>



</style>


<?php 
echo $model->printSavedClientActivity($provider, $model);

?>

<br>
<div class='row'>
  <div class='col-md-6'><a href="#" onclick="openEditWin('index.php?r=head/client-activity&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> </div>
</div>
