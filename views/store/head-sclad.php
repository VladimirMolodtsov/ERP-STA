<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Отдел снабжения - управление';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}

$curUser=Yii::$app->user->identity;
if (!( ($curUser->roleFlg & 0x0010) || ($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x0100) ) ) {return;}


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

/*Контейнер для листиков*/
.leaf-cont {
     height: 190px;  /* высота нашего блока */
     width:  190px;  /* ширина нашего блока */
     border: 0px solid #C1C1C1; /* размер и цвет границы блока */
     padding:5px;
     font-weight:bold; 
     box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    background:White;
}

/* листики */
.leaf {
     height: 60px; /* высота нашего блока */
     width:  85px;  /* ширина нашего блока */
     border: 0px solid #C1C1C1; /* размер и цвет границы блока */
     padding:5px;
     font-weight:bold; 
    text-align:center;
     //box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    //background:MintCream;
    display:inline-block;    
        position:relative; 
}
.leaf:hover {
     box-shadow: 0.4em 0.4em 5px #696969;
    border: 1px solid #C1C1C1; /* размер и цвет границы блока */
    cursor:pointer;
}

.leaf-txt {     
     font-size:12px;
}
.leaf-val {     
     font-size:25px;
}

.leaf-mark
{
     text-align:center;
     font-size: 12pх;
    font-weight: bold;
    background:Crimson;
     color: Yellow;
     width:  20px;
    height: 20px;
     display:inline-block;
     position:relative;
     /*top:2px;*/
    border-radius: 50%;    
}

.leaf-lbl
{
     padding: 2px;
     font-size: 12pt;
     color: black;
     width: 120px;
     display:inline-block;
     position:relative;
     top:2px;         
}

