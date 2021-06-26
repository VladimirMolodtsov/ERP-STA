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

$curTime = time()-strtotime(date("Y-m-d"))+$model->shiftTime;
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

.freetime:hover {    
    box-shadow: 0.4em 0.4em 5px #696969;
    cursor:pointer;
}


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
  font-weight:bold; 
  width:  180px;  /* ширина нашего блока */
  //display: inline;  
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
 return;
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


</script>

<h4><?= $model->getMangerFIO($userId ) ?></h4>
<div class ='row'>
  <div class ='col-md-4'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/buh/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-2' style='text-align:center'><b><?= date("d.m.Y",$now)  ?></b></div>
   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=tasks/buh/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-4'>   
  <?php if (($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x00100)) {
   echo "<a href='index.php?r=tasks/buh/event-log' >Все менеджеры</span></a>";     
  }
  ?>
  </div>
   
</div>

<div class='row'>

<div class='col-md-1'>

<div style='background-color:WhiteSmoke; width:177px; padding:2px; position:fixed;  overflow: auto; height:800px;'>
<div style='background-color:White; width:175px; padding:5px;'>
Пул Задач:
</div>

<div style='background-color:White; width:175px; padding:5px; margin-bottom:10px;'>
<?php if (($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x00100)) {
echo "От руководства&nbsp;&nbsp;<a href='#' class='btn' style='float:right; width:50px; color: White; padding:2px; background-color:DarkGreen;' onclick='addFreeTask();' > + </a>";
} 
else echo "От руководства:"
?>
</div>

<?php 
    $freeTasks= $model->getFreeTasks($userId); 
    
    $N = count($freeTasks);
    for ($i=0; $i<$N; $i++)
    {  
    if ( $userId == $freeTasks[$i]['creatorRef'] ) continue; //не свои
    $clickInnerAction = "";
    $clickAction = "";
    $add = "";
    if ( $curUser->id == $userId ) { $clickAction= "onclick='selectTask(".$freeTasks[$i]['id'].")'";}
   
    if (($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x00100)) {
       $add="<div class='close-box' onclick='removeTask(".$freeTasks[$i]['id'].")'><span class='glyphicon glyphicon-remove'></span></div>";        
       //if ( $curUser->id == $userId ) 
       {
           $clickAction ="";
           $clickInnerAction = "onclick='selectTask(".$freeTasks[$i]['id'].")'";}
    }
    
    $bg ="";
    if ($freeTasks[$i]['taskPriority'] == 1) $bg = "background-color:DarkTurquoise;";
    if ($freeTasks[$i]['taskPriority'] == 2) $bg = "background-color:Coral;";
    $ret= "<div id='taskbox_".$freeTasks[$i]['id']."' class='free-task' style='$bg' $clickAction title='".$freeTasks[$i]['note']."'><div $clickInnerAction class='inner-task'>".$freeTasks[$i]['orgTitle']." ".$freeTasks[$i]['planDate']."</div>";
    $ret.=$add;
    $ret.="</div>\n";

      echo $ret;
    }
?>

<div style='background-color:White; width:175px; padding:5px; margin-top:10px; '>
Личные:
</div>

<?php 
    for ($i=0; $i<$N; $i++)
    {  
    if ( $userId != $freeTasks[$i]['creatorRef'] ) continue; //не свои

    $clickInnerAction = "";
    $clickAction = "";
    $add = "";
    //if ( $curUser->id == $userId ) { $clickAction= "onclick='selectTask(".$freeTasks[$i]['id'].")'";}
    $add="<div class='close-box' onclick='removeTask(".$freeTasks[$i]['id'].")'><span class='glyphicon glyphicon-remove'></span></div>";        
    $clickAction ="";
    $clickInnerAction = "onclick='selectTask(".$freeTasks[$i]['id'].")'";
    $ret= "<div id='taskbox_".$freeTasks[$i]['id']."' class='free-task' $clickAction title='".$freeTasks[$i]['note']."'><div $clickInnerAction class='inner-task'>".$freeTasks[$i]['orgTitle']." ".$freeTasks[$i]['planDate']."</div>";
    $ret.=$add;
    $ret.="</div>\n";
    echo $ret;
    }
