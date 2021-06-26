<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\assets\AppAsset;

$this->title = 'Форма первого личного контакта';
//$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;
$moduleText=$model->getModuleText();

$model->loadData();

?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


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
 .phone {
	 font-size: 10pt;
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
  
  <p><b><i>Цель: выйти на отдел продаж. Получить е-почту, телефон и контактного лица отдела продаж.</i></b></p>
  Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($model->id)?>"><?= Html::encode($model->orgTitle)?></a></strong></u> <br>
  <div class="item-header"> Телефоны: &nbsp;      
	 <?php
	 $phoneList=$model->getCompanyPhone($model->id);
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
	 
	 
  </div> 
   <table border=0 style="border:0px" width=100%>
	<tr>
	
	<td width ='300px'>Телефон по которому состоялся контакт </td>
	<td ><?= $form->field($model, 'currentPhone')->label(false)?> </td>
	<td width="150px"  > <div style="position:relative; top:-7px; left:10px;"> &nbsp; <input class="btn btn-primary" style="width: 100px;" type="button" value="Позвонить" 
		onclick="javascript:doCall();"/> </div></td>
	</tr>
  </table>
    
  
   <table border=0 style="border:0px" width=100% >
   <tr>
   <td width='450px' valign='top' style='padding:5px;'>     
    <div class="part-header"> Речевой модуль первого личного контакта </div>
    <div class='spacer'></div>
      <p>
            <?php echo $moduleText; ?>;
      </p>
   
   <hr>
   <div class='prevContact'>
   <?php
    echo "<i>".$model->prevContactDate."</i> &nbsp;";
    echo $model->prevContactFIO;
    echo "<br>\n";
    echo $model->prevContactText;
   ?>
   </div>   
   </td>
	<td valign='top' style='padding:5px;'>
	<div class="part-header"> Результаты разговора</div>
	<div class='spacer'></div>
    <p align='center'><b> Заполняется только если состоялся разговор </b></p>     
    <table border=0 style="border:0px" width=100% >
     <tr><td>E-mail (Для первого КП)</td><td><?= $form->field($model, 'email')->label(false)?></td></tr>     
     <tr><td>ФИО первого контакта</td><td><?= $form->field($model, 'contactFIO')->label(false)?> </td></tr>
     <tr><td>Должность  первого контакта </td><td><?= $form->field($model, 'firstContactPosition')->label(false)?> </td></tr>
     <tr><td>Телефон снабжения</td><td><?= $form->field($model, 'contactPhone')->label(false)?> </td></tr>
     <tr><td>Имя специалиста снабженца</td><td><?= $form->field($model, 'name')->label(false)?></td></tr>
     </table>
	
	</td>
	</table>
	
	<?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Содержание разговора')?>
	
	<div class='row'>
    <div class='col-md-5'><p> Заполнил: <b>  <?= Html::encode($curUser->userFIO)?></b></p></div>
    <div class='col-md-5'></div>
    <div class='col-md-2' style='text-align:right;'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?></div>
    </div>
  
  
  <div style='visibility:hidden'> <?= $form->field($model, 'id')->label('Id')?></div>
  <?php ActiveForm::end(); ?>
   
   
