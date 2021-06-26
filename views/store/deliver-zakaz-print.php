<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$curUser=Yii::$app->user->identity;
$this->title = 'Задание на доставку';

$deliverRecord = $model-> prepareDeliver();
?>

<style>
td {
    height:30px;
}
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

.stick 
{
 padding:10px;
}

.local-box 
{
 border: 3px #000 solid;
 position: relative;
 display: block-inline;
 width: 20px;
 height: 20px;
 }

 h3{
	 align:center;
 }
 
.deliver-table
{
  width:100%;

} 

 @media print {
    .pbreak {
     page-break-after: always;
    } 
   } 

</style>

<script type="text/javascript">

</script>

<h3><?= Html::encode($this->title) ?></h3>


<table border='1' width="100%">

<tr>
	<td class='stick' width="200px"><b>Клиент:</b> </td>
	<td class='stick' width="300px"><?= Html::encode($model->orgTitle)?>  </td>	
	<td class='stick' style='background: Silver;' ><b>Корешок заявки </b><?= Html::encode($model->requestNum)?> </td>
</tr>	


<tr>
	<td class='stick'  width="200px"><b>Дата фактического <br>выполнения:</b> </td>
	<td class='stick'  width="300px">  </td>	
	<td class='stick'  rowspan=3 style="padding-left:200px" >  
	<div class='local-box'> </div>	Выполнено <br> &nbsp;<br>
	<div class='local-box'> </div> Документы сданы <br>
	</td>

</tr>	

<tr>
	<td class='stick'  width="200px"><b>Фактический вес (кг):</b> </td>
	<td width="300px">  </td>	
</tr>	

<tr>
	<td class='stick'  width="200px"><b>Затраты экспедитора</b> </td>
	<td width="300px"></td>	
</tr>	

<tr>
	<td class='stick'  width="200px"><b>Время (мин):</b> </td>
	<td width="300px">  </td>	
</tr>	

<tr>
	<td class='stick'  width="200px"><b>Затраты водителя</b> </td>
	<td width="300px"></td>	
</tr>	

</table>

<hr>
 
<table border='0' width="100%">
<tr>
    <td  colspan='2' style='background: Silver;'>
    <div style='text-align:right; padding-top:5px; padding-right: 50px; height:30px; ' > <b>Заявка №</b> <?= Html::encode($model->requestNum)?> &nbsp;&nbsp;&nbsp; <b>УПД:</b> <?= Html::encode($model->requestUPD)?>  </div>    
    </td>
</tr>
<tr>
	<td width="200px"><b>Доставка назначена:</b> </td>
	<td><?= Html::encode($model->requestDateReal)?> </td>
</tr>	

<tr>
	<td width="200px"><b>Очередность,важность:</b> </td>
	<td><?= Html::encode($deliverRecord->requestImportance)?> </td>
</tr>	

<tr>
	<td>  <b>Получатель</b></td>
	<td><?= Html::encode($model->orgTitle)?></td>	
</tr>	

<tr>
	<td><b>ИНН:</b></td>
	<td><?= Html::encode($model->orgINN)?></td>
</tr>	
<tr>
	<td ><b>Юр. адрес:</b>	</td>
	<td ><?= Html::encode($model->orgAdress)?> </td>	
</tr>	
<tr>
	<td><b>Точный адрес <br> доставки:</b></td>
	<td ><?= Html::encode($model->requestAdress)?> </td>
</tr>	

<tr>
	<td><b>Контакт ФИО, телефон:</b></td>
	<td><?= Html::encode($model->requestContact)?>  <?= Html::encode($model->requestPhone)?> </td>
</tr>	

<tr>
    <td  colspan='2' style='background: Silver;'>
    &nbsp;
    </td>
</tr>


<tr>
	<td>  <b>Отправитель</b></td>
	<td><?= Html::encode($model->orgFromTitle)?></td>	
</tr>	
<tr>
	<td ><b>Юр. адрес отправителя:</b>	</td>
	<td ><?= Html::encode($model->orgFromAdress)?> </td>	
</tr>	

</table>

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
                'attribute' => 'Наименование',
				'label'     => 'Наименование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
							return $model['requestGoodTitle'];					
			    }	                
            ],		

			[
                'attribute' => 'Количество',
				'label'     => 'Количество',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
							return number_format($model['requestCount'],2,'.',' ');					
			    }	                
            ],		
			
			[
                'attribute' => 'Ед.изм',
				'label'     => 'Ед.изм',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
							return $model['requestMeasure'];					
			    }	                

            ],		
					
        ],
    ]
	);
	?>

<table border='1' width="100%">

<tr>
	<td><b>Товар, вид, категория:</b></td>
	<td><b>К-во мест:</b></td>
  	<td><b>Объём:</b></td>
    <td><b>Вес, всего:</b></td>
</tr>	

<tr>
	<td><?php if(!empty($deliverRecord->requestCategory)) echo Html::encode($deliverRecord->requestCategory)  ?></td>
	<td><?php if(!empty($deliverRecord->requestPlaces)) echo Html::encode($deliverRecord->requestPlaces)  ?></td>
  	<td><?php if(!empty($deliverRecord->requestVolume)) echo Html::encode($deliverRecord->requestVolume)  ?></td>
    <td><?php if(!empty($deliverRecord->requestTotalWeight)) echo Html::encode($deliverRecord->requestTotalWeight)  ?></td>
</tr>	

    
</table>    
<table border='0' width="100%">

<tr>
	<td width="200px" ><b>С какого склада:</b></td>
	<td ><?= Html::encode($model->requestSclad)?></td>
  	<td colspan='2'><?= Html::encode($model->requestScladAdress)?></td>
</tr>	

<tr>
	<td><b>Дополнения/примечания:</b></td>
	<td><?= Html::encode($model->requestNote)?> </td>
</tr>	


<tr>
	<td class='middle_lbl'><b>Исполнитель:</b></td>
	<td ><?= Html::encode($model->requestExecutor)?>  </td>
</tr>	

<tr>
	<td class='middle_lbl'><b>Заполнил заявку:</b></td>
	<td ><?= Html::encode($model->userFIO)?>  </td>
</tr>	


</table>

