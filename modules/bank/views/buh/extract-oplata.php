<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выписка (расход денег).';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>
.table-small {
padding: 2px;
font-size:12px;
}
</style>

<script>
 function  linkExtract(id){
	window.parent.linkExtract(id);
 }     
 function  unLinkExtract(id){
	window.parent.unLinkExtract(id);
 }     

</script>

<?php




?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<p><b><?= Html::encode($this->title) ?></b>
<?php if ($model->flt == 'showAll'){?>
<a class='btn btn-default' style='margin-left:20px;' href="index.php?r=/bank/buh/extract-oplata&noframe=1&flt=showSel&refDocOplata=<?=$model->refDocOplata?>">Связанные</a>
<?php } else {
?>
<a class='btn btn-default' style='margin-left:20px;'  href="index.php?r=/bank/buh/extract-oplata&noframe=1&flt=showAll&refDocOplata=<?=$model->refDocOplata?>">Все</a>
<?php } ?>

</p>
<div style='text-align:right;'>

</div>
<br>
<div>
<?php if ($model->flt == 'showAll'){?>

<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='/bank/buh/extract-oplata'>
<input type='hidden' name='noframe' value='1'>
<input type='hidden' name='refDocOplata' value='<?=$model->refDocOplata?>'>
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
$refDocOplata = $model->refDocOplata;
$refSuppSchet = $model->refSuppSchet;
if ($model->refDocOplata > 0){
$ppNum =  $refDocOplata+10000;   
echo "    
<div class='spacer'></div>

<p> <b> Оплата по счету № $model->suppSchetNum 
от $model->suppSchetDate 
 Сумма счета $model->suppSchetSum
</b></p>    

<table class='table table-bordered table-striped'>
<thead>
<tr>
  <th>П/П </th>
  <th>Документ </th>
  <th>Сумма </th>
  <th>Контрагент </th>
</tr>
</thead>

<tbody>
<tr>
  <td>$ppNum </td>
  <td>$model->docShowNum </td>
  <td>$model->docToOplataSum </td>
  <td>$model->suppOrgTitle </td>
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
            'id',
     	    [
                'attribute' => 'recordDate',
				'label'     => 'Платежка',
                'format' => 'raw',
                'contentOptions'   =>   ['padding' => '2px', 'font-size' => '12px;'] , 
                'value' => function ($model, $key, $index, $column) {                
                    return $model['docNum']." <br> ".date("d.m.y", strtotime($model['recordDate']));
                }                
                
            ],		

     	    [
                'attribute' => '-',
				'label'     => 'Привязано',
                'format' => 'raw',
                'contentOptions'   =>   ['padding' => '2px', 'font-size' => '12px;'] , 
                'value' => function ($model, $key, $index, $column) {                
               
               $linkList=Yii::$app->db->createCommand("Select DISTINCT docIntNum from  
                {{%documents}},{{%doc_oplata}},{{%doc_extract_lnk}}
                 where  {{%documents}}.id={{%doc_oplata}}.refDocument                   
                   AND  {{%doc_extract_lnk}}.docOplataRef = {{%doc_oplata}}.id
                   AND {{%doc_extract_lnk}}.extractRef =:extractRef AND isLnk =1",                  
                 [':extractRef' => $model['id'],])->queryAll();                                
                 $res="";
                 for ($i=0; $i<count($linkList); $i++) 
                     $res.= "№ ".$linkList[$i]['docIntNum']."<br>";
                 
                return  $res;

                }                
                
            ],		


     	    [
                'attribute' => 'extractSelSum',
				'label'     => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    return number_format($model['debetSum'],2,'.','&nbsp;');
                }                
                
            ],		

            
     	    [
                'attribute' => 'creditOrgTitle',
				'label'     => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    return $model['creditOrgTitle'];
                }                

            ],		

            [
                'attribute' => 'action',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($refDocOplata) {                    
                
          
                $id = $model['id']."removeData"; 
                
                $val ="";
          
   
               $N=Yii::$app->db->createCommand("Select count(id) from  
                {{%doc_extract_lnk}}
                 where   {{%doc_extract_lnk}}.docOplataRef =:extractRef AND isLnk =1",                  
                 [':extractRef' => $refDocOplata,])->queryScalar();    
          
                if ($N >0){
                    $action =  "unLinkExtract(".$model['id'].");";                    
                    $style="color:Crimson;";    
                    $title = "Отвязать платежку";                    
                    $val ="<span class='glyphicon glyphicon-remove'></span>";                
                }
                else
                {
                    $action =  "linkExtract(".$model['id'].");";                    
                    $style="color:Green;";    
                    $title = "Отвязать платежку";
                    $val ="<span class='glyphicon glyphicon-plus'></span>";                
                     	
                }
                
                return \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => "padding:5px;margin:5px;".$style,
                   ]);
                
                    
               }
                
            ],		
            
            
        ],
    ]
	);
?>

