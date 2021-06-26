<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Календарь';
//$this->params['breadcrumbs'][] = $this->title;

/*$now = $model->showDate;
$prev= $model->showDate-24*3600;
$next= $model->showDate+24*3600;
 
$zero=strtotime (date("Y-m-d", $model->showDate)." 00:00:00"); //на начало дня
$shift = intval((time() - $zero)/3600)+4; // сколько прошло с начала дня
*/

$saleList = $model->getDaySaleList($month, $year);
$monthSale= $model->getMonthSaleList($year);
$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.bound{
    background-color:LightGray; 
    width:10px;
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

function chngMonth(shift){
    y=parseInt(document.getElementById('year').value);
    y+=shift;
    document.getElementById('year').value = y;
    document.getElementById('fltForm').submit();
}
function setMonth(m){
 document.location.href="index.php?r=/store/sale-calendar&noframe=1&month="+m+"&year=<?= $year ?>";   
}
</script> 
<div align='center'>
<form name='fltForm' id='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='/store/sale-calendar'>
<input type='hidden' name='noframe' value='1'>
<table border='0'  style='padding:5px' >
<tr>
<td valign='middle' style='width:10px;padding:2px'>
<span class='clickable 	glyphicon glyphicon-step-backward' title='Применить фильтр' onclick='chngMonth(-1);'></span>
</td>
<td style='width:100px;padding:5px'><input name='year' id='year' style='width:100px;padding:2px' class="form-control" onchange='chngMonth(0);' value='<?= $year ?>'> </td>
<td valign='middle' style='width:10px;padding:2px'>
<span class='clickable 	glyphicon glyphicon-step-forward' title='Применить фильтр' onclick='chngMonth(1);'></span>
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
    echo  \yii\helpers\Html::tag( 'div', $monthSale[$m]['all'], 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Реализаций за '.$m." месяц",
                     'style'   => $style,
          ]);    
    
    echo "</td>";
    
   echo "<td>";
   if ($m==$month) {$style='background-color:Crimson;color:White;';}
   else {$style='color:Crimson';}
   $action="setMonth(".$m.");";
    echo  \yii\helpers\Html::tag( 'div', $monthSale[$m]['err'], 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Ошибок за '.$m." месяц",
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
function printCell($d, $month, $year, $saleList)
{
    $dd=str_pad($d,2,'0',STR_PAD_LEFT);
    $mm=str_pad($month,2,'0',STR_PAD_LEFT);
   $action =  "setCalendarFilter(".$dd.",".$mm.",".$year.");";                    
    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $dd.".".$mm, 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Статистика за '.$dd." число",
          ]);    
    echo "</td>";        

    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $saleList[$d]['sale'] , 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Всего за '.$dd." число реализаций",
          ]);    
    echo "</td>";        
    
    echo "<td align='left'>";
       if (($saleList[$d]['err']) > 0) $style="color:Crimson";
                      else   $style="color:Green";

    echo  \yii\helpers\Html::tag( 'div', ($saleList[$d]['err']), 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Ошибок за '.$dd." число",
                     'style'   => $style,
          ]);    
    echo "</td>";        
}

 
   $n = date('t',strtotime($year."-".$month."-01"));
    
   $k=intval($n/3);
   
   for ($i=1;$i<=$k;$i++){
    
    echo "<tr>";   
    
    $d=$i;

    printCell($d, $month, $year, $saleList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+$k;
    printCell($d, $month, $year, $saleList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+2*$k;
    printCell($d, $month, $year, $saleList );       
        
    echo "</tr>";
   }
   
   $d++;
   while($d<=$n)
   {
   echo "<tr>";           
    echo "<td></td><td></td><td></td>";
    echo "<td class='bound'>&nbsp;</td>";    
    echo "<td></td><td></td><td></td>";
    echo "<td class='bound'>&nbsp;</td>";    
    printCell($d, $month, $year, $saleList );               
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
  print_r ($saleList);
 //print_r ($detailModel->debug);
 echo "</pre>";
 }
 

?>


