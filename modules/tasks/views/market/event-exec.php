<?php


use yii\helpers\Html;

$this->title = 'Время события';


$now=strtotime($date);

$prev=$now-24*3600;
$next=$now+24*3600;

$curTime = time()-strtotime(date("Y-m-d"))+$model::TIMESHIFT ;

//if ($next < time()) echo "dxthf";
//echo date("H:i", $curTime);
//echo "<br>";
//echo date("H:i");
?>

<style>


.event {    
    margin:0px; 
    text-align:center;
    padding:2px;     
    width:120px;
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
   padding:0px; 
    
}

th {
   padding:5px; 
    
}


</style>
<script>
function setTime(eventTime)
{
  window.parent.setSelectEventTime(eventTime);

}
</script>


<div class ='row'>
  <div class ='col-md-4'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/event-log&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-2' style='text-align:center'><?= date("d.m.Y",$now)  ?></div>
   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=tasks/market/event-log&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-4'>   
  </div>
   
</div>
<div align="center" >
<table border='1px' style="padding:0px;">
<?php
//class="table table-striped table-bordered"
$idx=0;
$freeTimeList = $model->getAllTimeList($date);
/*echo "<pre>";
print_r($freeTimeList);
echo "</pre>";*/

if ($model->managerCount ==0) return;

    echo "<tr>\n";   
    echo "<th  style='width:120px'>Время</th>";    
    for ($iCol=0;$iCol<$model->managerCount;$iCol++)
    {        
        echo "<th style='width:120px'><a href='index.php?r=tasks/market/event-exec-week&date=".date("d.m.Y",$now)."&id=".$freeTimeList[$iCol]['userData']['id']."'>".$freeTimeList[$iCol]['userData']['userFIO']."</a></th>";
    }
    //echo "<th></th>";    
    echo "</tr>\n";       

$step = 6;
$N = count($freeTimeList[0]['timeList'])-$step;
   for ($iRow=0;$iRow<$N;$iRow+=$step)
    {
    echo "<tr>\n";    
    
    $bg="";
    if ($curTime > $freeTimeList[0]['timeList'][$iRow]['time'] && $curTime <= $freeTimeList[0]['timeList'][$iRow+$step]['time'])
    {
     $bg="style='background-color:Orange;'";    
    }
    echo "<td align='center' $bg> ".$freeTimeList[0]['timeList'][$iRow]['strTime']." - ".$freeTimeList[0]['timeList'][$iRow+$step]['strTime']." <br>".    
    //date("H:i",$freeTimeList[0]['timeList'][$iRow]['time'])." ".date("H:i",$freeTimeList[0]['timeList'][$iRow+3]['time'])."<br>".
    "</td>";
    
    for ($iCol=0;$iCol<$model->managerCount;$iCol++)
    {
      for ($j=0; $j<$step; $j++) $timeList[$j]=$freeTimeList[$iCol]['timeList'][$iRow+$j];
      
      $curStatus = 0; 
      /*0 - нет событий 
        1 - ждем исполнения
        2 - в ходе исполнения
        3 - просрочено 
      */      
       
                                                $curTimeStatus=0; // Дефолтное не наступило
      if     ($now > time())                    $curTimeStatus=0; // Не наступило - более чем на сутки
      elseif ($next < time())                   $curTimeStatus=2; // просрочено - более чем на сутки в прошлом 
      elseif ($curTime >  $timeList[$step-1]['time']) $curTimeStatus=2; // просрочено сегодня
      elseif ($curTime >  $timeList[0]['time']) $curTimeStatus=1; // в ходе исполнения
      
      
 
       /*считаем число назначенных и число выполненных*/
       $eventCount=0;
       $eventFinished=0;
       $strDuty ="";
       $strAll ="";
       for ($i=0; $i< $step; $i++) 
       {
        if($timeList[$i]['eventRef'] == 0) continue;        
          $eventCount++;
          if($timeList[$i]['eventStatus'] == 2 )$eventFinished++;
          else $strDuty .=$timeList[$i]['taskTitle']."\n";        
          $strAll .=$timeList[$i]['taskTitle']."\n";                         
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
    
       echo "<td style='padding:0px;'><div class='gridcell event $bgclass' $strOut >$value </div></td>";       
    //$curTimeStatus $curTime ".$timeList[0]['time']."
    }   
            
    //echo "<td></td>";    
    echo "</tr>\n";
    }

    
    
?>

</table>
</div>