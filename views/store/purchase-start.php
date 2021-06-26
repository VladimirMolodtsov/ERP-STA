<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Закупки';
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
    height: 70px; /* высота нашего блока */
    width:  120px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-txt {    
    font-size:12px;
}
.leaf-val {    
    font-size:20px;
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

<script type="text/javascript">
idList=new Array();
function chngSelectAllGrid()
{

 for (i=0; i<idList.length; i++)
 {
   document.getElementById(idList[i]).checked = true; 
  }
}

function setSelectGrid ()
{
 
 var strRequest = 'store/purchase-create&varlist=';
 for (i=0; i<idList.length; i++)
 {
   if (document.getElementById(idList[i]).checked)  strRequest = strRequest +idList[i]+',';
  }
 
  openSwitchWin (strRequest);
}
</script>	

<h3><?= Html::encode($this->title) ?></h3>

<?php $leafValue=$model->getLeafValue(); ?>           
<div class="row">  
	<div class="col-md-3 button_menu">
    	<input class="btn btn-primary"  style="width:200px" type="button" value="Товары в закупках" onclick="javascript:document.location.href='index.php?r=store/purchase-table'"/>
        <br>&nbsp;<br>
        <input class="btn btn-primary"  style="width:200px;" type="button" value="Новый заказ" onclick="javascript:openWin('store/purchase-zakaz&noframe=1','storeWin');"/>
    </div>   

	<div class="col-md-9 button_menu">

<table border='0' width='100%'> 
        <tr>        
            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==1) echo "leaf-selected"; ?>' style='background:Brown; color:White;' 
                href='index.php?r=store/purchase-start&mode=1#detail_list'>
                <div class='leaf-txt'>Заявки: </div><div class='leaf-val'><?= $leafValue['orders'] ?> </div> </a>
            </td>

            <td> <a  class='btn btn-primary leaf  <?PHP if ($model->mode==2) echo "leaf-selected"; ?>' style='background:MintCream  ; color:Blue;'                  
                href='index.php?r=store/purchase-start&mode=2#detail_list'>
                <div class='leaf-txt' >Запросы, всего: </div>
                <div class='leaf-val' ><?= $leafValue['requestInWork'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>                

            <td> <a  class='btn btn-primary leaf  <?PHP if ($model->mode==3) echo "leaf-selected"; ?>' style='background:WhiteSmoke ; color:Blue;'         
                href='index.php?r=store/purchase-start&mode=3#detail_list'>
                <div class='leaf-txt'>В согласовании: </div>
                <div class='leaf-val'><?= $leafValue['requestInSogl'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>
               
            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==4) echo "leaf-selected"; ?>' style='background:MintCream ;  color:Blue;'          
                href='index.php?r=store/purchase-start&mode=4#detail_list'>
                <div class='leaf-txt'>Согласованы </div>
                <div class='leaf-val'><?= $leafValue['requestComplete'] ?></div> 
                <div class='leaf-sub'></div>
                </a>
             </td>

            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==5) echo "leaf-selected"; ?>' style='background:WhiteSmoke ; color:Blue;'          
                href='index.php?r=store/purchase-start&mode=5#detail_list'>
                <div class='leaf-txt'>Запросы в закупке: </div>
                <div class='leaf-val'><?= $leafValue['requestInPurchase'] ?></div> 
                <div class='leaf-sub'></div>
                </a>
            </td>
        </tr> 
   
        <tr>
           <td> <a  class='btn btn-primary leaf <?PHP if ($model->mode==6) echo "leaf-selected"; ?>' style='background:#f2ffe8  ; color:DarkBlue;'         
            href='index.php?r=store/purchase-start&mode=6#detail_list'>
                <div class='leaf-txt'>Закупка, всего: </div>
                <div class='leaf-val' ><?= $leafValue['purchaseInWork'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>            
            
            <td> <a  class='btn btn-primary leaf <?PHP if ($model->mode==7) echo "leaf-selected"; ?>' style='background:#ebffdb ; color:DarkBlue;'         
                            href='index.php?r=store/purchase-start&mode=7#detail_list'>
                <div class='leaf-txt'>Согласование:</div>      
                <div class='leaf-val'><?= $leafValue['purchaseInSogl'] ?></div>
                <div class='leaf-sub' ></div>
                </a>
            </td>

            <td><a  class='btn btn-primary leaf <?PHP if ($model->mode==8) echo "leaf-selected"; ?>' style='background:#e3ffcc ; color:DarkBlue;'           
                            href='index.php?r=store/purchase-start&mode=8#detail_list'>
                <div class='leaf-txt'>Закупка, в оплате: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInCash'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>        

            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==9) echo "leaf-selected"; ?>' style='background:#e3ffcc ; color:DarkBlue;'           
                            href='index.php?r=store/purchase-start&mode=9#detail_list'>
                <div class='leaf-txt'>В доставке: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInDeliver'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>        
            
            
            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==10) echo "leaf-selected"; ?>' style='background:#dbffbd  ; color:DarkBlue;'         
                            href='index.php?r=store/purchase-start&mode=10#detail_list'>
                <div class='leaf-txt'>В завершении: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInFinit'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>
        </tr> 
                
     </table>      


   </div>   
   
</div>      
<hr>
<div class="bottom_cont" style='width:1140px;'>
<?php 
$t = "";
 switch ($model->mode)
 {

   case 1:
    $t = " Заявки, от менеджеров продаж:";
   break;

   case 2:
    $t = " Запросы цены в работе:";
   break;

   case 3:
    $t = " Запросы цены на согласовании";
   break;
   
   case 4:
    $t = " Согласованные запросы:";
   break;

   case 5:
    $t = " Запросы цены включенные в действующую закупку:";
   break;
   
   case 6:
    $t = " Закупка в процессе работы:";
   break;

   case 7:
    $t = " Закупки в ходе согласования:";
   break;

   case 8:
    $t = " Закупки, в процессе оплаты:";
   break;

   case 9:
    $t = " Закупки, ожидание доставки:";
   break;

   case 10:
    $t = " Закупки, ожидание завершения:";
   break;

 }
 
 ?>


 
<a name="detail_list"></a>  <div class="part-header">  <?php   echo " ".$t;  ?>  </div>    
 <br>
 <?php 
 
 if($model->mode == 1)         echo $model->printGoodRequestList ($provider);
 elseif ($model->mode < 6  )   echo $model->printPurchaseZakazList($provider);
 else                          echo $model->printPurchesList($provider);

?>

</div>

<div class="row">  

    <div class="col-md-3 button_menu">
   </div>   

   <div class="col-md-3 button_menu">
   </div>   
<?php if ( $model->mode == 4 || $model->mode == 2) {?>
	<div class="col-md-3 button_menu">
        <div class='col-md-2'><input type='button' class='btn btn-primary grd_menu_btn' onClick='chngSelectAllGrid()' value='Выбрать все'></div>
    </div>   
	<div class="col-md-3 button_menu">
        <input type='button' class='btn btn-primary grd_menu_btn' onClick='setSelectGrid()' value='В закупку'>
   </div>   	
<?php }?>   
</div>      


<?php
if (!empty($model->debug)){
echo "<pre>";
print_r ($model->debug);
echo "</pre>";}
?>

 




