<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;

$this->title = 'Задание на доставку';

$deliverRecord = $model-> prepareDeliver();


?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script>  

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>


<style>
.button {
	width: 150px;
	font-size: 10pt;	
} 

.local_small {	
	padding: 2px;	 
	font-size: 10pt;	
} 

 .btn-block{
    padding: 2px;	 
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

.circle {
    width: 25px; /* задаете свои размеры */
    height: 25px;
    overflow: hidden;
	display: inline;
    background: #4169E1;
    padding: 2px; /* создание отступов */
	text-align: center;
    border-radius: 50%;
    /* не забываем о кроссбраузерности */
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%
    border: #FFF 1px solid;
	/* тень */
    box-shadow: 0px 1px 1px 1px #bbb; 
    -moz-box-shadow: 0px 1px 1px 1px #bbb;
    -webkit-box-shadow: 0px 1px 1px 1px #bbb;
	/**/
	float:left;
	margin-left:4px;
	margin-top: 0px;
	color:white;
}
.circle:hover{
	background:#0000CD
}

.box_shadow {
	box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}

.middle_lbl
{
 padding-left:15px;	
}
.executed {
    background: #4169E1;
	color:white;
}

.planned {
    background: #C0C0C0;
	color:white;
}

td 
{
 padding:4px;
}


</style>

<script type="text/javascript">

/*****************************************/
/***** Статусы ***************************/
/*****************************************/
var	deliverStatusList = new Array();
	deliverStatusList[0]="Создана";		
	deliverStatusList[1]="Подготовлена к отгрузке";		
	deliverStatusList[2]="Выдано  экспедитору";		
	deliverStatusList[3]="В доставке";		
	deliverStatusList[4]="Доставлено";		
	deliverStatusList[5]="Отчет сдан";		
	deliverStatusList[6]="Завершено";		

<?=$model->statusListJS ?>
    
var maxDeliverStatus = 6;
var curDeliverStatus = <?=$model->requestStatus?>;

function showAddGood()
{
    $('#add_zakaz_form').modal('show');
}

function setUPD()
{
  upd =  document.getElementById('requestUPD').value;
  openSwitchWin('store/deliver-setupd&id=<?= $model->id ?>&upd='+upd);
  
}
function setDeliverStatus(id)
{
    document.getElementById('deliver_status').innerHTML =deliverStatusList[id]+' '+statusCngList[id] ;	
}

function saveForm()
{
  if(document.forms["Mainform"]["deliversform-requestadress"].value=='' )  
  {
     alert('Поле "точный адрес доставки" должно быть заполнено');
     return; 
  }

 if(document.forms["Mainform"]["deliversform-requestscladadress"].value=='' )  
  {
     alert('Поле "адрес склада" должно быть заполнено');
     return; 
  }
  
    if(document.forms["Mainform"]["deliversform-requestadress"].value=='' )  
  {
     alert('Поле адрес доставки должно быть заполнено');
     return; 
  }

  
  document.forms["Mainform"].submit();
}


function chngDeliverStatus (n)
{
  var i=0;
  curDeliverStatus = n;
  for (i=1; i<=n; i++)  
  {
	id="deliverMarker_"+i;
	document.getElementById(id).style.backgroundColor ='#4169E1';
  }
  for (i=n+1; i<= maxDeliverStatus; i++)  
  {
	id="deliverMarker_"+i;
	document.getElementById(id).style.backgroundColor ='#C0C0C0';
  }  
  document.forms["Mainform"]["deliversform-requeststatus"].value=n;  
}

function restoreDeliverStatus()
{	
 setDeliverStatus(curDeliverStatus);
}
/**************************************/


function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setGood (requestGoodTitle, requestCount,  requestMeasure,requestGoodRef)
{
	document.location.href='index.php?r=store/deliver-zakaz&action=addGood&id=<?= $model->id ?>&requestGoodTitle='+requestGoodTitle+'&requestCount='+requestCount+'&requestMeasure='+requestMeasure+'&requestGoodRef='+requestGoodRef+'#contentlist';
}

function setSclad (scladTitle, scladAdres, scladRef)
{
	document.forms["Mainform"]["deliversform-requestsclad"].value=scladTitle;
	document.forms["Mainform"]["deliversform-requestscladadress"].value=scladTitle+": "+scladAdres;
	closeScladList();
}

function showAdd()
{
	if (document.getElementById("addRequest").style.visible == "hidden")
	{ document.getElementById("addRequest").style.visible = "visible"; }
	else document.getElementById("addRequest").style.visible = "hidden";
}

function showDialog(id, fnum, rowid)
{
    switch(fnum)
	{
		case 1:
			document.getElementById('dialogTitle').innerHTML= "Товар";
			document.forms["editZakazForm"]["action"].value='editGood';
			break;
		case 2:
			document.getElementById('dialogTitle').innerHTML= "Количество";
			document.forms["editZakazForm"]["action"].value='editCount';
			break;
		case 3:
			document.getElementById('dialogTitle').innerHTML= "Ед.изм";
			document.forms["editZakazForm"]["action"].value='editMeasure';
			break;
	}
	document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value=document.getElementById(id).innerHTML;
	if (document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value == "- ")
		{ document.forms["editZakazForm"]["edit_zakaz_form-proposal"].value ="";}	
	document.forms["editZakazForm"]["goodId"].value=rowid;

//Показ диалога

    $('#edit_zakaz_form').modal('show');
	//document.getElementById('edit_zakaz_form-proposal').focus();		
}

function showOrgList(v)
{   
//Показ диалога
if (v==1){document.getElementById('org_list_frame').src = 'index.php?r=store/deliver-org-list&noframe=1&act=1';}
if (v==2){document.getElementById('org_list_frame').src = 'index.php?r=store/deliver-org-list&noframe=1&act=2';}
    $('#org_list_form').modal('show');
}

function closeOrgList()
{ // ловим клик по крестику или подложке	
        $('#org_list_form').modal('hide');
}

function showGoodList()
{   
//Показ диалога
    $('#good_list_form').modal('show');  
}


function closeGoodList()
{ 
    $('#good_list_form').modal('hide');    
}


function showScladList()
{   
//Показ диалога
    $('#sclad_list_form').modal('show');  
}
	
function closeScladList()
	{ // ловим клик по крестику или подложке
	    $('#sclad_list_form').modal('hide');  
	}	
</script>

<?php $form = ActiveForm::begin(['id' => 'Mainform' ]); ?>

<div class='row'>
<div class='col-md-3' > 

<h4><?= Html::encode($this->title) ?>  № </h4>
</div>

<div class='col-md-3' > 
<?= $form->field($model, 'requestNum')->textInput(['style'=>'width:100px; margin:0px; padding:0px; left:0px'])->label(false)?> 
</div>

</div>

  
  
<table border='0'>
<tr>


	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Доставка назначена:</b> </td>
	<td><?= $form->field($model, 'requestDateReal')->textInput(['class' => 'tcal','style'=>'width:100px;'])->label(false)?> </td>    
    <td width="50px" style='vertical-align:top; padding:10px;' ><b>УПД:</b> </td>
	<td width="150px"><?= $form->field($model, 'requestUPD')->textInput(['style'=>'height:30px; width:150px; margin:0px; padding:0px; left:0px','id'=>'requestUPD' ])->label(false)?>  </td>    
    <td width="75px" style='vertical-align:top; padding:10px;' >
    <?php
    if ( $deliverRecord->isRefSupply < 0)
    {
      echo "<font color='Crimson'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span></font>";        
    }else
    {
      echo "<font color='Green'><span class='glyphicon glyphicon-ok-sign' aria-hidden='true'></span></font>";                
    }    
    ?>
    &nbsp;<a href="#" onclick='setUPD();' ><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></a>
    </td>
    <td width="250px" ><div style="padding-left:20px;"><?= $form->field($model, 'supplyType')->dropDownList([            
            '1' => 'Доставка клиенту',            
            '2' => 'Перемещение',
            '4' => 'Документы',
            '5' => 'Доставка от поставщика'
            ])->label(false) ?>
</div></td>
</tr>	
</table>
<table border='0' width="100%">
<tr>
	<td colspan='4'> <div style='text-align:center; background: Silver; font-weight:bold;'>Куда</div> </td>	
</tr>	


<tr>
	<td>
    <b><a href="#" onclick="javascript:showOrgList(1);" >Получатель</a></b><br><?= $form->field($model, 'refOrg')->hiddenInput()->label(false)?>
	</td>
	<td ><span id='orgTitle'><?= Html::encode($model->orgTitle)?></span></td>	
	<td><span class='middle_lbl'><b>ИНН:</b></span><span id='orgINN'>&nbsp;<?= Html::encode($model->orgINN)?></span></td>
	<td></td>
</tr>	

<tr>
	<td ><b>Юр. адрес:</b>	</td>
	<td colspan='3' ><span id='orgAdress'><?= Html::encode($model->orgAdress)?> </span></td>
	
</tr>	
<tr>
	<td><b>Точный адрес <br> доставки:</b></td>
	<td colspan='3'><?= $form->field($model, 'requestAdress')->textInput(['style'=>'width:725px; margin:0px; padding:0px; left:0px'])->label(false)?>  </td>
</tr>	

<tr>
	<td><b>Телефон:</b></td>
	<td><?= $form->field($model, 'requestPhone')->textInput(['style'=>'width:300px; margin:0px; padding:0px; left:0px'])->label(false)?>   </td>
	<td class='middle_lbl'><b>Контакт ФИО:</b></td>
	<td><?= $form->field($model, 'requestContact')->textInput(['style'=>'width:300px; margin:0px; padding:0px; left:0px'])->label(false)?>  </td>
</tr>	

<tr>
	<td colspan='4'> <div style='text-align:center; background: Silver; font-weight:bold;'>Откуда</div> </td>	
</tr>	

<tr>
	<td>
        <b><a href="#" onclick="javascript:showOrgList(2);" >Отправитель</a></b>
        <?= $form->field($model, 'refFromOrg')->hiddenInput()->label(false)?>
	</td>
	<td colspan='3'><span id='orgFromTitle'><?= Html::encode($model->orgFromTitle)?></span></td>	
    </tr>	

    <tr>
	<td ><b>Юр. адрес:</b>	</td>
	<td colspan='3' ><span id='orgFromAdress'><?= Html::encode($model->orgFromAdress)?> </span></td>	
    </tr>	

    
    <tr>
	<td>    
        <b><a href="#" onclick="javascript:showScladList();" >Со склада (адрес):</a></b>
        <?= $form->field($model, 'requestSclad')->hiddenInput(['style'=>'width:100px; margin:0px; padding:0px; left:0px'])->label(false)?></td>
	<td colspan='3'><?= $form->field($model, 'requestScladAdress')->textInput(['style'=>'width:450px; margin:0px; padding:0px; left:0px'])->label(false)?>   </td>
    </tr>	
   
</tr>	
</table>

<table border='0' width="100%">
<tr>
	<td colspan='6'> <div style='text-align:center; background: Silver; font-weight:bold;'>Исполнение</div> </td>	
</tr>	
<tr>
	<td ><b>Исполнитель:</b></td>
	<td ><?= $form->field($model, 'requestExecutor')->textInput(['style'=>'width:250px; margin:0px; padding:0px; left:0px'])->label(false)?>  
    </td>

	<td class='middle_lbl'><b>Способ:</b></td>
	<td >    <div style="padding-left:20px;"><?= $form->field($model, 'requestExecutorType')->dropDownList([            
            '1' => 'Экспедитор',            
            '2' => 'Самовывоз',
            '3' => 'Транс. комп.',
            ])->label(false) ?>  
    </td>    
    
<td width="200px"  class='middle_lbl' ><b>Доставка не позднее:</b> </td>
	<td><?= $form->field($model, 'requestDatePlanned')->textInput(['class' => 'tcal','style'=>'width:100px;'])->label(false)?> </td>    
  	
    
</tr>	


	<td colspan='3'  class='middle_lbl'> 
	<div style='width:100%; padding:5px; height:35px; background-color:GhostWhite'>
		<?php 			
		for ($i=0;$i<=6;$i++)
		{		
	     if ($i <= $model->requestStatus ){$style="executed";}
		       else {$style="planned";}			  
		$id = "deliverMarker_".$i;	   
		echo "<div  id=".$id." class='circle ".$style."' onclick='javascript:chngDeliverStatus(".$i.");'  onmouseover='javascript:setDeliverStatus(".$i.");' onmouseout='javascript:restoreDeliverStatus();'>".$i."</div>";
		}
		?>	
		</div>
	    <div id='deliver_status' style='width:100%;  font-size:12px height:25px; background-color:Gainsboro'> - </div>
	    <?= $form->field($model, 'requestStatus')->hiddenInput(['style'=>'width:100px; margin:0px; padding:0px; left:0px'])->label(false)?>  
	</td>

    <td colspan='3' rowspan='2' class='middle_lbl'> 

	<?= $form->field($model, 'requestNote')->textarea(['rows' => 3, 'cols' => 36, 'style'=>'width:410px; margin:0px; padding:0px; left:0px'])->label('Дополнения/примечания:')?>    
	</td>

</tr>
</table>

<table border='0' width="100%">

<tr>
	<td><b>Транспортная компания:</b></td>
	<td><b>Грузополучатель:</b></td>
  	<td><b>Плательщик за доставку:</b></td>
    <td><b>Доставить: </b></td>
</tr>	

<tr>
	<td><?= $model->transportName ?></td>
	<td><?= $model->consignee ?></td>
  	<td><?= $model->payer ?></td>
    <td><?php 
    if ( $model->isToTerminal == 1)  echo " До терминала"; 
                                      else  echo " По адресу"; 
    ?></td>
</tr>	



<tr>
	<td><b>Товар, вид, категория:</b></td>
	<td><b>К-во мест:</b></td>
  	<td><b>Объём:</b></td>
    <td><b>Вес, всего:</b></td>
</tr>	

<tr>
	<td><?= $form->field($model, 'requestCategory')->textInput()->label(false)?></td>
  	<td><?= $form->field($model, 'requestPlaces')->textInput()->label(false)?></td>
  	<td><?= $form->field($model, 'requestVolume')->textInput()->label(false)?></td>
  	<td><?= $form->field($model, 'requestTotalWeight')->textInput()->label(false)?></td>
</tr>	

<tr>
    <td style=''><b>Дата исполнения:</b></td>
	<td style='background:Silver;'><b>Водитель</b></td>
	<td style='background:Silver;'><b>Время (мин):</b></td>  	
    <td style='background:Silver;'><b>Затраты:</b></td>
</tr>	
<tr>
    <td><?= $form->field($model, 'factDate')->textInput(['class' => 'tcal','style'=>'width:200px;'])->label(false)?></td>
	<td></td>  	
  	<td><?= $form->field($model, 'requestTime')->textInput()->label(false)?></td>
  	<td><?= $form->field($model, 'factValue')->textInput()->label(false)?></td>
</tr>	

<tr>
    <td style=''></td>
	<td style='background:Silver;'><b>Экспедитор</b></td>    
  	<td style='background:Silver;'><b>Факт. вес (кг):</b></td>
    <td style='background:Silver;'><b>Затраты:</b></td>
</tr>	

<tr>
	<td></td>
  	<td></td>
    <td><?= $form->field($model, 'factWeight')->textInput()->label(false)?></td>  	
  	<td><?= $form->field($model, 'requestExpValue')->textInput()->label(false)?></td>
</tr>	
    
    
</table>  

<div class='row'>

    <div class='col-md-12' style='text-align:right' >
  <input type="button" id="btn-submit" style='background-color: ForestGreen;' class="btn btn-primary"  value="Сохранить" onclick='saveForm();'>	
   <?php //= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform']) ?> 
   <a class='btn btn-primary' href="#" onclick="javascript: if (confirm('Не сохраненные изменения будут потеряны! Выйти?'))
   {document.location.href='index.php?r=site/success';} "> Выйти </a> 
   <a class="btn btn-default local-buttons" href="#"  style='padding:2px' onclick="javascript:openWin('store/deliver-zakaz-print&noframe=1&id=<?= $model->id ?>&noframe=1','printWin');" ><img src='img/printer.png' alt='Печать'></a>


    </div>
</div>
 

<br>

<hr>
<?= $form->field($model, 'id')->hiddenInput()->label(false)?> 



<?php ActiveForm::end(); ?>

<?php Pjax::begin(); ?>
 
 <br>
 
 <?php		
	echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

		
			[
                'attribute' => 'requestGoodTitle',
				'label'     => 'Наименование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "good_".$model['id'];
					if (empty(trim($model['requestGoodTitle']))){$val="-";}
					                     else {$val=$model['requestGoodTitle'];}
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 1, ".$model['id'].");\">".$val." </div>";
					
                },
            ],		

			[
                'attribute' => 'requestCount',
				'label'     => 'Количество',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "count_".$model['id'];
					if (empty($model['requestCount'])){$val="-";}
					                     else {$val=$model['requestCount'];}

                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 2, ".$model['id'].");\">".$val." </div>";

                },
            ],		

			[
                'attribute' => 'requestGoodValue',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return number_format($model['requestGoodValue'],2,'.','&nbsp;');
                },
            ],		

			[
                'attribute' => '',
				'label'     => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return number_format($model['requestGoodValue']*$model['requestCount'],2,'.','&nbsp;');
                },
            ],		
			
			[
                'attribute' => 'requestMeasure',
				'label'     => 'Ед.изм',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "ed_".$model['id'];
					if (empty(trim($model['requestMeasure']))){$val="-";}
                    else                                      {$val=$model['requestMeasure'];}
                    return "<div class='gridcell' id='".$id."' onclick=\"showDialog('".$id."', 3, ".$model['id'].");\">".$val." </div>";
                },
            ],		
					
                    
                    
                    
			[
                'attribute' => 'id',
				'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a style='color:Crimson;' href='index.php?r=store/deliver-zakaz&action=delGood&goodId=".$model['id']."&id=".$model['requestDeliverRef']."'>
                    <span class='glyphicon glyphicon-remove' aria-hidden='true'></span></a>";
                },				
            ],		
			
        ],
    ]
	);
	?>
