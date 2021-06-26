<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;
$this->title = 'Товары в закупках';

if ($curUser->roleFlg & 0x0020) 
{$this->title .= ' - управление';}


?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

.btn-small {	
	padding: 2px;	 
	font-size: 10pt;	
} 
 
.gridcell {
	width: 100%;		
	height: 100%;
	/*background:DarkSlateGrey;*/
}	

.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.gridcell:hover{
	background:DarkSlateGrey;
	color:#FFFFFF;
}

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.table-local
{
    padding: 2px;
    font-size: 10pt;
}


.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
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
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}

th, td {
    padding: 5px;
}
</style>

<script type="text/javascript">
</script>	



<?php $leafValue=$model->getLeafValue(); ?>           
<div class="row">  
    <div class="col-md-3" >
    <h3><?= Html::encode($this->title) ?></h3>
   </div>   

   <div class="col-md-5" >
   </div>   

   <div class="col-md-3" style='margin-top:20px;'>
        <input class="btn btn-primary"  style="width:200px;" type="button" value="Новая закупка" onclick="javascript:openWin('store/purchase-zakaz&noframe=1','storeWin');"/>       
    </div>   

    <div class="col-md-1" >
    <a href='#' onclick='openWin("help/purchase-table","helpWin");'><span class='glyphicon glyphicon-question-sign' aria-hidden='true'></span></a>
   </div>   

</div>     
<br>&nbsp;<br> 

<table border='0' width='100%' > 
        <tr>        
            <td>
            <!--style='background:Brown; color:White;' 
            <a  class='btn btn-primary leaf  <?PHP if ($model->mode==1) echo "leaf-selected"; ?>' 
                href='index.php?r=store/purchase-table&mode=1#detail_list'>
                <div class='leaf-txt'> Запрос цены <br> от менеджера </div><div class='leaf-val'><?= ($leafValue['orders']) ?> </div> </a>
             -->
            </td>

            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==0) echo "leaf-selected"; ?>' style='background:WhiteSmoke; color:Blue;' 
                href='index.php?r=store/purchase-table&mode=0#detail_list'>
                <div class='leaf-txt'>Все заявки <br> в работе: </div><div class='leaf-val'><?= ($leafValue['orders']+$leafValue['requestInWork']) ?> </div> </a>
            </td>
            
            
            <td> <a  class='btn btn-primary leaf  <?PHP if ($model->mode==2) echo "leaf-selected"; ?>' style='background:MintCream  ; color:Blue;'                  
                href='index.php?r=store/purchase-table&mode=2#detail_list'>
                <div class='leaf-txt' >Заявки на стад.<br>запрос цены: </div>
                <div class='leaf-val' ><?= $leafValue['requestInWork'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>                

            <td> <a  class='btn btn-primary leaf  <?PHP if ($model->mode==3) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'         
                href='index.php?r=store/purchase-table&mode=3#detail_list'>
                <div class='leaf-txt'>Заявки на стад.<br> соглас. цены: </div>
                <div class='leaf-val'><?= $leafValue['requestInSogl'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>
               
            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==4) echo "leaf-selected"; ?>' style='background:MintCream ;  color:Blue;'          
                href='index.php?r=store/purchase-table&mode=4#detail_list'>
                <div class='leaf-txt'>Заявки на стад.<br>согласованы </div>
                <div class='leaf-val'><?= $leafValue['requestComplete'] ?></div> 
                <div class='leaf-sub'></div>
                </a>
             </td>

           <td> <a  class='btn btn-primary leaf <?PHP if ($model->mode==6) echo "leaf-selected"; ?>' style='background:#f2ffe8  ; color:DarkBlue;'         
            href='index.php?r=store/purchase-table&mode=6#detail_list'>
                <div class='leaf-txt'>Заявки на стад.<br>закупка, всего: </div>
                <div class='leaf-val' ><?= $leafValue['purchaseInWork'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>            
            
            <td> <a  class='btn btn-primary leaf <?PHP if ($model->mode==7) echo "leaf-selected"; ?>' style='background:LightYellow ; color:DarkBlue;'         
                            href='index.php?r=store/purchase-table&mode=7#detail_list'>
                <div class='leaf-txt'>Закупки в <br>cогласовании:</div>      
                <div class='leaf-val'><?= $leafValue['purchaseInSogl'] ?></div>
                <div class='leaf-sub' ></div>
                </a>
            </td>

            <td><a  class='btn btn-primary leaf <?PHP if ($model->mode==8) echo "leaf-selected"; ?>' style='background:#e3ffcc ; color:DarkBlue;'           
                            href='index.php?r=store/purchase-table&mode=8#detail_list'>
                <div class='leaf-txt'>Закупки включ.<br> в реестр оплат: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInCash'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>        

            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==9) echo "leaf-selected"; ?>' style='background:#e3ffcc ; color:DarkBlue;'           
                            href='index.php?r=store/purchase-table&mode=9#detail_list'>
                <div class='leaf-txt'>Закупки в<br>доставке: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInDeliver'] ?></div>
                <div class='leaf-sub'></div>
                </a>
            </td>        
            
            
            <td><a  class='btn btn-primary leaf  <?PHP if ($model->mode==10) echo "leaf-selected"; ?>' style='background:#dbffbd  ; color:DarkBlue;'         
                            href='index.php?r=store/purchase-table&mode=10#detail_list'>
                <div class='leaf-txt'>Закупки в<br> завершении: </div>
                <div class='leaf-val'><?= $leafValue['purchaseInFinit'] ?></div> 
                <div class='leaf-sub' ></div>
                </a>
            </td>
        </tr> 
                
  </table>      

<div class='spacer'></div>

  
<a name='detail_list'> </a>
<?php  
echo $model->printPurchaseTable ($provider, $model);

?>   
<br>   
<div class="row">  
   
</div>      
	

