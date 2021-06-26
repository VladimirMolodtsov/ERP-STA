<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;


$this->title = 'Работа с клиентами ';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0020)) {return;}

 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

 
<style>

table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
.leaf {
    height: 80px; /* высота нашего блока */
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
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}


.local_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 30px;
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

.child {
  padding:5px;
  text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}


.gridcell {
	width: 85px;		
	height: 100%;
    display: block;
	/*background:DarkSlateGrey;*/
}	
.gridcell:hover{
	background:Silver;
    cursor: pointer;
	color:#FFFFFF;
}
.editcell{
   width: 85px;		
   display:none;
   white-space: nowrap;
}


</style>




<table border='0' width='1140px'>

    <tr>
        <td>
            <h2><?= Html::encode($this->title) ?></h2>
        </td>

        <td>
        <input class="btn btn-primary"  style="width:200px" type="button" value="Контакты (Менеджер)" onclick="javascript:openWin('site/head-manager-activity','childWin');"/>
        </td>
        
        <td>
        <input class="btn btn-primary"  style="width:200px" type="button" value="Контакты (Организация)" onclick="javascript:openWin('site/manager-org-stat&period=30','childWin');"/>
        </td>

        <td>
        <input class="btn btn-primary"  style="width:200px" type="button" value="Речевые модули" onclick="javascript:openWin('site/modules','childWin');"/></td>
        </td>        

        <td>
        <input class="btn btn-primary"  style="width:200px" type="button" value="Настройка " onclick="javascript:openWin('site/config','childWin');"/></td>
        </td>
    </tr>    
    
</table>

