<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Новый клиент';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;


?>
<style>
.button {
    background-color: SlateBlue;
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
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 5px;
}
 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: Indigo ;
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
</style>

  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  <?= $form->field($model, 'orgTitle')->label('Название организации')?>
  
   <div class="part-header"> Информация о контакте </div>	
   
   <?= $form->field($model, 'contactPhone')->label('Телефон')?>
   <?= $form->field($model, 'contactEmail')->label('e-mail')?>   
   <?= $form->field($model, 'contactFIO')->label('Контактное лицо')?>

   <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
   
   <div class="part-header"> Адрес</div>	
   
   <?= $form->field($model, 'area')->label('Область')?>   
   <?= $form->field($model, 'city')->label('Город')?>   
   <?= $form->field($model, 'adress')->label('Адрес')?>   
   
   <div class="part-header"> Дополнительная информация</div>	
   
   <?= $form->field($model, 'phoneList')->label('Другие телефоны (через запятую)')?>   
   <?= $form->field($model, 'urlList')->label('Адреса сайтов (через запятую)')?>
   <?= $form->field($model, 'razdelList')->label('Разделы (через запятую)')?>
   
   <div class="part-header"> Потребности </div>
    <br>
	<table border="1" width="800px">
    <tr>
	    <td>&nbsp;</td>
		<td width="10%">Нет</td>
    	<td width="10%">1</td>
		<td width="10%">2</td>
		<td width="10%">3</td>
		<td width="10%">4</td>
		<td width="10%">5</td>
		<td width="10%">10</td>
		<td width="10%">20+</td>
	</tr>

	<?php 
	$needList = $model->getNeedList();
	$needListN = $model->getNeedListN();
	if ($needListN > 10) {$needListN =10;}
	for ($i=0; $i<$needListN; $i++)
	{ 
    $radioName = "needList_".$i;    
	echo "<tr>";
	echo "  <td>".Html::encode($needList[$i]["Title"])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 0, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 1, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 2, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 3, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 4, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 5, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 6, 'uncheck' => null])."</td>";
	echo "  <td>".$form->field($model, $radioName)->radio(['label' => '', 'value' => 7, 'uncheck' => null])."</td>";
	echo "</tr>";
	}
	?>
    </table>

	<p>
	
	
<?php    
	echo $form->field($model, 'period')->dropdownList(
	["7","15","30","60","90","180","по необходимости","никогда"],
	["7","15","30","60","90","180","0","-1"]
    )->label('Регулярность (через какое количество дней обычно делаются закупки)');
	?>
	
      
   <div class="item-header"> Заполнил<div>  
   <p><?= Html::encode($curUser->userFIO)?></p> 
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
   
   <?php ActiveForm::end(); ?>
   
   