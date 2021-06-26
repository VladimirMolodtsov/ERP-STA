<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\AppAsset;

$this->title = 'Форма первого личного контакта';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;
$moduleText=$model->getModuleText();

?>
<style>
.button {
    background-color: #e7e7e7;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px px;
	width: 150px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 
 .button_menu{
    padding: 25px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: rgb(63, 81, 181);
	font-size: 14pt;
 }
 .phone {
	 font-size: 10pt;
 }
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
</style>

<script>

function setPhone(phone)
{
  document.forms["w0"]["coldinitform-currentphone"].value=phone;
  document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w0"]["coldinitform-currentphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}

</script>

  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  <p>Цель: выйти на отдел продаж. Получить е-почту, телефон и контактного лица отдела продаж.</p>
  Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u> <br>
  <div class="item-header"> Телефоны:  
     <p class="phone">
	 <?php
	 $phoneList=$model->getCompanyPhone($record->id);
	 $cnt=count($phoneList);
     if ($cnt > 10) {$cnt = 10;}
	 for ($i=0;$i<$cnt;$i++)
	 {		        
	    echo "<a href='#' onclick='javascript:setPhone(".Html::encode($phoneList[$i]["phone"]).");'>".Html::encode($phoneList[$i]["phone"])."</a>";	    
    	if ($phoneList[$i]["status"] == 1){echo " <font color='green'>*</font>";}
		if ($phoneList[$i]["status"] == 2){echo " <font color='red'>*</font>";}
		echo " &nbsp; \n";
	 }
	 ?>  
	 </p>
	 
  </div> 
   <table border=0 style="border:0px" width=100%>
	<tr>
	<td ><?= $form->field($model, 'currentPhone')->label('Телефон по которому состоялся контакт ')?> </td>
	<td width="150px"  > <div style="position:relative; top:5px; left:10px;"> &nbsp; <input class="btn btn-primary" style="width: 100px;" type="button" value="Позвонить" 
		onclick="javascript:doCall();"/> </div></td>
	</tr>
  </table>
  <div class="part-header"> Речевой модуль первого личного контакта </div>
  <br>
  <p>
    <?php echo $moduleText; ?>;
  </p>
   
  
   
  <div class="part-header"> Результаты разговора</div>
  
   <table border=0 style="border:0px" width=100%>
   <tr>
   <td >  
    <p> Заполняется только если состоялся разговор</p>     
     <?= $form->field($model, 'email')->label('E-mail (почта отдела закупок)')?>
     <?= $form->field($model, 'contactPhone')->label('Телефон снабжения ')?> 
     <?= $form->field($model, 'name')->label('Имя специалиста снабженца ')?>
    </td>
	<td >
	<div class="button_menu">
    <div class="item-header"> Статус контакта </div>    
    <?= $form->field($model, 'speak_res')->radio(['label' => 'Контакт не завершен', 'value' => 3, 'uncheck' => null]) ?>      
	<?= $form->field($model, 'speak_res')->radio(['label' => 'Есть интерес', 'value' => 1, 'uncheck' => null]) ?>
    <?= $form->field($model, 'speak_res')->radio(['label' => 'Отказ', 'value' => 2, 'uncheck' => null]) ?>
	<?= $form->field($model, 'speak_res')->radio(['label' => 'Звонок не состоялся', 'value' => 0, 'uncheck' => null]) ?>	
	</div>
	</td>
	</table>
	
	<?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
    <div class="item-header"> Заполнил<div>  
    <p><?= Html::encode($curUser->userFIO)?></p> 
  
  <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
  <div style='visibility:hidden'> <?= $form->field($model, 'id')->label('Id')?></div>
  <?php ActiveForm::end(); ?>
   
   