<br>
<?php $leafValue=$model->getLeafValue(); ?>           
<div class='main_cont' style='height:450px; border-radius: 0%;' >
 <table border='0' width='1140px'> 
 <tr>
   <td>

    <table border='0' width='600px'> 
     <tr>        
        <td><a  class='btn btn-primary leaf' style='background:<?php if ($leafValue['leadHeadCount']>0) echo "Brown"; else echo "ForestGreen"; ?>; color:White; height:60px;' 
        href='#' onclick="openWin('site/head-leads-list','childwin');"><div class='leaf-txt' style='font-size:12px;' >Лиды, рассмотреть: </div>
        <div class='leaf-val' style='font-size:20px;' ><?= $leafValue['leadHeadCount'] ?></div> </a> </td>
        <td> </td>
      </tr> 

      <tr>        

        <td> <a  class='btn btn-primary leaf' style='background:MintCream  ; color:Gray;'                  href='index.php?r=head/head-activity&detail=9#detail_list'><div class='leaf-txt'>В работе: </div><div class='leaf-val'><?= $model->getCurrentEvents(2) ?></div> </a></td>
           <?php $val = $model->getNoEvents(); if ($val>0){$color="Brown";} else {$color="ForestGreen";}  ?>
        <td> <a  class='btn btn-primary leaf' style='background:WhiteSmoke;  color:<?= $color ?>;'         href='index.php?r=head/head-activity&detail=10#detail_list'><div class='leaf-txt'>Брошено: </div><div class='leaf-val'><?= $val;?></div> </a></td>        
        <td><a  class='btn btn-primary leaf' style='background:MintCream ; color:Green;'                   href='index.php?r=head/head-activity&detail=11#detail_list'><div class='leaf-txt'>Новых в работе: </div><div class='leaf-val'><?= $model->getCurrentEvents(3) ?></div> </a></td>                
        <td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Gray;'               href='index.php?r=head/head-activity&detail=12#detail_list'>  <div class='leaf-txt'>Доступно новых: </div><div class='leaf-val'><?= $cold_model->noContactCount(); ?></div> </a></td>                
     </tr>        
        
    </table>
   </td>
    
 <td align='center' valign='top'>
 <div class='leaf' style='margin-top:0px;width:400px; height:280px; background:White ; color:Black; '>
 <?php $stats=$model->getStats(); ?>
    <table border='0' width='400px'> 
    <tr>
    <td> </td>
    <td> Пред. месяц </td>
    <td> Тек. месяц </td>
    <td> Сегодня</td> 
    </tr>

    <tr>
    <td> Событий</td>
    <td> <?= $stats['p_events']?> </td>
    <td> <?= $stats['m_events']?> </td>
    <td> <?= $stats['d_events']?> </td>
    </tr>

    <tr>
    <td> Активность</td>
    <td> <?= $stats['p_activity']?> </td>
    <td> <?= $stats['m_activity']?> </td>
    <td> <?= $stats['d_activity']?> </td>
    </tr>

    <tr>
    <td> Контакты </td>
    <td> <?= $stats['p_contacts']?> </td>
    <td> <?= $stats['m_contacts']?> </td>
    <td> <?= $stats['d_contacts']?> </td>
    </tr>
    
    
    <tr>
    <td> Заявок </td>
    <td> <?= $stats['p_zakaz']?> </td>
    <td> <?= $stats['m_zakaz']?> </td>
    <td> <?= $stats['d_zakaz']?> </td>
    </tr>

    <tr>
    <td> Счетов</td>
    <td> <?= $stats['p_schet']?> </td>
    <td> <?= $stats['m_schet']?> </td>
    <td> <?= $stats['d_schet']?> </td>
    </tr>

    <?php    
        $y_to = date("Y",time());
        $y_from=$y_to;
        $m_to = date("m",time());
        $m_from = $m_to -1;
        if ($m_from == 0){$m_from = 12;$y_from--;}
    ?>
    
    <tr>
    <td> Оплаты</td>
    <td> <a href="" onclick="javascript:openWin('fin/oplata-src&m_from=<?= $m_from ?>&y_from=<?= $y_from ?>&m_to=<?= $m_from ?>&y_to=<?= $y_from ?>','finWin');"> <?= number_format($stats['p_oplata'], 0, ',', '&nbsp;')?> </a> </td>
    <td> <a href="" onclick="javascript:openWin('fin/oplata-src&m_from=<?= $m_to ?>&y_from=<?= $y_to ?>&m_to=<?= $m_to ?>&y_to=<?= $y_to ?>','finWin');"><?= number_format($stats['m_oplata'], 0, ',', '&nbsp;')?> </a> </td>
    <td> <a href="" onclick="javascript:openWin('fin/oplata-src&setDate=<?= date("Y-m-d") ?>','finWin');"><?= number_format($stats['d_oplata'], 0, ',', '&nbsp;')?> </a></td>
    </tr>

    <tr>
    <td> Отгрузки</td>
    <td> <a href="" onclick="javascript:openWin('fin/supply-src&m_from=<?= $m_from ?>&y_from=<?= $y_from ?>&m_to=<?= $m_from ?>&y_to=<?= $y_from ?>','finWin');"><?= number_format($stats['p_supply'], 0, ',', '&nbsp;')?> </a></td>
    <td> <a href="" onclick="javascript:openWin('fin/supply-src&m_from=<?= $m_to ?>&y_from=<?= $y_to ?>&m_to=<?= $m_to ?>&y_to=<?= $y_to ?>','finWin');"><?= number_format($stats['m_supply'], 0, ',', '&nbsp;')?> </a></td>
    <td> <a href="" onclick="javascript:openWin('fin/supply-src&setDate=<?= date("Y-m-d") ?>','finWin');"><?= number_format($stats['d_supply'], 0, ',', '&nbsp;')?> </a></td>
    </tr>
    <tr>

    <td> Закуплено</td>
    <td> <?= number_format($stats['p_supplier'], 0, ',', '&nbsp;')?> </td>
    <td> <?= number_format($stats['m_supplier'], 0, ',', '&nbsp;')?> </td>
    <td> <?= number_format($stats['d_supplier'], 0, ',', '&nbsp;')?> </td>
    </tr>
    
    </table>    
 </div>
 <br>

