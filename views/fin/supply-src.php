<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Отгрузка товара';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

function extendLeft()
{
  document.getElementById('fromDate').value = '<?= date("Y-m-d", strtotime($model->fromDate) - 24*3600)  ?>';
  document.getElementById('fltForm').submit();
}

function intendLeft()
{
<?php 
 if ($model->fromDate == $model->setDate) echo "return;";
?>

  document.getElementById('fromDate').value = '<?= date("Y-m-d", strtotime($model->fromDate) + 24*3600)  ?>';
  document.getElementById('fltForm').submit();
}


function extendRight()
{
  document.getElementById('toDate').value = '<?= date("Y-m-d", strtotime($model->toDate) + 24*3600)  ?>';
  document.getElementById('fltForm').submit();
}

function intendRight()
{
<?php 
 if ($model->toDate == $model->setDate) echo "return;";
?>

  document.getElementById('toDate').value = '<?= date("Y-m-d", strtotime($model->toDate) - 24*3600)  ?>';
  document.getElementById('fltForm').submit();
}





</script> 

<h3><?= Html::encode($this->title) ?></h3>


<div style='text-align:right;'>
<a href="#" onclick="openEditWin('index.php?r=fin/supply-src&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 
<br> <?= $model->getCfgValue(108) ?>&nbsp;&nbsp; <a href='index.php?r=data/sync-supply' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a>
</div>
<br>
<div>
<?php if ($model->setDate != '0') {
 
?>
  <form name='fltForm' id ='fltForm' method='get' action='index.php'>
  <input type='hidden' name='r' value='fin/supply-src'>
  <input type='hidden' name='setDate' value='<?= $model->setDate ?>'>
  <input type='hidden' name='wareRef' value='<?= $model->wareListRef ?>'>
  <table border='0' width='500px' style='padding:5px' >
  <tr>
  <td style='padding:5px'>От</td>
  <td style='padding:5px'> <a href="#" onclick="extendLeft();"><span class="glyphicon glyphicon-step-backward"></span></a> </td>
  <td><input class="form-control" type='date' id='fromDate' name='fromDate' value="<?= $model->fromDate ?>" ></td>     
  <td style='padding:5px'> <a href="#" onclick="intendLeft();"><span class="glyphicon glyphicon-step-forward"></span></a> </td>
  <td style='padding:5px'>До</td>
   <td style='padding:5px'> <a href="#" onclick="intendRight();"><span class="glyphicon glyphicon-step-backward"></span></a> </td>
  <td><input class="form-control" type='date' id='toDate' name='toDate' value="<?= $model->toDate ?>" ></td>
  <td style='padding:5px'> <a href="#" onclick="extendRight();"><span class="glyphicon glyphicon-step-forward"></span></a> </td>
  <td valign='bottom' style='padding:5px'><input class="form-control" type='submit' value='Фильтр'></td>
  </tr>
</table>
</form>
</br>
<?php } else {?>

<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='fin/supply-src'>
<input type='hidden' name='wareRef' value='<?= $model->wareListRef ?>'>
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
                'attribute' => 'supplyDate',
                'label'     => 'Дата',
                'format' => 'raw',
            ],        


             [
                'attribute' => 'supplyNum',
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
                'attribute' => 'supplyGood',
                'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return "<nobr>".mb_substr($model['supplyGood'],0,48 ,'UTF-8')."</nobr>";
                }                
                
                
            ],        
            
             [
                'attribute' => 'supplyCount',
                'label'     => 'К-во',
                'format' => 'raw',
            ],        
            
             [
                'attribute' => 'supplyEd',
                'label'     => 'Ед.изм.',
                'format' => 'raw',
            ],        
            
             [
                'attribute' => '',
                'label'     => 'цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['supplySumm']/$model['supplyCount'], 2, '.', '&nbsp;');
                }                

            ],        
            
             [
                'attribute' => 'supplySumm',
                'label'     => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['supplySumm'], 2, '.', '&nbsp;');
                }                
                
            ],        

            
             [
                'attribute' => 'orgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return "<nobr>".mb_substr($model['orgTitle'],0,24,'UTF-8')."</nobr>";
                }                

            ],        

 /*           [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
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