<?php Pjax::end(); ?>


<div class='row'>
	<div class="col-md-6">
	<p>
	<?php
	echo Alert::widget([
    'options' => [
        'class' => 'alert-warning'
    ],
    'body' => 'Сохранитесь перед добавлением/изменением товара'
]);
	//<span class='label label-warning'>Сохранитесь перед добавлением/изменением товара</span>
	?>
	</p>
	</div>	

  	<div class="col-md-2">
	</div>	
	
	<div class="col-md-2">
        <a  class='btn btn-primary button' onclick='showAddGood();' href="#" >Добавить товар</a>  
	</div>	
	<div class="col-md-2" >	   
		<a  class='btn btn-primary button' href="#" onclick='javascript:showGoodList();'> Добавить из списка </a>
	</div>	

 

</div>

   
<!--- ******************************************************  --->    
<!--- ******************************************************  --->  
<a name='contentlist'></a>
  <!--- Форма список товара ----->	
<?php
Modal::begin([
    'id' =>'good_list_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Товар</h4>',

]);?>
    
<?php  
   Pjax::begin(); 
echo \yii\grid\GridView::widget(
    [		        	
        'dataProvider' => $goodListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],						
            
     	    [
                'attribute' => 'goodTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				return "<a href='#' onclick='javascript:setGood(\"".$model['goodTitle']."\", \"".$model['count']."\", \"".$model['ed']."\", \"".$model['refWare']."\" );' >".$model['goodTitle']."</a>";
                },
            ],		

			[
                'attribute' => 'count',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'ed',
				'label'     => 'Ед.изм',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'price',
				'label'     => 'Цена',
                'format' => 'raw',
            ],		

            
        ],
    ]
	);

 Pjax::end();
