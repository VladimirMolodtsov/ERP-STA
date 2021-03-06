<?php


use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\widgets\Pjax;

$this->title = 'Время события';


$now=strtotime($date);

$prev=$now-7*24*3600;
$next=$now+7*24*3600;

//$curTime = time()-strtotime(date("Y-m-d"))+$model::TIMESHIFT;
//$curTime = time()-strtotime(date("Y-m-d"))+$model::TIMESHIFT+3600;
$curUser=Yii::$app->user->identity;
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
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
   padding:2px; 
   border-color:Gray; 
   /*font-size: 11px;*/
}

th {
   padding:5px; 
   border-color:Gray; 
}

.freetime {
    width:  180px;  /* ширина нашего блока */
    height: 20px;
}
/*
.freetime:hover {    
    box-shadow: 0.4em 0.4em 5px #696969;
    cursor:pointer;
}*/


.free-task {
     /* height: 0px; высота нашего блока */
    width:  170px;  /* ширина нашего блока */
    border: 1px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    padding-right:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    border-radius: 5%;
    position:relative;
    margin-top:2px;
}
.free-task:hover {    
    box-shadow: 0.4em 0.4em 5px #696969;
}

.cell {   
  /*font-weight:bold; */
  width:  100%;  /* ширина нашего блока */
  white-space: nowrap;
  /*display: inline;  */
}

.cell:hover{    
    //box-shadow: 0.4em 0.4em 5px #696969;    
    text-decoration: underline;
    cursor:pointer;
}

.selected-task {
    background-color:DarkBlue;
    color: White;
}


.inner-task {
    position:relative;
    display:inline;
    width:  150px;  /* ширина нашего блока */
    padding:0px;    
}
.inner-task:hover {
    cursor:pointer;
}


.info-box {
    position:relative;
    display:inline;
    vertical-align: top;
    float: right;
    color: Crimson;
    padding:2px;
    border: 2px solid #C1C1C1; /* размер и цвет границы блока */
    border-radius: 15%;
    top: -10px;  
    background-color:LightGray;
 }

.close-box {
    position:relative;
    display:inline;
    vertical-align: top;
    float: right;
    color: Crimson;
 }
.close-box:hover {
    cursor:pointer;
}

</style>

<script type="text/javascript">

var selectedTask=0;

function rejectTask(id)
{
    openSwitchWin("tasks/main/reject-task&id="+id);  
}

function addFreeTask()
{    
  $('#newTask').modal('show');     
 //openWin("/tasks/main/market-task&noframe=1&refManager=<?=$userId ?>", "childwin");      
}

function readTaskChange()
{
  $('#newTask').modal('hide'); 
  openSwitchWin("site/success");  
}

var oldBg= "";
var oldColor= "";
function selectTask (id)
{
    
   if(selectedTask > 0){
   var prevDivId="taskbox_"+selectedTask; 
   document.getElementById(prevDivId).style.backgroundColor =oldBg; 
   document.getElementById(prevDivId).style.color =oldColor; 
   }
   selectedTask = id;
   var divId="taskbox_"+id; 
   oldBg=  document.getElementById(divId).style.backgroundColor ;
   oldColor=  document.getElementById(divId).style.color ;
   document.getElementById(divId).style="color:White; background-color:DarkBlue;"; 
  
}

function unSelectTask ()
{
   if(selectedTask > 0){
   var prevDivId="taskbox_"+selectedTask;    
   document.getElementById(prevDivId).style.backgroundColor =oldStyle; 
   }
}

function acceptTask(dt, tm)
{
   if(selectedTask == 0) {
   alert('выберите задачу');
   return;}
    var strSrc= 'index.php?r=/tasks/main/market-task-accept&noframe=1';
    strSrc= strSrc +"&id="+selectedTask;
    strSrc= strSrc +"&dt="+dt;
    strSrc= strSrc +"&tm="+tm;    
    document.getElementById('acceptTaskFrame').src=strSrc;
    
    $('#acceptTaskDialog').modal('show');    
}

function removeTask (id)
{
    openSwitchWin("tasks/main/remove-task&id="+id);  
}

function markTaskDone(id)
{
  $('#markTaskDialog').modal('show');     
  var strSrc= 'index.php?r=/tasks/main/mark-task-done&noframe=1';
    strSrc= strSrc +"&eventRef="+id;
    document.getElementById('markTaskFrame').src=strSrc;
  
}



function showOrgList()
{
 $(orgSelectList).css('display', 'block'); // убираем у модального окна display: none;
					//.animate({opacity: 1}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз   
 //document.getElementById('orgSelectList').css('display', 'block');
 //document.getElementById('action').value = 'selectOrg';
 //document.getElementById('taskEditForm').submit();
 }

function setOrg(id, title)
{
  //$('.collapse-toggle').text("Контрагент: "+title);
  document.getElementById('orgRef').value = id;
  document.getElementById('orgTitle').value = title;
   $(orgSelectList).css('display', 'none');
 }


function saveData()
{
  document.getElementById('taskEditForm').submit(); 
  //window.parent.readTaskChange();   
}

function changeShowDate()
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=/tasks/market/event-exec-week&id=<?= $userId ?>&noframe=1&date='+showDate ;
}
</script>

<h4><?= $model->getMangerFIO($userId ) ?></h4>
<div class ='row'>
  <div class ='col-md-3'>   
  <a href="index.php?r=tasks/market/event-exec-week-detail&id=<?= $userId ?>&date=<?= date("Y-m-d",$now) ?>" >Детально</a>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-3' style='text-align:center'>
