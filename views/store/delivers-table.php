<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Завершенные доставки.';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>

<br>
<div>
<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='store/delivers-table'>
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
      


    '{{%user}}.userFIO', 
    '{{%schet}}.schetNum', 
    '{{%schet}}.schetDate', 
    '{{%schet}}.schetSumm', 
    'requestSclad'
    'reason', 
    '{{%supplier_schet_header}}.schetNum as supplierSchetNum', 
    '{{%supplier_schet_header}}.schetDate as supplierSchetDate',        
    'requestNote',
    'factDate'

      
     	    [
                'attribute' => 'requestDatePlanned',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		


     	    [
                'attribute' => 'id',
				'label'     => 'Номер',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'title',
				'label'     => 'Клиент',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'goodTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                        
                }                                
            ],		
	
            
  
            
        ],
    ]
	);
?>

