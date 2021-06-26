<?php


use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\widgets\Pjax;


$this->title = 'Время события';


$now=strtotime($date);

$prev=$now-24*3600;
$next=$now+24*3600;

$curTime = time()-strtotime(date("Y-m-d"))+$model::TIMESHIFT;
$curUser=Yii::$app->user->identity;
//echo date("H:i",$curTime);
$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/tasks/event-exec-detail.js');
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<style>

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

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


.circle {
    width: 20px; /* задаете свои размеры */
    height: 20px;
    overflow: hidden;
    display: inline;
    background: White;
    padding: 2px; /* создание отступов */
    text-align: center;
    border-radius: 50%;
    /* не забываем о кроссбраузерности */
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%
    border: #FFF 1px solid;
    /* тень */
    box-shadow: 0px 1px 1px 1px #bbb; 
    -moz-box-shadow: 0px 1px 1px 1px #bbb;
    -webkit-box-shadow: 0px 1px 1px 1px #bbb;
    /**/
    float:left;
    margin-left:4px;
    margin-top: 0px;
    color:white;
}
.circle:hover{
    background:#0000CD;
}

.hidden {
 display:none;   
}
.clickable{  
  color:blue;
  display:inline;
}


.clickable:hover{  
  text-decoration:underline;
  cursor:pointer;   
}

.info_cell {
    background: WhiteSmoke;
    font-size:15px;
}

.empty_cell {
    height:20px;
}

</style>


<script type="text/javascript">
function regContact(orgRef)
{
    openWin("site/reg-contact&id=" +orgRef, "orgwin");
}


function rejectTask(id)
{
    openSwitchWin("tasks/main/reject-task&id="+id);  
}

function showSchet (id, eventRef)
{

openWin('market/market-schet&src=task&noframe=1&id='+id,'sdelkawin');
//    $('#schetDialog').modal('show');   
//reloadSchet(id);
}

function showZakaz (orgRef, id)
{
 openWin('market/market-zakaz&src=task&noframe=1&orgId='+orgRef+'&zakazId='+id,'sdelkawin');
}

function reloadSchet(id)
{
console.log(id);
getSchetData(id); 
}

function showActiveSdelka(id)
{
  document.getElementById('sdelkaFrame').src = 'index.php?r=site/active-sdelka&noframe=1&id='+id;
  $('#sdelkaDialog').modal('show');     
}
</script>



<h4><?= $model->getMangerFIO($userId ) ?></h4>
<div class ='row'>
  <div class ='col-md-4'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/event-exec-detail&id=<?= $userId ?>&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-2' style='text-align:center'><b><?= date("d.m.Y",$now)  ?></b></div>
   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=tasks/market/event-exec-detail&id=<?= $userId ?>&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-1'>   
  </div>
  
  <div class ='col-md-1'>   
    <a href="index.php?r=tasks/market/event-exec-week&id=<?= $userId ?>&date=<?= date("Y-m-d",$now) ?>" ><span class='glyphicon glyphicon-th'></span></a>   
  </div>
  <div class ='col-md-1'>   
    <a href="index.php?r=tasks/market/event-exec-detail-print&noframe=1&id=<?= $userId ?>&date=<?= date("Y-m-d",$now) ?>" ><span class='glyphicon glyphicon-print'></span></a>   
  </div> 
</div>



<div class='row'>

<div class='col-md-2'>

<div style='background-color:WhiteSmoke; width:195px; padding:2px; position:fixed;  overflow: auto; height:90%;'>
<div style='background-color:White; width:185px; padding:5px;'>
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
    $ret= "<div id='taskbox_".$freeTasks[$i]['id']."' class='free-task' style='$bg' $clickAction title='".$freeTasks[$i]['note']."'><div $clickInnerAction class='inner-task'>".$freeTasks[$i]['taskTitle']." ".$freeTasks[$i]['planDate']."</div>";
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
    $ret= "<div id='taskbox_".$freeTasks[$i]['id']."' class='free-task' $clickAction title='".$freeTasks[$i]['note']."'><div $clickInnerAction class='inner-task'>".$freeTasks[$i]['taskTitle']." ".$freeTasks[$i]['planDate']."</div>";
    $ret.=$add;
    $ret.="</div>\n";
    echo $ret;
    }
