<?php
use app\assets\AppAsset;
use yii\helpers\Html;
/* @var $this yii\web\View */
/*
0x0001 1  - Оператор по работе с исходными данными
0x0002 2  - Оператор холодных звонков  
0x0004 4  -	Менеджер активных продаж
0x0008 8  - Менеджер по кадрам
*/
$this->title = 'Автоматизация работы с клиентами';
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
.razdel{
    padding: 25px;
}

 .button_menu{
    padding: 15px;	 
 }

 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: blue;
	font-size: 14pt;
 }
 
 .rlead{
    padding: 10px;	 
	color: black;
	text-align: right;	
	font-size: 14pt;
 }

  
 .clead{
    padding: 10px;	 
	color: black;
	text-align: center;	
	font-size: 14pt;
 }
 
.disable{	
  background-color: LightGray;	
  width:250px
}

.disable:hover{	
  background-color: LightGray;	  
}
.enable{	  
  width:250px
}

 
</style>

<div class="site-index">

    <div class="jumbotron">		
<?php if (Yii::$app->user->isGuest){  ?>
        <p class="lead"> Для начала работы авторизуйтесь</p>
        <p><a class="btn btn-lg btn-success" href="index.php?r=site/login">Войти в систему</a></p>
<?php } ?>
     </div>
<?php if (Yii::$app->user->isGuest == false){  
      $curUser=Yii::$app->user->identity;
 ?>

 
<div class="body-content">
        <p class="lead"> Текущий пользователь: <b><?= Html::encode($curUser->userFIO) ?></b></p>

    <table border="0" width = 90%>
	
	<tr>	
		<td>
			Всего организаций в базе <?= $model->getOrgCount(); ?>
		</td>			
		<td>
		    Всего заказов    <?= $model->getZakazAllCount(); ?><br>	
			Заказов за месяц <?= $model->getZakazMonthCount(); ?>	
		</td>			
		<td>
		    Всего счетов     <?= $model->getSchetAllCount(); ?><br>	
			Счетов за месяц  <?= $model->getSchetMonthCount(); ?>				
		</td>			

    </tr>	

	<tr>	

    </tr>	
	
	<tr>	
		<td >
			<b> Анализ заинтересованности: </b>
		</td>			
		
		<td>
			<input class="btn btn-primary enable" type="button" value="По отраслям" onclick="javascript:window.location='index.php?r=market/marketing-interes-by-razdel'"/>
		</td>			

		<td>
			<input class="btn btn-primary enable" type="button" value="По городам" onclick="javascript:window.location='index.php?r=market/marketing-interes-by-city'"/>
		</td>			

    </tr>	

	<tr>	
		<td >
			<b> Анализ заявок: </b>
		</td>			
		
		<td>
			<input class="btn btn-primary enable" type="button" value="По отраслям" onclick="javascript:window.location='index.php?r=market/marketing-zakaz-by-razdel'"/>
		</td>			

		<td>
			<input class="btn btn-primary enable" type="button" value="По городам" onclick="javascript:window.location='index.php?r=market/marketing-zakaz-by-city'"/>
		</td>			

    </tr>	

	<tr>	
		<td >
			<b> Анализ счетов: </b>
		</td>			
		
		<td>
			<input class="btn btn-primary enable" type="button" value="По отраслям" onclick="javascript:window.location='index.php?r=market/marketing-schet-by-razdel'"/>
		</td>			

		<td>
			<input class="btn btn-primary enable" type="button" value="По городам" onclick="javascript:window.location='index.php?r=market/marketing-schet-by-city'"/>
		</td>			

    </tr>	

	
	<tr>	
		<td>
			<b> Детализация по клиентам: </b>		
		</td>			

		<td>
			<input class="btn btn-primary enable" type="button" value="Отчет о заказах" onclick="javascript:window.location='index.php?r=site/marketing-zakaz'"/>
		</td>			
		<td>
			<input class="btn btn-primary enable" type="button" value="Отчет о счетах" onclick="javascript:window.location='index.php?r=site/marketing-schet'"/>
		</td>			

    </tr>	

	<tr>	
		<td>
		<b> Работа с исходными данными: </b>	
		</td>			
		<td>
			<input class="btn btn-primary enable" style="" type="button" value="Рыночные цены" onclick="javascript:openWin('store/marketing-price','priceWin')"/>
		</td>			

	   <td>
			<input class="btn btn-primary enable" style="background-color: ForestGreen;" type="button" value="Загрузить данные" onclick="javascript:window.location='index.php?r=data/data-start'"/>
		</td>			


    </tr>	
	
    </table>	
 
</div>
<?php } ?>
</div>