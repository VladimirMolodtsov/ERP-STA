<?php
use app\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Работа с внешними данными';
?>

<script type="text/javascript">

function doAction(actionCode)
{
  document.forms["w0"]["datasyncgoogle-actioncode"].value=actionCode;
  document.forms["w0"].submit();
}

</script>

<style>
 	

.btn-local {
  width: 150px;
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

		
 <?php $form = ActiveForm::begin(); 

 ?>	 
 

    <table border="0" width = 100%>
	<tr>	
		<td width="20%">
			<b> Клиенты из  из 1С </b>	
		</td>			
		<td>
		<?= $form->field($model, 'googleClientsUrl')->label(false)?>				
		</td>			
	   <td width="200px">
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; 
			<input class="btn btn-primary btn-local"  type="button" value="Загрузить данные" onclick="javascript:doAction(1);"/>
	   </div>
		</td>			
    </tr>	
	
	<tr>	
		<td width="20%">
			<b> Счета из 1С </b>	
		</td>			
		<td>
		<?= $form->field($model, 'importSchetUrl')->label(false)?>				
		</td>			
	   <td width="200px">
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; 
			<input class="btn btn-primary btn-local"  type="button" value="Загрузить данные" onclick="javascript:doAction(2);"/>
		</div>
		</td>			
    </tr>	

	<tr>	
		<td width="20%">
			<b> Оплата из 1С </b>	
		</td>			
		<td>
		<?= $form->field($model, 'importOplataUrl')->label(false)?>				
		</td>			
	   <td width="200px">
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; 
			<input class="btn btn-primary btn-local"  type="button" value="Загрузить данные" onclick="javascript:doAction(3);"/>
		</div>
		</td>			
    </tr>	
	
	<tr>	
		<td width="20%">
			<b> Отгрузка из 1С </b>	
		</td>			
		<td>
		<?= $form->field($model, 'importPostavkaUrl')->label(false)?>				
		</td>			
	   <td width="200px">
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; 
			<input class="btn btn-primary btn-local"   type="button" value="Загрузить данные" onclick="javascript:doAction(4);"/>
		</div>
		</td>			
    </tr>	
		
   <tr>	
		<td width="20%">
		&nbsp;
		</td>			
		<td>
		&nbsp;
		</td>			
	   <td width="200px">
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; <br> &nbsp;
			<input class="btn btn-primary btn-local"  style="background-color: ForestGreen;" type="button" value="Синхронизировать" onclick="javascript:doAction(5);"/>
		</div>
		</td>			
    </tr>			
		

   <tr>	
		<td colspan=3>
		 <hr>
		</td>			
    </tr>			

		
	<tr>	
		<td colspan='2' align='left'>
		<b> Загрузка списка организаций из csv файла: </b>	
		</td>			
	   <td>
	   <div style="position:relative; top:-5px; left:10px;"> &nbsp; 
			<input class="btn btn-primary  btn-local" type="button" value="Загрузить данные" onclick="javascript:window.location='index.php?r=site/csv-upload'"/>
		</div>			
		</td>			
    </tr>	
	
    </table>	
	
	
	<?=$form->field($model, 'actionCode')->hiddenInput()->label(false)?>
	
    <?php ActiveForm::end(); ?>
	
	
	
</div>
<?php } ?>
</div>