<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;


$curUser=Yii::$app->user->identity;
$this->title = 'Регистрация лида';

if (empty($model->nextContactDate )) $model->nextContactDate = date("d.m.Y", time());
?>


  <h2><?= Html::encode($this->title) ?></h2>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

</script>

 <?php $form = ActiveForm::begin(); ?>  
<div class='row'>
 
  <div class="col-md-7">
		<?= $form->field($model, 'note')->textarea(['id' => 'note','rows' => 10, 'cols' => 25])->label('Комментарий')?>		
        <?= $form->field($model, 'contactId')->hiddenInput(['id' => 'contactId',])->label(false)?>	
        <?= $form->field($model, 'orgId')->hiddenInput(['id' => 'orgId',])->label(false)?>	
  </div>   
  <div class="col-md-1">
  </div>   
  <div class="col-md-4">	 
       <ul>
          <li> Организация/Клиент: <a href='#'  onclick="javascript:openWin('site/org-detail&orgId=<?= $model->orgId ?>', 'contactWin');" > <?= $model->contactOrgTitle ?> </a>
          <li> Телефон: <a href='#'  onclick="javascript:openWin('site/reg-contact&id=<?= $model->orgId ?>&phone=<?=$model->contactPhone?>&contactFIO=<?= Html::encode($model->contactFIO) ?>', 'contactWin');"> <?= $model->contactPhone ?> </a>
          <li> Контактное лицо: <?= $model->contactFIO ?> 
        </ul>   	 
     <HR>
  </div> 
</div>

<div class='row'>
	<div class="col-md-6">		
	</div>   
	<div class="col-md-1">			
     <input class="btn btn-primary"  style="width: 150px; background: ForestGreen;" type="button" value="Рассмотрено" onclick="document.location.href='index.php?r=site/accept-lead&contactId=<?= $model->contactId ?>'; "/>
	</div>     
    <div class="col-md-1">		
    </div>     
	<div class="col-md-1">		    
    	<?= Html::submitButton('Сохранить ', ['class' => 'btn btn-primary', 'style' => 'width: 150px;']) ?>
	</div>   
	<div class="col-md-1">		
    </div>     
	<div class="col-md-1">		
     <input class="btn btn-primary"  style="width: 150px;" type="button" value="Закрыть" onclick="javascript:window.opener.location.reload(false); window.close();"/>
	</div>   
 </div>
  <?php ActiveForm::end(); ?>
   
