<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Заказы клиентам.';

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
<a href="#" onclick="openEditWin('index.php?r=fin/zakaz-src&frame=0&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 
</div>
<br>
<div>
<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='fin/zakaz-src'>
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
       
                  

       
             [
                'attribute' => 'formDate',
                'label'     => 'Дата',
                'format' => 'raw',
            ],        


             [
                'attribute' => 'id',
                'label'     => 'Номер',
                'format' => 'raw',
            ],        

            
             [
                'attribute' => 'goodTitle',
                'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                if (!empty($model['good'])) 
                    return "<nobr>".mb_substr($model['good'],0,48 ,'UTF-8')."</nobr>";
                else
                    return "<nobr>".mb_substr($model['initialZakaz'],0,48 ,'UTF-8')."</nobr>";                    
                }                                
            ],        

             [
                'attribute' => 'count',
                'label'     => 'К-во',
                'format' => 'raw',
            ],        
            
             [
                'attribute' => 'ed',
                'label'     => 'Ед.изм.',
                'format' => 'raw',
            ],        
            
            
             [
                'attribute' => 'goodSumm',
                'label'     => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['count']*$model['value'],2,'.','');
                }                                
             ],        
            
             [
                'attribute' => 'contentActive',
                'label'     => 'Товар <br>активен',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    if ($model['contentActive'] == 1) return 'Да';
                    return '&nbsp;';
                }                                
                
             ],        
            
             [
                'attribute' => 'zakazActive',
                'label'     => 'Заказ <br>активен',
                'format' => 'raw',
                'encodeLabel' => false,
                'value' => function ($model, $key, $index, $column) {                
                    if ($model['zakazActive'] == 1) return 'Да';
                    if (empty ($model['zakazActive']) && empty ($model['schetNum'])) return 'Отказ';
                    return '&nbsp;';
                }                                
                
             ],        
            
             [
                'attribute' => 'schetNum',
                'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return $model['schetNum'];
                }                                
                
             ],                    
            
            
             [
                'attribute' => 'orgTitle',
                'label'     => 'Поставщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return "<nobr>".mb_substr($model['orgTitle'],0,24,'UTF-8')."</nobr>";
                }                
            ],        
    
        ],
    ]
    );
?>

