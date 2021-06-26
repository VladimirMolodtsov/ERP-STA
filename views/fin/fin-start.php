<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Финансовые документы';
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
		<td colspan=2>
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
	
	</tr>	

    <tr>
		<td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Счета клиентам" onclick="javascript:openWin('fin/client-schet-src','finWin');"/></td>
		</td>

        <td>
            <input class="btn btn-primary"  style="width:260px" type="button" value="Заказы" onclick="javascript:openWin('fin/zakaz-src','finWin');"/></td>
		</td>
	
	</tr>	
    
    
    
  	<tr>
		<td colspan=2>
            <h3> Закупки </h3>
		</td>
	
	</tr>	

    
	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Расход денег" onclick="javascript:openWin('fin/supplier-oplata-src','finWin');"/></td>
		</td>


        <td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Приход товара" onclick="javascript:openWin('fin/supplier-wares-src','finWin');"/>
		</td>
	
	</tr>	


	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Счета поставщиков" onclick="javascript:openWin('fin/supplier-schet-src','finWin');"/></td>
		</td>


        <td>
        <input class="btn btn-primary"  style="width:260px;background-color: DarkGreen ; " type="button" value="Реестр Оплат" onclick="javascript:document.location.href='index.php?r=fin/oplata-reestr';"/></td>
		</td>
	
	</tr>	
	
        
</table>


