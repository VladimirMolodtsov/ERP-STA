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


</script>



<h4><?= $model->getMangerFIO($userId ) ?></h4>
<div class ='row'>
  <div class ='col-md-4'>   
  </div>
   <div class ='col-md-1'>   
   </div>
   <div class ='col-md-2' style='text-align:center'><b><?= date("d.m.Y",$now)  ?></b></div>
   <div class ='col-md-1' style='text-align:right'>    
   </div>
  <div class ='col-md-4'>   
  </div>
   
</div>



<table width='80%' border='1px' style='border-color:Gray;'>


<!--  <tr>
    <th colspan=6 align='center' >Описание задачи</th>    
    <th colspan=8 class='info_cell' align='center'>Статус сделки</th>    
    <th colspan=1 align='center'> Результат выполнения</th> 
  </tr>       -->



  <tr>
<!--    <th style='width:75px'>Время</th>    -->
    <th     > Задача</th>    
    
 <th style='width:100px'> Сделка</th>
    
<!--    <th style='width:300px'> Цель </th>                  
    <th style='width:50px'> <span class='glyphicon glyphicon-phone-alt' title='Звонок'></span> </th>
    <th style='width:50px'> Док. </th>   --> 

<th style='width:150px' class='info_cell'>Было</th>
<th style='width:150px' class='info_cell'>Стало</th>
<!--<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-file' title='Заявка'></span> </th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-list-alt' title='Счет'></span> </th>                
<th style='width:50px' class='info_cell'><span class='' title='Предоплата/гарантия'>п/о</span> </th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-shopping-cart' title='Отгрузка товара'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-usd' title='Поступление денег ($+)'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-folder-open' title='Документы'></span></th>
<th style='width:50px' class='info_cell'><span class='glyphicon glyphicon-ok-sign' title='Стоп (Сделка завершена)'></span></th> -->


<!--<th style='width:50px'><span class='glyphicon glyphicon-ok' title='Выполнено'></span></th> -->
<th style='width:50px'>Звонков</th> 
<th style='width:50px'>Разговоров</th> 
<th style='width:50px'>Прогресс</th> 
<!--<th> Комментарий </th>-->
  </tr>       

<?php
//class="table table-striped table-bordered"
$idx=0;
$freeTimeList = $model->getFreeTimeList($date, $userId);

$N = $model ->pN; //count($freeTimeList);
$hN=0;

$emptyShow =0; //показана ли пустая строка
   for ($iRow=0;$iRow<$N;$iRow++)
    {   
        $bg="White;";
        /* Формируем строку вывода*/                

        $strOutPut ="";
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
           if (empty ($sdelkaData) )
           {
                $sdelkaData['refSchet']=0;  
                $sdelkaData['docStatus']=0;  
                $sdelkaData['cashState']=0;  
                $sdelkaData['supplyState']=0;  
                $sdelkaData['isSchetActive']=0;
           }
           
           $emptyShow =0; //сброс показа пустой строки
           $strOutPut .= "<td style='background-color:$bg'>".$timeList['taskTitle']."</td>";
            
            /*Сделка */         
            if (empty($timeList['ref_zakaz'])) $strOutPut .= "<td style='background-color:$bg;'></td>";            
            else  {
             if(!empty($sdelkaData['refSchet'])) $val="<div>счет № ".$sdelkaData['schetNum']."</div>";                          
             else $val="Заявка № ".$sdelkaData['ref_zakaz'];                          
             $strOutPut .= "<td style='background-color:$bg;'>".$val."</td>";            
             }
             /*Цель */
/*            if (mb_strlen($freeTimeList[$iRow]['eventNote'],'utf-8') > 220 ) $add = "...";   else $add = "";            
            $strOutPut .= "<td style='background-color:$bg;'><div style='font-size:10pt;' title='".$freeTimeList[$iRow]['eventNote']."'>";
            $strOutPut .= mb_substr($freeTimeList[$iRow]['eventNote'],0,150,'utf-8').$add."</div></td>";    */
        
/*               $id = "isPhone".$timeList['eventRef'];                       
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
                $strOutPut .= "<td align='center'>".$val."</td>";*/
/*****/
/*               $id = "isDocument".$timeList['eventRef'];                       
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
*/        

/*************************/
    $strOutPut .= $model->showPrevStatus($timeList, $now);    
    $strOutPut .= $model->showCurrentStatus($timeList,$sdelkaData);
/******************/                
//Выполнено
            if ($timeList['eventStatus'] != 2) $val = "&nbsp;";
            else $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-ok',
                   ]);
             //   $strOutPut .= "<td align='center'>".$val."</td>";
        
            
            /*if (mb_strlen($freeTimeList[$iRow]['execNote'],'utf-8') > 150 ) $add = "...";    else $add = "";            
            $strOutPut .= "<td style='background-color:$bg;'><div id='".$id."' style='font-size:10pt;' title='".$freeTimeList[$iRow]['execNote']."'>";
            $strOutPut .= mb_substr($freeTimeList[$iRow]['execNote'],0,220,'utf-8').$add."</div></td>";*/    