?>



  
</div>
</div>
<div class='col-md-10'>
<table width='100%' border='1px' style='border-color:Gray;'>


  <tr>
    <th colspan=6 align='center' >Описание задачи</th>    
    <th colspan=8 class='info_cell' align='center'>Статус сделки</th>    
    <th colspan=2 align='center'> Результат выполнения</th> 
  </tr>       



  <tr>
    <th style='width:75px'>Время</th>    
    <th style='width:250px'> Задача</th>    
    
 <th style='width:250px'> Сделка</th>
    
    <th style='width:200px'> Цель </th>                  
    <th style='width:50px'> <span class='glyphicon glyphicon-phone-alt' title='Звонок'></span> </th>
    <th style='width:50px'> Док. </th>    


<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-question-sign' title='Спрос'></span> </th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-file' title='Заявка'></span> </th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-list-alt' title='Счет'></span> </th>                
<th style='width:50px' class='info_cell'><span class='' title='Предоплата/гарантия'>п/о</span> </th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-shopping-cart' title='Отгрузка товара'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-usd' title='Поступление денег ($+)'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-folder-open' title='Документы'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-ok-sign' title='Стоп (Сделка завершена)'></span></th> 


    <th style='width:50px'><span style='color:Green;' class='glyphicon glyphicon-ok' title='Выполнено'></span></th> 
    <th> Комментарий </th>
  </tr>       

<?php
//class="table table-striped table-bordered"
$idx=0;
$freeTimeList = $model->getFreeTimeList($date, $userId);

$N = $model ->pN; //count($freeTimeList);
$hN=0;
/*echo "<pre>";
print_r($freeTimeList);
echo "</pre>";
return;*/
$emptyShow =0; //показана ли пустая строка
   for ($iRow=0;$iRow<$N;$iRow++)
    {   
       
       //полосатим
       //if ($hN%2 == 0) $bg="WhiteSmoke;"; else 
        $bg="White;";
        //Подсветим текущее время               
        if ($iRow < ($N-2) && $now < time() && $next > time() )           
            if ($curTime > $freeTimeList[$iRow]['time'] && $curTime < $freeTimeList[$iRow+1]['time']  ) $bg="Orange;";       
        
        if ($freeTimeList[$iRow]['time']%3600==0) {$hN++; $emptyShow =0;} //Начало каждого часа обнулим
        
        /* Формируем строку вывода*/                
        $strOutPut = "<td align='center' style='background-color:$bg'> ".$freeTimeList[$iRow]['strTime']." </td>"; // время

        $timeList=$freeTimeList[$iRow];            
        if (!empty ($timeList['eventTime'])) // есть назначенная задача
        {                
           /*Получим данные по сделке*/
           if (!empty($timeList['ref_zakaz'])){               
            $sdelkaData = Yii::$app->db->createCommand( 'SELECT formDate, isFormed, schetNum, schetDate, docStatus, 
            cashState, supplyState, isSchetActive, {{%schet}}.id as refSchet
            from {{%zakaz}} left join {{%schet}}  on {{%schet}}.refZakaz = {{%zakaz}}.id  where {{%zakaz}}.id=:refZakaz ', 
            [  ':refZakaz' => intval($timeList['ref_zakaz']),   ])->queryOne();
           }
            $emptyShow =0; //сброс показа пустой строки
            if ($timeList['orgRef'] > 0){
                $strOutPut .= "<td style='background-color:$bg'>                
                <div class='clickable' onclick='showOrg(".$timeList['orgRef'].");' >".$timeList['taskTitle']."</div>";                
            if ($timeList['eventStatus'] != 2)   $strOutPut .= "<span onclick='rejectTask(".$timeList['eventRef'].");'  class='glyphicon glyphicon-remove clickable' style='color:Crimson;top:-10px;float:right;font-size:12px;'></span>";
                $strOutPut .= "<span onclick='showActiveSdelka(".$timeList['orgRef'].");'  class='glyphicon glyphicon-info-sign clickable' style='float:right;font-size:17px;'></span>
                </td>
                ";
            }    
            else
                $strOutPut .= "<td style='background-color:$bg'>".$timeList['taskTitle']."</td>";
            
            /*Сделка */         
            if (empty($timeList['ref_zakaz'])) $strOutPut .= "<td style='background-color:$bg;'></td>";            
                                         else  {
             if(!empty($sdelkaData['refSchet'])) $val="<div class='clickable' style='color:blue;' onclick='showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");'>счет № ".$sdelkaData['schetNum']." от ".date('d.m.y', strtotime($sdelkaData['schetDate']))."</div>";                          
             else $val="<a href='#' onclick='showZakaz(".$timeList['orgRef'].",".$timeList['ref_zakaz'].");' Заявка № ".$sdelkaData['ref_zakaz']." от ".date('d.m.y', strtotime($sdelkaData['formDate']))."</a>";                          
             $strOutPut .= "<td style='background-color:$bg;'>".$val."</td>";            
             }
             /*Цель */
            if (mb_strlen($freeTimeList[$iRow]['eventNote'],'utf-8') > 220 ) $add = "...";   else $add = "";            
            $strOutPut .= "<td style='background-color:$bg;'><div style='font-size:10pt;' title='".$freeTimeList[$iRow]['eventNote']."'>";
            $strOutPut .= mb_substr($freeTimeList[$iRow]['eventNote'],0,150,'utf-8').$add."</div></td>";    
        
               $id = "isPhone".$timeList['eventRef'];                       
               $action = "switchData(".$timeList['eventRef'].", 'isPhone');";                                  
               if ($timeList['isPhone'] == 1 )$style='background:DarkBlue;';
                                         else $style='background:White;';
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => 'Звонок',
                   ]);
                $strOutPut .= "<td align='center'>".$val."</td>";
