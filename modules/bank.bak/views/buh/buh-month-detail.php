<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Работа старшего бухгалтера';
$this->params['breadcrumbs'][] = $this->title;

    $logArray = $buhmodel->getMonthExecute(time());

?>

 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.wait {    
    background-color:Gray;    
    margin:0px; 
    text-align:center;
    width:95px;    
}
.done {    
    background-color:Green;    
    color:White;
    text-align:center;        
    width:95px;
}
.warning {    
    background-color:Orange;    
    color:White;
    text-align:center;        
    width:95px;
}
.error {    
    background-color:Crimson;    
    color:White;
    text-align:center;    
    width:95px;
}
td {
   padding:0px; 
    
}

th {
   padding:5px; 
    
}

.current {
  border-collapse: collapse;   
  border-style: solid;
  border: 4px;
  border-color: Orange;
}

</style>


<script type="text/javascript">

function openExtractDetail()
{
  document.location.href='index.php?r=bank/operator/load-bank&showDate=<?= $bankmodel->showDate ?>' 
}

function openDocDetail()
{
  document.location.href='index.php?r=bank/operator/load-doc&showDate=<?= $bankmodel->showDate ?>' 
}


function openBuhDetail()
{
  document.location.href='index.php?r=bank/buh/buh-statistics&dtstart=<?= date("Y-m-d",$bankmodel->showDate) ?>' 
}

</script> 




<table class='table table-stripped table-bordered'>
<thead>
<tr>
    <th width="50px">Контрольная точка</th>
    <th >Действие</th>
<?php
for ($i=1; $i<=$logArray['nd'];$i++ )
echo "<th width='15px'> $i </th>";
?>    
</tr>
</thead>
<tbody>


<tr>
    <td><?=$modelLog->borderArray['login'][0][1] ?></td>
    <td>Логин</td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'login',0 ); ?>       
</tr>


<tr>
    <td><?=$modelLog->borderArray['load'][0][1] ?> </td>
    <td>Загрузка</td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'load', 0); ?>       
</tr>

<tr>
    <td><?=$modelLog->borderArray['sync'][0][1] ?> </td>
    <td>Синхронизация</td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'sync', 0 ); ?>       
</tr>


<tr>
    <td><?=$modelLog->borderArray['load'][1][1] ?> </td>
    <td>Загрузка </td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'load', 1); ?>       
</tr>

<tr>
    <td><?=$modelLog->borderArray['sync'][1][1] ?> </td>
    <td>Синхронизация</td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'sync', 1 ); ?>       
</tr>


<tr>
    <td><?=$modelLog->borderArray['load'][2][1] ?> </td>
    <td>Загрузка </td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )     showRes($logArray, $i, 'load', 2); ?>           
</tr>

<tr>
    <td><?=$modelLog->borderArray['sync'][2][1] ?> </td>
    <td>Синхронизация</td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )    showRes($logArray, $i, 'sync', 2 ); ?>       
</tr>


<tr>
    <td><?=$modelLog->borderArray['load'][3][1] ?> </td>
    <td>Загрузка </td>
    <?php for ($i=1; $i<=$logArray['nd'];$i++ )     showRes($logArray, $i, 'load', 3); ?>           
</tr>


</tbody>
</table>