.leaf-top
{
  top:2px;     
  float:right;  
}
.leaf-center
{
  height: 70px; /* высота нашего блока */
  width:  80px;  /* ширина нашего блока */    
  top:20px;  
  left:5px;  
  float:center;      
}
.leaf-bottom-left
{
  top:20px;  
  float:left;    
}
.leaf-bottom-right
{
  margin-right:5px;    
  top:20px;     
  float:right;     
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

.small_btn {
    //background-color: Green ;
     //width: 50px;
     font-size: 10px;
     margin:4px;
     padding:4px;
} 

</style>


 <script>
function switchDetail(detail)
{     
     document.location.href="index.php?r=store/head-sclad&detail="+detail+"#detail_list";
}

function switchGoodAnalyze(id)
{
     openSwitchWin("store/switch-analyze&id="+id);
}

</script> 


<h2><?= Html::encode($this->title) ?></h2>
<hr>
<div class='main_cont' style='height:250px; border-radius: 0%;' >
 <table border='0' width='1140px'> 
 <tr>
   <td>
   
   <table border='0' width='650px'> 
    <tr>
    <td>
        <div class='leaf-cont'>
        <?php //echo "<div class='leaf-mark'> ! </div>"; ?>
        <div class='leaf-lbl'>Отгрузки</div>
        <?php $color='DarkGreen'; if ($leafValue['requestNew'] >0) $color='Brown'; ?>
        <div class='leaf leaf-top' style='color:<?= $color ?>;' onclick="javascript:switchDetail('1');" >
             <div class='leaf-txt' >Новые: </div><div class='leaf-val' ><?= $leafValue['requestNew'] ?></div> 
        </div>
        <div class='leaf leaf-center' style='color:DarkBlue;' onclick="javascript:switchDetail('10');" >
            <div class='leaf-txt' style='font-size:13px;' >Активные: </div>
            <div class='leaf-val' style='font-size:35px;' ><?= $leafValue['requestInExec']+$leafValue['requestNew']+$leafValue['requestFinished'] ?></div>
        </div>
        <div class='leaf leaf-bottom-left' style='color:DarkBlue;' onclick="javascript:switchDetail('2');">
        <div class='leaf-txt' >В процессе: </div>
        <div class='leaf-val' ><?= $leafValue['requestInExec'] ?></div>
        </div>
        <div class='leaf leaf-bottom-right' style='color:DarkGreen;' href='#' onclick="javascript:switchDetail('3');">
            <div class='leaf-txt' >Выполнено: </div><div class='leaf-val' ><?= $leafValue['requestFinished'] ?></div> 
        </div>
       </div>
    </td>
              
        <td><div class='leaf-cont'>
        <div class='leaf-mark' style='visibility: hidden;' > ! </div>
        <div class='leaf-lbl'>Доставка</div>
        <div class='leaf leaf-top' style='color:Blue;' href='#' onclick="javascript:switchDetail('4');">
            <div class='leaf-txt' >Новые: </div><div class='leaf-val' ><?= $leafValue['deliverNew'] ?></div>
        </div>
        <div class='leaf leaf-center' style='color:Blue;' href='#' onclick="javascript:switchDetail('15');">
            <div class='leaf-txt' >Активные: </div><div class='leaf-val' style='font-size:35px;' >
            <?= $leafValue['deliverNew']+ $leafValue['deliverProcess']+$leafValue['deliverFinit'] ?></div> 
        </div>
        
        <div class='leaf leaf-bottom-left' style='color:Blue;' href='#' onclick="javascript:switchDetail('5');">
            <div class='leaf-txt' >В процессе: </div><div class='leaf-val' style='' ><?= $leafValue['deliverProcess'] ?></div> 
        </div>

        <div class='leaf leaf-bottom-right'  style='color:ForestGreen;' href='#' onclick="javascript:switchDetail('6');">
            <div class='leaf-txt' >Доставлено: </div><div class='leaf-val' ><?= $leafValue['deliverFinit'] ?></div> 
        </div>
       </div></td>

       <td><div class='leaf-cont'>
        <div class='leaf-mark' style='visibility: hidden;' > ! </div>
        <div class='leaf-lbl' >Закупки</div>
        <div class='leaf leaf-top' style='color:DarkGreen;' onclick="javascript:switchDetail('7');" >
             <div class='leaf-txt' >Поставщиков: </div><div class='leaf-val' ><?= $leafValue['supplierCount'] ?></div> 
        </div>
              
        <div class='leaf leaf-center' style='color:Brown;' onclick="javascript:document.location.href='index.php?r=store/purchase-table&mode=0';" >
            <div class='leaf-txt' >Активные: </div><div class='leaf-val' style='font-size:35px;' ><?= $leafValue['supplierActiveSchet'] ?></div>
        </div>
        
        <!--<div class='leaf leaf-bottom-left'></div>-->
        <div class='leaf leaf-bottom-right' style='color:DarkGreen;' href='#' onclick="javascript:switchDetail('9');">
            <div class='leaf-txt' >Товар: </div><div class='leaf-val' ><?= $leafValue['goodPositions'] ?></div> 
        </div>
       </div></td>       


       <td><div class='leaf-cont'>
        <div class='leaf-mark' style='visibility: hidden;' > ! </div>
        <div class='leaf-lbl'>Склад</div>
        <div class='leaf leaf-top' style='color:DarkCyan;' href='#' onclick="javascript:switchDetail('11');">
            <div class='leaf-txt' >В Заказах: </div><div class='leaf-val' style='font-size:15px;' >
                <?= number_format($leafValue['otlozheno'],0,'.','&nbsp;') ?></div>
        </div>       

        
        <?php
            if     ($leafValue['storeStatus'] > 80) $color = 'ForestGreen';
            elseif ($leafValue['storeStatus'] > 50) $color = 'DarkOliveGreen';
            elseif ($leafValue['storeStatus'] > 30) $color = 'DarkOrange';
              else                                  $color = 'Crimson';
        
        ?>

        <div class='leaf leaf-center' style='color:<?= $color ?>;' href='#' onclick="javascript:switchDetail('12');">            
            <div class='leaf-txt' >Наполнение: </div><div class='leaf-val' style='font-size:30px;' ><?= number_format($leafValue['storeStatus'],1,'.','&nbsp;') ?>%</div> 
        </div>
        
        <div class='leaf leaf-bottom-left'  style='color:DarkBlue;' href='#' onclick="javascript:switchDetail('13');">
        <div class='leaf-txt' >На складе: </div><div class='leaf-val' style='font-size:15px;' >
                <?= number_format($leafValue['amount'],0,'.','&nbsp;') ?></div>
        </div>
        <div class='leaf leaf-bottom-right' style='color:DarkGreen;' href='#' onclick="javascript:switchDetail('14');">
        <div class='leaf-txt' >В пути: </div><div class='leaf-val' style='font-size:15px;' >
                <?= number_format($leafValue['inTransit'],0,'.','&nbsp;') ?></div></div>
       </div></td>       
       
       
    </tr>
   
   </table>   
 </td>
 <!-- Правая часть -->
 <td align='center' valign='top'>
 <div class='leaf-cont' style='margin-top:20px;width:350px; height:190px; background:White ; color:Black; '>
 
 <?php $stats=$model->getStats(); ?>
     <table border='0' width='350px' style='text-align:left;'> 
     <tr>
     <td> </td>
     <td>За месяц </td>
     <td>Сегодня</td> 
     </tr>

     <tr>
        <td> Заявок поступило </td>
        <td> <?= $stats['m_accept']?> </td>
        <td> <?= $stats['d_accept']?> </td>
     </tr>

        <tr>
        <td> Отказано </td>
        <td> <?= $stats['m_reject']?> </td>
        <td> <?= $stats['d_reject']?> </td>
     </tr>
    <tr>
        <td> Отгрузок завершено </td>
        <td> <?= $stats['m_supplied']?> </td>
        <td> <?= $stats['d_supplied']?> </td>
     </tr>

     <tr>
        <td> Закупок завершено </td>
        <td> <?= $stats['m_buy']?> </td>
        <td> <?= $stats['d_buy']?> </td>
     </tr>

     <tr>
        <td> Доставок завершено </td>
        <td> <a href="#" onclick="openWin('head/deliver-wares-finit','deliverWin');" ><?= $stats['m_delivered']?> </a></td>
        <td> <a href="#" onclick="openWin('head/deliver-wares-finit','deliverWin');" ><?= $stats['d_delivered']?> </a></td>
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
<?php  $t ="Детализация: "; 

       switch($model->detail)
      {
           case 1:
               $t .="отгрузки, новые "; 
          break;
          
          case 2:
               $t .="отгрузки, в процессе "; 
          break;

          case 3:
               $t .="отгрузки, выполнено "; 
          break;

        
          case 4:
               $t .="доставка, новые "; 
          break;

          case 5:
               $t .="доставка, в процессе "; 
          break;
          
          case 6:
               $t .="доставка, выполнено "; 
          break;

        case 15:
               $t .="доставка, все "; 
          break;

        
        /* закупки */
          case 7:
               $t .="закупки, список поставщиков "; 
          break;

          case 8:
               $t .="закупки, активные счета от поставщиков "; 
          break;
          
          case 9:
               $t .="закупки, товар - поставщики "; 
          break;
        
        /*Склад*/
          case 11:
               $t .="склад, товар в заказах, не поставлен "; 
          break;

          case 12:
               $t .="склад, наполнение "; 
          break;
        
        
        case 13:
               $t .="склад, товар на складе "; 
          break;

        case 14:
               $t .="склад, товар в пути "; 
          break;
        
        
          default:
          
          break;
      }
      


?>
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
        case 15:
               $deliverModel->printDeliverRequest($provider);          
          break;

        /* закупки */
          case 7:
               $model->printSupplierList($provider);          
          break;

          case 8:
               $model->printSupplierSchetList($provider);               
          break;
          
          case 9:
               $model->printSupplierGoods($provider);          
          break;
        
        /*Склад*/
          case 11:
               $model->printGoodsInOrder($provider);          
          break;

          case 12:
               $model->printGoodsInPredict($provider);          
          break;
        
        
        case 13:
               $model->printGoodsInStore($provider);          
          break;

        case 14:
               $model->printGoodsInTransit($provider);          
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

<?php if ($model->detail >= 11 && $model->detail <= 14) {?>
    <p> <a href="#" onclick="openEditWin('index.php?r=store/sclad-start2&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить Excell</a> </p>
    <p> <a href="#" onclick="openEditWin('index.php?r=store/sclad-start2&<?= Yii::$app->request->queryString  ?>&format=google');"> Выгрузить GoogleSheet</a> </p>
<?php } ?> 
 