/*****/
               $id = "isDocument".$timeList['eventRef'];                       
               $action = "switchData(".$timeList['eventRef'].", 'isDocument');";                                  
               if ($timeList['isDocument'] == 1 )$style='background:DarkBlue;';
                                         else $style='background:White;';

               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Документ',
                   ]);
                $strOutPut .= "<td align='center'>".$val."</td>";
        

/*************************/
$isShowNext = 0;
/*Спрос**/
               $id = "isSpros".$timeList['eventRef'];                       
               if (empty($timeList['orgRef'])) $action ="";
                                         else  $action = "regContact(".$timeList['orgRef'].");";                                                                  
               if (!empty($timeList['ref_zakaz']))
               {
                $style='background:Green;';
                $class = 'btn btn-primary circle';
                $isShowNext = 1;   
               }
               else    {                        
                $class = 'glyphicon glyphicon-question-sign clickable';                  
                $style="";
                 $isShowNext = 0;   
               //$val= "<span onclick='".$action."' style='color:Blue;' class='glyphicon glyphicon-question-sign clickable' title='Спрос'></span>"; 
              }
               $val = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => $class,
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Спрос',
                   ]);

                $strOutPut .= "<td align='center'  class='info_cell' >".$val."</td>";
        

/*Заявка**/
               $id = "isZayavka".$timeList['eventRef'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showZakaz(".$timeList['orgRef'].",".$timeList['ref_zakaz'].");";                                  

               $isShowCur = $isShowNext; 
               if (!empty($sdelkaData['refSchet']))  $isExec= true;
               else {$isExec= false; $isShowNext = 0;
                $sdelkaData['refSchet']=0;  
                $sdelkaData['docStatus']=0;  
                $sdelkaData['cashState']=0;  
                $sdelkaData['supplyState']=0;  
                $sdelkaData['isSchetActive']=0;}
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-file', 'Заявка');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                
/*Счет**/
               $id = "isSchet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                  

               $isShowCur = $isShowNext;                
               if ($sdelkaData['docStatus'] >= 2)  $isExec= true;
                                             else {$isExec= false; $isShowNext = 0; }
               
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-list-alt', 'Счет');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                                         
                                         
/*Деньги**/
               $id = "isCashGarant".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                        else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                   

               $isShowCur = $isShowNext;                
               if ($sdelkaData['cashState'] >= 1)  $isExec= true;
                                             else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-check', 'Предоплата/гарантия');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                                         
/*Т-**/
               $id = "isSupply".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    
               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 1)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-shopping-cart', 'Отгрузка товара');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                               
/*$+**/
               $id = "isCashGet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

               $isShowCur = $isShowNext; 
               if ($sdelkaData['cashState'] >= 4)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-usd', 'Поступление денег ($+)');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                   
/*Док.**/
               $id = "isDocGet".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 4)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-folder-open', 'Документы');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                   
                                         
