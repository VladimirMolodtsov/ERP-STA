<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Резервирование товара';
$this->params['breadcrumbs'][] = $this->title;

$zakazRecord=$model->getZakazRecord();
		 
?>
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
 
 .table-small{
	font-size: 10pt;
	 }

	
	
 /* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 28px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
} 	
	
.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	
	

 
</style>

<script type="text/javascript">
function chngState()
{
	  
  document.getElementById('marketzakazform-resrvestatus').checked=document.getElementById('getstate').checked;
  //document.getElementById('marketschetform-schetstatus').click();
}

function openWin(url, wname)
{
  wid=window.open(url,  wname,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
  <?php
  /*window.open("index.php?r="+url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=500,width=750,height=900'); */
  ?>
}

</script>



  <h2><?= Html::encode($this->title) ?></h2>
  
  Наименование компании <u><strong><?= Html::encode($zakazRecord['title'])?></strong></u> <br>
	Заявка номер  <?= Html::encode($zakazRecord['id'])?>  от <?= Html::encode($zakazRecord['formDate'])?>
	
    <div class="part-header"> Текущая заявка </div> 
    <br>	
    <?php	
	/* <a href='#' id='edit_zakaz'> </a>*/
	echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getZakazDetailProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			
   			[
                'attribute' => 'initialZakaz',
				'label'     => 'Начальный заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if (empty(trim($model['initialZakaz']))){$val="-";}
					else {$val=$model['initialZakaz'];}
					if ($model['isActive'] == 1) 
					{
                    return $val;
					}
					return "<div class='nonActiveCell'>".$val." </div>";
					
                },
            ],		

					
			[
                'attribute' => 'good',
				'label'     => 'Предложенный товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					if (empty(trim($model['good']))){$val="-";}
					else {$val=$model['good'];}
					if ($model['isActive'] == 1) 
					{
                    return $val;
					}
					return "<div class='nonActiveCell'>".$val." </div>";
					
                },
	
            ],		

			[
                'attribute' => 'spec',
				'label'     => 'Спецификация',
                'format' => 'raw',
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
                'attribute' => 'value',
				'label'     => 'Цена',
                'format' => 'raw',
            ],		
							
			[
                'attribute' => 'dopRequest',
				'label'     => 'Доп. условия',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'dostavka',
				'label'     => 'Доставка',
                'format' => 'raw',
            ],											
        ],
    ]
	);
	?>

		
    <?php $form = ActiveForm::begin(); ?>					
    <div class="part-header" > Зарезервировано </div>	  
    <br>
	 <table border=0 width="600px">
	<tr>	
		<td width="100px">
		<label class="switch">
		<input type="checkbox"  id="getstate" name="getstate" onclick="chngState();">
		<span class="slider round" ></span>
		</label>
	</td>
	<td>
	
	<?=$form->field($model, 'resrveStatus')->checkbox([ 'style'=>'visibility:hidden', 'label' => '',]);?>	
	</td>
	<td>
<!--<a  class='btn btn-primary' href="https://docs.google.com/spreadsheets/d/15ZegKR2QKt13sRPsYsjdtqZveZFGoHo5vw_GZZJqiBs/edit#gid=1079560211" target='_blank'>
	К резервированию
	</a> -->
	
		<td><a  class='btn btn-primary'  href="#" onclick="javascript:openWin('index.php?r=store/reserved&zakazId=<?= $model->zakazId ?>');">	Резервирование </a>	</td>
		<td><a  class='btn btn-primary' style='background-color:Gray'  href="#" onclick="javascript:openWin('<?= $model->getCfgValue(2); ?>','scldwin');">	Резервирование (старое)</a><nobr></td>
	 </tr></table>	
  </br>   
   <div class="item-header"> Выполнил<div>  
   <p><?= Html::encode($curUser->userFIO)?></p> 
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>   
   <div style='visibility:hidden'>   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false);?>
   <?php ActiveForm::end(); ?>
   
   