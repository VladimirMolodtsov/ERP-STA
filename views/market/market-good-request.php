<?php

/* @var $this yii\web\View */

//use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Запрос на закупку товара';
//$this->params['breadcrumbs'][] = $this->title;

//print_r($zakazRecord);

?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>

<style>
.button {
    background-color: #e7e7e7;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px px;
	width: 150px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 



 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkSlateGrey;
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
  .contact_title {
    margin:5px 0px;
    padding:10px;
	font-size: 10pt;    
}
  .contact_text {
    margin:5px 0px;
    padding:10px;
    border:1px solid #ffbc80;
    background: Beige;
	font-size: 10pt;    
}

.table-small{
	font-size: 10pt;
	 }

 
 .phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
	font-size: 10pt;    
}
/* кликабельный текст */
.phones {
    color:#f70;
    cursor: help
	font-size: 10pt;    
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;	
}


.form-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkSlateGrey;
	font-size: 12pt;
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
 
#add_zakaz_form {
	width: 900px; 	
	height: 250px; /* Рaзмеры дoлжны быть фиксирoвaны */
	font-size: 12pt;
	border-radius: 5px;
	border: 3px #000 solid;
	background: #fff;
	position: fixed; /* чтoбы oкнo былo в видимoй зoне в любoм месте */
	top: 45%; /* oтступaем сверху 45%, oстaльные 5% пoдвинет скрипт */
	left: 50%; /* пoлoвинa экрaнa слевa */
	margin-top: -150px;
	margin-left: -400px; /* тут вся мaгия центрoвки css, oтступaем влевo и вверх минус пoлoвину ширины и высoты сooтветственнo =) */
	display: none; /* в oбычнoм сoстoянии oкнa не дoлжнo быть */
	opacity: 0; /* пoлнoстью прoзрaчнo для aнимирoвaния */
	z-index: 5; /* oкнo дoлжнo быть нaибoлее бoльшем слoе */
	padding: 20px 10px;
}
/* Кнoпкa зaкрыть для тех ктo в тaнке) */
#add_zakaz_form #add_zakaz_close {
	width: 21px;
	height: 21px;
	position: absolute;
	top: 10px;
	right: 10px;
	cursor: pointer;
	display: block;
}

#edit_zakaz_form {
	width: 900px; 	
	height: 250px; /* Рaзмеры дoлжны быть фиксирoвaны */
	font-size: 12pt;
	border-radius: 5px;
	border: 3px #000 solid;
	background: #fff;
	position: fixed; /* чтoбы oкнo былo в видимoй зoне в любoм месте */
	top: 45%; /* oтступaем сверху 45%, oстaльные 5% пoдвинет скрипт */
	left: 50%; /* пoлoвинa экрaнa слевa */
	margin-top: -150px;    
	margin-left: -400px; /* тут вся мaгия центрoвки css, oтступaем влевo и вверх минус пoлoвину ширины и высoты сooтветственнo =) */
	display: none; /* в oбычнoм сoстoянии oкнa не дoлжнo быть */
	opacity: 0; /* пoлнoстью прoзрaчнo для aнимирoвaния */
	z-index: 5; /* oкнo дoлжнo быть нaибoлее бoльшем слoе */
	padding: 20px 10px;
}
/* Кнoпкa зaкрыть для тех ктo в тaнке) */
#edit_zakaz_form #edit_zakaz_close {
	width: 21px;
	height: 21px;
	position: absolute;
	top: 10px;
	right: 10px;
	cursor: pointer;
	display: block;
}

/* Пoдлoжкa */
#overlay {
	z-index:3; /* пoдлoжкa дoлжнa быть выше слoев элементoв сaйтa, нo ниже слoя мoдaльнoгo oкнa */
	position:fixed; /* всегдa перекрывaет весь сaйт */
	background-color:#000; /* чернaя */
	opacity:0.8; /* нo немнoгo прoзрaчнa */
	-moz-opacity:0.8; /* фикс прозрачности для старых браузеров */
	filter:alpha(opacity=80);
	width:100%; 
	height:100%; /* рaзмерoм вo весь экрaн */
	top:0; /* сверху и слевa 0, oбязaтельные свoйствa! */
	left:0;
	cursor:pointer;
	display:none; /* в oбычнoм сoстoянии её нет) */
}
	