<?php   
   echo DatePicker::widget([
    'name' => 'show_date',
    'id' => 'show_date',
    'value' => date("d.m.Y",$now),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
]);
//<b><?= date("d.m.Y",$now)  </b>
?>      
   </div>
   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=tasks/market/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-4'>   
  <?php if (($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x00100)) {
   echo "<a href='index.php?r=tasks/market/event-log' >Все менеджеры</span></a>";     
  }
  ?>
  </div>
   
</div>

<div class='spacer'></div>

<div class='row'>
<div class='col-md-12'>

<table width='1020' border='1px' style='border-color:Gray;'>
<?php
//class="table table-striped table-bordered"
/*
определим начало
*/
$weekday = date ("w", $now);
$weekstart = $now - ($weekday -1)*24*3600; 
$weekend = $weekstart+5*24*3600; 
$weekName=['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
echo "<tr>";
   echo "<th >Время</th>";    

for ($i=0;$i<5;$i++)
  {
    echo "<th style='width:200px;text-align:center;'><a href='index.php?r=tasks/market/event-exec-detail&id=".$userId."&date=".date("d.m.Y",$weekstart+$i*24*3600)."'>".date("d.m.Y",$weekstart+$i*24*3600)." ".$weekName[$i]."</a></th>";    
  }

echo "</tr>";



$step = 6;
$iRow=0; // отображаемая колонка

for ($i=0;$i<5;$i++)
  {
    $freeTimeList[$i] = $model->getFreeTimeList(date("d.m.Y",$weekstart+$i*24*3600), $userId);
  }


    $N = count($freeTimeList[0])-$step;
        

   for ($idxRow=0;$idxRow<$N;$idxRow+=$step)
    {       
   $strRow = "<tr>\n";          
     if ($iRow%2 == 0) $bg="WhiteSmoke;"; else  $bg="White;";
     
     $iRow++;                
     $strRow .="<td align='center' style='background-color:$bg'> ".$freeTimeList[0][$idxRow]['strTime']." </td>";
    
    // а теперь по каждой клетке отдельно
    for ($iCol=0;$iCol<5;$iCol++)
    {        
        //получим список для всех задач в заданном шаге
        
        $taskN =0;//число задач всего
        $taskNExec =0;//число задач выполнено
        $orgRef=0;//ссылка на первый из контактов
        $taskTitle="";//Название первого не выполненного
        $taskLastTitle="";//Название последнего
        $taskAllTitle="";//Название всех
        $taskPriority =0;
        
                    
        $curTime= strtotime(date("Y-m-d", $weekstart+$iCol*24*3600)." 00:00")+ $model::TIMESHIFT;// // на начало дня          
        
        for($i=$idxRow; $i< $idxRow+$step; $i++)
        { 
           $timeList=$freeTimeList[$iCol][$i];    
           if (empty($timeList['eventTime']) )  continue; 


           $taskN++;               
           $taskLastTitle = $timeList['taskTitle'];
          // $taskAllTitle .= $timeList['taskTitle']."\n";   
          
          $title = $timeList['taskTitle'];
          
           if ($timeList['eventStatus'] == 2) 
           {           
             $execTime=strtotime($timeList['executeDateTime']) + $model::TIMESHIFT;             
             $endTime= $curTime+$timeList['time']+2*$step*$model::INTERVAL;
             if ($execTime > $endTime) $bgs = 'Yellow';                    
                                                                  else $bgs = 'LawnGreen'; 
                                                                  
             $title.= " ".date("d.m H:i",$execTime ) ;                                                                     
/*
             $title.= " / ".date("d.m H:i",$weekstart ) ;                                                                     
             $title.= " / ".date("d.m H:i",$curTime ) ;                                                                     
             $title.= " / ".$iCol." ".$curTime  ;  
*/             
             $taskNExec++;
           } 
           else 
           {
             $bgs = $bg;    
           }
           
           
             $orgRef = $timeList['orgRef']; 
             $taskAllTitle .= "<div class='cell' style='background-color:$bgs' title='".$title."' ";
             if(!empty ($orgRef)) $taskAllTitle .= " onclick='openWin(\"site/contacts-detail&id=".$orgRef."\", \"childwin\")' ";
             $taskAllTitle .= " >".mb_substr($timeList['taskTitle'],0, 20, 'utf-8')."</div> ";                       
        }
            
        //Нет задач    
        $color= 'Black';
        $bgs = $bg;
        if($taskN ==0) $strRow .=  "<td style='background-color:$bg'><div class='freetime' > </div></td>";      
        else
        {
          if ($taskN == $taskNExec) $bgs = 'LawnGreen'; 
          if ($taskPriority == 1)   $bgs = 'DarkTurquoise'; 
          if ($taskPriority == 2)   $bgs = 'Coral'; 
          $strRow .= "<td style='background-color:$bgs'>";
          $strRow .=$taskAllTitle;
//          $strRow .= "<div class='cell'  title='".$taskAllTitle."' ";
//          if(!empty ($orgRef)) $strRow .= " onclick='openWin(\"site/contacts-detail&id=".$orgRef."\", \"childwin\")' ";
//          $strRow .= " >".$taskTitle."</div> ";
        //  $strRow .= "<div class='info-box'>".$taskNExec."/".$taskN."</div>";
          $strRow .= "</td>";
        }          
    }       
       $strRow .= "</tr>\n"; 
       echo $strRow ;
  }      
?>

</table>
</div>

</div>


<?php
/*echo "<pre>";
print_r($freeTimeList);
echo "</pre>";*/
?>
