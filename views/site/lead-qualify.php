<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
//use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Лид. Описание товара';


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/site/lead.js');

$this->registerCssFile('@web/phone.css');

?>

<style>
.btn-local {
    padding:4px;    
    font-size:12px;
}
.search {
   position: relative;  
   display: inline; 
   floating:left; 
   overflow: hidden;
   margin-top:5px;
}

.help-block {
display:none;
}


.form-group {
padding:0px;
margin:0px;
}

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


<script type="text/javascript">

</script>



<?php $form = ActiveForm::begin(['id' => 'mainForm',
        //'options' => [ 'class' => 'signup-form form-register1'],

]); ?>  
<table style='width:520px; font-size:12px; ' border='0' class='table table-striped'>
<tr>
  <td width='250px' >Что (Наименование): вид бумаги</td>
  <td style='padding:0px;'><?= $form->field($model, 'leadWareName')->textarea([
		'id' => 'leadWareName', 
		'style' => 'font-size:12px; height:40px; width:100%'
		])->label(false)?>		
</td>
<td><?php 
if(!empty($model->leadWareName))echo "<span id='leadWareNameSign' style='font-size:14px;color:Green' class='glyphicon glyphicon-star'></span>";
else echo "<span id='leadWareNameSign' style='font-size:14px;color:Crimson' class='glyphicon glyphicon-star-empty'></span>";
?></td>
</tr>  

<tr>
  <td>Характеристики <span style='font-size:11px;'>(плотность, прочность, цвет
  мягкость, влаго и жиропроницаемость)</span></td>
  <td  style='padding:0px;'><?= $form->field($model, 'leadWareDetail')->textarea([
		'id' => 'leadWareDetail', 
        'style' => 'font-size:12px; height:50px; width:100%'
        ])->label(false)?>		
</td>
<td><?php 
if(!empty($model->leadWareDetail))echo "<span id='leadWareDetailSign' style='font-size:14px;color:Green' class='glyphicon glyphicon-star'></span>";
else echo "<span id='leadWareDetailSign' style='font-size:14px;color:Crimson' class='glyphicon glyphicon-star-empty'></span>";
?></td>

</tr>  

<tr>
  <td> 
  <table border='0'><tr><td width='150px'>
  РАЗМЕРЫ<br> (формат, ширина,<br> длинна, вес):
  </td>
  <td>
  <div style='margin-top:-5px;'><?= $form->field($model, 'isLeaf')->checkbox(['id' => 'isLeaf','label' => 'Листы', ])?></div>
  <div style='margin-top:-10px;'><?= $form->field($model, 'isRolls')->checkbox(['id' => 'isRolls','label' => 'Ролики', ])?></div>
  <div style='margin-top:-10px;'><?= $form->field($model, 'isBobine')->checkbox(['id' => 'isBobine','label' => 'Бобины', ])?></div>
  </td></tr></table>
  </td>
  <td style='padding:0px;'>  
  <?= $form->field($model, 'leadWareSize')->textarea([
		'id' => 'leadWareSize', 
        'style' => 'font-size:12px; height:50px;  width:100%'		
])->label(false)?>		
</td>
<td><?php 
if(!empty($model->leadWareSize))echo "<span id='leadWareSizeSign' style='font-size:14px;color:Green' class='glyphicon glyphicon-star'></span>";
else echo "<span id='leadWareSizeSign' style='font-size:14px;color:Crimson' class='glyphicon glyphicon-star-empty'></span>";
?></td>

</tr>  

<tr>

  <td>  
    
</td>
</tr>  


<tr>
  <td>
    <table border='0' width ='100%'> <tr>  
    <td>Сколько </td>
    <td style='padding-left:5px;padding-right:5px;padding-top:0px;'> 
    <?= $form->field($model, 'leadWareCount')->textInput([
		'id' => 'leadWareCount', 
		'style' => 'font-size:12px; width:100px',
		'placeholder' => 'количество',
		])->label(false)?>		
	</td> 	
    <td style='padding-left:5px;padding-right:5px;'> 
    <?= $form->field($model, 'leadWareEd')->textInput([
		'id' => 'leadWareEd', 
		'style' => 'font-size:12px;',
		'placeholder' => 'ед.'
		])->label(false)?>		
	</td> 	
	<td><?php 
