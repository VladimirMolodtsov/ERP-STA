<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Канбан';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;

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

.arrow-left {
  border: 24px solid transparent; 
  border-left-color: steelblue;  
  border-right: 0;
  display:inline-block;  
  margin: -19px 0px;
  }

.arrow-body {
    display:inline-block;
    background: steelblue;    
    width:1000px;
    text-align: center;
    color: white;
    position:relative;
    font-weight:bold;
  }

.selected_tab {	 width:120px; background-color: LimeGreen;	     }
.normal_tab   {	 width:120px; background-color: Green; 	 }

.selected_tab_v {	 width:120px; background-color:  LimeGreen;	     }
.normal_tab_v   {	 width:120px; background-color: Grey; 	 }

.tab_container {
				height: 70px; /* высота нашего блока */
				border: 0px solid #C1C1C1; /* размер и цвет границы блока */
				text-align: right;
                display: inline;
				}

  
  
</style>


<div style='width:1024px; text-align: right;'>
<div style='width:200px; display: inline;'> </div>

<div class='tab_container'> 

<?php

  $style1= "normal_tab_v";
  $style2= "selected_tab_v";

if ($model->mode == 1)
{
  $style1= "selected_tab_v";  
  $style2= "normal_tab_v";  
}


  $style3="selected_tab";
  $style4="normal_tab";
  
  
?>

<a  class="btn btn-primary <?=$style1?>" 
href="index.php?r=market/market-kanban&detail=<?= $model->detail?>&mode=1">Мое</a>
<div style='font-size:30px; display: inline; top:7px; position:relative'>/</div>
<a  class="btn btn-primary <?=$style2?>" <?php if (!($curUser->roleFlg & 0x0080))echo "disabled";?> 
href="index.php?r=market/market-kanban&detail=<?= $model->detail?>&mode=2">Общее</a>

<div style='display:inline-block; width:150px;'></div>
<a  class="btn btn-primary <?=$style4?>" 
href="index.php?r=market/market-start&mode=1">События</a>
<a  class="btn btn-primary <?=$style3?>" 
href="index.php?r=market/market-kanban&mode=1">Канбан</a>
<!-- <a  class="btn btn-primary <?=$style4?>" href="index.php?r=market/market-start&mode=3">Формы</a> -->
</div>
</div>

<br>
<?php $leafValue=$model->getLeafValue();
 ?>           
