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

$errorList = $model->getErrorList($month, $year);
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
function setErrFilter (d,month,year)
{
 window.parent.setErrFilter (d,month,year);   
    
}

function chngMonth(){
    document.getElementById('fltForm').submit();
}

</script> 
<div align='center'>
<form name='fltForm' id='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='bank/operator/doc-error-calendar'>
<input type='hidden' name='noframe' value='1'>
<table border='0' width='100%' style='padding:5px' >
<tr>
<td style='width:150px;padding:2px'>
<select style='width:150px;padding:2px' name='month' class="form-control" onchange='chngMonth();'>
<?php
for ($i=1; $i<=12; $i++)
{
    $p = "<option value='".$i."'";
    if ($i == $month) $p .= " selected";
    $p .= ">".$monthList[$i]."</option>";
    echo $p;
}
?>
</select>
</td>
<td style='width:100px;padding:5px'><input name='year'  style='width:100px;padding:2px' class="form-control" onchange='chngMonth();' value='<?= $year ?>'> </td>
<td valign='middle' style='padding:2px'>
<span class='clickable 	glyphicon glyphicon-filter' title='Применить фильтр' onclick='chngMonth();'></span>
</td>
<td>
Ошибок: <?=$model->monthErr?> из <?=$model->monthAll?> документов.
</td>
</tr>

</table>
</form>
</div>

</table>

<table class='table table-small table-striped'>
<?php
function printCell($d, $month, $year, $errorList)
{
   $action =  "setErrFilter(".$d.",".$month.",".$year.");";                    
   if ($errorList[$d]['err'] > 0) $style="color:Crimson";
                      else   $style="color:Green";
    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $d.".".$month.".".$year, 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'ошибки за '.$d." число",
                     'style'   => $style,
          ]);    
    echo "</td>";        

    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $errorList[$d]['err'], 
          [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'ошибки за '.$d." число",
                     'style'   => $style,
          ]);    
    echo "</td>";        
    echo "<td align='left'>";
    echo  \yii\helpers\Html::tag( 'div', $errorList[$d]['all'], 
          [
                     //'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'ошибки за '.$d." число",
                 //    'style'   => $style,
          ]);    
    echo "</td>"; 

}

 
   $n = date('t',strtotime($year."-".$month."-01"));
    
   $k=intval($n/3);
   
   for ($i=1;$i<=$k;$i++){
    
    echo "<tr>";   
    
    $d=$i;

    printCell($d, $month, $year, $errorList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+$k;
    printCell($d, $month, $year, $errorList );       
    echo "<td class='bound'>&nbsp;</td>";    
    
    $d=$i+2*$k;
    printCell($d, $month, $year, $errorList );       
        
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
    printCell($d, $month, $year, $errorList );               
    echo "</tr>";    
    $d++;  
   }
   
   
?>
</table>

<?php

   

?>