table.menu    { border-left:0px solid; border-spacing: 15px;	 border-collapse: separate; }
tr.menu-row   { height:30px; }
td.menu-point { background:DarkSlateGrey; color:#FFFFFF; font-weight:bold; text-align:center; padding:10px; border:0px}
a.menu-point  {  color:#FFFFFF; font-weight:bold;  font-style: normal; }
a.menu-point:hover {  color:#FFFFFF; font-weight:bold; }    

</style>

<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["Mainform"]["marketzakazform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["Mainform"]["marketzakazform-contactphone"].value,'doCall','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}

function showAdd()
{
	if (document.getElementById("addRequest").style.visible == "hidden")
	{ document.getElementById("addRequest").style.visible = "visible"; }
	else document.getElementById("addRequest").style.visible = "hidden";
}

function showDialog(id,	good, count, marketDate,sklad)
{
//alert(id);

	document.forms["editZakazForm"]["recId"].value=id;
	document.forms["editZakazForm"]["edit-good"].value=good;
	document.forms["editZakazForm"]["edit-count"].value=count;
	document.forms["editZakazForm"]["edit-marketDate"].value=marketDate;
	document.forms["editZakazForm"]["edit-sklad"].value=sklad;
	

//Показ диалога
		$('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
				$('#edit_zakaz_form') 
					.css('display', 'block') // убираем у модального окна display: none;
					.animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
		document.forms["editZakazForm"]["edit-good"].focus();	
		});
	
 
	//document.getElementById('edit_zakaz_form-proposal').focus();		
}

</script>

<script type="text/javascript">
$(document).ready(
function() 
{ // вся магия после загрузки страницы
	$('a#add_zakaz').click( 
	function(event)
	{ // ловим клик по ссылки с id="go"
		event.preventDefault(); // выключаем стандартную роль элемента
		$('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предъидущей анимации
				$('#add_zakaz_form') 
					.css('display', 'block') // убираем у модального окна display: none;
					.animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
		document.getElementById('add-good').focus();	
		});		
	}
	);
	
	$('a#edit_zakaz').click( 
	function(event)
	{ // ловим клик по ссылки с id="go"
		event.preventDefault(); // выключаем стандартную роль элемента
		$('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предидущей анимации
				$('#edit_zakaz_form') 
					.css('display', 'block') // убираем у модального окна display: none;
					.animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
		});
	}
	);

	/* Закрытие модального окна, тут делаем то же самое но в обратном порядке */
	$('#add_zakaz_close, #overlay').click( 
	function()
	{ // ловим клик по крестику или подложке
		$('#add_zakaz_form')
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	}
	);
	
	$('#edit_zakaz_close, #overlay').click( 
	function()
	{ // ловим клик по крестику или подложке
		$('#edit_zakaz_form')
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	}
	);
}

);
</script>


  <h2><?= Html::encode($this->title) ?></h2>

  <div class="item-header"> 	
	Запрос цены  №  <?= Html::encode($requestRecord['id'])?>  от <?= Html::encode($requestRecord['formDate'])?>.
  </div>

    <div class="part-header"> Текущий запрос </div> 
    <br>	
    <?php	
	/* <a href='#' id='edit_zakaz'> </a>*/
	
	if ($requestRecord['isFormed'] == 0)
	{
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
                'attribute' => 'good',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "good_".$model['id'];
					$paramString = "'".$model['id']."'";
					$paramString .=",'".$model['good']."'";
					$paramString .=",'".$model['count']."'";
					$paramString .=",'".$model['marketDate']."'";
					$paramString .=",'".$model['sklad']."'";
					
					if (empty(trim($model['good']))){$val="-";}else {$val=$model['good'];}
					return "<div class='gridcell' id='".$id."' onclick=\"showDialog(".$paramString.");\">".$val." </div>";
					
                },
            ],		

			[
                'attribute' => 'count',
				'label'     => 'К-во',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "count_".$model['id'];
					$paramString = "'".$model['id']."'";
					$paramString .=",'".$model['good']."'";
					$paramString .=",'".$model['count']."'";
					$paramString .=",'".$model['marketDate']."'";
					$paramString .=",'".$model['sklad']."'";
					
					if (empty(trim($model['count']))){$val="-";}else {$val=$model['count'];}
					return "<div class='gridcell' id='".$id."' onclick=\"showDialog(".$paramString.");\">".$val." </div>";

                },
            ],		
			
			[
                'attribute' => 'marketDate',
				'label'     => 'Дата поставки',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "marketDate".$model['id'];
					$paramString = "'".$model['id']."'";
					$paramString .=",'".$model['good']."'";
					$paramString .=",'".$model['count']."'";
					$paramString .=",'".$model['marketDate']."'";
					$paramString .=",'".$model['sklad']."'";
					
					if (empty(trim($model['marketDate']))){$val="-";}else {$val=$model['marketDate'];}
					return "<div class='gridcell' id='".$id."' onclick=\"showDialog(".$paramString.");\">".$val." </div>";
                },
            ],		
			
			[
                'attribute' => 'sklad',
				'label'     => 'Склад',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$id = "sklad".$model['id'];
					$paramString = "'".$model['id']."'";
					$paramString .=",'".$model['good']."'";
					$paramString .=",'".$model['count']."'";
					$paramString .=",'".$model['marketDate']."'";
					$paramString .=",'".$model['sklad']."'";
					
					if (empty(trim($model['sklad']))){$val="-";}else {$val=$model['sklad'];}
					return "<div class='gridcell' id='".$id."' onclick=\"showDialog(".$paramString.");\">".$val." </div>";
                },
            ],		
					

			[
                'attribute' => 'Удалить',
				'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
	                  return "<a class='btn btn-primary' href='index.php?r=market/market-good-request&action=delRequestRec&id=".$model['refRequest']."&recId=".$model['id']."'>Удалить</a>";
                },
            ],							
					
        ],
    ]
	);
	}
