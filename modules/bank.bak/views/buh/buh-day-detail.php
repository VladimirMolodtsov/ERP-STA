<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - рабочий день старшего бухгалтера';
$this->params['breadcrumbs'][] = $this->title;

$now=$bankmodel->showDate;

$prev=$bankmodel->showDate-24*3600;
$next=$bankmodel->showDate+24*3600;
 
$timeshift=4*3600;

$zero=strtotime (date("Y-m-d")." 00:00:00"); //на начало дня
$shift = ((time() - $zero)/3600)+4; // сколько прошло с начала дня
$shift = 2*$shift ;

echo "<p> Текущее время: ".date("d.m.Y H:i", time()+$timeshift)."</p>";

$bankmodel->loadTaskList();
$docmodel ->loadTaskList();
$platmodel->loadTaskList();
$shipmodel->loadTaskList();
$buhShedule = $buhmodel ->prepareExecute();

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

.gridcell{
 height:25px;

}

td {
   padding:0px; 
    
}

th {
   padding:5px; 
    
}

.current {
  border-collapse: collapse;   
  border-style: solid;
  border: 4px;
  border-color: Orange;
}

</style>


<script type="text/javascript">

function openExtractDetail()
{
  document.location.href='index.php?r=bank/operator/load-bank&showDate=<?= $bankmodel->showDate ?>' 
}

function openDocDetail()
{
  document.location.href='index.php?r=bank/operator/load-doc&showDate=<?= $bankmodel->showDate ?>' 
}


function openBuhDetail()
{
  document.location.href='index.php?r=bank/buh/buh-statistics&dtstart=<?= date("Y-m-d",$bankmodel->showDate) ?>' 
}


</script> 

<div class ='row'>
  <div class ='col-md-4'>   
  </div>
  
   <div class ='col-md-1'>   
       <a href="index.php?r=bank/buh/buh-day-detail&showDate=<?= $prev ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-2' style='text-align:center'><?= date("d.F.Y", $now) ?></div>
   <div class ='col-md-1' style='text-align:right'>
    <?php if ($next < time()) { ?>  
       <a href="index.php?r=bank/buh/buh-day-detail&showDate=<?= $next ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   <?php } ?>  
   </div>
  
  <div class ='col-md-4'>   
  </div>
</div>

<?php //class='table table-striped' ?>
<div align='center'>
<table style=' padding:0px; border-collapse: collapse;'  >

<thead>
<tr>
  <th width="95px"><b>Дедлайн</b></th>
  <th width="95px"><b>Выписка</b></th>
  <th width="95px"><b>Платежи</b></th> 
  <th width="95px"><b>Отгрузки</b></th>
  <th width="95px"><b>Документы</b></th>    
  <th width="15px" >&nbsp;</td>    
  <th width="95px"><b>Ст. бухг.</b></th>    
  
</tr>

</thead>
<tbody>
<?php 
function printStatus($i, $taskArray, $nameArray, $action)
{
  $bg="background-color:WhiteSmoke;";  
  if($i%2) $bg="";  
  
  if     ($taskArray[$i] == 1) echo "<td style='$bg'><div class='gridcell wait'    onclick='".$action."();'>".$nameArray[$i]."</div></td>\n";
  elseif ($taskArray[$i] == 2) echo "<td style='$bg'><div class='gridcell done'    onclick='".$action."();'>".$nameArray[$i]."</div></td>\n";
  elseif ($taskArray[$i] == 3) echo "<td style='$bg'><div class='gridcell warning' onclick='".$action."();'>".$nameArray[$i]."</div></td>\n";
  elseif ($taskArray[$i] == 4) echo "<td style='$bg'><div class='gridcell error'   onclick='".$action."();'>".$nameArray[$i]."</div></td>\n";
                        else   echo "<td style='$bg'>&nbsp;</td>\n";
}

function printExtStatus($ind, $shedulArray, $action, $timeshift)
{
  $bg="background-color:WhiteSmoke;";  
  if($ind%2) $bg="";    
  
  
  
  $execute = $shedulArray[$ind]['execute'];
  $nameList=$shedulArray[$ind]['nameList'];
  $detailList=$shedulArray[$ind]['detail'];
  
  if ($shedulArray[$ind]['nGrp']== 0 || $execute ['plan'] == 0) 
  { 
    echo "<td style='$bg'>&nbsp;</td>\n"; 
    return;
  } //нет запланированных событий
  
  $name = "";
  for ($i=0;$i<$shedulArray[$ind]['nGrp'];$i++)
  {
      $name .= $nameList[$i]."<br>";      
  }

  $nEx= count($detailList) ;
  $detail="";
  
  for ($i=0;$i<$nEx;$i++)
  {
      if ($detailList[$i]['timeReal'] == 0) $realExecute = " N/A";
                                       else $realExecute = $detailList[$i]['realExecute'];
      $detail .= $detailList[$i]['titleTask']." ".$realExecute." (".$detailList[$i]['startTime']."-".$detailList[$i]['deadTime'].")\n";
  }

  
  $class ="wait";
  
  
  if ($shedulArray[$ind]['stTime'] > time()+$timeshift) 
  {
      echo "<td style='$bg'><div class='gridcell $class' title='$detail' onclick='".$action."();'>".$name."</div></td>\n";
      return;
  } // Рано
   
       if ($execute ['plan'] > $execute ['exec']  ) $class ="error";
  else if ($execute ['plan'] > $execute ['good']  ) $class ="warning";
  else if ($execute ['plan'] <= $execute ['good'] ) $class ="done";
  

  echo "<td style='$bg'><div class='gridcell $class' title='$detail' onclick='".$action."();'>".$name."</div></td>\n";  
}

/***********************************************/

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
  echo "<td style='width:95px; $bg'> ".sprintf('%02d', $hp).":".sprintf('%02d', $mp)."-".sprintf('%02d', $h).":".sprintf('%02d', $m)."</td>\n"; 
  
  // Выписка (оператор банка)
  printStatus($i, $bankmodel->taskArray, $bankmodel->nameArray, "openExtractDetail");  
  // Платежки
  printStatus($i, $platmodel->taskArray, $platmodel->nameArray, "openPlatDetail"); 
  // Отгрузки
  printStatus($i, $shipmodel->taskArray, $shipmodel->nameArray, "openShipDetail"); 
  // Оцифровка первички
  printStatus($i, $docmodel->taskArray, $docmodel->nameArray, "openDocDetail"); 
   
  echo "<td></td>";
 
  printExtStatus($i, $buhShedule, "openBuhDetail", $buhmodel ->timeshift); 
 
 
  echo "</tr>\n";
 
}

?>    
    
    
    
</tbody>
</table>
</div>

<?php 
echo "<pre>";

//print_r ($docmodel->taskArray);
//print_r($buhShedule);
echo "</pre>";
?>

