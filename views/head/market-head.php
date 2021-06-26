<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Коммерческий директор';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
   
/*    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0008)) {return;}
*/

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

h3 {

text-align:center;
}


.leaf-round { 
   border-radius: 50%;
    height: 75px; /* высота нашего блока */
    width:  75px;  /* ширина нашего блока */    
}

.leaf-ellipse{ 
   border-radius: 50%;
    height: 75px; /* высота нашего блока */
    width: 150px;  /* ширина нашего блока */    
}


</style>


<h2><?= Html::encode($this->title) ?></h2>


<?php $leafValue=$model->getLeafValue(); ?>           
<div class='main_cont' style='height:250px; border-radius: 0%;' >
 <table border='0' width='1140px'> 

 <tr>
   <td valign='top'>

    <table border='0' width='600px'> 
<tr> 

<td style='width:110px;'><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:#dbffbd; color:DarkBlue; height: 60px;	width:  90px;' href='#' onclick="openWin('site/head-leads-list','listLeadWin');">
<div style="font-size:14px">Лиды: </div><div style="font-size:16px"><?=  $leafValue['leadHeadCount']?></div> </a>
</div>
</td>

<td width='60'><div style='width:70px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:#dbffbd; color:DarkBlue; height: 60px;	width:  60px;' href='#' onclick="openWin('site/new-lead','newLeadWin');">
<div style="margin-top:15px;font-size:14px"><span class='glyphicon glyphicon-plus'></span></div> </a>
</div>
</td>
<td width='100' ><div style='width:60px;'></div></td>

<td><div style='width:100px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:MintCream; color:DarkBlue; height: 60px;	width:  90px;' href='#' onclick='openFullWin("head/sdelka-list&noframe=1","sdelkaWin");' >
<div style="font-size:14px">Сделки: </div><div style="font-size:16px"><?= $leafValue['allDeal'] ?></div> </a>
</div>
</td>

<td width='100'><div style='width:70px;text-align:center;'> <a  class='btn btn-primary leaf' style='position:relative;  top:-10px; background:MintCream; color:DarkBlue; height: 60px;	width:  60px;' href='#' onclick="openWin('market/market-zakaz-create&id=0','childWin');" >
<div style="margin-top:15px;font-size:14px"><span class='glyphicon glyphicon-plus'></span></div> </a>
</div>
</td>

<td></td>
<td></td>
</tr>        

    
    
        <tr>
       <?php
            if     ($leafValue['storeStatus'] > 80) $color = 'ForestGreen';
            elseif ($leafValue['storeStatus'] > 50) $color = 'DarkOliveGreen';
            elseif ($leafValue['storeStatus'] > 30) $color = 'DarkOrange';
              else                                  $color = 'Crimson';
        ?>

        <td  colspan=2><a  class='btn btn-primary leaf' style='background:Blue ; color:White;'
                   href='index.php?r=head/client-reestr'><div class='leaf-txt' >Реестр клиентов: </div><div class='leaf-val' ><?= $leafValue[11] ?></div> </a></td>                        
        
        
       <td colspan=2><a  class='btn btn-primary leaf' style='background:MintCream ; color:<?= $color ?>;' href='#' onclick="openWin('store/ware-grp-sclad','scladWin')">
        <div class='leaf-txt'>Склад: </div>
        <div class='leaf-val'><?php// number_format($leafValue['storeStatus'],1,'.','&nbsp;') ?></div>
        <div class='leaf-sub' ></div>
        </a></td>        
        
<!--        <td><a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Blue;' href='index.php?r=/head/sdelka-list'>
            <div class='leaf-txt' >Всего сделок: </div>
            <div class='leaf-val'><?= $leafValue['allDeal'] ?></div> 
            <div class='leaf-sub'><?= $leafValue['allDealSumm'] ?></div>
        </a></td>                
