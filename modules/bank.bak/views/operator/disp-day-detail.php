<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - рабочий день оператора';
$this->params['breadcrumbs'][] = $this->title;

$now=$bankmodel->showDate;

$prev=$bankmodel->showDate-24*3600;
$next=$bankmodel->showDate+24*3600;
 


$zero=strtotime (date("Y-m-d")." 00:00:00"); //на начало дня
$shift = ((time() - $zero)/3600)+4; // сколько прошло с начала дня
$shift = 2*$shift ;

//echo "<p>".date("Y-m-d h:i:s")." $shift</p>";

$bankmodel->loadTaskList();
$docmodel ->loadTaskList();
$platmodel->loadTaskList();
$shipmodel->loadTaskList();
$buhmodel ->loadTaskList();

//echo $shift/2;

?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.wait {    
    background-color:Gray;    
    margin:0px; 
    text-align:center;
    width:95px;    
}
.done {    
    background-color:Green;    
    color:White;
    text-align:center;        
    width:95px;
}
.warning {    
    background-color:Orange;    
    color:White;
    text-align:center;        
    width:95px;
}
.error {    
    background-color:Crimson;    
    color:White;
    text-align:center;    
    width:95px;
}
.duty {
   padding: 10px;
   border-collapse: collapse;   
   width:95px;
}

</style>


<script type="text/javascript">

function openExtractDetail()
{
  document.location.href='index.php?r=bank/operator/disp-log&showDate=<?= $bankmodel->showDate ?>' 
}

function openDocDetail()
{
  document.location.href='index.php?r=bank/operator/load-doc&showDate=<?= $bankmodel->showDate ?>' 
}

</script> 

<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=bank/operator/disp-day-detail&showDate=<?= $prev ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-10' style='text-align:center'><?= date("d.F.Y", $now) ?></div>
   <div class ='col-md-1' style='text-align:right'>
    <?php if ($next < time()) { ?>  
       <a href="index.php?r=bank/operator/disp-day-detail&showDate=<?= $next ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   <?php } ?>  
   </div>
</div>

<?php //class='table table-striped' ?>
<table style='width:900px; border-collapse: collapse;' border=0>
<thead>
<tr>
  <td width="95px"><b>Дедлайн</b></td>
  <td width="95px"><b>Выписка</b></td>
  <td width="95px"><b>Платежи</b></td> 
  <td width="95px"><b>Отгрузки</b></td>
  <td width="95px"><b>Документы</b></td>    
  <td width="15px">&nbsp;</td>    
  <td width="95px"><b>Ст. бухг.</b></td>    
  <td></td>
</tr>

</thead>
<tbody>
<?php 
function printStatus($i, $taskArray, $nameArray, $action)
{
  $bg="background-color:WhiteSmoke;";  
  if($i%2) $bg="";  
  
  if     ($taskArray[$i] == 1) echo "<td style='$bg'><div class='gridcell wait'    onclick='".$action."();'>".$nameArray[$i]."</div></td>";
  elseif ($taskArray[$i] == 2) echo "<td style='$bg'><div class='gridcell done'    onclick='".$action."();'>".$nameArray[$i]."</div></td>";
  elseif ($taskArray[$i] == 3) echo "<td style='$bg'><div class='gridcell warning' onclick='".$action."();'>".$nameArray[$i]."</div></td>";
  elseif ($taskArray[$i] == 4) echo "<td style='$bg'><div class='gridcell error'   onclick='".$action."();'>".$nameArray[$i]."</div></td>";
                        else   echo "<td style='$bg'>&nbsp;</td>\n";
}
for ($i=9*2-1; $i<18*2; $i++  )
{
 $bg="background-color:WhiteSmoke;";
 if($i%2) $bg="";    
 $h = intval(($i+1)/2);
 $m= 60*(($i+1)/2-$h);

 $hp = intval(($i)/2);
 $mp= 60*(($i)/2-$hp);

 
  echo "<tr>\n";
  if (($i)< $shift && ($i+1) > $shift) $bg="background-color:NavajoWhite;";
  echo "<td style='width:95px; $bg'> ".sprintf('%02d', $hp).":".sprintf('%02d', $mp)."-".sprintf('%02d', $h).":".sprintf('%02d', $m)."</td>"; 
  
  // Выписка (оператор банка)
  printStatus($i, $bankmodel->taskArray, $bankmodel->nameArray, "openExtractDetail");  
  // Платежки
  printStatus($i, $platmodel->taskArray, $platmodel->nameArray, "openPlatDetail"); 
  // Отгрузки
  printStatus($i, $shipmodel->taskArray, $shipmodel->nameArray, "openShipDetail"); 
  // Оцифровка первички
  printStatus($i, $docmodel->taskArray, $docmodel->nameArray, "openDocDetail"); 
   
  echo "<td></td>";
 
  printStatus($i, $buhmodel->taskArray, $buhmodel->nameArray, "openBuhDetail"); 
 
 
  echo "</tr>\n";
 
}

?>    
    
    
    
</tbody>
</table>


<?php 
//echo "<pre>";
//print_r ($docmodel->taskArray);
//print_r($bankmodel->taskArray);
//echo "</pre>";
?>

