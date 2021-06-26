<?php


use yii\helpers\Html;

$this->title = 'Время события';


$now=strtotime($date);

$prev=$now-24*3600;
$next=$now+24*3600;

$curTime = time()-strtotime(date("Y-m-d"))+$model->shiftTime;
//echo date("H:i",$curTime);
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<style>

.event {    
    margin:0px; 
    text-align:center;
    padding:2px;     
    width:95px;
    height:20px;    
}

.none {    
    background-color:WhiteSmoke;
}

.wait {    
    background-color:Gray;        
}
.done {    
    background-color:Green;    
    color:White;
}
.warning {    
    background-color:Orange;    
    color:White;
}
.error {    
    background-color:Crimson;    
    color:White;
}

td {
   padding:2px; 
   border-color:Gray; 
}

th {
   padding:5px; 
   border-color:Gray; 
}

.time_cont{
	cursor: pointer;
    text-align:center;
    padding:5px; 
}

</style>

<div class ='row'>
  <div class ='col-md-2'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/event-exec-short&noframe=1&id=<?= $userId ?>&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   
   <b><?= date("d.m.Y",$now)  ?></b>
       <a href="index.php?r=tasks/market/event-exec-short&noframe=1&id=<?= $userId ?>&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
      <a href="#" onclick='openWin("tasks/market/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$now) ?>", "childwin");'><span class='glyphicon glyphicon-th'></span></a>   
  </div>
  
  <div class ='col-md-4'>   
  </div>
   
</div>

<div align="center" class='time_cont' onclick='openWin("tasks/market/event-exec-detail&date=<?= $date ?>&id=<?= $userId ?>", "childwin")' >
<table width='100%' border='1px' style='border-color:Gray;'>

<?php
//class="table table-striped table-bordered"
$idx=0;
$freeTimeList = $model->getFreeTimeList($date, $userId);

$range=6;
$N = count($freeTimeList)-$range;
   for ($iRow=0;$iRow<$N;$iRow+=$range)
    {
   echo "<tr>\n";    

    $eRow = $iRow+$range; 
    $bg="";
    if ($curTime > $freeTimeList[$iRow]['time'] && $curTime <= $freeTimeList[$eRow]['time'])
    {
    $bg="style='background-color:Orange;'";    
    }
    echo "<td align='center' $bg> ".$freeTimeList[$iRow]['strTime']." - ".$freeTimeList[$eRow]['strTime']." <br>".        
    "</td>";
    
      
      $curStatus = 0; 
      /*0 - нет событий 
        1 - ждем исполнения
        2 - в ходе исполнения
        3 - просрочено 
      */      
       
                                                $curTimeStatus=0; // Дефолтное не наступило
      if     ($now > time())                    $curTimeStatus=0; // Не наступило - более чем на сутки
      elseif ($next < time())                   $curTimeStatus=2; // просрочено - более чем на сутки в прошлом 
      elseif ($curTime >  $freeTimeList[$eRow]['time']) $curTimeStatus=2; // просрочено сегодня
      elseif ($curTime >  $freeTimeList[$iRow]['time']) $curTimeStatus=1; // в ходе исполнения
      
       /*считаем число назначенных и число выполненных*/
       $eventCount=0;
       $eventFinished=0;
       $strDuty ="";
       $strAll ="";
       for ($i=0; $i< $range; $i++) 
       {
        if($freeTimeList[$iRow+$i]['eventRef'] == 0) continue;        
          $eventCount++;
          if($freeTimeList[$iRow+$i]['eventStatus'] == 2 )$eventFinished++;
          else $strDuty .=$freeTimeList[$iRow+$i]['orgTitle']."\n";        
          $strAll .=$freeTimeList[$iRow+$i]['orgTitle']."\n";                         
       }
   
       /* Определим цвет */  
       $bgclass="none";
       $value  ="0";
       $strOut ="";
   
       if     ($eventCount == 0   ){$bgclass="none"; $value  ="&nbsp;"; $strOut="";} //нет событий
       elseif ($curTimeStatus ==0 ){$bgclass="wait";    $value  =$eventCount; $strOut="title='".$strAll."'";} //ждем
       elseif ($curTimeStatus ==1 && $eventCount >  $eventFinished ){$bgclass="warning"; $value  =$eventCount; $strOut="title='".$strDuty."'";} //в процессе
       elseif ($curTimeStatus ==1 && $eventCount == $eventFinished ){$bgclass="done"; $value  =$eventCount; $strOut="title='".$strAll."'";} //все сделано
       elseif ($curTimeStatus ==2 && $eventCount == $eventFinished ){$bgclass="done"; $value  =$eventCount; $strOut="title='".$strAll."'";} //все сделано
       elseif ($curTimeStatus ==2 && $eventCount >  $eventFinished ){$bgclass="error"; $value  =$eventCount-$eventFinished; $strOut="title='".$strDuty."'";} //просрочено
    
       echo "<td width='95px' style='padding:0px;'><div class='gridcell event $bgclass' $strOut >$value </div></td>";       
    //$curTimeStatus $curTime ".$timeList[0]['time']."



    echo "</tr>\n";
    }

        
?>

</table>
</div>

<?php
/*echo "<pre>";
echo $date ;
echo "\n";
echo $userId;
print_r($freeTimeList);
echo "</pre>";*/
?>