?>



  
</div>
</div>
<div class='col-md-1'></div>
<div class='col-md-10'>
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
    echo "<th style='width:200px;text-align:center;'><a href='index.php?r=tasks/buh/event-exec-detail&id=".$userId."&date=".date("d.m.Y",$weekstart+$i*24*3600)."'>".date("d.m.Y",$weekstart+$i*24*3600)." ".$weekName[$i]."</a></th>";    
  }

echo "</tr>";


$idx=0;
$N=0;

for ($i=0;$i<5;$i++)
  {
    $freeTimeList[$i] = $model->getFreeTimeList(date("d.m.Y",$weekstart+$i*24*3600), $userId);
    $N = max($N, count($freeTimeList[$i]));
  }

for ($iRow=0;$iRow<64;$iRow++)
    {       
   echo "<tr>\n";    
      
   if ($iRow%2 == 0) $bg="WhiteSmoke;";
               else  $bg="White;";
               
    /*if ($iRow < ($N-2) && $now < time() && $next > time() )
    {    
    if ($curTime > $freeTimeList[$iRow]['time'] && $curTime < $freeTimeList[$iRow+1]['time']  )
    {
      $bg="Orange;";   
    }
    }*/
    
    echo "<td align='center' style='background-color:$bg'> ".$freeTimeList[0][$iRow]['strTime']." </td>";
    
    for ($iCol=0;$iCol<5;$iCol++)
    {        
    $timeList=$freeTimeList[$iCol][$iRow];    
    $add ="";
    if (!empty ($timeList['taskTitle'])) 
    {        
      if ($timeList['eventStatus'] == 2) { $bgs ='LawnGreen'; 
      //$add = "<span  style=' position:relative;align:right;font-size:10px;'>".date("d.m H:i",strtotime($timeList['executeDateTime']))."</span>";
      }
      else {
         $bgs=$bg; 
         if ($timeList['taskPriority'] == 1) $bgs = "DarkTurquoise;";
         if ($timeList['taskPriority'] == 2) $bgs = "Coral;";
      //<span class='glyphicon glyphicon-ok' style='color:Green;' onclick='markTaskDone(".$timeList['eventRef'].")'></span>
      //$add="<div class='close-box'><span class='glyphicon glyphicon-remove' onclick='rejectTask(".$timeList['eventRef'].")'></span></div>";        
      }
      echo "<td style='background-color:$bgs'><div class='cell'
      title='".$timeList['eventNote']."'
      onclick='openWin(\"site/contacts-detail&id=".$timeList['orgRef']."\", \"childwin\")' >".$timeList['taskTitle']."</div> ".$add."</td>";       
      
    }
    else echo "<td style='background-color:$bg'><div class='freetime' onclick='acceptTask(\"".date("d.m.Y",$weekstart+$iCol*24*3600)."\",\"".$timeList['strTime']."\");'> </div></td>";
    }
   
       
       echo "</tr>\n"; 
  }      
?>

</table>
</div>

</div>


<?php
Modal::begin([
    'id' =>'newTask',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Создать задачу </h4>',
]);?><div style='width:550px'>

<div id='orgSelectList' style='display: none;'> 
<?php    
    Pjax::begin();
    $userArray = $orgModel->getManagerList();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $orgModel,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed small'],
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    $ret= "<a href='#' onclick='setOrg(".$model['id'].",\"".$model['orgTitle']."\" );' >".$model['orgTitle']."</a>";
                    return $ret;
                },
            ],		
				
            [
	
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
				'filter'    => $userArray,
                
            ],
			
        ],
    ]
);
/*echo Collapse::widget([
    'items' => [
        [            
            'label' => "Контрагент ...",
            'labelOptions' => ['id' => 'orgTitleCollapseLabel'],
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => ['id' => 'orgTitleCollapse']
        ]
    ]
]); 
 */
