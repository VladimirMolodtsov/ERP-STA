<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;


$curUser=Yii::$app->user->identity;
$this->title = 'Настройка сценариев';

$scenarioData = $model->getScenarioList();
$scenarioTitles = $scenarioData['scenarioTitles'];
$statusArray = $scenarioData['statusArray'];
?>

<script type="text/javascript" src="phone.js"></script>  
<link rel="stylesheet" type="text/css" href="phone.css" />


<style>


</style>

<script type="text/javascript">

function showEditBox(boxId)
{

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
 
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
    
}

function closeEditBox(boxId)
{
if (boxId == "0") {return;}

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    

}

function addScenario()
{
 $('#addScenarioDialog').modal('show');   
}

function newScenario()
{
 openSwitchWin('store/add-scenario&name='+document.getElementById('scenarioName').value)
}

function editScenarioName(name, id)
{
 document.getElementById('editName').value = name;   
 document.getElementById('editScenarioId').value = id;
 $('#editScenarioDialog').modal('show');   
}

function setScenario()
{
 id = document.getElementById('editScenarioId').value;
 name= document.getElementById('editName').value; 
 openSwitchWin('store/edit-scenario&id='+id+'&name='+name);
}
function chngStatus(etap, id, curStatus)
{
 if (curStatus == 0) setStatus=1;
               else  setStatus=0;
 openSwitchWin('store/set-scenario-status&etap='+etap+'&id='+id+'&status='+setStatus);   
}

function saveTime (j, i, scenid)
{
 id = "edit_time_"+j+"_"+i; 
 val = document.getElementById(id).value; 
 openSwitchWin('store/set-scenario-time&etap='+j+'&id='+scenid+'&val='+val);   
}

</script>

<h3><?= Html::encode($this->title) ?></h3>

<table border=0 width='100%'>
<tr>
<td><div style='position:relative;top:-6px;'><table  class='table table-bordered table-striped'>
<thead>
<tr>
    <th width='50px' rowspan='2'>№</th>
    <th style="width:350px;text-align:right;" ><div style='height:100px;'>Название сценария:</div></th>
</tr>    
<tr>
    <th  >Этап</th>
</tr>    
</thead>

<tbody>

<?php

$cnt = count($statusArray); 
for ($j=1; $j <= $cnt; $j++){    
?>
 <tr>
    <td ><?= $j ?></td>
    <td ><?= $statusArray[$j]['name'] ?></td>           
 </tr>
<?php
}
?>
    
</tbody>
</table>
</div></td>


<td><div style='position:relative;top:0px;overflow:auto; width:1000px;'><table  class='table table-bordered table-striped' id='main-table'>
<thead>
<tr>
    
    <?php 
    $scenN = count($scenarioTitles);
    for ($i=$scenN-1; $i>=0; $i--  )
    {
      echo "<th colspan=2 width='150px'><div style='height:100px;'>";  
      echo "<a href='#' onclick='editScenarioName(\"".$scenarioTitles[$i]['name']."\",".$scenarioTitles[$i]['id']." )' >".$scenarioTitles[$i]['name']."</a>";  
      echo "</div></th>";    
    }
    ?>
    <th style="width:350px;text-align:right;" >Название сценария:</th>    
</tr>    
<tr>
    <th  >Этап</th>
    <?php 
    $scenN = count($scenarioTitles);
    for ($i=$scenN-1; $i>=0; $i--  )
    {
      echo "<th>Вкл.</th>";        
      echo "<th>Дней</th>";        
    }
    ?>
</tr>    

</thead>
<tbody>

<?php

$cnt = count($statusArray); 
for ($j=1; $j <= $cnt; $j++){    
?>
<tr>
    <?php 
    for ($i=$scenN-1; $i>=0; $i--  )
    {
      echo "<td width='75px'><a href='#' onclick='chngStatus(".$j.",".$scenarioTitles[$i]['id'].",".$statusArray[$j][$i]['inUse'].");' ";  
      	if ($statusArray[$j][$i]['inUse'] == 1 ){ $isUse = true;}
					else                        { $isUse = false;}
                    echo \yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                        ['class' => 'label label-' . ($isUse ? 'success' : 'danger'),]);
      echo "</td>";       

  $value = $statusArray[$j][$i]['time'];
  $id = "time_".$j."_".$i;                                   
  $ret ="<div id='viewBox_".$id."' class='gridcell'  style='width:75px;  text-align:left;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$value."</div>"; 
  $ret.="<div id='editBox_".$id."' class='editcell'  style='width:75;' ><nobr>";
  $ret.="<input  id='edit_".$id."' style='width:45px;' value='".$value."'>";
  $ret.="<a href ='#' onclick=\"javascript:saveTime('".$j."','".$i."','".$scenarioTitles[$i]['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
  $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
  $ret.="</nobr></div>";

      echo "<td width='75px'>";  
        echo $ret; 
      echo "</td>";    
    }
    ?>
    <td ><?= $statusArray[$j]['name'] ?></td>               
</tr>
<?php
}
?>
    
</tbody>
</table>
</div>
</td>
</tr>
</table>


<p>
<a class='btn btn-default' href="#" onclick="javascript: addScenario(); "> Добавить сценарий </a>
</p>



<?php
/********************************************************************************/

Modal::begin([
    'id' =>'addScenarioDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h2>Создать сценарий</h2>',
]);?>

<label for="scenarioName" >Название сценария:</label>
<input class='form-control' name='scenarioName' id='scenarioName'  value='Имя сценария' > 
<br>
<p>
    <a class='btn btn-default' href="#" onclick="javascript: newScenario(); "> Сохранить </a>
</p>

<?php
Modal::end();
?>

<?php
Modal::begin([
    'id' =>'editScenarioDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h2>Создать сценарий</h2>',
]);?>

<label for="editName" >Название сценария:</label>
<input class='form-control' name='editName' id='editName'  value='Имя сценария' > 
<input type='hidden' name='editScenarioId' id='editScenarioId'  value='0' > 
<br>
<p>
    <a class='btn btn-default' href="#" onclick="javascript: setScenario(); "> Сохранить </a>
</p>

<?php
Modal::end();
?>
