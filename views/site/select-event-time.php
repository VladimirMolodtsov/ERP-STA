<?php


use yii\helpers\Html;

$this->title = 'Время события';

?>

<style>

td{
font-size:11px;
}
</style>


<script>

function setTime(eventTime)
{
  window.parent.setSelectEventTime(eventTime);

}
</script>
<table class="table table-striped table-small" style="padding:2px;">

<?php

$idx=0;
$freeTimeList = $model->getFreeTimeList($date, $userid);

for ($iRow=8;$iRow<19;$iRow++)
{
    echo "<tr>\n";
    for ($iCol=0;$iCol<60;$iCol=$iCol+5)
    {
    $time=sprintf("%02d:%02d",$iRow, $iCol);
    $curTime = strtotime($time) - strtotime(date("Y-m-d"));
    $idx = intval( ($curTime - 8.5*3600)/300 );
    if($idx<0){
    echo "<td></td>";
    continue;
    }     
     if($freeTimeList[$idx]['eventRef'] > 0)echo "<td><a href='#' style='color:Crimson;' title='".$freeTimeList[$idx]['orgTitle']."' >".$time."</a></td>";
     else                                   echo "<td><a href='#' onclick='setTime(\"".$time."\");'>".$time."</a></td>";
    }
    echo "</tr>\n";
}
?>


</table>
