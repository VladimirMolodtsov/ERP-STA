<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выписка (приход денег).';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>
.table-small {
padding: 1px;
font-size:12px;
}
</style>

<script>
 function  linkDoc(id){
	window.parent.linkSchet(id);
 }     
 function  unLinkDoc(id){
	window.parent.unLinkSchet(id);
 }     

</script>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<p><b><?= Html::encode($this->title) ?></b>
<?php if ($model->flt == 'showAll'){?>
<a class='btn btn-default' style='margin-left:20px;' href="index.php?r=/bank/operator/doc-extract&noframe=1&flt=showSel&refExtract=<?=$model->refExtract?>">Связанные</a>
<?php } else {
?>
<a class='btn btn-default' style='margin-left:20px;' href="index.php?r=/bank/operator/doc-extract&noframe=1&flt=showAll&refExtract=<?=$model->refExtract?>">Все</a>
<?php } ?>

</p>
<div style='text-align:right;'>

</div>
<br>
<div>
<?php if ($model->flt == 'showAll'){?>

<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='/bank/operator/doc-extract'>
<input type='hidden' name='noframe' value='1'>
<input type='hidden' name='refExtract' value='<?=$model->refExtract?>'>
<table border='0' width='500px' style='padding:5px' >
<tr>
<td>От</td>
<td>
<select name='m_from' class="form-control" style='padding:2px;font-size:12px;'>
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
<td><input name='y_from' class="form-control"  style='padding:2px;font-size:12px;' value='<?= $model->y_from ?>'> </td>

<td rowspan="2" valign='bottom'>
<input class="form-control" type='submit' value='Применить'>
</td>
</tr>


<tr>
<td>До</td>
<td>
<select name='m_to' class="form-control"  style='padding:2px;font-size:12px;' >
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
<td><input name='y_to' class="form-control"  style='padding:2px;font-size:12px;' value='<?= $model->y_to ?>'> </td>

</tr>
</table>
</form>
<?php } ?>
</div>
</br>


<?php
$refExtract = $model->refExtract;
if ($model->refExtract> 0){
 $model->loadExtractData($refExtract);   
echo "    
<div class='spacer'></div>

<table class='table table-bordered table-striped'>
<thead>
<tr>
  <th>П/П </th>
  <th>Плательщик </th>
  <th>Получатель </th>
  <th>Сумма </th>
</tr>
</thead>

<tbody>
<tr>
  <td>$model->docNum</td>
  <td>$model->debetOrgTitle </td>
  <td>$model->creditOrgTitle </td>
  <td>".number_format($model->extractSum,2,'.','&nbsp;')." </td>
</tr>
<tr>
  <td colspan=4>$model->description</td>
</tr>
</tbody>
</table>
";    
}   


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
        'columns' => [

	       [
                'attribute' => 'lnkOrgTitle',
				'label'     => 'Контрагент',
                'format' => 'raw',
                'contentOptions'   =>   ['padding' => '2px', 'font-size' => '12px;'] , 
                'value' => function ($model, $key, $index, $column) {                
                    return $model['orgTitle'];
                }                
                
            ],	

     	    [
                'attribute' => 'schetDate',
				'label'     => 'Счет',
                'format' => 'raw',
                'contentOptions'   =>   ['padding' => '2px', 'font-size' => '12px;'] , 
                'value' => function ($model, $key, $index, $column) {                
                
                $val = \yii\helpers\Html::tag( 'div',                                
               $model['schetNum']."<br>от&nbsp;".date("d.m.Y", strtotime($model['schetDate'])).
               '1C:'.$model['ref1C'],                 
                   [
                     'style'   => "padding:5px;margin:0px;font-size:12px;",
                   ]);                   
                return $val;
                }                

            ],		
     	    [
                'attribute' => 'schetSumm',
				'label'     => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    return number_format($model['schetSumm'],2,'.','&nbsp;');
                }                
                
            ],		

     	    [
                'attribute' => '-',
				'label'     => 'Привязано',
                'format' => 'raw',
                'contentOptions'   =>   ['padding' => '2px', 'font-size' => '12px;'] , 
                'value' => function ($model, $key, $index, $column) {                
                $res="";
                if (!empty($model['docNum'])) $res= "П/П № ".$model['docNum'];
                return  $res;
               }                
                
            ],		

            [
                'attribute' => 'action',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($refExtract) {                    
          
                $id = $model['id']."removeData"; 
                
                $val ="";
                
                if ($refExtract == $model['refExtract'] ){
                    $action =  "unLinkDoc(".$model['id'].");";                    
                    $style="color:Crimson;";    
                    $title = "Отвязать счет";                    
                    $val ="<span class='glyphicon glyphicon-remove'></span>";                
                }
                else
                {
                    $action =  "linkDoc(".$model['id'].");";                    
                    $style="color:Green;";    
                    $title = "Привязать счет";
                    $val ="<span class='glyphicon glyphicon-plus'></span>";                
                     	
                }
                
                return \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => "padding:5px;margin:0px;".$style,
                   ]);
                
                    
               }
                
            ],		
            
            
        ],
    ]
	);
?>