?>   

<?php
Modal::end();
?>
  
	
<!--- ******************************************************  --->  
  <!--- Форма список товара ----->	
<?php
Modal::begin([
    'id' =>'sclad_list_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Склад</h4>',

]);?>
    
<?php  
   Pjax::begin(); 
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $scladListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		
			[
                'attribute' => 'sladTitle',
				'label'     => 'Склад',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				return "<a href='#' onclick='javascript:setSclad(\"".$model['sladTitle']."\", \"".$model['scladAdress']."\",\"".$model['id']."\" );' >".$model['sladTitle']."</a>";
                },
            ],		
			
			[
                'attribute' => 'scladAdress',
				'label'     => 'Адрес',
                'format' => 'raw',
			],

        ],
    ]
	);

 Pjax::end();
?>   
   <br>   
<?php
Modal::end();
?>
  
	
   
<!--- ******************************************************  --->  
<!--- ******************************************************  --->     
<!--- Форма добавления ----->	
<?php
Modal::begin([
    'id' =>'add_zakaz_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Добавить в доставку</h4>',

]);?>
    
	<form action="index.php" method="GET">		
		<input type="hidden" name="id" value="<?=$model->id?>" />
		<input type="hidden" name="action" value="addGood" />
        <input type="hidden" name="noframe" value='1' />
		<input type="hidden" name="r" value="store/deliver-zakaz" />
		<br>
			<label class="control-label" for="deliversform-requestgoodtitle">Товар</label>
			<input type="text" id="deliversform-requestgoodtitle" class="form-control" name="requestGoodTitle" value=""> 
			<br>				
			<label class="control-label" for="deliversform-requestсount">Количество</label>
			<input type="text" id="deliversform-requestсount" class="form-control" name="requestCount" value="">	
			<br>
			<label class="control-label" for="deliversform-requestmeasure">Ед.изм</label>
			<input type="text" id="deliversform-requestmeasure" class="form-control" name="requestMeasure" value="">				
		<br>		
			<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actAddDeliver']) ?>
   	</form>