-->

                   
       <td  colspan=2>
           <?PHP
              $nonLink = $mailModel->getAllNonLinkMail();
              if ($nonLink == 0) {$bg='#dbffbd'; $cl = 'DarkBlue';}
                        else {$bg='Crimson'; $cl = 'White';}
              ?>
              <div  class='btn btn-primary leaf ?>' style='background: <?=$bg ?>; color:<?= $cl ?>;' onclick="javascript:document.location.href='index.php?r=site%2Fprocess-mail';">
                <div class='leaf-txt' style='font-size:  14px'> Не обработано <br> почты </div>
                <div class='leaf-val'  style='font-size: 20px'><?= $nonLink ?></div> 
                <div class='leaf-sub' ></div>
              </div>
         </td>     

                   
        </tr>         

        <tr>

       <td width='200px' height='110px' >
        <!--<div class='leaf leaf-round' style='background:Blue; color:White; left:0px; top:0px;'  
        onclick='openExtWin("https://docs.google.com/spreadsheets/d/12XnPTG5Fewhh2fZBJzKIGDfiyXv382vL7Io1RcjGnCY/edit?usp=sharing","plan");;'> <div class='leaf-txt' style='margin-left:25px; margin-top:20px;' >IT</div>
        </div>-->
       </td>
       
       <TD></TD>
       
       
         
 
         
       </tr>         


        
    </table>
   </td>
    
 <td align='center' valign='top'>
 <div class='leaf' style='margin-top:-40px;width:420px; height:320px; background:White ; color:Black; '>
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
    
    	</tr>
	<td ><a href='#'onclick='openWin("/bank/operator/show-income","childWin")' >Выписка</a></td>
    <td> <a href='#'onclick='openWin("/bank/operator/show-income&flt=month","childWin")' ><?= number_format($stats['p_extract'],0,'.','&nbsp;')?></a></td>
  	<td> <a href='#'onclick='openWin("/bank/operator/show-income&flt=month","childWin")' ><?= number_format($stats['m_extract'],0,'.','&nbsp;')?></a></td>
	<td> <a href='#'onclick='openWin("/bank/operator/show-income&flt=now","childWin")'   ><?= number_format($stats['d_extract'],0,'.','&nbsp;')?></a></td>
	</tr>

    
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
  

 
 </td></tr>
</table>  
</div>

<table  width="600px" border=0>  

   <tr>
      <td colspan=3>
            <h3> Продажи </h3>
      </td>
   
   </tr>   


   <tr>
      <td>
      <input class="btn btn-primary"  style="width:260px" type="button" value="Приход денег" onclick="javascript:openWin('fin/oplata-src','finWin');"/></td>
      </td>


        <td>
      <input class="btn btn-primary"  style="width:260px" type="button" value="Отгрузка товара" onclick="javascript:openWin('fin/supply-src','finWin');"/>
      </td>
   
      <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Счета клиентам" onclick="javascript:openWin('fin/client-schet-src','finWin');"/></td>
      </td>

      <td>
      </td>
   
   </tr>   


   <tr>
        <td>
        <input class="btn btn-primary"  style="width:260px" type="button" value="Контакты (Менеджер)" onclick="javascript:openWin('site/head-manager-activity','childWin');"/>
        </td>
        
        <td>
        <input class="btn btn-primary"  style="width:260px" type="button" value="Контакты (Организация)" onclick="javascript:openWin('site/manager-org-stat&period=30','childWin');"/>
        </td>


        <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Планирование и контроль продаж" onclick="javascript:openWin('head/client-activity','childWin');"/></td>
      
       </td>

        <td>
                    <input class="btn btn-primary"  style="width:260px" type="button" value="Активность" onclick="javascript:document.location.href='index.php?r=tasks/market/task-global-control';"/>         
       </td>       
       
               
   </tr>   

 
   <tr>
        <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Договора" onclick="javascript:document.location.href='index.php?r=head/contracts-list';"/> 
        </td>
        
        <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Список задач" onclick="javascript:document.location.href='index.php?r=/tasks/market/task-list';"/>         
        
        
        </td>


        <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Календарь" onclick="javascript:document.location.href='index.php?r=tasks/market/event-log';"/>         
       </td>       
        
        <td>
                    <input class="btn btn-primary"  style="width:260px" type="button" value="Контроль" onclick="javascript:document.location.href='index.php?r=tasks/market/task-control';"/>         
       </td>       
   </tr>   


    

   <tr>
      <td colspan=3>
            <h3> Поставки </h3>
      </td>
   
   </tr>   
<?php $leafValue = $model->getMarketDirectoryLeafValue(); ?>

   <tr>
      <td>
      <?php $t="Закупки. На согласование  ".$leafValue['zaprosNotAccepted']; ?> 
            <input class="btn btn-primary"  style="width:260px" type="button" value="<?= $t ?>" onclick="javascript:document.location.href='index.php?r=store/zapros-table&mode=1';"/></td>
      </td>

        <td>
              <input class="btn btn-primary"  style="width:260px" type="button" value="Склад-управление" onclick="javascript:document.location.href='index.php?r=store/head-sclad&detail=12';"/>
      </td>


      <td>
      <?php $t="Согласование отгрузок ".$leafValue['supplyRequestNotAccepted']; ?> 
            <input class="btn btn-primary"  style="width:260px" type="button" value="<?= $t ?>" onclick="openWin('store/supply-request-reestr&mode=1','childWin');"/></td>      
      </td>
        
   </tr>   



   <tr>
      <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Незавершенные счета" onclick="javascript:openWin('head/active-sdelka','childWin');"/></td>
      </td>

        <td>
      </td>


      <td>
      </td>
        
   </tr>   

        
</table>





