<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - отслеживание загрузок выписок';
$this->params['breadcrumbs'][] = $this->title;

$modelLog->resetReportDataTime();
$logArray = $modelLog->getLogData();



    $curMonth = $modelLog->reportMonth;
    $curYear =  $modelLog->reportYear;
 
    $nextYear  = $curYear;  
    $nextMonth = $curMonth+1;
    if ($nextMonth == 13) {$nextMonth=1; $nextYear++;}    
     
    $prevYear  = $curYear;   
    $prevMonth = $curMonth-1;
    if ($nextMonth == 0) {$nextMonth=12; $nextYear--;}    

    $logPrevCount=$modelLog->getLogCount($prevMonth, $prevYear);
    $logNextCount=$modelLog->getLogCount($nextMonth, $nextYear);
    
    
?>




<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<?php
/*массив с данными, день, маркер действия, номер этапа */
//showRes($logArray, 1, 'load', 0)
function showRes($logArray, $d, $action, $etap)
{    
 $list = $logArray['list'][$action];//
 $ref  = $logArray['execute'][$d][$action];
 
 /*Еще не наступило*/
    if ($logArray['execute'][$d]['dateTime'] > time()) 
    {
        echo "<td >  </td>";   
        return;
    }
    
    $ptr = $ref[$etap]['v']; // ищем ссылку   
    if ( $ptr == -1) // Не нашли 
    {
        echo "<td style='background-color: Crimson;'>&nbsp;</td>";   
        return;
    }
 
 //print_r($list[$ptr]);
 
 $dateTime =  strtotime($list[$ptr]['actionDateTime']); 
 $userFIO  =  $list[$ptr]['userFIO']; 
 $refUser  =  $list[$ptr]['refUser']; 
 
 
 if ($dateTime > $ref[$etap]['w'])
 {
     echo "<td style='background-color: Orange; font-size:20px;'><a href='#' title='".$userFIO." ".date("d.m H:i",$dateTime +4*3600)." / ".date("d.m H:i",$ref[$etap]['w'] +4*3600)."' >*</a></td>";   
     return;
 }
     echo "<td style='background-color: DarkGreen;  font-size:20px;'><a href='#' title='".$userFIO." ".date("d.m H:i",$dateTime +4*3600)." / ".date("d.m H:i",$ref[$etap]['w'] +4*3600)."' >*</a></td>";   
     return;
    
}


//showRes($logArray, 6, 'load', 0);
?>


<div class ='row'>
   <div class ='col-md-1'>
   <?php if ($logPrevCount > 0) { ?>
       <a href="index.php?r=bank/operator/load-log&reportMonth=<?= $prevMonth ?>&reportYear=<?= $prevYear ?>" ><span class='glyphicon glyphicon-backward'></span></a>
     <?php } ?>  
   </div>
   <div class ='col-md-10' style='text-align:center'><?= date("F.Y", strtotime($curYear."-".$curMonth."-01")) ?></div>
   <div class ='col-md-1' style='text-align:right'>
    <?php if ($logNextCount > 0) { ?>  
       <a href="index.php?r=bank/operator/load-log&reportMonth=<?= $nextMonth ?>&reportYear=<?= $nextYear ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   <?php } ?>  
   </div>
</div>

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

<?php
 //  echo "<pre>";
 //  print_r($modelLog->borderArray);
 //  print_r($logArray);
 //  echo "</pre>";
   
?>


<hr>
<div class="item-header">Загруженные выписки:</div> 
<?= \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
             
           [
                'attribute' => 'uploadTime',
                'label'     => 'Загружена',
                'format' => 'raw', 
                //'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['uploadTime'])+4*3600);
               }
               
            ],         

            [
                'attribute' => 'creationDate',
                'label'     => 'Дата создания',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return "<a href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$model['id']."','childWin');\" >".date("d.m.Y H:i:s", strtotime($model['creationDate'])+4*3600)."</a>";
               }
                       
            ],            
                                    
            [
                'attribute' => 'userFIO',
                'label'     => 'Оператор',
                'format' => 'raw',            
            ],            

            /****/
        ],
    ]
); 

?>


