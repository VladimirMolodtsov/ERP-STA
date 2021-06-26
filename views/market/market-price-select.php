<?php

/* @var $this yii\web\View */

//use yii\helpers\Html;
//use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* $session = Yii::$app->session;		
 $session->open();
 $priceList=$session->get('MarketPrice');*/

?>

<style>

table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
</style>

<?php $form = ActiveForm::begin(['id' => 'priceForm']); ?>
<div style="width:850px; height:550px; overflow-y: scroll;" > 
<table border="1px" style="width:800px; padding:5px; border-style:solid ;  border-width: 1px; border-color : black"> 
	<tr>
	<td>Товар</td>
	<td>Остаток</td>
	<td>Ед. изм.</td>
	<td>Цена</td>
	<td>Количество</td>
	</tr> 	
	
	<?php
	//foreach ($modelArray as $index => $model) 
	for ($index=0; $index< count($modelArray); $index++)
	{
	$model = $modelArray[$index];
	$i=$index;
	echo "<tr>";	
	echo "<td>".Html::encode($priceList[$i]['GoodTitle'])."</td>";		
	echo "<td>".Html::encode($priceList[$i]['RemainCount'])."</td>";		
	echo "<td>".Html::encode($priceList[$i]['ed'])."</td>";		
	echo "<td>".Html::encode($priceList[$i]['Val'])."</td>";		
	echo "<td>".$form->field($model, "[$index]count")->label(false)."</td>";		
	echo "</tr>";
	}
	?>

</table>	
</div>
<br>
 <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform']) ?> <input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:history.back();"/> 
 <?php ActiveForm::end(); ?>




  
