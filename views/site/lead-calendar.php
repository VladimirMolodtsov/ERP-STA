<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Календарь ошибок';
//$this->params['breadcrumbs'][] = $this->title;

/*$now = $model->showDate;
$prev= $model->showDate-24*3600;
$next= $model->showDate+24*3600;
 
$zero=strtotime (date("Y-m-d", $model->showDate)." 00:00:00"); //на начало дня
$shift = intval((time() - $zero)/3600)+4; // сколько прошло с начала дня
*/

$leadList = $model->getDayLeadList($month, $year);
$monthLead= $model->getMonthLeadList($year);
$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.bound{
    background-color:LightGray; 
    width:20px;
}
td{
 font-size:11px;
}
</style>


<script type="text/javascript">
function setCalendarFilter (d,month,year)
{
 window.parent.setCalendaFilter (d,month,year);   
    
}

function chngMonth(){
    document.getElementById('fltForm').submit();
}
function setMonth(m){
 document.location.href="index.php?r=site/lead-calendar&noframe=1&month="+m+"&year=<?= $year ?>";   
}
</script> 
<div align='center'>
<form name='fltForm' id='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='site/lead-calendar'>
<input type='hidden' name='noframe' value='1'>
<table border='0' width='100%' style='padding:5px' >
<tr>
<td style='width:100px;padding:5px'><input name='year' style='width:100px;padding:2px' class="form-control" onchange='chngMonth();' value='<?= $year ?>'> </td>
<td valign='middle' style='padding:2px'>
<span class='clickable 	glyphicon glyphicon-filter' title='Применить фильтр' onclick='chngMonth();'></span>
</td>
<td>

</td>
</tr>

</table>
</form>
</div>

</table>

<table border=0 width='100%'>
<tr><td>
  <table class='table table-small table-striped'>
 <?php
   for($m=1;$m<=12;$m++)
   {
    echo "<tr>";
    echo "<td>";
    echo $monthList[$m];
    echo "</td>";
    echo "<td>";
   if ($m==$month) {$style='background-color:DarkBlue;color:White;';}
   else {$style='';}
   $action="setMonth(".$m.");";
    echo  \yii\helpers\Html::tag( 'div', $monthLead[$m]['all'], 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Лидов за '.$m." месяц",
                     'style'   => $style,
          ]);    
    
    echo "</td>";
    echo "</tr>";
   } 
 ?>   
 </table>   
</td>

<td width=30px>&nbsp;</td>

<td valign='top'>
<table class='table table-small table-striped'>
<?php
function printCell($d, $month, $year, $leadList)
{
    $dd=str_pad($d,2,'0',STR_PAD_LEFT);
    $mm=str_pad($month,2,'0',STR_PAD_LEFT);
   $action =  "setCalendarFilter(".$dd.",".$mm.",".$year.");";                    
   if ($leadList[$d]['all'] > 0) $style="color:Crimson";
                      else   $style="color:Green";
    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $dd.".".$mm.".".$year, 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Лидов за '.$dd." число",
                     'style'   => $style,
          ]);    
    echo "</td>";        

    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $leadList[$d]['all'], 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Лидов за '.$dd." число",
                     'style'   => $style,
          ]);    
    echo "</td>";        
}

 
   $n = date('t',strtotime($year."-".$month."-01"));
    
   $k=intval($n/3);
   
   for ($i=1;$i<=$k;$i++){
    
    echo "<tr>";   
    
    $d=$i;

    printCell($d, $month, $year, $leadList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+$k;
    printCell($d, $month, $year, $leadList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+2*$k;
    printCell($d, $month, $year, $leadList );       
        
    echo "</tr>";
   }
   
   $d++;
   while($d<=$n)
   {
   echo "<tr>";           
    echo "<td></td><td></td>";
    echo "<td class='bound'>&nbsp;</td>";    
    echo "<td></td><td></td>";
    echo "<td class='bound'>&nbsp;</td>";    
    printCell($d, $month, $year, $leadList );               
    echo "</tr>";    
    $d++;  
   }
   
   
?>
</table>

</td></tr></table>

<?php

 if(!empty($detailModel->debug))
 {
  echo "<pre>";
 print_r ($detailModel->debug);
 echo "</pre>";
 }
 

?>


