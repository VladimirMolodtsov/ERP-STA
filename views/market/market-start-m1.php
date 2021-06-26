<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use yii\grid\GridView;
use yii\data\SqlDataProvider;
use yii\widgets\Pjax;
use kartik\date\DatePicker;

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

//$noContactCount = $cold_model->noContactCount();
//$readyCount     = $cold_model->readyCount();
//print_r ($currentStatus);

$mode = $model->mode;
if ($model->userShow ==1) $mode = 2;

?>

<style>
table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}

 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkSlateGrey ;
	font-size: 14pt;
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


.selected_tab {	 width:100px; background-color: LimeGreen;	     }
.normal_tab   {	 width:100px; background-color: Green; 	 }

.selected_tab_v {	 width:80px; background-color:  LimeGreen;	     }
.normal_tab_v   {	 width:80px; background-color: Grey; 	 }

.tab_container {
				width:  320px;  /* ширина нашего блока */
				height: 70px; /* высота нашего блока */
				border: 0px solid #C1C1C1; /* размер и цвет границы блока */
				text-align: right;
				}


/* События */
.dealed { color:DimGrey; }
.todoed { color:Black; font-weight: bold; }


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



.leaf {
	height: 100px; /* высота нашего блока */
	width:  150px;  /* ширина нашего блока */
	border: 0px solid #C1C1C1; /* размер и цвет границы блока */
	padding:5px;
	font-weight:bold; 
	box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}
.leaf:hover {
	box-shadow: 0.4em 0.4em 5px #696969;
}

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 30px;
}

.leaf-sub {    
    font-size:11px;
    text-align: right;
    color:DimGrey;
}
.local_lbl
{
	
	padding: 2px;
	font-size: 10pt;
	background: white;
	color: black;
	border:1px solid;
	width: 120px;
	border-radius: 4px;
	display:inline-block;
	position:relative;
	top:2px;
	
}

</style>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


 <script>
function showDeliver()
{
  d = document.getElementById("deliverDate").value;
  url = 'store/show-deliver&detail=15&dFrom='+d+'&dTo='+d+'&format=print&noframe=1';
  openWin(url,'childwin');
}

function showRequestList()
{
  url = 'store/supply-request-reestr&noframe=1&mode=1';
  openWin(url,'childwin');
}

