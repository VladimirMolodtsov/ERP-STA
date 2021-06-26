<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Отдел снабжения';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
	
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0010)) {return;}



 $leafValue = $model->getLeafValue();
 

 ?>
 

 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

 
<style>

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


table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
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

.leaf-txt {	
	font-size:15px;
}
.leaf-val {	
	font-size:25px;
}


.local_btn {
	font-size: 12px;
	margin:4px;
	padding:4px;
	width:100px;
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

.arrow-left {
  border: 10px solid transparent; 
  border-left-color: steelblue;  
  border-right: 0;
  display:inline-block;  
  margin: -5px 10px;
  }


</style>


 <script>
function switchDetail(detail)
{	
	document.location.href="index.php?r=store/sclad-start2&detail="+detail+"#detail_list";
}
</script> 


<h2><?= Html::encode($this->title) ?></h2>

<div class="row">  
	<div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px" type="button" value="Состояние склада и отвесы" onclick="javascript:openWin('data/sync-price','childWin');"/>
   </div>   

	<div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px;" type="button" value="Заявки на отгрузку" onclick="javascript:openWin('store/supply-request-list','childWin');"/>
   </div>   
   
	<div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px"   type="button" value="Задания экспедитору" onclick="javascript:openWin('store/deliver-list','childWin');"/>
   </div>   

   <div class="col-lg-3 button_menu">
		<input class="btn btn-primary"  style="width:220px" disabled="true"  type="button" value="Заявки на закупку" onclick="javascript:openWin('store/good-request-list','childWin');"/>
   </div>   

</div>      
<hr>
<div class='main_cont' style='height:450px; border-radius: 0%;' >
 <table border='0' width='1140px'> 
 <tr>
   <td>

	<table border='0' width='600px'> 
		<tr>		
		<td> <b>Заявки </b></td>
		<td> <b>Задания экспедитору</b></td>
		<td> <b>Закупки</b> </td>		
		</tr> 
		
		<tr>				
          <?php $color='DarkGreen'; if ($leafValue['requestNew'] >0) $color='Brown'; ?>
		  <td> <a  class='btn btn-primary leaf' style='background:MintCream; color:<?= $color ?>;' href='#' onclick="javascript:switchDetail('1');">
			 <div class='leaf-txt' >Новые: </div><div class='leaf-val' ><?= $leafValue['requestNew'] ?></div> 
		  </a></td>				
	
		  <td> <a    class='btn btn-primary leaf' style='background:MintCream; color:Blue;' href='#' onclick="javascript:switchDetail('4');">
			 <div class='leaf-txt' >Новые: </div><div class='leaf-val' ><?= $leafValue['deliverNew'] ?></div> 
		  </a></td>				

		  <td> <a disabled="true"  class='btn btn-primary leaf' style='background:MintCream; color:Blue;' href='#' onclick="javascript:switchDetail('7');">
			 <div class='leaf-txt' >Новые: </div><div class='leaf-val' ><?= $leafValue[9] ?></div> 
		  </a></td>						
		</tr> 


		<tr>				
		  <td> <a  class='btn btn-primary leaf' style='background:DarkBlue; color:White;' href='#' onclick="javascript:switchDetail('2');">
			 <div class='leaf-txt' >В процессе: </div><div class='leaf-val' ><?= $leafValue['requestInExec'] ?></div> 
		  </a></td>				
	
		  <td> <a    class='btn btn-primary leaf' style='background:Blue; color:White;' href='#' onclick="javascript:switchDetail('5');">
			 <div class='leaf-txt' >В доставке: </div><div class='leaf-val' ><?= $leafValue['deliverProcess'] ?></div> 
		  </a></td>				

		  <td> <a disabled="true"  class='btn btn-primary leaf' style='background:MintCream; color:Blue;' href='#' onclick="javascript:switchDetail('8');">
			 <div class='leaf-txt' >В процессе: </div><div class='leaf-val' ><?= $leafValue[6] ?></div> 
		  </a></td>						
		</tr> 


		<tr>				
		  <td> <a  class='btn btn-primary leaf' style='background:DarkGreen; color:White;' href='#' onclick="javascript:switchDetail('3');">
			 <div class='leaf-txt' >Выполнено: </div><div class='leaf-val' ><?= $leafValue['requestFinished'] ?></div> 
		  </a></td>				
	
		  <td> <a   class='btn btn-primary leaf' style='background:ForestGreen; color:White;' href='#' onclick="javascript:switchDetail('6');">
			 <div class='leaf-txt' >Доставлено: </div><div class='leaf-val' ><?= $leafValue['deliverFinit'] ?></div> 
		  </a></td>				

		  <td> <a disabled="true"  class='btn btn-primary leaf' style='background:MintCream; color:Blue;' href='#' onclick="javascript:switchDetail('9');">
			 <div class='leaf-txt' >Выполнено: </div><div class='leaf-val' ><?= $leafValue[9] ?></div> 
		  </a></td>						
		</tr> 

		
	</table>
   </td>

 <td align='center' valign='top'>
 <div class='leaf' style='margin-top:0px;width:370px; height:220px; background:White ; color:Black; '>
 <?php $stats=$model->getStats(); ?>
	<table border='0' width='360px'> 
	</tr>
	<td> </td>
	<td> За месяц </td>
	<td> Сегодня</td> 
	</tr>

	</tr>
	<td> Заявок принято</td>
	<td> <?= $stats['m_accept']?> </td>
	<td> <?= $stats['d_accept']?> </td>
	</tr>

	</tr>
	<td> Поставок завершено</td>
	<td> <?= $stats['m_delivered']?> </td>
	<td> <?= $stats['d_delivered']?> </td>
	</tr>

	</tr>
	<td> Закупок завершено </td>
	<td> <?= $stats['m_buy']?> </td>
	<td> <?= $stats['d_buy']?> </td>
	</tr>

	</table>	
 </div>
 <br>
 
 </td></tr>
</table>  
</div>	
<br>
<a name="detail_list"></a>
<div class="bottom_cont">
<?php  $t ="Детализация"; ?>
<div class="part-header">  <?php   echo " ".$t;  ?>  </div>	
<br>
 <?php     
 
 	 switch($model->detail)
	 {
	 	case 1:
			$model->printSupplyRequest($provider);		
		break;
		
		case 2:
			$model->printSupplyRequest($provider);		
		break;

		case 3:
			$model->printSupplyRequest($provider);		
		break;

        
		case 4:
			$deliverModel->printDeliverRequest($provider);		
		break;

		case 5:
			$deliverModel->printDeliverRequest($provider);		
		break;
		
		case 6:
			$deliverModel->printDeliverRequest($provider);		
		break;

        
		case 7:
			$model->printSupplyRequest($provider);		
		break;

		case 8:
			$model->printSupplyRequest($provider);			
		break;
		
		case 9:
			$model->printSupplyRequest($provider);		
		break;

		default:
			$model->printSupplyRequest($provider);		
		break;
	 }
 	
 ?>

<br>

</div>

<br>
<?php
if (!empty($model->debug)){
echo "<pre>";
print_r ($model->debug);
echo "</pre>";}
?>


 


