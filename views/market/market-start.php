<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use yii\grid\GridView;
use yii\data\SqlDataProvider;
use yii\widgets\Pjax;

$this->title = 'Отдел продаж';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;

$currentlyInWork= $model->getCurrentlyInWork();

/*
 $d = date("d");
 $m = date("m");
 $y = date("Y");
*/

 $d = $model->d;
 $m = $model->m;
 $y = $model->y;

 $prev_y = $y;
 $prev_m = $m -1;
 if($prev_m == 0){$prev_m = 12; $prev_y--;}
 
 $next_y = $y;
 $next_m = $m +1;
 if($next_m == 13){$next_m = 1; $next_y++;}

$currentStatus = $model->getCurrentStatus();

$noContactCount = $cold_model->noContactCount();
$readyCount     = $cold_model->readyCount();
//print_r ($currentStatus);

?>
<style>
.button {
    background-color: #e7e7e7;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px;
	width: 120px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 

.btncal {
	font-size: 10px;
	margin:4px;
	padding:4px;
} 

table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkSlateGrey ;
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
  .detail_title {
    margin:5px 0px;
    padding:10px;
	font-size: 10pt;    
}
  .detail_text {
    margin:5px 0px;
    padding:10px;
    border:1px solid #ffbc80;
    background: Beige;
	font-size: 10pt;    
}
 
 
table.calendar    { border-left:1px solid #999;}
tr.header-row  {   }
tr.calendar-row  {  height:50px; }
td.calendar-month{ background:DarkSlateGrey  ; color:#fff; font-weight:bold; text-align:center; padding:3px; border:0px}
td.calendar-day  { min-height:50px; align:right; font-size:8pt; position:relative; } * html div.calendar-day { height:50px; }
td.calendar-day:hover  { background:#eceff5; }

td.selected-day        { min-height:50px; align:right; font-size:10px; position:relative; } * html div.calendar-day { height:50px; }
td.selected-day:hover  { background:#eceff5; }


td.calendar-day-np  { background:#eee; min-height:50px; } * html div.calendar-day-np { height:30px; }
td.calendar-day-head { background:#ccc; font-weight:bold; text-align:center; width:30px; padding:5px; border-bottom:1px solid #999; border-top:1px solid #999; border-right:1px solid #999; }
/*div.day-number           { width:20px; height:20px; float:left; background:#999;          padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }
div.cur-day-number       { width:20px; height:20px; float:left; background:DarkSlateGrey; padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }
div.selected-day-number  { width:20px; height:20px; float:left; background:ForestGreen;   padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }
*/
div.day-number           { width:20px; height:20px; position: relative ; top:0 ; left:0; background:#999;          padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }
div.cur-day-number       { width:20px; height:20px; position: relative ; top:0 ; left:0; background:DarkSlateGrey; padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }
div.selected-day-number  { width:20px; height:20px; position: relative ; top:0 ; left:0; background:ForestGreen;   padding:5px; color:#fff; font-weight:bold;  margin:-1px -5px 0 0;  text-align:center; }

td.selected-day, td.calendar-day, td.calendar-day-np { width:30px; padding-left:5px; padding-top:1px; padding-bottom:1px; border-bottom:1px solid #999; border-right:1px solid #999; }
div.no-event    { width:20px; height:20px; position: relative; top:0 ; left:0; } 
div.fail-event  { width:20px; height:20px; position: relative; top:0 ; left:0; background:LightCyan   ; color:red; font-weight:bold; font-size: 10pt; text-align: center;} 
div.norm-event  { width:20px; height:20px; position: relative; top:0 ; left:0; background:LightCyan   ; font-weight:bold; font-size: 10pt;  text-align: center;} 



table.menu    { border-left:0px solid; border-spacing: 15px;	 border-collapse: separate; }
tr.menu-row   { height:30px; }
td.menu-point { background:DarkSlateGrey  ; color:#FFFFFF; font-weight:bold; text-align:center; padding:10px; border:0px}
a.menu-point  {  color:#FFFFFF; font-weight:bold;  font-style: normal; }
a.menu-point:hover {  color:#FFFFFF; font-weight:bold; }


.disable      {  background-color: LightGray;	  width:120px; }
.disable:hover{	 background-color: LightGray;	  }
.enable       {	 width:120px;   }
.enable_cur  {	 width:120px; background-color: DodgerBlue;	  }


.selected_tab {	 width:120px; background-color: LimeGreen;	     }
.normal_tab   {	 width:120px; background-color: MediumSeaGreen;	 }
.tab_container {
				width:  788px;  /* ширина нашего блока */
				height: 70px; /* высота нашего блока */
				border: 0px solid #C1C1C1; /* размер и цвет границы блока */
				text-align: right;
				}


/* События */
.dealed { color:DimGrey; }
.todoed { color:Black; font-weight: bold; }

.amount_sup {	
	color:black ;
	font-size: 12px;
	font-weight: normal;
}
.amount {
	vertical-align: bottom;
	display: inline;
	color:DarkGrey ;
	padding: 3px;
	font-size: 18px;
	font-weight: bold;
	font-stretch: ultra-condensed;
}

.amount_fail {
	vertical-align: bottom;
	display: inline;
	color:Crimson;
	padding: 3px;
	font-size: 18px;
	font-weight: bold;
	font-stretch: ultra-condensed;
}


/* блок внешнего контейнера */
.main_cont {
width:  1188px;  /* ширина нашего блока */
height: 500px; /* высота нашего блока */
background: WhiteSmoke; /* цвет фона */
border: 1px solid #C1C1C1; /* размер и цвет границы блока */
border-radius: 2%;
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}

.bottom_cont {
width:  1188px;  /* ширина нашего блока */
border: 1px solid #C1C1C1; /* размер и цвет границы блока */	
background: WhiteSmoke; /* цвет фона */
}
/* блок основного рабочего поля */
.wrk_field {
width:  600px;  /* ширина нашего блока */
height: 300px; /* высота нашего блока */
border-radius: 2%;
background: Gainsboro ; /* цвет фона */
border: 1px solid DimGrey; /* размер и цвет границы блока */
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}

.top_menu_cont
{
width:  600px;  /* ширина нашего блока */
height: 70px; /* высота нашего блока */
border: 0px solid DimGrey; /* размер и цвет границы блока */
/*position: relative ; top:0 ; left:0;*/
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}

.bottom_menu_cont
{
	
margin-top:10px;
width:  600px;  /* ширина нашего блока */
height: 60px; /* высота нашего блока */
border: 0px solid DimGrey; /* размер и цвет границы блока */
/*position: relative ; top:0 ; left:0;*/
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}


/* блок для нового календаря */
.calendar_cont {
width: 250px; /* ширина нашего блока */
height: 327px; /* высота нашего блока */
//background: #fff; /* цвет фона, белый */
border: 0px solid #C1C1C1; /* размер и цвет границы блока */
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}



.cl_day {
height: 100px; /* высота нашего блока */
width:  250px;  /* ширина нашего блока */
background: WhiteSmoke; /* цвет фона */
border: 0px solid #C1C1C1; /* размер и цвет границы блока */
/*overflow-x: scroll;  прокрутка по горизонтали */
/*overflow-y: scroll;  прокрутка по вертикали */
}

.cl_label {
height: 100px; /* высота нашего блока */
width:  90px;  /* ширина нашего блока */
background: White; /* цвет фона */
border: 0px solid #C1C1C1; /* размер и цвет границы блока */
padding:5px;
font-weight:bold; 
float:left;
}

.cl_event {
height: 100px; /* высота нашего блока */
width:  150px;  /* ширина нашего блока */
background: White; /* цвет фона */
border: 0px solid #C1C1C1; /* размер и цвет границы блока */
padding:5px;
font-weight:bold; 
float:right;
}


.leaf {
	height: 100px; /* высота нашего блока */
	width:  150px;  /* ширина нашего блока */
	border: 0px solid #C1C1C1; /* размер и цвет границы блока */
	padding:5px;
	font-weight:bold; 
	box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}


</style>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

 <script>

function openFindWin()
{
  var str=document.forms["w0"]["marketcalendarform-strsearch"].value;
  if (str.trim() == '') return;
	//escape
  wid=window.open("index.php?r=market/market-search&findString="+(str.trim()),'childwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}


function switchTab(tab)
{
  det=<?=$model->detail?>;  
  mode=<?=$model->mode?>;  
  window.open("index.php?r=market/market-start&tab="+tab+"&detail="+det+"&mode="+mode,'_parent'); 
}

function switchDetTab(det)
{
  tab=<?=$tab?>;  
  mode=<?=$model->mode?>;  
  window.open("index.php?r=market/market-start&tab="+tab+"&detail="+det+"&mode="+mode,'_parent'); 
}


function reload_page() {
  location.reload();
}
//setInterval("reload_page()", 60000);

</script> 

<?php $form = ActiveForm::begin(); ?>					
<table border='0' width=100%><tr>
<td width="300px"><?=$form->field($model, 'strSearch')->label(false)?></td>
<td><div style="position:relative; top:-6px; left:10px;">
<input class="btn btn-primary"  style="width: 175px;" type="button" value="Найти" onclick="javascript:openFindWin();"/></div></td>
<td><div class='tab_container'> 
<?php
$style1=$style2=$style3="";
switch($model->mode)
{
  case 1:
  $style1= "selected_tab";
  $style2=$style3="normal_tab";
  break;

  case 2:
  $style2= "selected_tab";
  $style1=$style3="normal_tab";
  break;

  case 3:
  $style3= "selected_tab";
  $style2=$style1="normal_tab";
  break;
}
?>
<a  class="btn btn-primary <?=$style1?>" href="index.php?r=market/market-start&tab=<?= $model->tab?>&detail=<?= $model->detail?>&mode=1">События</a>
<a  class="btn btn-primary <?=$style3?>" href="index.php?r=market/market-start&tab=<?= $model->tab?>&detail=<?= $model->detail?>&mode=3">Все</a>
</div></td>
</tr></table>
<?php ActiveForm::end(); ?>

<?php if ($model->mode == 1) { ?>
<div class='main_cont' style='height:400px; border-radius: 0%;' >
 <table border='0' width='1140px'> 
 <tr><td>
	<table border='0' width='700px'> 
		<tr>		
		<td> <b>Основные цели </b></td>
		<td> <b>Контакты</b></td>
		<td> <b>Холодная база	</b>	</td>
		</tr> 

		<tr>		
		<td> <div  class='leaf' style='background:Blue ; color:White;'> <div style="font-size:15px">На сегодня: </div><div style="font-size:30px"><?= $model->getCurrentEvents()?></div> </div></td>
		<td> <div  class='leaf' style='background:Indigo  ; color:White;'> <div style="font-size:15px">На сегодня: </div><div style="font-size:30px"><?= $model->getCurrentEvents()?></div> </div></td>
		<td> <div  class='leaf' style='background:SteelBlue  ; color:White;'> <div style="font-size:15px">Продолжить: </div><div style="font-size:30px"><?= $model->getOtherEvents()?></div> </div></td>
		</tr> 

		<tr>
		<td> <div  class='leaf' style='background:CornflowerBlue  ; color:White;'> <div style="font-size:15px">Других: </div><div style="font-size:30px"><?= $model->getOtherEvents()?></div> </div></td>
		<td> <div  class='leaf' style='background:MediumOrchid   ; color:White;'> <div style="font-size:15px">Других: </div><div style="font-size:30px"><?= $model->getOtherEvents()?></div> </div></td>
		<td> <div  class='leaf' style='background:Teal   ; color:White;'> <div style="font-size:15px">Новых: </div><div style="font-size:30px"><?= $model->getCurrentEvents()?></div> </div></td>		
		</tr> 

		<tr>
		<td> <div  class='leaf' style='background:Green  ; color:White;'> <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents()?></div> </div></td>
		<td> <div  class='leaf' style='background:OliveDrab   ; color:White;'> <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents()?></div> </div></td>		
		<td> <div  class='leaf' style='background:SeaGreen    ; color:White;'> <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents()?></div> </div></td>		
		</tr> 


		
	</table>
  </td></tr>
</table>  
</div>

<?php } ?>   
<?php if ($model->mode == 2 || $model->mode == 3) { ?>
<!--------------   Кнопки работы старт  ------------------------------------>
<!--  внешний контейнер ------->
<div class='main_cont'>
 <table border='0' width='1140px'> 
 <tr><td>
<!---------- работа с базой ----->
<table border='0' width='700px'> 
<tr><td>
<!---------- основной ----->
<div class="top_menu_cont">
 <table border='0' width='100%'> 
 <!--- верхние кнопки -->
 <tr> 
 
	<td valign="bottom" width="33%">
	<?php if($curUser->roleFlg & (0x0080|0x0002|0x0004) ){ $style= "enable"; $onclick= "javascript:switchTab('1')";
										if ($tab == 1){$style= "enable_cur";}
										} 
 	                       else { $style= "disable"; $onclick= "";} 
	?>	
	<div class="amount_sup"><?= Html::encode($noContactCount) ?></div>
	<nobr><input class="btn btn-primary <?=$style?>" style="width: 175px;" type="button" value="Холодная база"  onclick="<?=$onclick?>"/>
	<?php
   	 if ($currentStatus['cold_fail'] > 0)    { echo	"<div class='amount_fail'>".$currentStatus['cold_fail']."</div>"; }
	                                    else { echo	"<div class='amount'>".$currentStatus['cold_all']."</div>";}
	?>
	</nobr>
	</td>         
	
	<td valign="bottom" width="33%">
	<div class="amount_sup"><?= Html::encode($model->getAllAvailableClients()) ?></div>
    <nobr><input class="btn btn-primary" style="width: 175px;"  type="button" value="Клиенты"        onclick="javascript:openWin('market/market-client-select','childWin');"/>
	<div class="amount" > <?= Html::encode($model->getMyClients()) ?> </div></nobr>
	</td>         
 </tr>
 </table>	
</div>
 <!--- основное рабочее поле -->
 
 <div class='wrk_field'>
 <!---   Продажи      ---> 
 <?php if($tab == 3) { ?> 
 <table border=0 width=100%> 
 <tr> 
	<td colspan=3 align='center'><b>Работа со счетами:</b></td>         
 </tr>
<tr> 
	<td width='300px'>Cчета в оплате: </td>           
	<td width='75px'><?= Html::encode($model->getSchetInCash()) ?></td>
	<td><input class="btn btn-primary" style="width: 75px;"  type="button" value=">>"     onclick="javascript:openWin('market/market-schet-select','childWin')"/></td> 	
</tr>

<tr> 
	<td>Поставка: </td>           
	<td><?= Html::encode($model->getSchetInSupply()) ?></td>
	<td><input class="btn btn-primary" style="width: 75px;"  type="button" value=">>"  onclick="javascript:openWin('market/market-schet-supply-select','childWin')"/></td> 	
</tr>

<tr> 
	<td colspan=3 ><hr></td> 	
</tr>


<tr> 
	<td width='300px'>Не найдены в 1С: </td>           
	<td width='75px'><?= Html::encode($model->getSchetNoRef1C()) ?></td>
    <td rowspan=3><input class="btn btn-primary" style="width: 160px;background:Green;"  type="button"  value="Синхронизация с 1С"     onclick="javascript:document.location.href='index.php?r=data/market-schet-sync'"/>
	<div style="font-size:8pt; margin:5px;">
		Процесс синхронизации с 1С занимает до 10 мин. Рекомендуется запускать в нерабочее время. 
	</div>
	</td> 		
	
</tr>

<tr> 
	<td width='300px'>Оплата не подтверждена 1С: </td>           
	<td width='75px'><?= Html::encode($model->getSchetNo1COplata()) ?></td>
</tr>

<tr> 
	<td width='300px'>Поставка не подтверждена 1С: </td>           
	<td width='75px'><?= Html::encode($model->getSchetNo1CSupply()) ?></td>
</tr>

  </table>
 <?php } ?> 
<!---   Холодные звонки --->
<?php if($tab == 1) { ?> 
 <table border=0 width=100%> 
  <tr> 
	<td colspan=3 align='center'><b>Работа с холодной базой:</b></td>         
  </tr>
 <tr>
	<td width='300px'>Всего в базе:</td>
	<td width='75px'><?= Html::encode($cold_model->availableCount()) ?></td>
	<td ><input class="btn btn-primary" style="width: 75px;" type="button" value=">>" onclick="javascript:openWin('cold/cold-view','childWin');"/></td>
</tr>
  
<tr>
<td>
	<span class="dealed">Не взято в работу:</span><br>
    <span class="todoed">Первый контакт</span></td>
</td>
	<td><?= Html::encode($noContactCount) ?></td>
	<td ><input class="btn btn-primary" style="width: 75px;" type="button" value=">>" onclick="javascript:openWin('cold/cold-init-select','childWin');"/></td>
</tr>

<tr>
<td>
	<span class="dealed">Не завершен первый контакт:</span><br>
    <span class="todoed">Завершить</span></td>
</td>
	<td><?= Html::encode($cold_model->haveNoFinishedContactCount()) ?></td>
	<td ><input class="btn btn-primary" style="width: 75px;"  type="button" value=">>" onclick="javascript:openWin('cold/cold-init-continue','childWin');"/></td>
</tr>

<tr>
<td>Отказы:</td>
	<td><?= Html::encode($cold_model->rejectCount()) ?></td>
	<td><input class="btn btn-primary"  style="width: 75px;" type="button" value=">>" onclick="javascript:openWin('cold/cold-reject','childWin');"/></td>
</tr>

<tr>
<td><span class="dealed">Данные снабженца получены:</span><br>
    <span class="todoed">Выявить потребности</span></td>
<td><?= Html::encode($cold_model->haveContactCount()) ?></td>
<td ><input class="btn btn-primary"  style="width: 75px;" type="button" value=">>" onclick="javascript:openWin('cold/cold-need-select','childWin');"/></td>
</tr>

 </table>
 <?php } ?> 

 <!---   Заявки      --->
<?php if($tab == 2) { ?> 
 <table border=0 width=100%> 
   <tr> 
	<td colspan=3 align='center'><b>Работа с заявками:</b></td>         
  </tr>

<tr>
	<td width='300px'>
	<span class="dealed">Потребности известны:</span><br>
    <span class="todoed">Получить первичную заявку</span>	
	</td>
	<td  width='75px'><?= Html::encode($readyCount) ?></td>
	<td ><input class="btn btn-primary"  style="width: 75px;" type="button" value=">>" onclick="javascript:openWin('cold/cold-schet-select','childWin');"/></td>
</tr>
  
 <tr> 
	<td>
	<span class="dealed">Есть первичная заявка:</span><br>
    <span class="todoed">Начать согласование</span>	
     </td>  		
	<td><?= Html::encode($model->getCurrentlyNotInWork()) ?></td> 
	<td><input class="btn btn-primary" style="width: 75px;"  type="button" value=">>"  onclick="javascript:openWin('market/market-zakaz-select','childWin')"/></td> 
 </tr>
 
 <tr> 
	<td>
	<span class="dealed">Заявки на согласовании:</span><br>
    <span class="todoed">Выписать счет</span>		
	</td>           
	<td><?= Html::encode($model->getCurrentlyInWork()) ?></td>
	<td><input class="btn btn-primary" style="width: 75px;"  type="button" value=">>"    onclick="javascript:openWin('market/market-zakaz-inwork','childWin')"/></td> 	
</tr>


 </table>
 <?php } ?> 

 </div>
  <!--- основное поле конец -->
 <div class="bottom_menu_cont">
 <table border="0" width=100% valign='bottom'>
 <tr>
 <td width=33%><input class="btn btn-primary" style="width: 175px;"  type="button" value="Регистрация заказа" onclick="javascript:openWin('market/market-find','childWin');"/></td>
 <td width=33%><input class="btn btn-primary" style="width: 175px;"  type="button" value="Регистрация контакта"   onclick="javascript:openWin('market/market-find&mode=2','childWin');"/></td>
 </td>
 </tr>
 </table>
 </div>
 
 </td> 
<!---------- Правый ряд-----> 
<td>
<!--- правые кнопки -->
<table border='0' width='100%'> 
 <tr> 
	<?php 
	if($curUser->roleFlg & (0x0080|0x0004))
	{ 
	$style= "enable"; $onclick= "javascript:switchTab('2')"; if ($tab == 2){$style= "enable_cur";}
	} 
 	else { $style= "disable"; $onclick= "";} 
	?>	
	<td valign="bottom">	
	<div class="amount_sup"><?= Html::encode($readyCount) ?></div>	
	<nobr> <input class="btn btn-primary <?=$style?>"   type="button" value="Заявки"  onclick="<?=$onclick?>"/>
	<?php
	if ($currentStatus['zakaz_fail'] > 0)    { echo	"<div class='amount_fail'>".$currentStatus['zakaz_fail']."</div>"; }
	                                    else { echo	"<div class='amount'>".$currentStatus['zakaz_all']."</div>";}
										
	?></nobr>

	</td>         
</tr>
<tr>	
	<?php 
	if($curUser->roleFlg & (0x0080|0x0004))
	{
	$style= "enable"; $onclick= "javascript:switchTab('3')";	if ($tab == 3){$style= "enable_cur";}
    } 
    else { $style= "disable"; $onclick= "";}     
	?>	
	<td valign="bottom">
	<nobr> <input class="btn btn-primary <?=$style?>" type="button" value="Счета"  onclick="<?=$onclick?>"/>
	<?php
	if ($currentStatus['schet_fail'] > 0)    { echo	"<div class='amount_fail'>".$currentStatus['schet_fail']."</div>"; }
	                                    else { echo	"<div class='amount'>".$currentStatus['schet_all']."</div>";}
	
	?></nobr>	
	</td>         
 </tr>
<tr>	
		<td>
		<?php		
		$onclick = "javascript:openWin('market/market-request-list' ,'childWin')"
		// echo "<a  class='btn btn-primary enable' href=\"#\" onclick=\"javascript:openExtWin('".$model->getCfgValue(10)."');\"> Склад </a>"; 		
		 ?>
		<input class="btn btn-primary enable" type="button" value="Доставка"  onclick="<?=$onclick?>">
		
		 </td> 
 </tr> 

<tr>	
		<td>
		<?php		
		$onclick = "javascript:openWin('store/price','childWin')"		
		 ?>
		<input class="btn btn-primary enable" type="button" value="Прайс"  onclick="<?=$onclick?>">
		 </td> 
 </tr> 

 
 <tr>	
		<td>
		<?php		
		$onclick = "javascript:openExtWin('index.php?r=market/market-good-list')";
		 ?>
		<a class="btn btn-primary enable" href='index.php?r=market/market-good-list' target='blank'>Закупка</a>
		 </td> 
 </tr> 

 
</table>	
<!--- конец правые кнопки -->

</td>
</tr>
</table>	

<!------------------------------->
</td> 
 <td>
<!---------- Календарь ----->
<div>  Просрочено: <font color='Crimson'><?=$model->getFailedEvents()?></font> Ожидается: <font color='Green'><?=$model->getFutureEvents()?></font> </div> 
<!--<div> Ноябрь 2017  </div>-->
 <?php
  echo "Текущая дата <b>".date ("d.M.Y")."</b>";
 ?>
<div class='calendar_cont'>

<?php  echo $model->draw_calendar($d,$m,$y); ?>
<!--
<div class ='cl_day'>
  <div class ='cl_label'> 14 <br> вторник </div>
  <div class ='cl_event '> Назначено: </div>
</div>

<a href="index.php?r=market/market-start&tab=<?=$model->tab?>&detail=<?=$model->detail?>&mode=<?=$model->mode?>&m=<?=$prev_m?>&y=<?=$prev_y?>"> << </a>   <a href="index.php?r=market/market-start&tab=<?=$model->tab?>&detail=<?=$model->detail?>&mode=<?=$model->mode?>&m=<?=$next_m?>&y=<?=$next_y?>"> >> </a>   
-->
</div>

 </td>
 </tr>
 </table>
 </div>
 <!------  *****  ------->
<!--------------   Кнопки работы старт  ------------------------------------>
<?php }?> 
 
 
 
 
 
<?php if ($model->mode == 1 || $model->mode == 3) { ?>
<!------  Запланированные события  ------->
 <table border='0' width='100%'> 
 <tr>
 <td valign="top">
<table border='0' width='500px'> 
 <tr> 
	<td>
		<?php if($model->detail == 0){ $style= "enable_cur"; } else {$style= "enable";} $onclick= "javascript:switchDetTab('0')";?>		
		
		<input class="btn btn-primary <?=$style?>"  type="button" value="Не выполнено"  onclick="<?=$onclick?>"/> 
	</td>         

	<td>
		<?php if($model->detail == 1){ $style= "enable_cur"; } else {$style= "enable";}  $onclick= "javascript:switchDetTab('1')";?>		
		<input class="btn btn-primary <?=$style?>"  type="button" value="На сегодня"  onclick="<?=$onclick?>"/> 
	</td>         

	<td>
		<?php if($model->detail == 2){ $style= "enable_cur"; }  else {$style= "enable";} $onclick= "javascript:switchDetTab('2')";?>		
		<input class="btn btn-primary <?=$style?>"  type="button" value="Просрочено"  onclick="<?=$onclick?>"/> 
	</td>         

	<td>
		<?php if($model->detail == 3){ $style= "enable_cur"; }  else {$style= "enable";} $onclick= "javascript:switchDetTab('3')";?>		
		<input class="btn btn-primary <?=$style?>"  type="button" value="Все"  onclick="<?=$onclick?>"/> 
	</td>         	
 </tr>
 </table>	
 
 <div class="bottom_cont">
 <div class="part-header"> Детализация задач на дату: 
 <?php if (empty ($model->filtDate))  {echo date("d-m-Y");}
       else  {echo  $model->filtDate;} ?> 
 </div>	
 <br>
 <?php
 if($model->detail == 0) $filterEventList = array( 	"1" => "назначено",	"3" => "просрочено");
 if($model->detail == 1) $filterEventList = array(	"1" => "назначено",	"2" => "выполнено",	"3" => "просрочено");
 if($model->detail == 2) $filterEventList = array(	"1" => "назначено",	"3" => "просрочено");
 if($model->detail == 3) $filterEventList = array(	"1" => "назначено",	"2" => "выполнено",	"3" => "просрочено");
 
 
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $detailProvider,
		'filterModel' => $model,	
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],			
            [
                'attribute' => 'event_date',
				'label'     => 'Дедлайн',
                'format' => ['datetime', 'php:d-m-Y'],
            ],			

            [
                'attribute' => 'eventTitle',
				'label'     => 'Тип события',
                'format' => 'raw',			
            ],			
			
            [
                'attribute' => 'refEvent',
				'filter'=>array(
				"0" => "Продолжить контакт",
				"1" => "Выяснение потребностей",				
				"3" => "Согласовать заявку",				
				"4" => "Резерв товара",
				"5" => "Выписать счет",
				"6" => "Ожидается: Счет получен клиентом",
				"7" => "Ожидается: Оплата произведена",
				"8" => "Ожидается: Гарантийные документы получены",
				"9" => "Ожидается: Деньги дошли",
				"10" => "Ожидается: Задание на отгрузку",
				"11" => "Ожидается: Поставка произведена",
				"12" => "Ожидается: Клиент подвердил поставку",
				"13" => "Ожидается: Отзыв получен",
				"14" => "Ожидается: Работа со счетом завершена",
				),
				'label'     => 'Событие',
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {					
				 return $model['eventNote'];				          
                },
			],	

/*            [
                'attribute' => 'title',
				'label'     => 'Организация',
                'format' => 'raw',
			],	*/
			[
                'attribute' => 'title',
				'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
							return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgId']."\",\"childWin\")' >".$model['title']."</a>";				          				          
                },
            ],		

			
			
/*			[
                'attribute' => 'eventStatus',
				'label'     => 'Статус',
                'format' => 'raw',
				'filter'=> $filterEventList,
                'value' => function ($model, $key, $index, $column) {
		
			     $statusTitles = array(	"1" => "назначено",	"2" => "выполнено",	"3" => "просрочено"	); 
				 							
                 return $statusTitles[$model['eventStatus']];
                },
            ],		*/
				
			[
                'attribute' => 'note',
				'label'     => 'Комментарий',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
					$r="";
				 if (!empty($model['contactDate'])){$r =  date("d.m.Y", strtotime($model['contactDate']))."<br>";}
				 if (!empty($model['contactFIO'])){$r.= $model['contactFIO']."<br>";}
				 if (!empty($model['note'])){$r.= $model['note'];}
				 return $r;
				}
			],	

			[
                'attribute' => 'id',
				'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
										
					if ($model['eventStatus'] == 2) {return "&nbsp;";}
					$commStr = "class='btn btn-primary' style='width: 110px;'  type='button'";
					switch ($model['ref_event'])
					 {
						case 0: 
						/*Холодный звонок*/
						//http://192.168.1.53/phone/web/index.php?r=cold/cold-init&id=28409							
							return "<input ".$commStr." value='Продолжить'  onclick=\"javascript:openWin('cold/cold-init&id=".$model['orgId']."','childWin');\" />";	
						break;

						case 1: 
						//http://192.168.1.53/phone/web/index.php?r=cold/cold-need&id=28417
						/*Выяснение потребностей*/							
							return "<input ".$commStr." value='Потребности'  onclick=\"javascript:openWin('cold/cold-need&id=".$model['orgId']."','childWin');\" />";	
						break;

						case 2: 						
						/*Первичная Заявка на счет*/
						//http://192.168.1.53/phone/web/index.php?r=cold/cold-schet&id=27153
						return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('cold/cold-schet&id=".$model['orgId']."','childWin');\" />";	
						break;

						case 3: 						
						/*Заявка на счет*/
						if ($model['zakazId'] == 0)
						{
						////http://192.168.1.53/phone/web/index.php?r=market/market-zakaz-create&id=27153
							return "<input ".$commStr." value='Заявка'  onclick=\"javascript:openWin('market/market-zakaz-create&id=".$model['orgId']."','childWin');\" />";	
						}		
						//http://192.168.1.53/phone/web/index.php?r=market/market-zakaz&orgId=29136&zakazId=8						
						return "<input ".$commStr." value='К заявке'  onclick=\"javascript:openWin('market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."','childWin');\" />";
						break;
						
						case 4: 						
						/*Резервирование товара*/
						//http://192.168.1.53/phone/web/index.php?r=market/market-reserve-zakaz&orgId=28417&zakazId=12
						return "<input ".$commStr." value='Резерв.'  onclick=\"javascript:openWin('market/market-reserve-zakaz&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />";
						break;
						
						case 5: 						
						/*Регистрация счета*/
						return "<input ".$commStr." value='К счету'  onclick=\"javascript:openWin('market/market-reg-schet&orgId=".$model['orgId']."&eventId=".$model['id']."&zakazId=".$model['zakazId']."','childWin');\" />";
						break;

						case 6: 
						/*Ведение счета*/
						//http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12					
						 $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
											[':refZakaz' => $model['zakazId'] ])->queryOne();
						 if (empty ($schetId)) {return "&nbsp;";}
						 return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />";
						break;

						case 7: 
						/*Поставка*/
						//http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12					
						 $schetId = Yii::$app->db->createCommand('SELECT id from {{%schet}} where refZakaz=:refZakaz', 
											[':refZakaz' => $model['zakazId'] ])->queryOne();
						 if (empty ($schetId)) {return "&nbsp;";}
						 return "<input ".$commStr." value='Счет'  onclick=\"javascript:openWin('market/market-schet&id=".$schetId['id']."','childWin');\" />";
						break;

						case 8: 
						/*Произвольный*/
						//http://192.168.1.53/phone/web/index.php?r=market/market-schet&id=12											 
						 return "<input ".$commStr." value='Контакт'  onclick=\"javascript:openWin('site/reg-contact&singleWin=1&id=".$model['orgId']."','childWin');\" />";
						break;
												
					}
               },
            ],		
/*			[
                'attribute' => 'id',
				'label'     => 'Выполнено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if ($model['eventStatus'] == 2) return "&nbsp;";
					return "<a class='btn btn-primary' href='index.php?r=market/event-mark&id=".$model['id']."'>Отметить</a>";
                  },
            ],		
*/
        ],
    ]
); 
?>
</div>
<br>
<?php }?> 

 
 
 