//Звонков
           $val =Yii::$app->db->createCommand("SELECT count(id) FROM {{%ats_log}} 
                 where orgRef=:ref_org and DATE(call_start)=:eventDate and 
                 (event='NOTIFY_OUT_END' OR  event='NOTIFY_END')",
                 [':ref_org'=>$timeList['orgRef'],
                 ':eventDate'=>date('Y-m-d', $now),
                 ])->queryScalar();
           if ($val == 0 ) $val="<font color='Gray'>".$val."</font>";
                    else   $val="<b><font size=+1>".$val."</font></b>";
          $strOutPut .= "<td align='center'>".$val."</td>";

//Разговоров
           $val =Yii::$app->db->createCommand("SELECT count(id) FROM {{%ats_log}} 
                 where orgRef=:ref_org and DATE(call_start)=:eventDate and duration > 30 
                 AND (event='NOTIFY_OUT_END' OR  event='NOTIFY_END')",
                 [':ref_org'=>$timeList['orgRef'],
                 ':eventDate'=>date('Y-m-d', $now),
                 ])->queryScalar();
           if ($val == 0 ) $val="<font color='Gray'>".$val."</font>";
                    else   $val="<b><font size=+1>".$val."</font></b>";
    
          $strOutPut .= "<td align='center'>".$val."</td>";
    
//смена статуса
           $chngStatus =Yii::$app->db->createCommand("
                   SELECT sum(isChangeStatus) as chngStatus from {{%contact}} 
                   WHERE date(contactDate) =:DATE AND ref_org=:ref_org            
                   ",
                 [':ref_org'=>$timeList['orgRef'],
                 ':DATE'=>date('Y-m-d', $now),
                 ])->queryScalar();
            
            $newSd= Yii::$app->db->createCommand(
                "SELECT count(DISTINCT({{%zakaz}}.id)) from {{%zakaz}} where  
                {{%zakaz}}.refOrg=:ref_org AND DATE(formDate) = :DATE", 
                [':ref_org'=>$timeList['orgRef'], 
                ':DATE' => date('Y-m-d', $now)])->queryScalar();     
            $val = $newSd+$chngStatus;     
           if ($val == 0 ) $val="&nbsp;";
                    else   $val="<span class='glyphicon glyphicon-ok-circle'></span>";
    
          $strOutPut .= "<td align='center'>".$val."</td>";

    
  
        
        }
        else //пустая строка
        {        
//            $strOutPut .="<td style='background-color:$bg'><div class='freetime empty_cell' ";
//            $strOutPut .="onclick='acceptTask(\"".date("d.m.Y",$now)."\",\"".$timeList['strTime']."\");'> </div></td>";
/*            $strOutPut .="
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
            

            <td class='empty_cell' style='background-color:$bg'></td>
            ";                 
*/            
            $emptyShow =2; //сброс показа пустой строки                        
         }   
    
    if ($emptyShow <=1) {
        echo "<tr>\n";         
        echo $strOutPut;
        echo "</tr>\n";
       } 
    }

        
?>

</table>
