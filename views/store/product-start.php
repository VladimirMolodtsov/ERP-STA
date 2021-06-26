<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Производство';
$this->params['breadcrumbs'][] = $this->title;

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
    height: 60px; /* высота нашего блока */
    width:  150px;  /* ширина нашего блока */
    border: 1px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    position:relative;
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}


.leaf-small {
    height: 80px; /* высота нашего блока */
    width:  150px;  /* ширина нашего блока */
}

.leaf-main {
    height: 100px; /* высота нашего блока */
    width:  200px;  /* ширина нашего блока */
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






 <table border='0' width='600px'> 

    <tr>
       <td width='200px' height='110px' >
        <div class='leaf leaf-round' style='background:Blue; color:White; left:0px; top:0px;'  
        onclick='openExtWin("https://docs.google.com/spreadsheets/d/12XnPTG5Fewhh2fZBJzKIGDfiyXv382vL7Io1RcjGnCY/edit?usp=sharing","plan");;'> <div class='leaf-txt' style='margin-left:25px; margin-top:20px;' >IT</div>
        </div>
       </td>


        <td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Работа экспедитора" onclick="javascript:window.location='index.php?r=store/deliver-execute'"/>
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
	
	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Сценарии отгрузки" onclick="javascript:document.location.href='index.php?r=store/scenario-editor'; "/></td>
		</td>


        <td>
        
		</td>
	
	</tr>	
        

 
 </table>
 
 
 
 
 
 
 
 
 
 
 
 
 
 