<table border='0' width='360px'> 
<tr>
<td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Blue;' href='index.php?r=site/head-sale&detail=0#detail_list'>
        <div class='leaf-txt' >Всего сделок: </div>
        <div class='leaf-val'><?= $leafValue['allDeal'] ?></div> 
        <div class='leaf-sub'><?= $leafValue['allDealSumm'] ?></div>
</a></td>                

<td><a  class='btn btn-primary leaf' style='background:Blue ; color:White;'
                   href='index.php?r=site/head-sale&detail=15#detail_list'><div class='leaf-txt' >Реестр клиентов: </div><div class='leaf-val' ><?= $leafValue[11] ?></div> </a></td>                
</tr>

<tr>
<td colspan='2' align='center' style='padding:4px;'>
   <input class="btn btn-primary"  style="width:200px" type="button" value="Статистика за год " onclick="javascript:openWin('site/stat-year-sales','childWin');"/></td>
</td>                
</tr>


</table>
  

 
 </td></tr>
</table>  
</div>


<div class="bottom_cont" style='width:1140px;'>
<?php 
$t = "";
 switch ($model->detail)
 {
   case 0:
    $t = " Всего не закрытых сделок:";
   break;

   case 1:
    $t = " Заявки, по которым нет привязанного счета и не начался процесс согласования:";
   break;

   case 2:
    $t = " Заявки, по которым нет привязанного счета, но начался процесс согласования (контактов > 1):";
   break;

   case 3:
    $t = " Счета новые";
   break;
   
   case 4:
    $t = " Счета в работе, оплаты и отгрузки  не начались:";
   break;

   case 5:
    $t = " Задание на отгрузку, ждем движение:";
   break;
   
   case 6:
    $t = " Оплачено, в процессе отгрузки:";
   break;

   case 7:
    $t = " Отгружено, но оплата не началась:";
   break;

   case 8:
    $t = " Отгружено, в процессе оплаты:";
   break;

   case 9:
    $t = " Активность по произвольным контактам:";
   break;

   case 10:
    $t = " Брошенные клиенты - нет активной деятельности (последний контакт > 30 дней назад):";
   break;
   
   case 11:
    $t = " Активность по обработке холодной базы:";
   break;   
  
   case 12:
    $t = " Доступно в холодной базе:";
   break;   
    
   case 13:
    $t = " Ошибка (превышение) по суммам:";
   break;   
   
   case 14:
    $t = " Несовпадение номенклатуры и превышение по суммам:";
   break;   

   case 15:
    $t = " Реестр клиентов:";
   break;   
   
   default:
    $t = "Не завершенные сделки на сегодня:";
   break;
 

 }
 
 ?>


 
<a name="detail_list"></a>  <div class="part-header">  <?php   echo " ".$t;  ?>  </div>    
 <br>
 <?php 
 
 //if($model->detail < 9)          echo $model->printCurrentDealList  ($detailProvider, $model);
 //elseif ($model->detail == 9  )  echo $model->printContactEventList  ($detailProvider, $model);
 if ($model->detail == 10 )  echo $model->printLostList  ($detailProvider, $model);
 elseif ($model->detail == 11 )  echo $model->printContactEventList  ($detailProvider, $model);
 elseif ($model->detail == 12 )  echo $cold_view_model->printColdList($detailProvider, $cold_view_model );
 elseif ($model->detail == 15 )  echo $model->printClientReestr  ($detailProvider, $model);
 //else                            echo $model->printCurrentDealList  ($detailProvider, $model);
 
 
?>

<br>

<a href="#" onclick="openEditWin('index.php?r=site/head-sale&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 


</div>



<?php
if (!empty($model->debug)){
echo "<pre>";
print_r ($model->debug);
echo "</pre>";}
?>

 




