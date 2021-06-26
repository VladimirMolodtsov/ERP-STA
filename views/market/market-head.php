<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Коммерческий директор';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
   
/*    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0008)) {return;}
*/

$leafValue = $model->getMarketDirectoryLeafValue();
 ?>
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
 
<style>
table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 15px;
}
 .button_menu{
    padding: 15px;    
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

h3 {
    text-align: center;
}    
 
</style>


<h2><?= Html::encode($this->title) ?></h2>

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
        
   </tr>   

    
    

   <tr>
      <td colspan=3>
            <h3> Поставки </h3>
      </td>
   
   </tr>   


   <tr>
      <td>
      <?php $t="Закупки. На согласование  ".$leafValue['zaprosNotAccepted']; ?> 
            <input class="btn btn-primary"  style="width:260px" type="button" value="<?= $t ?>" onclick="javascript:document.location.href='index.php?r=store/zapros-table&mode=1';"/></td>
      </td>


        <td>
      
      </td>


      <td>
      <?php $t="Согласование отгрузок ".$leafValue['supplyRequestNotAccepted']; ?> 
            <input class="btn btn-primary"  style="width:260px" type="button" value="<?= $t ?>" onclick="javascript:document.location.href='index.php?r=head/supply-request-list';"/></td>      
      </td>
        
   </tr>   

        
</table>





