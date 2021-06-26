<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Поступление денег.';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>
<div style='text-align:right;'>
<a href="#" onclick="openEditWin('index.php?r=fin/oplata-src&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 
<br> <?= $model->getCfgValue(107) ?>&nbsp;&nbsp; <a href='index.php?r=data/sync-oplata' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a>
</div>
<br>
<div>

<?php if ($model->setDate != '0') {
    
  echo "<h4>".$model->setDate."</h4>";  
} else {?>

<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='fin/oplata-src'>
<table border='0' width='500px' style='padding:5px' >
<tr>
<td>От</td>
<td>
<select name='m_from' class="form-control">
<?php
for ($i=1; $i<=12; $i++)
{
    $p = "<option value='".$i."'";
    if ($i == $model->m_from) $p .= " selected";
    $p .= ">".$monthList[$i]."</option>";
    echo $p;
}
?>
</select>
</td>
<td><input name='y_from' class="form-control" value='<?= $model->y_from ?>'> </td>

<td rowspan="2" valign='bottom'>
<input class="form-control" type='submit' value='Применить'>
</td>
</tr>


<tr>
<td>До</td>
<td>
<select name='m_to' class="form-control">
<?php
for ($i=1; $i<=12; $i++)
{
    $p = "<option value='".$i."'";
    if ($i == $model->m_to) $p .= " selected";
    $p .= ">".$monthList[$i]."</option>";
    echo $p;
}
?>
</select>
</td>
<td><input name='y_to' class="form-control" value='<?= $model->y_to ?>'> </td>
</tr>

</table>
</form>
<?php }?>
</div>
</br>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
     	    [
                'attribute' => 'oplateDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'oplateNum',
				'label'     => 'Номер',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'schetNum1',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return "<nobr>".$model['schetNum']." от ".$model['schetDate']."</nobr>";
                }                
            ],		
            
                             
     	    [
                'attribute' => 'oplateSumm',
				'label'     => 'Сумма',
                'format' => 'raw',
            ],		

            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return "<nobr>".mb_substr($model['orgTitle'],0,24,'UTF-8')."</nobr>";
                }                

            ],		

            [
                'attribute' => 'orgINN',
				'label'     => 'ИНН',
                'format' => 'raw',
            ],		
/*
            [
                'attribute' => 'orgKPP',
				'label'     => 'Плательщик',
                'format' => 'raw',
            ],		
*/            
     	    [
                'attribute' => 'refSchet',
				'label'     => 'Привязан',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 	if (intval(empty($model['refSchet']) > 0) ){ $isFlg = false;}
					else                        { $isFlg = true;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);

                },

            ],		
            
     	    [
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',

            ],		
            
            
        ],
    ]
	);
?>