else {
	
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
                'attribute' => 'good',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if (empty(trim($model['good']))){$val="-";}else {$val=$model['good'];}
					return $val;
					
                },
            ],		

			[
                'attribute' => 'count',
				'label'     => 'К-во',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if (empty(trim($model['count']))){$val="-";}else {$val=$model['count'];}
					return $val;	

                },
            ],		
			
			[
                'attribute' => 'marketDate',
				'label'     => 'Дата поставки',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
					if (empty(trim($model['marketDate']))){$val="-";}else {$val=$model['marketDate'];}
					return $val;	
                },
            ],		
			
			[
                'attribute' => 'sklad',
				'label'     => 'Куда выгрузить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
					if (empty(trim($model['sklad']))){$val="-";}else {$val=$model['sklad'];}
					return $val;	
                },
            ],		
					
					
        ],
    ]
	);	
}

	
?>
 <?php 	if ($requestRecord['isFormed'] == 0) { ?>
	<p align='right'>
	<a  class='btn btn-primary'  href="#" id="add_zakaz">Добавить</a> &nbsp;&nbsp;&nbsp;	
	</p>
 <?php 
 $butstr = "Сформировать";
 echo Html::submitButton($butstr, ['class' => 'btn btn-primary', 'name' => 'actMainform']) ;
 } 
else {
	
	echo "<p> Заявка сформирована";
	$butstr = "Повторить заявку";
}
 
 ?>
	
   <?php $form = ActiveForm::begin(['id' => 'Mainform']); ?>    
   <?= $form->field($model, 'requestId')->hiddenInput()->label(false)?> 
   <?php ActiveForm::end(); ?>
	   
   <!--- Форма добавления ----->	
	<div id="add_zakaz_form">
	<span id="add_zakaz_close">X</span>
	<form action="index.php" method="GET">	
		<input type="hidden" name="requestId" value="<?=$requestRecord['id']?>" />
		<input type="hidden" name="id" value="<?=$requestRecord['id']?>" />
		<input type="hidden" name="r" value="market/market-good-request" />
		<input type="hidden" name="action" value="addRequest" />
		<br>
		<div class="form-header"> Добавить в заявку </div>						
		<table width=100%" border="0">
		<tr>
		<td><label class="control-label" for="add-good">Товар</label>	<input type="text" id="add-good" class="form-control" name="good" value="">	</td>
		<td width="15%"><label class="control-label" for="add-count">К-во</label>	<input type="text" id="add-count" class="form-control" name="count" value="">	</td>
		<td width="15%"><label class="control-label" for="add-marketDate">Дата закупки</label>	<input type="text" id="add-marketDate" class="form-control" name="marketDate" value="<?= date("d.m.Y")?>">	</td>
		<td width="25%"><label class="control-label" for="add-sklad">Склад</label>	<input type="text" id="add-sklad" class="form-control" name="sklad" value="">	</td>		
		</tr>
		<tr>
		<td colspan='4' align='right'><br><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actAddZakaz']) ?></td>
		</table>				
   	</form>
  </div>
  
  <!-------------->

   <!--- Форма Редактирования ----->	
  <div id="edit_zakaz_form">
	<span id="edit_zakaz_close">X</span>		 
		<form action="index.php" method="GET" id="editZakazForm">
		<input type="hidden" name="requestId" value="<?=$requestRecord['id']?>" />
		<input type="hidden" name="id" value="<?=$requestRecord['id']?>" />
		<input type="hidden" name="action" value="editRequest" />
		<input type="hidden" name="r" value="market/market-good-request" />
		<input type="hidden" name="recId" value="" />
		<br>
		<div id="dialogTitle" class="form-header"> Редактировать заявку </div>					
		<table width=100%" border="0">
		<tr>
		<td><label class="control-label" for="edit-good">Товар</label><input type="text" id="edit-good" class="form-control" name="good" value="">	</td>
		<td width="15%"><label class="control-label" for="edit-count">К-во</label>	<input type="text" id="edit-count" class="form-control" name="count" value="">	</td>
		<td width="15%"><label class="control-label" for="edit-marketDate">Дата </label><input type="text" id="edit-marketDate" class="form-control" name="marketDate" value="">	</td>
		<td width="25%"><label class="control-label" for="edit-sklad">Склад</label>	<input type="text" id="edit-sklad" class="form-control" name="sklad" value="">	</td>		
		</tr>
		<tr>
		<td colspan='4' align='right'><br><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'actAddZakaz']) ?></td>
		</table>				
		</form>
  </div>
  <div id="overlay"></div>
  <!-------------->