function openMyWin(url)
{
  wid=window.open("index.php?r="+url,'childwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}

function openExtWin(url)
{
  wid=window.open(url,'extChildwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}


function openFindWin()
{
  var str=document.forms["w0"]["marketcalendarform-strsearch"].value;
  if (str.trim() == '') return;
	//escape
  wid=window.open("index.php?r=market/market-search&noframe=1&findString="+(str.trim()),'childwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
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

function showResult (id)
{
    openWin('site/manager-result&noframe=1&uid='+id,'statWin');
    
    openWin('site/manager-result&noframe=1&y=<?= date('Y') ?>&m=<?= date('n') ?>&uid='+id,'statWin');

}


function reload_page() {
  location.reload();
}
//setInterval("reload_page()", 60000);

</script> 

<?php $form = ActiveForm::begin(); ?>					
<table border='0' width='1140px'><tr>
<td>

<?php $phoneStat=$model->getPhoneDayStatistics(); ?>
<div style='width:110px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  
background:WhiteSmoke; color:DarkBlue; top:-10px; height: 70px;	width:  100px;' href='#' onclick="openWin('/zadarma/ats/show-log','listLeadWin');">
<div style="font-size:14px">Звонки: </div><div style="color:Crimson; font-size:16px"><?=$phoneStat['dayCancel']?></div> 
<div class='leaf-sub'>не отв.  <?= date("d.m.y") ?></div>
</a>
</div>
</td>

<?php
  $nonLink = $mailModel->getMyNonLinkMail();
  if ($nonLink == 0) {$bg='#dbffbd'; $cl = 'DarkBlue';}
                else {$bg='Crimson'; $cl = 'White';}
?>

<td ><div style='width:110px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; 
background:<?=$bg?>; color:<?=$cl?>; height: 70px;	width:  100px;' href='index.php?r=site/get-mail'>
<div style="font-size:14px">Почта: </div><div style="font-size:16px"><?= $nonLink ?></div> </a>
</div>
</td>

<td><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:#dbffbd; color:DarkBlue; 
height: 70px;	width:  100px;' href='#' onclick="openWin('site/head-leads-list','listLeadWin');">
<div style="font-size:14px">Лиды: </div><div style="font-size:16px"><?= $model->getLeadsInWork()?></div> </a>
</div>
</td>

<td><div style='width:70px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:#dbffbd; color:DarkBlue; 
height: 70px;	width:  70px;' href='#' onclick="openWin('site/new-lead','newLeadWin');">
<div style="margin-top:15px;font-size:14px"><span class='glyphicon glyphicon-plus'></span></div> </a>
</div>
</td>

<td ><div style='width:10px;'></div></td>

<?php $sdelkaStat = $model->getZakazInWork(); ?>
<td><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; 
background:MintCream; color:DarkBlue; height: 70px;	width:  100px;' href='#' onclick='openFullWin("head/sdelka-list&noframe=1","sdelkaWin");' >
<div style="font-size:14px">Сделки: </div><div style="font-size:16px"><?= $sdelkaStat['allDeal'] ?></div> </a>
</div>
</td>

<td><div style='width:70px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; 
background:MintCream; color:DarkBlue; height: 70px;	width:  70px;' href='#' onclick="openWin('market/market-zakaz-create&noframe=1&id=0','childWin');" >
<div style="margin-top:15px;font-size:14px"><span class='glyphicon glyphicon-plus'></span></div> </a>
</div>
</td>


<td ><div style='width:10px;'></div></td>

<td><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; 
background:MintCream; color:DarkBlue; height: 70px;	width:  100px;' href='#' onclick='openFullWin("/managment/head/plan-production&noframe=1","childWin");' >
<div style="font-size:12px"> План <br> производства </div><div style="font-size:16px"></div> </a>
</div>
</td>


<td><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; 
background:MintCream; color:DarkBlue; height: 70px;	width:  100px;' href='#' onclick='openFullWin("/managment/head/ready-production&noframe=1","childWin");' >
<div style="font-size:12px"> Готовая <br> продукция </div><div style="font-size:16px"></div> </a>
</div>
</td>

<td ><div style='width:10px;'></div></td>

<td style='text-align:left;'>
<div style='margin-top:-12px;'>
<?php $action = "openSmallWin('store/ware-grp-sclad', 'wareGrpWin')";  ?>
<div class='btn btn-default' style='width:80px;'  onclick="<?= $action?>">Остатки</div>
<br>
<?php $action = "openWin('store/ware-price','priceWin');"; ?>
<div class='btn btn-primary' style='width:80px;' onclick="<?= $action?>">Прайс</div>
</div>
</td>

<td style='text-align:right;'>
<div style='margin-top:-12px;'>
<?php

  $style1= "selected_tab_v";
  $style2= "normal_tab_v";

if ($model->userShow == 1)
{
  $style1= "normal_tab_v";
  $style2= "selected_tab_v";
}

  $style3="selected_tab";
  $style4="normal_tab";
  
  
?>

<a  class="btn btn-primary <?=$style1?>" href="index.php?r=market/market-start&type=<?= $model->type ?>&tab=<?= $model->tab?>&detail=<?= $model->detail?>&mode=1">Мое</a>
<br>
<a  class="btn btn-primary <?=$style2?>" <?php if (!($curUser->roleFlg & 0x0080))echo "disabled";?> href="index.php?r=market/market-start&type=<?= $model->type ?>&tab=<?= $model->tab?>&detail=<?= $model->detail?>&mode=2">Общее</a>
</div>
</td>
<td>
<input class="btn btn-primary" style="width: 80px;"  type="button" value="Контакт" onclick="javascript:openWin('market/market-find&mode=2','childWin');"/>

</td>
</tr></table>
<?php ActiveForm::end(); ?>


<div class='main_cont' style='height:450px; border-radius: 0%;' >
 <table border='0' width='950px'> 
 <tr>
   <td>
	<table border='0' width='500px'> 
		<tr>		
		<td> <b>Работа с заявками <br> и счетами </b></td>
		<td> <table  border='0'><tr>
                <td><b>Контакты  </b></td> 
                <td><a class='btn btn-primary leaf' style='background:WhiteSmoke; color:Gray; height:25px; width:50px' href="index.php?r=market/market-start&mode=1&type=10#detail_list" > <?= Html::encode($model->getAllAvailableClients()) ?> </a> <br> 
                    <a class='btn btn-primary leaf' style='background:WhiteSmoke; color:Gray; height:25px; width:50px; margin-top:2px' href="index.php?r=market/market-start&mode=1&type=11#detail_list" >  <?= Html::encode($model->getMyClients()) ?> </a></td>
             </tr></table>
        </td>
		<td> 
				<table  border='0'><tr><td><b>Найти новых <br> клиентов	</b>	</td>
				<?php /*<td><a href=# class='btn btn-primary leaf' style='background:WhiteSmoke; color:Gray; height:25px; width:50px; margin-top:0px' onclick="javascript:openMyWin('cold/cold-view');" > <?= Html::encode($cold_model->availableCount()) ?> </a></td>
				*/?>
				</tr></table>
		</td>
		</tr> 
<!-- <div  class='leaf' style='background:Blue ; color:White;'> </div>-->
		<tr>		
		<td> <a  class='btn btn-primary leaf' style='background:MintCream  ; color:Crimson;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=1#detail_list'><div style="font-size:15px">На сегодня: </div><div style="font-size:30px"><?= $model->getCurrentEvents(4)?>/<?= $model->getCurrentEvents(1)?></div> </a></td>
		<td> <a  class='btn btn-primary leaf' style='background:MintCream  ; color:Blue;'    href='index.php?r=market/market-start&mode=<?= $mode ?>&type=4#detail_list'><div style="font-size:15px">На сегодня: </div><div style="font-size:30px"><?= $model->getCurrentEvents(5)?>/<?= $model->getCurrentEvents(2)?></div> </a></td>
		<td> <a  class='btn btn-primary leaf' style='background:MintCream  ; color:Gray;'    href='index.php?r=market/market-start&mode=<?= $mode ?>&type=7#detail_list'><div style="font-size:15px">Продолжить: </div><div style="font-size:30px"><?= $model->getCurrentEvents(3) + $model->getOtherEvents(3)?></div> </a></td>
		</tr> 

		<tr>
		<td> <a  class='btn btn-primary leaf' style='background:Crimson    ; color:White;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=2#detail_list'><div style="font-size:15px">Далее: </div><div style="font-size:30px"><?= $model->getOtherEvents(4)?>/<?= $model->getOtherEvents(1)?></div> </a></td>
		<td> <a  class='btn btn-primary leaf' style='background:SteelBlue  ; color:White;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=5&show=1#detail_list'> <div style="font-size:15px">Далее: </div><div style="font-size:30px"><?= $model->getOtherEvents(5)?>/<?= $model->getOtherEvents(2)?></div> </a></td>
		<td> <a  class='btn btn-primary leaf' style='background:Gray       ; color:White;' href=# > <div style="font-size:15px">Взять новых: </div><div style="font-size:30px"><?= $model->noContactCount();?></div> </a></td>
		</tr> 

		<tr>
		<td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Green;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=3#detail_list'>  <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents(1)?></div> </a></td>
		<td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Green;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=6#detail_list'>  <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents(2)?></div> </a></td>		
		<td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Green;' href='index.php?r=market/market-start&mode=<?= $mode ?>&type=9#detail_list'>  <div style="font-size:15px">Выполнено: </div><div style="font-size:30px"><?= $model->getFinishedTodayEvents(3)?></div> </a></td>		
		</tr> 		
	</table>
   </td>
	
  <td align='left' valign='top'>
 <!-- onclick='javascript:showResult(<?= $curUser->id ?>)' --> 
 <div class='leaf' style='margin-top:20px;width:390px; height:250px; background:White ; color:Black; ' >
 <?php $myStats=$model->getMyStats(); ?>
	<table border='0' width='390px'> 
	</tr>
	<td> </td>
	<td> За месяц </td>
	<td> Сегодня</td> 
	</tr>

	</tr>
	<td> Завершенных событий</td>
	<td> <?= $myStats['m_events']?> </td>
	<td> <?= $myStats['d_events']?> </td>
	</tr>

	</tr>
	<td> Контактов</td>
	<td> <?= $myStats['m_contacts']?> </td>
	<td> <?= $myStats['d_contacts']?> </td>
	</tr>

	</tr>
	<td> Заявок </td>
	<td> <?= $myStats['m_zakaz']?> </td>
	<td> <?= $myStats['d_zakaz']?> </td>
	</tr>

	</tr>
	<td> Счетов</td>
	<td> <?= $myStats['m_schet']?> </td>
	<td> <?= $myStats['d_schet']?> </td>
	</tr>

	</tr>
	<td> Оплаты</td>
	<td> <?= number_format($myStats['m_oplata'],0,'.','&nbsp;')?> </td>
	<td> <?= number_format($myStats['d_oplata'],0,'.','&nbsp;')?> </td>
	</tr>

	</tr>
	<td> Поставки</td>
	<td> <?= number_format($myStats['m_supply'],0,'.','&nbsp;')?> </td>
	<td> <?= number_format($myStats['d_supply'],0,'.','&nbsp;')?> </td>
	</tr>
	
	</tr>
	<td ><a href='#'onclick='openWin("/bank/operator/show-income","childWin")' >Выписка</a> <?= date("d.m.y H:i", strtotime($myStats['last_extract'])+4*3600 ) ?> </td>
  	<td> <a href='#'onclick='openWin("/bank/operator/show-income&flt=month","childWin")' ><?= number_format($myStats['m_extract'],0,'.','&nbsp;')?></a></td>
	<td> <a href='#'onclick='openWin("/bank/operator/show-income&flt=now","childWin")'   ><?= number_format($myStats['d_extract'],0,'.','&nbsp;')?></a></td>
	</tr>
	
	</table>	
 </div>
 <br>
 <table>  
 <tr> 
	<td width='175px'>Не найдены в 1С: </td>           
	<td width='75px'><?= Html::encode($model->getSchetNoRef1C()) ?></td>
    <td></td>
</tr>

<tr> 
	<td>Запрос на отгрузку: </td>           
	<td></td>
    <td><input class="btn btn-primary"  type="button"  value="Показать"  	onclick="javascript:showRequestList();"/></td>    
</tr>

<tr> 
	<td>Задания на доставку: </td>           
	<td><!--<input type='date' class='form-control' name='deliverDate' id='deliverDate' value='<?= date("Y-m-d") ?>' > -->
   <?php   
    echo DatePicker::widget([
    'name' => 'deliverDate',
    'id' => 'deliverDate',
    'value' => date("d.m.Y"),   
    'removeButton' => false, 
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'options' => ['style' =>'width:100px',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      

	
	</td>
    <td><input class="btn btn-primary"  type="button"  value="Показать"  	onclick="javascript:showDeliver();"/></td>    
</tr>

<!--
<tr>
    <td rowspan=2><!--<input class="btn btn-primary" style="width: 160px;background:Green;"  type="button"  value="Синхронизация с 1С"    
	onclick="javascript:openEditWin('data/sync-all');"/>
	<div style="font-size:8pt; margin:5px;">
		Процесс синхронизации с 1С занимает до 10 мин. Рекомендуется запускать в нерабочее время. 
	</div>
	</td> 		
</tr>-->
</table>  
 
  </td>
  
  <td align='left' valign='top'>
  <div class='leaf' style='margin-top:20px;width:260px; height:310px; background:White ; color:Black; padding:px; '>
  	<iframe width='250px' height='300px' frameborder='no'   src='index.php?r=tasks/market/event-exec-short&id=<?= $curUser->id ?>&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	     
  </div>    
  </td>
  </tr>
</table>  
</div>
 
 <div class="bottom_cont">
<?php 
$t = "";
switch ($model->type)
{
    case 1:
        $t = "Работа с заявками и счетами на сегодня (события запланированные на сегодня и просроченные):";
        $viewGrid = $model->printCurrentDealEventList  ($provider, $model);
    break;
    
    case 2:
         $t = "Работа с заявками и счетами далее :";
         $viewGrid = $model->printCurrentDealEventList  ($provider, $model);
    break;
    
    case 3:
         $t = "Работа с заявками и счетами выполнено:"; 
         $viewGrid = $model->printCurrentDealEventList  ($provider, $model);
    break;

    case 4:
        $t = "Контакты с клиентами  на сегодня (события запланированные на сегодня и просроченные):";
        $viewGrid = $model->printContactEventList($provider, $model);
    break;

    case 5:
        $t = "Контакты с клиентами  далее:";
        $viewGrid = $model->printContactEventList($provider, $model);
    break;

    case 6:
        $t = "Контакты с клиентами  выполнено:";    
        $viewGrid = $model->printContactEventList($provider, $model);
    break;

    case 7:
        $t = "Продолжить работу с новыми клиентами (события запланированные на сегодня и просроченные):";
        $viewGrid = $model->printSimpleEventList  ($provider, $model);
    break;

    case 8:
        $t = "Взять в работу новых клиентов:";
        $viewGrid = $model->printSimpleEventList  ($provider, $model);
    break;

    case 9:
        $t = "Работа с новыми клиентами выполнено:";
        $viewGrid = $model->printSimpleEventList  ($provider, $model);
    break;

    case 10:
        $t = "Клиенты все:";
        $viewGrid = $model->printClientList  ($provider, $model);
    break;

        case 11:
        $t = "Клиенты мои:";
        $viewGrid = $model->printClientList  ($provider, $model);
    break;

    
    default:
        $t = "Работа с заявками и счетами на сегодня:";
        $viewGrid = $model->printCurrentDealEventList  ($provider, $model);
    break;
}
    
 ?>
<a name="detail_list"></a> 
 <div class="part-header"> <?php   echo " ".$t;   ?> </div>	
 <br>
 <?php 

    echo $viewGrid; 

?>
</div>
<br>

<?php
if (!empty($model->debug)){
echo "<pre>";
print_r ($model->debug);
echo "</pre>";}
?>

 
