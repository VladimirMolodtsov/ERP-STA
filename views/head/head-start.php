<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Управление';
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




<?php 
$leafValue=$model->getLeafValue();
$stats=$model->getStats();
 ?>           

 <table border='0' width='600px'> 

    <tr>
       <td width='200px' height='110px' >
        <div class='leaf leaf-round' style='background:Blue; color:White; left:0px; top:0px;'  
        onclick='openExtWin("https://docs.google.com/spreadsheets/d/12XnPTG5Fewhh2fZBJzKIGDfiyXv382vL7Io1RcjGnCY/edit?usp=sharing","plan");;'> <div class='leaf-txt' style='margin-left:25px; margin-top:20px;' >IT</div>
        </div>
       </td>

       <td width='200px' height='110px'>
        <div class='leaf leaf-ellipse' style='background:Blue; color:White; left:0px; top:0px;'  
        onclick='document.location.href="index.php?r=head/head-sale&detail=1";'>
        <div class='leaf-txt' style='margin-left:25px; margin-top:20px;' >Управление</div>
        </div>
       </td>

       <td width='200px'>       
        <div class='leaf leaf-ellipse' style='background:Blue; color:White; left:0px; top:0px;'  
        onclick='openHead();'> <div class='leaf-txt' style='margin-left:45px; margin-top:20px;' >Кадры</div>
        </div>
       </td>
    </tr>

 
    <tr>
       <td width='200px' height='110px'>
        <div class='leaf ' style='background:LightGreen; color:Black; left:50px; top:0px;'  
        onclick='openWin("fin/oplata-src","fin");'> <div class='leaf-txt' style='margin-left:10px; margin-top:10px;' >Реестр приходов</div>
        </div>
       </td>

       <td width='200px'>
        <div class='leaf leaf-ellipse' style='background:DarkGreen; color:White; left:30px; top:0px; height: 55px; width: 100px; '  
        onclick='openWin("fin/oplata-src","fin");'> <div class='leaf-txt' style='margin-left:15px; margin-top:10px;' >Деньги</div>
        </div>
       </td>
    

       <td width='200px' height='110px'>       
        <div class='leaf' style='background:Crimson; color:White; left:-50px; top:0px;'  
        onclick='openWin("head/oplata-reestr","fin");'> 
        <div class='leaf-txt' style='margin-left:10px; margin-top:10px;' >Реестр оплат</div>
        </div>
       </td>
    </tr>



    <tr>
       <td width='200px'>
        <div class='leaf leaf-ellipse' style='background:Blue; color:White; left:-20px; top:0px;'  
        onclick='document.location.href="index.php?r=head/head-sale&detail=0#detail_list";'>
        <div class='leaf-txt' style='margin-left:30px; margin-top:20px;' >Продажи</div>
        </div>
       </td>

       <td width='200px'>
 
       </td>

       
       <td width='200px'>       
        <div class='leaf leaf-ellipse' style='background:Blue; color:White; left:20px; top:0px;'  
        onclick='openWin("store/sclad-start2","sclad");'> <div class='leaf-txt' style='margin-left:30px; margin-top:20px;' >Снабжение</div>
        </div>
       </td>
    </tr>

    

    <tr>
       <td width='200px'>
        <div class='leaf leaf-ellipse' style='background:Indigo; color:White; left:50px; top:0px;'  
        onclick='openWin("store/sclad-start2&detail=10#detail_list","sclad");'> 
        <div class='leaf-txt' style='margin-left:30px; margin-top:20px;' >Отгрузки</div>
        </div>
       </td>

       <td width='200px'>
        <div class='leaf leaf-ellipse' style='background:DarkGreen; color:White; left:30px; top:0px; height: 55px; width: 100px; '  
        onclick='openWin("store/sclad-start2&detail=12#detail_list","sclad");'> 
        <div class='leaf-txt' style='margin-left:15px; margin-top:10px;' >Склад</div>
        </div>
       </td>


       <td width='200px'>       
        <div class='leaf leaf-ellipse' style='background:MidnightBlue ; color:White; left:-50px; top:0px;'  
        onclick='openWin("store/purchase-table&mode=0","sclad");'> 
        <div class='leaf-txt' style='margin-left:30px; margin-top:20px;' >Закупки</div>
        </div>
       </td>
    </tr>

        

 
 </table>
 
 
 
 
 
 
 
 
 
 
 
 
 
 