<?php
Modal::end();
?>
  
  <!-------------->

   <!--- Форма Редактирования ----->	
<?php
Modal::begin([
    'id' =>'edit_zakaz_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Добавить в доставку</h4>',

]);?>

    <form action="index.php" method="GET" id="editZakazForm">		
		<input type="hidden" name="id" value="<?=$model->id?>" />        
		<input type="hidden" name="r" value="store/deliver-zakaz" />
		<input type="hidden" name="action" value="" />
        <input type="hidden" name="noframe" value='1' />
		<input type="hidden" name="goodId" value="" />
		<br>
			<div id="dialogTitle" class="form-header"> Редактировать доставку </div>					
			<div id='editLabel'>Значение: </div>
			<input type="text" id="edit_zakaz_form-proposal" class="form-control" name="proposal" value="">	
			<p style="text-align: center; padding-bottom: 10px;">
			<br>		
			            
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actEditDeliver']) ?>
			</p>
		</form>
<?php
Modal::end();
?>
  
  <!--- Форма список организаций ----->	  
<?php
Modal::begin([
    'id' =>'org_list_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Контрагент</h4>',

]);?>
	<iframe id='org_list_frame' width='500px' height='600px' frameborder='no'   src='index.php?r=store/deliver-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
<?php
Modal::end();
?>
   

<!--

-->  
  <div id="overlay"></div>
  
<script type="text/javascript">
	restoreDeliverStatus();
</script>	
 
<?php
if (!empty($model->debug)) {
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>  
  <!-------------->