/*Стоп.**/
               $id = "isFinished".$sdelkaData['refSchet'];                       
               if (empty($timeList['orgRef'])) {$action =""; $isShowNext = 0;}
                                         else  $action = "showSchet(".$sdelkaData['refSchet'].",".$timeList['eventRef'].");";                                                                    

                                         
               $isShowCur = $isShowNext; 
               if ($sdelkaData['supplyState'] >= 5)  $isExec= true;
                                               else {$isExec= false; $isShowNext = 0; }
                                         
               $val = $model->showMarker($id, $action, $isShowCur, $isExec, 'glyphicon glyphicon-ok-sign', 'Стоп (Сделка завершена)');
               $strOutPut .= "<td align='center'  class='info_cell'>".$val."</td>\n";
                                         
//Выполнено
            if ($timeList['eventStatus'] == 2) $style= 'background-color:Green';
            else                               $style= 'background-color:White';
    
            $id = "isExec".$timeList['eventRef'];                       
            
               if (!empty($timeList['orgRef']) ) $action = "showOrg(".$timeList['orgRef'].")";                                  
                                            else $action = "setExec(".$timeList['eventRef'].");";                                  
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Выполнено',
                     'style'   => $style,
                   ]);
                $strOutPut .= "<td align='center'>".$val."</td>";
        
            $id = "execNote".$timeList['eventRef'];                 
    
            if (mb_strlen($freeTimeList[$iRow]['execNote'],'utf-8') > 150 ) $add = "...";    else $add = "";            
            $strOutPut .= "<td style='background-color:$bg;'><div id='".$id."' style='font-size:10pt;' title='".$freeTimeList[$iRow]['execNote']."'>";
            $strOutPut .= mb_substr($freeTimeList[$iRow]['execNote'],0,220,'utf-8').$add."</div></td>";    
    
        }
        else //пустая строка
        {        
            $strOutPut .="<td style='background-color:$bg'><div class='freetime empty_cell' ";
            $strOutPut .="onclick='acceptTask(\"".date("d.m.Y",$now)."\",\"".$timeList['strTime']."\");'> </div></td>";
            $strOutPut .="
            <td class='empty_cell' style='background-color:$bg'></td>            
            <td class='empty_cell' style='background-color:$bg'></td>
            <td class='empty_cell' style='background-color:$bg'></td>
                        
            <td class='empty_cell' style='background-color:$bg'></td>
            
            <td  class='info_cell'></td>            
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            <td  class='info_cell'></td>
            

            <td class='empty_cell' style='background-color:$bg'></td>
            <td class='empty_cell' style='background-color:$bg'></td>";                 
            $emptyShow ++; //сброс показа пустой строки            
         }   
    
    if ($emptyShow <=1) {
        echo "<tr>\n";         
        echo $strOutPut;
//        echo "<td>".$emptyShow."</td>";
//        $m=$freeTimeList[$iRow]['time']%3600;
//        echo "<td>".$m."</td>";
        echo "</tr>\n";
       } // покажем или занятые или одну строку после 
    }

        
?>

</table>

<?php
/*echo "<pre>";
print_r($freeTimeList);
echo "</pre>";*/
?>

<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/tasks/market/save-event-exec-detail']);
echo $form->field($modelForm, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($modelForm, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($modelForm, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
ActiveForm::end(); 
?>


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
    <td class='flbl'>Заголовок задачи</td>
    <td colspan=2><?= $form->field($modelForm, 'taskTitle')->textInput(['id'=>'taskTitle'])->label(false)?></td>                
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
    <?= $form->field($modelForm, 'note')->textarea(['id' => 'note','rows' => 5, 'cols' => 20])->label('Цель')?></td> 
    
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
    'id' =>'setExecDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);

$form = ActiveForm::begin(['id' => 'setExecForm', 'action' => 'index.php?r=/tasks/market/save-set-exec']);
echo $form->field($modelForm, 'dataRequestId' )->hiddenInput(['id' => 'setExecRequestId' ])->label(false);
echo $form->field($modelForm, 'dataVal' )->textarea([
        'id' =>'setExecDataVal', 
        'style' => 'width:100%; height:140px;', 
        ])->label('Комментарий к исполнению');
echo "<div align='center'><input type='button' value='Сохранить' onclick='setExecSave();'></div>";
//echo "<div align='center'><input type='submit' ></div>";
ActiveForm::end(); 

Modal::end();?>




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
Modal::begin([
    'id' =>'sdelkaDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='sdelkaFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=market/market-schet&src=task&noframe=1&id=37055' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>
