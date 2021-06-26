<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Средний ежедневный расход товара.';

$record = $model->loadAvRashod();
if ($record == false) return;
?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setAvRashod()
{
  val = document.getElementById('avRashod').value;  
  openSwitchWin('store/set-av-rashod&id=<?= $model->id ?>&val='+val);
  window.opener.location.reload(false);   
 // window.close();
}
</script >

<h3><?= Html::encode($this->title) ?> <?= $record->title?></h3>

<table width=95% class='table'>
<tr>
    <td colspan=2>Текущая оценка среднего расхода в день</td>
    <td><input class='form-control'  name='avRashod' id='avRashod' value='<?= $record->avRashod ?>'></td>
    <td><input type='button' class='btn btn-primary' value='Установить' onclick='setAvRashod();' ></td>
</tr>

<?php if ($model->rashodDate['supplyN'] > 0) {?>
<tr>
    <td>Текущая поставка:</td>
    <td>Дата: <?= date("d.m.Y", strtotime($model->rashodDate['curSupplyDate'])) ?></td>
    <td>К-во: <?= $model->rashodDate['curSupplyCount'] ?></td>
    <td>Сумма: <?= $model->rashodDate['curSupplySumm'] ?></td>
</tr>

<tr>
    <td>Текущий остаток:</td>    
    <td>К-во: <?= $record->amount ?></td>
    <td><?= $record->ed ?></td>
</tr>


<tr>
    <td>Расход на сегодня:</td>    
    <td><b> <?= number_format($model->rashodDate['cur'],2,'.','&nbsp;') ?> </b></td>
    <td><?= $record->ed ?></td>
    <td>за 30 дней <?= number_format(30*$model->rashodDate['cur'],0,'.','&nbsp;') ?></td>
</tr>

<tr>
    <td>Расход на последнюю отгрузку (<?= date("d.m.Y",strtotime($model->rashodDate['lastSupplyDate'])) ?>):</td>    
    <td><b> <?= number_format($model->rashodDate['last'],2,'.','&nbsp;') ?> </b></td>
    <td><?= $record->ed ?></td>
    <td>за 30 дней  <?= number_format(30*$model->rashodDate['last'],0,'.','&nbsp;') ?> </td>
</tr>

<?php } ?>

<?php if ($model->rashodDate['supplyN'] > 1) {?>
<tr>
    <td>Предыдущая поставка:</td>
    <td>Дата: <?= date("d.m.Y", strtotime($model->rashodDate['prevSupplyDate'])) ?></td>
    <td>К-во: <?= $model->rashodDate['prevSupplyCount'] ?></td>
    <td>Сумма: <?= $model->rashodDate['prevSupplySumm'] ?></td>
</tr>

<tr>
    <td>Расход за предыдущий период:</td>    
    <td><b> <?= number_format($model->rashodDate['prev'],2,'.','&nbsp;') ?> </b></td>
    <td><?= $record->ed ?></td>
    <td>за 30 дней <?= number_format(30*$model->rashodDate['prev'],0,'.','&nbsp;') ?> </td>
</tr>

<?php } ?>




<tr>
    <td>Расход за 180 дней:</td>    
    <td><b> <?= number_format($model->rashodDate['180d'],2,'.','&nbsp;') ?> </b></td>
    <td><?= $record->ed ?></td>
    <td>за 30 дней <?= number_format(30*$model->rashodDate['180d'],0,'.','&nbsp;') ?> </td>
</tr>



</table>




<h4> Поступление товара</h4>
<?php
echo \yii\grid\GridView::widget(
    [

        'dataProvider' => $warePrihodListProvider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],


			[
                'attribute' => 'requestDate',
				'label'     => 'Поставка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
		
					return $model['requestNum']." ".date("d.m.Y", strtotime($model['requestDate']));
                },
            ],		

			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'wareEd',
				'label'     => 'Ед.',
                'format' => 'raw',
             ],		
             
             [
                'attribute' => 'wareSumm',
				'label'     => 'На сумму',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['wareSumm'],2,'.','&nbsp;');
                },
             ],		

        ],
    ]
	);   
?>

<h4> Отпуск товара</h4>
<?php
echo \yii\grid\GridView::widget(
    [
    
        'dataProvider' => $wareRashodListProvider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],


			[
                'attribute' => 'supplyDate',
				'label'     => 'Отгрузка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
		
					return date("d.m.Y", strtotime($model['supplyDate']));
                },
            ],		

			[
                'attribute' => 'schetDate',
				'label'     => 'По счету',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return $model['schetNum']." от ".date("d.m.Y", strtotime($model['schetDate']));
                },
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Клиент',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'supplyCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'supplyEd',
				'label'     => 'Ед.',
                'format' => 'raw',
             ],		
             
             [
                'attribute' => 'supplySumm',
				'label'     => 'На сумму',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {		
					return number_format($model['supplySumm'],2,'.','&nbsp;');
                },
             ],		

        ],
    ]
	);
    
    
    
    
?>