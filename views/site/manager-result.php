<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Результативность менеджера';
$managerRecord= $model->getManagerRecord($userId);

$dataAll = $model->prepareUserActivityArray($userId);

$dayInMonth=date("t", strtotime($model->year."-".$model->month));
$d = $dataAll[$dayInMonth+$model->month];

$totalData = $model->getTotalSum();

if (Yii::$app->user->isGuest == true){ return;}    
?>
<style>
 .marked{
	background-color: Silver ;
 }
 .itogo{
	font-weight: bold;
 }
</style>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

</script >

<h3><?= Html::encode($this->title) ?></h3>
<?php
  $monthTitles = array(
	"1" => "Январь",
	"2" => "Февраль",
	"3" => "Март",
	"4" => "Апрель",
	"5" => "Май",
	"6" => "Июнь",
	"7" => "Июль",
	"8" => "Август",
	"9" => "Сентябрь",
	"10" => "Октябрь",
	"11" => "Ноябрь",
	"12" => "Декабрь"); 


if(!empty($managerRecord)) 
{
   echo "<h4>".$managerRecord->userFIO.". Месяц: ".$monthTitles[$model->month]."</h4> "; 
}



$borderData = [
                 [
                   'totalS' => 10000000,
                   'schetN' => 25,
                   'oplateN' => 25,
                   'supplyN' => 25,
                   'schetS'  => 2200000,
                   'oplateS' => 2200000,
                   'supplyS' => 2200000,
                   'contactN' => 1000,
                   'clientN' => 300,
                   'recomended' => 50000,
                 ],

                 [
                   'totalS' => 0,
                   'schetN' => 40,
                   'oplateN' => 40,
                   'supplyN' => 40,
                   'schetS' => 2000000,
                   'oplateS' => 2000000,
                   'supplyS' => 2000000,
                   'contactN' => 1200,
                   'clientN' => 300,
                   'recomended' => 45000,
                 ],
                 [
                   'totalS' => 0,
                   'schetN' => 30,
                   'oplateN' => 30,
                   'supplyN' => 340,
                   'schetS' => 1800000,
                   'oplateS' => 1800000,
                   'supplyS' => 1800000,
                   'contactN' => 1200,
                   'clientN' => 300,
                   'recomended' => 40000,
                 ],

                 [
                   'totalS' => 0,
                   'schetN' => 25,
                   'oplateN' => 25,
                   'supplyN' => 25,
                   'schetS' =>  1500000,
                   'oplateS' => 1500000,
                   'supplyS' => 1500000,
                   'contactN' => 1000,
                   'clientN' => 200,
                   'recomended' => 35000,
                 ],

                 [
                   'totalS' => 0,
                   'schetN' => 20,
                   'oplateN' => 20,
                   'supplyN' => 20,
                   'schetS' =>  1200000,
                   'oplateS' => 1200000,
                   'supplyS' => 1200000,
                   'contactN' => 1000,
                   'clientN' => 150,
                   'recomended' => 30000,
                 ],

                 [
                   'totalS' => 0,
                   'schetN' => 15,
                   'oplateN' => 15,
                   'supplyN' => 15,
                   'schetS' => 1000000,
                   'oplateS' => 1000000,
                   'supplyS' => 1000000,
                   'contactN' => 1000,
                   'clientN' => 120,
                   'recomended' => 25000,
                 ],
                 [
                   'totalS' => 0,
                   'schetN' => 10,
                   'oplateN' => 10,
                   'supplyN' => 10,
                   'schetS' => 800000,
                   'oplateS' => 800000,
                   'supplyS' => 800000,
                   'contactN' => 1000,
                   'clientN' => 100,
                   'recomended' => 20000,
                 ],
                 
               ];
               
               
$n = 0;
$row = 1; 
//Первый прошедший все проверки
$bN =count($borderData);
for ($i= 0; $i< $bN; $i++)
{
$n = $i;    
if ($borderData[$i]['totalS'] > $totalData['oplata'] ) {$row = 1;continue;}
if ($borderData[$i]['schetN'] > $d['schetNew']       ) {$row = 2;continue;}
if ($borderData[$i]['oplateN'] > $d['oplatNum']      ) {$row = 3;continue;}
if ($borderData[$i]['supplyN'] >  $d['supplyNum']    ) {$row = 4;continue;}
if ($borderData[$i]['schetS'] > $d['schetSum']       ) {$row = 5;continue;}
if ($borderData[$i]['oplateS'] > $d['oplataSum']     ) {$row = 6;continue;}
if ($borderData[$i]['supplyS'] > $d['supplySum']     ) {$row = 7;continue;}
if ($borderData[$i]['contactN'] > $d['allActivity']  ) {$row = 8;continue;}
if ($borderData[$i]['clientN'] > $d['clientNum']     ) {$row = 9;continue;}
break;        
}
                      
