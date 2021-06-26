<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & (0x0020|0x0008+0x0100))) {return;}

$this->title = 'Детализация работы с клиентами';
//$this->params['breadcrumbs'][] = $this->title;

$monthTitle = [
 0 => 'none',
 1 => 'Январь',
 2 => 'Февраль',
 3 => 'Март',
 4 => 'Апрель',
 5 => 'Май',
 6 => 'Июнь',
 7 => 'Июль',
 8 => 'Август',
 9 => 'Сентябрь',
 10 => 'Октябрь',
 11 => 'Ноябрь',
 12 => 'Декабрь',
 13 => 'none',
 ];

     $cur = $model->fixDate (['m' => (date('n')-$model->monthShift),   'y' => date('Y')]);

       $curM = $cur["m"];
       $curY = $cur["y"];
   
       $curD = date ("d");

        $dayInMonth=date("t", strtotime($curY."-".$curM));               
       if ($model->monthShift == 0) $curD = date ("d");
       else $curD = $dayInMonth;

       
       /*С какого месяца смотрим*/
       $startM= $curM-2;
       while($startM< 1)$startM++;
?>
  <h2><?= Html::encode($this->title) ?></h2>
<style>
.local_cell{
    padding:5px;    
    width:30px;  

 .marked{
	background-color: Silver ;
 }
 .itogo{
	font-weight: bold;
 }
    
}
 
</style>

<div class="container" style="overflow: auto;">
<table class='table table-bordered'  width="100%" style='padding:5px;' >
<thead>
    <tr>
        <th><div style='width:200px;'>&nbsp;</div></th>
        <?php for ($m=$startM; $m<=$curM; $m++) echo "<th style='width:30px'> </th>"; ?>
        <th style='background:lightBlue; font-weight:bold; padding-left:50px' colspan='<?=$dayInMonth?>'><?=$monthTitle[$curM]?></th>
    </tr>

<?php

$managerRecord= $model->getManagerRecord($userId);
if(!empty($managerRecord)) 
{
   echo "<h4>".$managerRecord->userFIO."</h4>"; 
}

$activityList = $model->getUserActivityArray($userId);
              
echo "<tr>\n";       
echo "<td style='padding:5px;'>Период</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td style='padding:5px;'>".$monthTitle[$m]."</td>";      
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'><div  class='local_cell'>".$d."</div></td>";       
echo "</tr>\n";       
       
echo "</thead> <tbody> ";

echo "<tr>\n";       
echo "<td style='padding:5px;'>Общая активность</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['allActivity']."</td>";       
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['allActivity']."</td>";       
echo "</tr>\n";       

echo "<tr>\n";       
echo "<td style='padding:5px;'>Клиентов, уник.</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['clientNum']."</td>";       
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['clientNum']."</td>";       
echo "</tr>\n";       

       
echo "<tr>\n";       
echo "<td  style='padding:5px;'>Лиды, зарегистрировано</td>"; 
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['leadsReg']."</td>";               
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['leadsReg']."</td>";       
echo "</tr>\n";       

echo "<tr>\n";       
echo "<td  style='padding:5px;'>Лиды, обработано</td>";        
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['leadsProcessed']."</td>";                
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['leadsProcessed']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td  style='padding:5px;'>Контакты, без заявок</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['contactsEmpty']."</td>";                
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['contactsEmpty']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td  style='padding:5px;'>Создано новых заявок</td>";       
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['zakazNew']."</td>";                  
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['zakazNew']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td  style='padding:5px;'>Работа с заявками</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['zakazWork']."</td>";                  
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['zakazWork']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Новые счета</td>";  
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['schetNew']."</td>";                         
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['schetNew']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Количество оплат (1C)</td>"; 
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['oplatNum']."</td>";                                 
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['oplatNum']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Заявок на отгрузку</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['requestNum']."</td>";                                 
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['requestNum']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Количество поставок (1C)</td>";   
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['supplyNum']."</td>";      
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['supplyNum']."</td>";       
echo "</tr>\n";             

/*
echo "<tr>\n";       
echo "<td style='padding:5px;'>закрыто сделок</td>"; 
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['schetFinit']."</td>";              
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['schetFinit']."</td>";       
echo "</tr>\n";             
*/
echo "<tr>\n";       
echo "<td style='padding:5px;'>Сумма счетов, т.р.</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['schetSum']."</td>";              
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['schetSum']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Cумма оплат, т.р.</td>";  
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['oplataSum']."</td>";       
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['oplataSum']."</td>";       
echo "</tr>\n";             

echo "<tr>\n";       
echo "<td style='padding:5px;'>Cумма отгрузок,т.р.</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['supplySum']."</td>";       
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['supplySum']."</td>";       
echo "</tr>\n";             
/*
echo "<tr>\n";       
echo "<td style='padding:5px;'>ОХВАЧЕНО КЛИЕТОВ</td>";         
for ($m=$startM; $m<=$curM; $m++) echo "<td class='local_cell'>".$activityList[$m+$dayInMonth]['clientNum']."</td>";       
for ($d=1; $d<=$dayInMonth; $d++) echo "<td class='local_cell'>".$activityList[$d]['clientNum']."</td>";       
echo "</tr>\n";             
*/
       

?>
</tbody>
</table>
</div>

<?php
/*echo "<pre>";

print_r($model->debug);

echo "</pre>";*/
?>