/*
 
*/
Pjax::end(); 
?>
</div>   
   
  <?php $form = ActiveForm::begin(['id' => 'taskEditForm']); ?>
  <?= $form->field($modelForm, 'action')->hiddenInput(['id' => 'action'])->label(false)?>
  <?= $form->field($modelForm, 'executorRef')->hiddenInput(['id' => 'executorRef'])->label(false)?>
  <?= $form->field($modelForm, 'orgRef')->hiddenInput(['id'=>'orgRef'])->label(false)?></td>        

  <table border='0' width='530px'>
  <tr>   
    <td class='flbl'>Приоритет</td>    
    <td colspan=2>
    <?= $form->field($modelForm, 'taskPriority')->dropDownList($modelForm->getPriorityList())->label(false)?></td>        
    </tr>
    <tr>   
    <td class='flbl'>Контрагент</td>
    <td colspan=2><nobr><input id='orgTitle'  name='orgTitle' readonly='true'  style="height: 35px; width: 350px;" value='<?= $modelForm->orgTitle ?>'><input class="btn btn-primary btn-local"  style="width: 35px; height:35px; margin-top:-5px" type="button" value="..." onclick="javascript:showOrgList();"/></nobr>        
    <br>
    </td>
  </tr>
    
  <tr>    
    <td class='flbl'>Начало исполнения</td>
    <td><?= $form->field($modelForm, 'startDate')->textInput(['id' => 'startDate', 'type' => 'date'])
        ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
    ?></td>  
    <td><?= $form->field($modelForm, 'startTime')->textInput(['id' => 'startTime', 'type' => 'time'])
     ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 30,
        'showSeconds' => false,
        'showMeridian' => false
    ]
    ])->label(false)
    ?></td>  
  </tr>
  <tr>
    <td class='flbl'>Плановое окончание</td>
    <td><?= $form->field($modelForm, 'planDate')->textInput(['id' => 'planDate', 'type' => 'date'])        
    ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
?></td>    
    <td><?= $form->field($modelForm, 'planTime')->textInput(['id' => 'planTime', 'type' => 'time'])
         ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 10,
        'showSeconds' => false,
        'showMeridian' => false
    ]
    ])->label(false)
?></td>    
  </tr>

  <tr>
    <td class='flbl'>Дедлайн</td>
    <td><?= $form->field($modelForm, 'deadDate')->textInput(['id' => 'deadDate', 'type' => 'date'])
            ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
?></td>    
    <td><?= $form->field($modelForm, 'deadTime')->textInput(['id' => 'deadTime', 'type' => 'time'])
         ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 30,
        'showSeconds' => false,
        'showMeridian' => false
    ]
    ])->label(false)
?></td>    
  </tr>

  <tr>    
    <td colspan=3>
    <?= $form->field($modelForm, 'note')->textarea(['id' => 'note','rows' => 5, 'cols' => 20])->label('Комментарий')?></td> 
    
  </tr>
  
  <tr>
    <td colspan='3' align='right'> 
     <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', ])  ?>
    <?php // <a href="" onclick='saveData();' class='btn btn-primary'>Сохранить</a>     ?>
    </td>  
  </tr>
  
  </table>
  
  
  <?php ActiveForm::end(); ?>





</div>
<?php Modal::end();?>







<?php
Modal::begin([
    'id' =>'acceptTaskDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='acceptTaskFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/tasks/main/market-task&noframe=1&refManager=<?=$userId ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'markTaskDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='markTaskFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/tasks/main/mark-task-done&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>





<?php
/*echo "<pre>";
print_r($freeTimeList);
echo "</pre>";*/
?>