if(!empty($model->leadWareCount))echo "<span id='leadWareCountSign' style='font-size:14px;color:Green' class='glyphicon glyphicon-star'></span>";
else echo "<span id='leadWareCountSign' style='font-size:14px;color:Crimson' class='glyphicon glyphicon-star-empty '></span>";
?></td>
	</tr></table>
  </td>
  <td>      
    <table border='0' width ='100%'> <tr>
  	<td width='75px'> На сумму</td> 
    <td style='padding-left:5px;padding-right:5px; width:85px;'> 
    <?= $form->field($model, 'leadWareSum')->textInput([
		'id' => 'leadWareSum', 
        'style' => 'font-size:12px; width:75px'		
		])->label(false)?>		
	</td> 	
	<td align='left'>руб</td> 
	</tr></table>    
</td>
</tr>  

<tr>
  <td>
    <table border='0' width ='100%'> <tr>
  	<td width='30px'>Когда</td> 
    <td style='padding-left:5px;padding-right:5px; width:80px;'> 
    <?php   
    echo DatePicker::widget([
    'name' => 'planDate',
    'id' => 'planDate',
    'value' => $model->leadWareDate,    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => "$('#leadWareDate').val($('#planDate').val());",],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>		
	</td> 	
	</tr></table>    
  </td>
  <td>
  <table border='0' width ='100%'> <tr>
    <td>Как часто закупается</td> 
    <td style='padding-left:5px;padding-right:5px;'> <?= $form->field($model, 'leadFrequency')->textInput([
		'id' => 'leadFrequency', 
        'style' => 'font-size:12px; width:105px;',		
        'placeholder' => 'Число дней'
		])->label(false)?>		
	</td> 		
	</tr></table>             
</td>
</tr>  
<tr>
  <td>Место отгрузки<span style='font-size:11px;'>
  (самовывоз, доставка, ТК, до терминала, до двери)</span></td>
  <td style='padding:0px;'><?= $form->field($model, 'leadTargetPlace')->textarea([
		'id' => 'leadTargetPlace', 
        'style' => 'font-size:12px;  height:40px; width:100%'		
        ])->label(false)?>		
</td>
</tr>     


<tr>
  <td>КУДА (город)</td>
  <td style='padding:0px;'><?= $form->field($model, 'leadTargetCity')->textInput([
		'id' => 'leadTargetCity', 
		'style' => 'font-size:12px;'
		])->label(false)?>		
</td>
	<td><?php 
if(!empty($model->leadTargetCity))echo "<span id='leadTargetCitySign' style='font-size:14px;color:Green' class='glyphicon glyphicon-star'></span>";
else echo "<span id='leadTargetCitySign' style='font-size:14px;color:Crimson' class='glyphicon glyphicon-star-empty '></span>";
?></td>
</tr>  



<tr>
  <td>Для чего используется бумага</td>
  <td style='padding:0px;'><?= $form->field($model, 'leadUse')->textarea([
		'id' => 'leadUse', 
        'style' => 'font-size:12px; height:40px; width:100%'		
        ])->label(false)?>		
</td>
</tr>



<tr>
  <td>Что использовали раньше</td>
  <td style='padding:0px;'><?= $form->field($model, 'leadPrevAnalog')->textarea([
		'id' => 'leadPrevAnalog', 
        'style' => 'font-size:12px; height:40px; width:100%'		
        ])->label(false)?>		
</td>
</tr>

<tr>
  <td>Чем занимается компания</td>
  <td style='padding:0px;'><?= $form->field($model, 'leadCompanyGoal')->textarea([
		'id' => 'leadCompanyGoal', 
        'style' => 'font-size:12px; height:40px; width:100%'		
        ])->label(false)?>		
</td>
</tr>

<tr>
  <td colspan='2' align='right' >
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'width:100px; background-color: DarkGreen ;']) ?>
        <div class='btn btn-default' onclick='window.close()'>Закрыть</div>
  </td>
</tr>

</table>



     <?= $form->field($model, 'leadWareDate')->hiddenInput(['id' => 'leadWareDate', ])->label(false)?>		
     <?= $form->field($model, 'leadId')->hiddenInput(['id' => 'leadId',])->label(false)?>	
     <?= $form->field($model, 'refContact')->hiddenInput(['id' => 'refContact',])->label(false)?>	
  
<?php ActiveForm::end(); ?>





 
 
