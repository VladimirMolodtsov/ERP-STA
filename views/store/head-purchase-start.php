<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Закупки - управление';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;

    
$leafValue=$model->getLeafValue();    
 ?>
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


</script> 

<h2><?= Html::encode($this->title) ?></h2>



<div class="row">  
	<div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px" type="button" value="Запросы на закупку <?= $leafValue['purchase_zakaz']?> / <?= $leafValue['purchase_zakaz_all']?>" onclick="javascript:openWin('store/head-purchase-zakaz-list','childWin');"/>
   </div>   

	<div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px;" type="button" value="Активные закупки <?= $leafValue['purchase']?> / <?= $leafValue['purchase_all']?>" onclick="javascript:openWin('store/head-purchase-list','childWin');"/>
   </div>   
   
</div>      
<hr>
