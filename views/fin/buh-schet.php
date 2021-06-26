<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Бухгалтерские счета';
$curUser=Yii::$app->user->identity;



?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>

</style>
  
<script>
function syncBuhSchet()
{
 stDate = document.getElementById('stDate').value;
 enDate = document.getElementById('enDate').value;
 document.location.href='index.php?r=data/sync-buh-schet&stDate='+stDate+'&enDate='+enDate;

}

function openPeriod()
{
 stDate = document.getElementById('stDate').value;
 enDate = document.getElementById('enDate').value;
 document.location.href='index.php?r=fin/buh-schet&stDate='+stDate+'&enDate='+enDate;
}

function addNewRow(isCredit)
{
   openSwitchWin('fin/buh-schet-add&isCredit='+isCredit); 
}

function openForEdit(id)
{
  openWin('fin/buh-schet-cfg&id='+id,'editWin'); 
}

</script>
<?php
  $data= $model->getBuhSchetData();
  $Nc = count($data);
?>


<div class ='row'>
   <div class ='col-md-2'>   
       
   </div>
   
   <div class ='col-md-2'>   
    <input class="form-control" type='date' name='stDate' id='stDate' value='<?= $model->stDate ?>'>   
   </div> 
    <div class ='col-md-2'> 
    <input class="form-control" type='date' name='enDate' id='enDate' value='<?= $model->enDate ?>'>
    </div> 
    <div class ='col-md-1'>
       <a href='#' onclick='openPeriod();' ><span class='glyphicon glyphicon-ok'></span></a>
   </div>
   
   
   <div class ='col-md-2'>
       
   </div>
 
  <div class ='col-md-2' style='text-align:center'><?= $model->syncDateTime ?></div>
  <div class='col-md-1' style='text-align:right;'><a href='#' onClick='syncBuhSchet();'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>

<div class='spacer'></div>


<pre>
<?php 
//print_r ($model->debug);
//    print_r($data);

 ?>

</pre>

<table class='table table-stripped'>
<!--<thead>
<tr>
    <th>Наименование обьекта учета</th>
    <th colspan='2'>Счета учета</th>
    <th colspan='4'>Значения</th>    
</tr>
</thead>
-->
<thead>
<tr>
    <th>АКТИВЫ</th>
    <th>Счет</th>
    <th>Субсчет</th>
    <th>Субcубсчет</th>
    <th>СНДТ</th>
    <th>ОБДТ</th>
    <th>ОБКТ</th>
    <th>СКДТ</th>
</tr>
</thead>

<tbody>
<?php
 $SNDT = 0;
 $OBDT = 0;
 $OBKT = 0;
 $SKDT = 0;
 $lastId=0;
 for ($i=0; $i< $Nc; $i++)
 {
   if ($data[$i]['isCredit'] == 1) continue;
   echo "<tr>\n";
   if ($lastId != $data[$i]['reportId'])
   {
       $lastId = $data[$i]['reportId'];
       echo "<td><a href='#' onclick='openForEdit(".$data[$i]['reportId'].")'>".$data[$i]['reportTitle']."</a></td>";
   }else
   {
      echo "<td></td>";   
   }
       
   echo "<td>".$data[$i]['schet']."</td>";
   echo "<td>".$data[$i]['subSchet']."</td>";
   echo "<td>".$data[$i]['subSubSchet']."</td>";
   echo "<td>".number_format($data[$i]['SNDT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['OBDT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['OBKT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['SKDT'],0,'.','&nbsp;')."</td>";   
   echo "</tr>\n"; 
   
    $SNDT += $data[$i]['SNDT'];
    $OBDT += $data[$i]['OBDT'];
    $OBKT += $data[$i]['OBKT'];
    $SKDT += $data[$i]['SKDT'];

 }

?>

<tr>
    <td colspan=4>ИТОГО</td>

    <td><?= number_format($SNDT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($OBDT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($OBKT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($SKDT,0,'.','&nbsp;') ?></td>
</tr>
</tbody>
</table>

<div class ='row'>
  <div class ='col-md-10'></div>
  <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewRow(0);'><span class='glyphicon glyphicon-plus'></span></a></div>  
</div>



<table class='table table-stripped'>
<!--<thead>
<tr>
    <th>Наименование обьекта учета</th>
    <th colspan='2'>Счета учета</th>
    <th colspan='4'>Значения</th>    
</tr>
-->

<thead>
<tr>
    <th>Источники</th>
    <th>Счет</th>
    <th>Субсчет</th>
    <th>Субcубсчет</th>
    <th>СНКТ</th>
    <th>ОБДТ</th>
    <th>ОБКТ</th>
    <th>СККТ</th>
</tr>
</thead>
<tbody>


 
<?php
 $SNKT = 0;
 $OBDT = 0;
 $OBKT = 0;
 $SKKT = 0;
  $lastId=0;
 for ($i=0; $i< $Nc; $i++)
 {
   if ($data[$i]['isCredit'] == 0) continue;
   echo "<tr>\n";
   
   if ($lastId != $data[$i]['reportId'])
   {
       $lastId = $data[$i]['reportId'];
       echo "<td><a href='#' onclick='openForEdit(".$data[$i]['reportId'].")'>".$data[$i]['reportTitle']."</a></td>";
   }else
   {
      echo "<td></td>";   
   }

   echo "<td>".$data[$i]['schet']."</td>";
   echo "<td>".$data[$i]['subSchet']."</td>";
   echo "<td>".$data[$i]['subSubSchet']."</td>";
   echo "<td>".number_format($data[$i]['SNKT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['OBDT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['OBKT'],0,'.','&nbsp;')."</td>";
   echo "<td>".number_format($data[$i]['SKKT'],0,'.','&nbsp;')."</td>";   
   echo "</tr>\n"; 
   
    $SNKT += $data[$i]['SNKT'];
    $OBDT += $data[$i]['OBDT'];
    $OBKT += $data[$i]['OBKT'];
    $SKKT += $data[$i]['SKKT'];

 }

?>



<tr>
    <td colspan=4>ИТОГО</td>

    <td><?= number_format($SNKT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($OBDT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($OBKT,0,'.','&nbsp;') ?></td>
    <td><?= number_format($SKKT,0,'.','&nbsp;') ?></td>
</tr>
</tbody>
</table>

<div class ='row'>
  <div class ='col-md-10'></div>
  <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewRow(1);'><span class='glyphicon glyphicon-plus'></span></a></div>  
</div>
