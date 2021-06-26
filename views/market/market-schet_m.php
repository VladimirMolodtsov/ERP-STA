<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Ведение счета';
//$this->params['breadcrumbs'][] = $this->title;

$model->loadSchetData();

$listStatus = $model-> getListStatus();
		 
?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<link rel="stylesheet" type="text/css" href="css/zvonki-common.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>
 	
 /* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 28px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
} 	


.circle {
    width: 25px; /* задаете свои размеры */
    height: 25px;
    overflow: hidden;
	display: inline;
    background: #4169E1;
    padding: 5px; /* создание отступов */
    border-radius: 50%;
    /* не забываем о кроссбраузерности */
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%
    border: #FFF 1px solid;
	/* тень */
    /*box-shadow: 0px 1px 1px 1px #bbb; 
    -moz-box-shadow: 0px 1px 1px 1px #bbb;
    -webkit-box-shadow: 0px 1px 1px 1px #bbb;
	*/
	float:left;
	margin-left:0px;
	margin-top: 0px;
	color:white;
}
.circle:hover{
	background:#0000CD
}

.executed {
    background: #4169E1;
	color:white;
}

.planned {
    background: #C0C0C0;
	color:white;
}

 
</style>

<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function chngState()
{
	  
  document.getElementById('marketschetform-schetstatus').checked=document.getElementById('getstate').checked;
  //document.getElementById('marketschetform-schetstatus').click();
}

function chngState2()
{
	  
  document.getElementById('marketschetform-schetstatus2').checked=document.getElementById('getstate2').checked;
  //document.getElementById('marketschetform-schetstatus').click();
}