<div class='main_cont' style='width:1024px;height:350px; border-radius: 0%;' >
 <table border='0' width='1024px'> 
        
        <tr>        
            <td colspan='5' align='center' ><div >
                <div class='arrow-body'>Подготовка сделки </div><div class='arrow-left'></div> 
                </div>
            </td>
        </tr> 
        <tr>        
            <td colspan='5' align='center' >
            </td>
        </tr> 
        
        <tr>        
            <td><a  class='btn btn-primary leaf' style='background:Brown; color:White;' 
                href='#' onclick="javascript:openWin('site/lead-list','childwin');">
                <div class='leaf-txt'>Лиды: </div><div class='leaf-val'><?= $marketModel->getLeadsInWork()?></div> </a>
            </td>

            <td> <a  class='btn btn-primary leaf' style='background:MintCream  ; color:Blue;'                  
                href='index.php?r=market/market-kanban&detail=1&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt' >Заявки, новые: </div>
                <div class='leaf-val' ><?= $leafValue['newZakazMy'] ?>/<?= $leafValue['newZakaz'] ?></div> 
                <div class='leaf-sub' ><?= $leafValue['newZakazSumm'] ?></div>
                </a>
            </td>                

            <td> <a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Blue;'         
                href='index.php?r=market/market-kanban&detail=2&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>Заявки, в работе: </div>
                <div class='leaf-val'><?= $leafValue['zakazInWorkMy'] ?>/<?= $leafValue['zakazInWork'] ?></div>
                <div class='leaf-sub'><?= $leafValue['zakazInWorkSumm'] ?></div>
                </a>
            </td>
               
            <td><a  class='btn btn-primary leaf' style='background:MintCream ;  color:Blue;'          
                href='index.php?r=market/market-kanban&detail=3&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>Счета, новые </div>
                <div class='leaf-val'><?= $leafValue['newSchetMy'] ?>/<?= $leafValue['newSchet'] ?></div> 
                <div class='leaf-sub'><?= $leafValue['newSchetSumm'] ?></div>
                </a>
             </td>

            <td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Blue;'          
                href='index.php?r=market/market-kanban&detail=4&mode=<?=$model->mode?>#detail_list'> 
                <div class='leaf-txt'>Счета, в работе: </div>
                <div class='leaf-val'><?= $leafValue['schetInWorkMy'] ?>/<?= $leafValue['schetInWork'] ?></div> 
                <div class='leaf-sub'><?= $leafValue['schetInWorkSumm'] ?></div>
                </a>
            </td>
        </tr> 

        <tr>        
            <td colspan='5' align='center' ><div style='align:center;background: GhostWhite ;width:1050px;height:40px'></div> </td>
        </tr> 
        
     
        <tr>
        <td> </td>                
            <td> <a  class='btn btn-primary leaf' style='background:#f2ffe8  ; color:DarkBlue;'         
            href='index.php?r=market/market-kanban&detail=5&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>Отгрузка, ждет: </div>
                <div class='leaf-val' ><?= $leafValue['supplyWaitMy'] ?>/<?= $leafValue['supplyWait'] ?></div> 
                <div class='leaf-sub' ><?= $leafValue['supplyWaitSumm'] ?></div>
                </a>
            </td>            
            
            <td> <a  class='btn btn-primary leaf' style='background:#ebffdb ; color:DarkBlue;'         
                href='index.php?r=market/market-kanban&detail=6&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>В отгрузке:</div>      
                <div class='leaf-val'><?= $leafValue['supplyProcMy'] ?>/<?= $leafValue['supplyProc'] ?></div>
                <div class='leaf-sub' ><?= $leafValue['supplyProcSumm'] ?></div>
                </a>
            </td>

            <td><a  class='btn btn-primary leaf' style='background:#e3ffcc ; color:DarkBlue;'           
                href='index.php?r=market/market-kanban&detail=7&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>В Оплате: </div>
                <div class='leaf-val'><?= $leafValue['cashProcMy'] ?>/<?= $leafValue['cashProc'] ?></div>
                <div class='leaf-sub'><?= $leafValue['cashProcSumm'] ?></div>
                </a>
            </td>        

            <td><a  class='btn btn-primary leaf' style='background:#dbffbd  ; color:DarkBlue;'         
                href='index.php?r=market/market-kanban&detail=8&mode=<?=$model->mode?>#detail_list'>
                <div class='leaf-txt'>В завершении: </div>
                <div class='leaf-val'><?= $leafValue['finitProcMy'] ?>/<?= $leafValue['finitProc'] ?></div> 
                <div class='leaf-sub' ><?= $leafValue['finitProcSumm'] ?></div>
                </a>
            </td>
        </tr> 
        
        <tr>        
            <td colspan='5' align='center' > </td>
        </tr> 

        <tr>        
            <td colspan='5' align='center' ><div >
                <div class='arrow-body' style='background: ForestGreen;' > Исполнение сделки </div><div class='arrow-left' style='border-left-color: ForestGreen;'></div> 
                </div>
            </td>
        </tr> 
        
        
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
 
 if($model->detail < 9)          echo $model->printCurrentDealList  ($detailProvider, $model);
 elseif ($model->detail == 9  )  echo $model->printContactEventList  ($detailProvider, $model);
 elseif ($model->detail == 10 )  echo $model->printLostList  ($detailProvider, $model);
 elseif ($model->detail == 11 )  echo $model->printContactEventList  ($detailProvider, $model);
 elseif ($model->detail == 12 )  echo $cold_view_model->printColdList($detailProvider, $cold_view_model );
 elseif ($model->detail == 15 )  echo $model->printClientReestr  ($detailProvider, $model);
 else                            echo $model->printCurrentDealList  ($detailProvider, $model);
 
 
?>

<br>

<a href="#" onclick="openEditWin('index.php?r=site/head-start&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 


</div>



<?php
if (!empty($model->debug)){
echo "<pre>";
print_r ($model->debug);
echo "</pre>";}
?>

 