?>

<p>
<b> Рекомендованная сумма: <?= $borderData[$n]['recomended'] ?> </b>
<p>
<div class="container" style="overflow: auto;">      
<table class='table table-bordered'>
<thead>
<tr>
 <th>№</th>     
 <th >Наименование </th>      
 <?php
 $bg = "";
    for ($i=0; $i< $bN; $i++)
    {        
     if ($n==$i) $bg = "style='background-color: Silver;'";
     else $bg = "";     
     echo "<th ".$bg.">".$borderData[$i]['recomended']."</th>";
    }
 ?>
  <th >из СRМ</th>     
</tr>
</thead>     

  <tbody>         
        <tr>
        <td>1</td>     
        <td>Выручка по фирме</td>     
 
        <?php
            $max= $borderData[0]['totalS'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['totalS'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['totalS'];  $val = number_format($borderData[$i]['totalS'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==1) echo "style='background-color: Silver;'";  ?> ><?= number_format($totalData['oplata'],0,'.','&nbsp;') ?></td>        
        </tr>
        
        <tr>
        <td>2</td>     
        <td>К-во лично <br>выставленных счетов</td>     
        <?php
            $max= $borderData[0]['schetN'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['schetN'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['schetN'];  $val = number_format($borderData[$i]['schetN'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==2) echo "style='background-color: Silver;'";  ?> ><?= $d['schetNew'] ?></td>        
        </tr>
        <tr>
        <td>3</td>     
        <td>Получено оплат</td>     
        <?php
            $max= $borderData[0]['oplateN'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['oplateN'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['oplateN'];  $val = number_format($borderData[$i]['oplateN'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==3) echo "style='background-color: Silver;'";  ?> ><?= $d['oplatNum'] ?></td>
        </tr>

        <tr>
        <td>4</td>     
        <td>Произведено личных отгрузок</td>     
        <?php
            $max= $borderData[0]['supplyN'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['supplyN'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['supplyN'];  $val = number_format($borderData[$i]['supplyN'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==4) echo "style='background-color: Silver;'";  ?> ><?= $d['supplyNum'] ?></td>        
        </tr>

        <tr>
        <td>5</td>     
        <td>Сумма лично выставленных счетов</td>     
        <?php
            $max= $borderData[0]['schetS'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['schetS'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['schetS'];  $val = number_format($borderData[$i]['schetS'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==5) echo "style='background-color: Silver;'";  ?> ><?=  number_format($d['schetSum'],0,'.','&nbsp;') ?></td>        
        </tr>

        <tr>
        <td>6</td>     
        <td>Личная выручка</td>     
        <?php
            $max= $borderData[0]['oplateS'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['oplateS'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['oplateS'];  $val = number_format($borderData[$i]['oplateS'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td<?php if ($row ==6) echo "style='background-color: Silver;'";  ?> ><?=  number_format($d['oplataSum'],0,'.','&nbsp;') ?></td>        
        </tr>

        <tr>
        <td>7</td>     
        <td>Сумма личных отгрузок</td>     
        <?php
            $max= $borderData[0]['supplyS'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['supplyS'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['supplyS'];  $val = number_format($borderData[$i]['supplyS'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==7) echo "style='background-color: Silver;'";  ?> ><?=  number_format($d['supplySum'],0,'.','&nbsp;') ?></td>        
        </tr>

        <tr>
        <td>8</td>     
        <td>Активность</td>     
        <?php
            $max= $borderData[0]['contactN'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['contactN'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['contactN'];  $val = number_format($borderData[$i]['contactN'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==8) echo "style='background-color: Silver;'";  ?> ><?= $d['allActivity'] ?></td>        
        </tr>

        <tr>
        <td>9</td>     
        <td>Охвачено клиентов</td>     
        <?php
            $max= $borderData[0]['clientN'];
            for ($i=0; $i< $bN; $i++)
            {   
                $val=$borderData[$i]['clientN'];        
                if ($val == 0) $val = "менее ".number_format($max,0,'.','&nbsp;');
                else { $max= $borderData[$i]['clientN'];  $val = number_format($borderData[$i]['clientN'],0,'.','&nbsp;'); }
                echo "<td>".$val."</td>";
            }
        ?> 
        <td <?php if ($row ==9) echo "style='background-color: Silver;'";  ?> ><?= $d['clientNum'] ?></td>
        </tr>
        
  </tbody>      
</table> 
</div>     