function setPhone(phone)
{
  document.forms["w1"]["marketschetform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w1"]["marketschetform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}

function openWin(url, wname)
{
  wid=window.open(url,  wname,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}

function setSchetStatus(id)
{
var	statusList = new Array();
statusList[0]="";	
<?PHP
$schetStatus=$listStatus['schet_status'];
for ($i=0;$i<count($schetStatus);$i++)
{
	echo "statusList[".$schetStatus[$i]['razdelOrder']."]='".$schetStatus[$i]['Title']."';\n";
}
?>
document.getElementById('schet_status').innerHTML =statusList[id];	
}

function restoreSchetStatus()
{
 document.getElementById('schet_status').innerHTML ='<?=$schetStatus[0]['Title']?>';
}

function setCashStatus(id)
{
var	statusList = new Array();
statusList[0]="";	
<?PHP
$cashStatus=$listStatus['cash_status'];
for ($i=0;$i<count($cashStatus);$i++)
{
	echo "statusList[".$cashStatus[$i]['razdelOrder']."]='".$cashStatus[$i]['Title']."';\n";
}
?>
document.getElementById('cash_status').innerHTML =statusList[id];	
}

function restoreCashStatus()
{
 document.getElementById('cash_status').innerHTML ='<?=$cashStatus[0]['Title']?>';
}

function setSupplyStatus(id)
{
var	statusList = new Array();
statusList[0]="";	
<?PHP
$supplyStatus=$listStatus['supply_status'];
for ($i=0;$i<count($supplyStatus);$i++)
{
	echo "statusList[".$supplyStatus[$i]['razdelOrder']."]='".$supplyStatus[$i]['Title']."';\n";
}
?>
document.getElementById('supply_status').innerHTML =statusList[id];	
}

function restoreSupplyStatus()
{
 document.getElementById('supply_status').innerHTML ='<?=$supplyStatus[0]['Title']?>';
}


</script>

<!---------------------------------------------------------------------->
<!---------------------------------------------------------------------->
  <div class="page-title"><?= Html::encode($this->title) ?>  № <strong><?= Html::encode($model->schetNumber)?></strong> 
  от: <u><strong><?= Html::encode($model->schetDate)?></strong></u> </div>
  <div class="item-header">	Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u>   </div>

 <hr noshade size='5'>
 <!--- Регистрация контакта старт--->	
  <?php $form = ActiveForm::begin(['id' => 'Mainform',
        'layout'=>'horizontal',
        'options' => ['class' => 'form-inline'],
        /*'fieldConfig' => [
             'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            'horizontalCssClasses' => [
            //   'label' =>   'col-sm-4',
            //    'offset' =>  'col-sm-offset-4',
            //    'wrapper' => 'col-sm-8',
                'error' => '',
                'hint' => '',
            ],
        ],*/
  ]); ?>

  <!-- Верхний блок background-color:gray-->  
<div style='width:1000px; height:180px; '>  
  
  <div style='width:520px;  height:180px; position: relative;  left: 0px; display:inline-block; float:left; background-color:AliceBlue'>			
  <table style='width:520px;'>   
   <tr>
	<td width='80px'>Счет </td>
	<td>
		<div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
		<?php 
		for ($i=1;$i<=count($schetStatus);$i++)
		{		
	     if ($i <2 ){$style="executed";}
		       else {$style="planned";}			  
		echo "<div class='circle ".$style."' onmouseover='javascript:setSchetStatus(".$i.");' onmouseout='javascript:restoreSchetStatus();'>".$i."</div>";
		}
		?>	
		</div>
	    <div id='schet_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$schetStatus[0]['Title']?> </div>
	</td>
   </tr> 
   <tr>
	<td>Деньги</td>
	<td>
		<div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
		<?php 
		for ($i=1;$i<=count($cashStatus);$i++)
		{		
	     if ($i <2 ){$style="executed";}
		       else {$style="planned";}			  
		echo "<div class='circle ".$style."' onmouseover='javascript:setCashStatus(".$i.");' onmouseout='javascript:restoreCashStatus();'>".$i."</div>";
		}
		?>	
		</div>
	    <div id='cash_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$cashStatus[0]['Title']?> </div>
	</td>
   </tr> 
   <tr>
	<td>Товар</td>
	<td>
		<div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
		<?php 
		for ($i=1;$i<=count($supplyStatus);$i++)
		{		
	     if ($i <2 ){$style="executed";}
		       else {$style="planned";}			  
		echo "<div class='circle ".$style."' onmouseover='javascript:setSupplyStatus(".$i.");' onmouseout='javascript:restoreSupplyStatus();'>".$i."</div>";
		}
		?>	
		</div>
	    <div id='supply_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$supplyStatus[0]['Title']?> </div>
	</td>
   </tr> 

  </table>
  </div>
  <!-- Контактные данные -->
  <div style="position:relative; width:450px; top:5px; display:inline-block; float:right; margin-right:10px; ">
  
     <table border=0 style="border:0px; width:100%; padding:5px" ><tr>
	<td>Телефон:</td>
	<td><div style="position:relative;left:-60px"><?= $form->field($model, 'contactPhone')->textInput(['style'=>'width:170px; margin:0px; padding:10px; left:0px'])->label(false)?></div></td>
	<td valign='top'> <input class="btn btn-primary" style="width: 150px; " type="button" value="Позвонить" onclick="javascript:doCall();"/> </td>
	</tr><tr>	
	<td>E-Mail:</td>
	<td><div style="position:relative;left:-60px"><?= $form->field($model, 'contactEmail')->textInput(['style'=>'width:170px; margin:0px; padding:10px; left:0px'])->label(false)?> </div></td>
    <td valign='top'><a class="btn btn-primary" style="width: 150px;" target="_blank" href='index.php?r=site/mail&email=<?php echo $model->contactEmail ?>'>Написать письмо</a></td>
    </tr><tr>	
	<td colspan='3'>Контактное лицо:<div style="position:relative;left:-50px"><?= $form->field($model, 'contactFIO')->textInput(['style'=>'width:350px; margin:0px; padding:10px; left:0px'])->label(false)?> </div></td>
    
	</tr></table>
  
 	<p class="phone"><span class="phones" onclick="view('phone_list'); return false">Показать остальные телефоны...</span></p>
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
   </span> <p>	
 </div> 
 
 <!-------------------------------------->
</div>	



  
  <div style='width:1000px; height:180px;'>  
    <!-- Выводим последний контакт -->
    <div style='width:350px; position: relative;  left: 0px; display:inline-block; float:left;'>			
	<div class="small_part-header"> Предыдущий контакт   
	<span style="position:relative;left:40px;font-size:10px"><a href ="index.php?r=site/contacts-detail&id=<?= $model->id ?>"> (Просмотреть историю контактов) </a></span>
	</div>
	<div style='background-color: BlanchedAlmond; height:150px;'>
	<?php
	 //mb_internal_encoding("UTF-8");
	 //mb_http_output('UTF-8'); 
	 $contactsDetail=$model->getContactDetail();
	 $cnt = count ($contactsDetail);
	 if ($cnt> 1) $cnt = 1;
	 for ($i=0;$i<$cnt;$i++)
	 {	
		echo "<div class='contact_title'> <b>";
		echo date("d-m-Y",strtotime( $contactsDetail[$i]['contactDate']))." </b> ";
		echo $contactsDetail[$i]['contactFIO']."  ".$contactsDetail[$i]['phone']."</div>\n";
		if (mb_strlen($contactsDetail[$i]['note'])> 260){echo "<div>".Html::encode(mb_substr($contactsDetail[$i]['note'],0,260))."...</div>\n";}		       
		else {echo "<div>".$contactsDetail[$i]['note']."</div>\n";}		       
	 }
	 ?>  
	 </div>
    </div>	 

	<!-- Статусы -->
  <div style='width:200px; position: relative;  left: 0px; display:inline-block; float:left;'>
    <br>
	<nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 3, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;Отложено </nobr><br>
	<nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 2, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;Отказ</nobr><br>
	<br>
	<div style="margin-left:15px;">Дата следущего чекаута</div>
	<div style="margin-left:-35px;"><?= $form->field($model, 'nextdate')->textInput(['class' => 'tcal',])->label(false)?></div>
  </div>
  
  <div style="position:relative; width:450px; top:20px; display:inline-block; float:left; margin-left:-60px; ">
	<div style = "margin-left:120px;">Комментарий: </div>
    <?= $form->field($model, 'note')->textarea(['rows' => 5, 'cols' => 50])->label(false)?>
  </div>

<hr>

 <div style="position:relative; top:30px; display:inline-block; float:right; margin-right:0px">
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform']) ?> </div>
  </div>
 
 </div>

  	

<!--- Контакт финиш--->  

   <?= $form->field($model, 'id')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>    
   <?php ActiveForm::end(); ?>
<!--- ******************************************************  --->  

	
   