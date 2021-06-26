<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Работа с персоналом';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
	
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0008)) {return;}

 ?>
 
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

 
 
</style>

 <script>
function openWin(url)
{
  window.open("index.php?r="+url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1050,height=700'); 
  <?php
  /*window.open("index.php?r="+url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=500,width=750,height=900'); */
  ?>
}
</script> 

<h2><?= Html::encode($this->title) ?></h2>








<table  width="600px" border=0>  

	<tr>
    	<td colspan='2' style='text-align:center;'>
            <h4 >Отдел продаж</h4>
		</td>

    	<td colspan='1' style='text-align:center;'>
            <h4 >Финансовый отдел </h4>
		</td>
				
	</tr>	


	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Активность по менеджерам " onclick="javascript:window.location='index.php?r=site/head-manager-activity'"/></td>
		</td>


        <td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Статистика активных продаж" onclick="javascript:window.location='index.php?r=site/personal-market'"/>
		</td>

		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Оператор банка" onclick="javascript:window.location='index.php?r=bank/operator/load-log'"/>
		</td>
	
	</tr>	


	<tr>
   	<td colspan='2' style='text-align:center;'>
		<h4 >Доставка и снабжение</h4>
		</td>
	</tr>	
    
	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Затраты на доставку" onclick="javascript:window.location='index.php?r=store/deliver-execute'"/></td>
		</td>


        <td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Работа экспедитора" onclick="javascript:window.location='index.php?r=store/deliver-execute'"/>
		</td>
	
	</tr>	


    
	<tr>
    	<td colspan='2' style='text-align:center;'>
		<h4 >Настройка и управление</h4>
		</td>
	</tr>	

	<tr>
    	<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Управление ролями" onclick="javascript:window.location='index.php?r=site/role'"/>
		</td>
	
		<td>
	    <input class="btn btn-primary"  style="width:260px" type="button" value="Доступ для помощника менеджера " onclick="javascript:window.location='index.php?r=market/client-managment'"/></td>
		</td>
	</tr>	

	<tr>
		<td>
		<input class="btn btn-primary"  style="width:260px" type="button" value="Привязка клиента " onclick="javascript:window.location='index.php?r=site/stat-orgs'"/></td>
		</td>
		<td>
		
		</td>
        
	</tr>	
    

	<tr>
    	<td>
	    <input class="btn btn-primary"  style="width:260px" type="button" value="Речевые модули" onclick="javascript:window.location='index.php?r=site/modules'"/></td>
		</td>	

        
		<td>
	    <input class="btn btn-primary"  style="width:260px" type="button" value="Настройка " onclick="javascript:window.location='index.php?r=site/config'"/></td>
		</td>
	</tr>	

	
</table>


