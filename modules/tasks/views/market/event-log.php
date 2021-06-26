<?php


use yii\helpers\Html;

$this->title = 'Время события';

?>

<script>
function setTime(eventTime)
{
  window.parent.setSelectEventTime(eventTime);

}
</script>
<table class="table table-striped" style="padding:2px;">

<?php
$idx=0;
$freeTimeList = $model->getAllTimeList($date);

if ($model->managerCount ==0) return;

    echo "<tr>\n";   
    echo "<th>Время</th>";    
    for ($iCol=0;$iCol<$model->managerCount;$iCol=$iCol+10)
    {        
    echo "<th>".$freeTimeList[$iCol]['userData']['userFIO']."</th>";
    echo "</tr>\n";


for ($iRow=0;$iRow<64;$iRow++)
{
    echo "<tr>\n";    
    
    echo "<td>".$freeTimeList[$iRow]['timeList'][0]['time']."</td>";
    
    for ($iCol=0;$iCol<$model->managerCount;$iCol=$iCol+10)
    {
      $timeList=$freeTimeList[$iRow]['timeList'][$iCol];
      echo "<td>".$timeList['eventRef']."</td>";       
    }
    echo "</tr>\n";
}
?>


</table>
