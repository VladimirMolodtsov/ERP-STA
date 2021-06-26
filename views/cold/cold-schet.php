<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Первичная заявка на Счёт';
$this->params['breadcrumbs'][] = $this->title;

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

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
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkGreen ;
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
  .contact_title {
    margin:5px 0px;
    padding:10px;
	font-size: 10pt;    
}
  .contact_text {
    margin:5px 0px;
    padding:10px;
    border:1px solid #ffbc80;
    background: Beige;
	font-size: 10pt;    
}

 
 .phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
	font-size: 10pt;    
}
/* кликабельный текст */
.phones {
    color:#f70;
    cursor: help
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;

	
/* прокручивалка*/	

	 .container{
         width:800px;
		 height:200px;
         margin:0px auto;
         padding:40px 0;
     }
     .scrollbox{
         width:800px;
         height:300px;
         overflow:auto; overflow-x:hidden;
     }
     .content > p{
         background:#eee;
         color:#666;
         font-family:Arial, sans-serif; font-size:0.75em;
         padding:5px; margin:0;
         text-align:rightright;
     } 
}
 
</style>

<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["w0"]["coldschetform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w0"]["coldschetform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}
</script>

  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  <p>Цель: получить заявку на счет.</p>
  
  Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u> <br>
  
  <table border=0 style="border:0px" width=100%>
	<tr>	   
	<td ><?= $form->field($model, 'contactPhone')->label('Телефон ')?> </td>
	<td width="150px"  > <div style="position:relative; top:5px; left:10px;"> &nbsp; <input class="btn btn-primary" style="width: 100px;" type="button" value="Позвонить" 
		onclick="javascript:doCall();"/> </div></td>
	</tr>
  </table>
  
    <p class="phone">
	<span class="phones" onclick="view('phone_list'); return false">Показать остальные телефоны...</span>
	</p>
    <p>
	<span id="phone_list" class="phone_view">
	 <?php
	 $phoneList=$model->getCompanyPhones();
	 for ($i=0;$i<count($phoneList);$i++)
	 {		        
	    echo "<a href='#' onclick='javascript:setPhone(".Html::encode($phoneList[$i]["phone"]).");'>".Html::encode($phoneList[$i]["phone"])."</a>";	    	    
    	if ($phoneList[$i]["status"] == 1){echo " <font color='green'>*</font>";}
		if ($phoneList[$i]["status"] == 2){echo " <font color='red'>*</font>";}
		echo ";\n";
	 }
	 ?>  
   </span> </p>
	  
     <div class="part-header"> Данные о контактах</div>

	<?php
	 $contactsDetail=$model->getContactDetail();	 
 	 $cnt = count ($contactsDetail);
	 if ($cnt> 1) $cnt = 1;
	 for ($i=0;$i<$cnt;$i++)
	 {	
		if ( ($contactsDetail[$i]['contactFIO'] =="-" || $contactsDetail[$i]['contactFIO'] =="") && ($contactsDetail[$i]['note']=="")) {continue;}
		echo "<div class='contact_title'> <b>";
		echo date("d-m-Y",strtotime( $contactsDetail[$i]['contactDate']))." </b> ";
		echo $contactsDetail[$i]['contactFIO']."  ".$contactsDetail[$i]['phone']."<br></div>\n";
		echo "<div>".$contactsDetail[$i]['note']."<br></div>\n";		
        echo "<hr>";
	 }
	 
	 ?>  
     <p><a class="btn btn-primary" href="index.php?r=site/contacts-detail&id=<?= $model->id ?>"> История контактов </a></p>
  
   <div class="part-header"> Первичная заявка</div> 
   <br>  
   <table border=0 style="border:0px" width=100%>
  	<?php 	
	$needList = $model->getNeedList();
	$needListN = $model->getNeedListN();;
	if ($needListN > 10) {$needListN =10;}
	for ($i=0; $i<$needListN; $i+=2)
	{ 
        echo "<tr>";
		$checkName = "needList_".$i;    
		echo "<td>".$form->field($model, $checkName)->checkbox(['label' => $needList[$i]["Title"]])."</td>";			
		$j=$i+1;	
		if (($j)<$needListN)
		{		
		$checkName = "needList_".$j;    	
		echo "<td>".$form->field($model, $checkName)->checkbox(['label' => $needList[$j]["Title"]])."</td>";			
		}		
		echo "</tr>";
	}
	echo "<tr>";	
	echo "<td colspan=2>".$form->field($model, 'otherGood')->label('Другой товар')."</td>";
	echo "</tr>";
	?>
	</table>    
 
 
<!--- Контакт старт--->
	
   <div class="part-header"> Результаты разговора</div>
  
   <table border=0 style="border:0px" width=100%>
   <tr>
   <td width="60%">  
    <p> Заполняется только если состоялся разговор</p>     
     <table border =0  style="border:0px"><tr>
	 <td width='80%'><?= $form->field($model, 'contactEmail')->label('e-mail')?></td>
     <td><div style="position:relative; top:-5px; left:10px;">&nbsp;<a  class="btn btn-primary" target="_blank" href='index.php?r=site/mail&email=<?php echo $model->contactEmail ?>'>Написать письмо</a></div></td>
	 </tr></table>					 
     <?= $form->field($model, 'contactFIO')->label('Контактное лицо')?>	
	 <?= $form->field($model, 'nextdate')->textInput(['class' => 'tcal',])->label('Дата следующего контакта ')?>
    </td>
	<td style="padding-left: 50px;">	
    <div class="item-header"> Статус контакта </div>    
    <?= $form->field($model, 'status')->radio(['label' => 'Отложить', 'value' => 3, 'uncheck' => null]) ?>      
	<?= $form->field($model, 'status')->radio(['label' => 'Информация получена', 'value' => 1, 'uncheck' => null]) ?>
    <?= $form->field($model, 'status')->radio(['label' => 'Отказ',               'value' => 2, 'uncheck' => null]) ?>
	<?= $form->field($model, 'status')->radio(['label' => 'Звонок не состоялся', 'value' => 0, 'uncheck' => null]) ?>		
	</td>
	</tr>
	<tr>
	<td colspan=2>
	<?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
	</td>
	</tr>
  </table>
<!--- Контакт финиш--->  
 
   
   <div class="item-header"> Заполнил<div>  
   <p><?= Html::encode($curUser->userFIO)?></p> 
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
   <div style='visibility:hidden'>   <?= $form->field($model, 'id')->label('Id')?>   </div>
   <?php ActiveForm::end(); ?>
   
   