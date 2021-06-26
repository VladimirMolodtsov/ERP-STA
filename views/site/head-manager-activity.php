<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Активность по менеджерам';

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & (0x0020|0x0008|0x0100))) {return;}

    $cur = $model->fixDate (['m' => (date('n')-$model->monthShift),   'y' => date('Y')]);
    $pr1 = $model->fixDate (['m' => (date('n')-$model->monthShift-1), 'y' => date('Y')]);
    $pr2 = $model->fixDate (['m' => (date('n')-$model->monthShift-2), 'y' => date('Y')]);


    $itogo = array();
    $itogo ['oplateS'] =0;
    $itogo ['efficient'] =0;
    $itogo ['allActivity'] =0;
    $itogo ['uniqClient'] =0;    
    $itogo ['inLead'] =0;
    $itogo ['inContact'] =0;
    $itogo ['inDeals'] =0;
    $itogo ['schetN'] =0;
    $itogo ['contatToSchetP'] =0;
    $itogo ['schetS'] =0;
    $itogo ['schetToOplataP'] =0;
    $itogo ['mediumSchet'] =0;
    $itogo ['month0'] =0;
    $itogo ['month1'] =0;
    $itogo ['month2'] =0;
    
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
function statDetail (id)
{
    openWin('site/stat-detail&monthShift=<?= $model->monthShift ?>&id='+id,'statWin');
}


function oplataDetail (id)
{
    openWin('site/manager-oplata-list&y=<?= $cur['y'] ?>&m=<?= $cur['m'] ?>&uid='+id,'statWin');
}

function clientDetail (id)
{
    openWin('site/manager-org-activity&y=<?= $cur['y'] ?>&m=<?= $cur['m'] ?>&uid='+id,'statWin');
}

function schetDetail (id)
{
    openWin('site/manager-schet-activity&y=<?= $cur['y'] ?>&m=<?= $cur['m'] ?>&uid='+id,'statWin');
}

function showResult (id)
{
    openWin('site/manager-result&noframe=1&monthShift=<?= $model->monthShift ?>&y=<?= $cur['y'] ?>&m=<?= $cur['m'] ?>&uid='+id,'statWin');
}


</script >

<h3><?= Html::encode($this->title) ?></h3>

<div class="container" style="overflow: auto;">      
<table class='table table-bordered'>
<thead>
<tr>
 <th>&nbsp;</th>     
 <th colspan='4' style='text-align:center;'>
 <a href='index.php?r=site/head-manager-activity&noframe=1&monthShift=<?= ($model->monthShift+1) ?>'> <?= date('M', mktime(0, 0, 0, $pr1['m'], 1, $pr1['y'])) ?> << </a>
 <span style='margin-left:30px;margin-right:30px;'> <?= date('M.Y', mktime(0, 0, 0, $cur['m'], 1, $cur['y'])) ?> </span>
 <?php if ($model->monthShift > 0) { 
 $next = $model->fixDate (['m' => (date('n')-($model->monthShift-1)),   'y' => date('Y')]);
 ?>
 <a href='index.php?r=site/head-manager-activity&noframe=1&monthShift=<?= ($model->monthShift-1) ?>'> >> <?= date('M', mktime(0, 0, 0, $next['m'], 1, $next['y'])) ?> </a>
 <?php } ?>
 </th>     
 <th colspan='3'>Структура  Активности </th>     
 <th colspan='6'>Конверсия </th>     
 <th colspan='3'>По месяцам</th>     
</tr>

<tr>
 <th>&nbsp;</th>     
 <th>Менеджер</th>     
 
 <th>Выруч.</th>     
 <th>Произв.</th>     
 <th>Клиен.</th>     
 <th>Активн.</th>     
 
 <th>Лиды</th>     
 <th>Контакты</th>     
 <th>Сделки</th>     
 
 <th>К-во <br>Счетов</th>     
 <th>Конв.<br>актив.</th>     
 <th>Сумма<br>Счетов</th>     
 <th>Сумма<br>Оплат</th>     
 <th>Конв.<br>Счетов</th>     
 <th>Средн. <br>чек</th>     
 
 
 
 <th><?= date('M', mktime(0, 0, 0, $pr2['m'], 1, $pr2['y'])) ?>  </th>     
 <th><?= date('M', mktime(0, 0, 0, $pr1['m'], 1, $pr1['y'])) ?>  </th>     
 <th><?= date('M', mktime(0, 0, 0, $cur['m'], 1, $cur['y'])) ?>  </th>     

 
</tr>
</thead>     

  <tbody> 
  <?php for($i=0; $i<count($model->dataArray); $i++ ) { 
    
//    $deals = $model->dataArray[$i]['allActivity']+$model->dataArray[$i]['inLead']+$model->dataArray[$i]['inContact']+$model->dataArray[$i]['inDeals']+$model->dataArray[$i]['schetN'] +$model->dataArray[$i]['oplateS'];
//    if ($deals == 0) continue;    
    $itogo ['oplateS'] += $model->dataArray[$i]['oplateS'];
    $itogo ['allActivity'] +=$model->dataArray[$i]['allActivity'];
    $itogo ['uniqClient'] +=$model->dataArray[$i]['uniqClient'];
    $itogo ['inLead'] +=$model->dataArray[$i]['inLead'];
    $itogo ['inContact'] +=$model->dataArray[$i]['inContact'];
    $itogo ['inDeals'] +=$model->dataArray[$i]['inDeals'];
    $itogo ['schetN'] +=$model->dataArray[$i]['schetN'];
    $itogo ['schetS'] +=$model->dataArray[$i]['schetS'];
    $itogo ['month0'] +=$model->dataArray[$i]['month'][0];
    $itogo ['month1'] +=$model->dataArray[$i]['month'][1];
    $itogo ['month2'] +=$model->dataArray[$i]['month'][2];

  ?>    
      <tr>
        <td><a href='#' onclick='javascript:showResult(<?= $model->dataArray[$i]['id']?>)'>
        <span class="glyphicon glyphicon-user" aria-hidden="true"></span></a>
        </td>     

        <td><a href='#' onclick='javascript:statDetail(<?= $model->dataArray[$i]['id']?>)'><?= $model->dataArray[$i]['userFIO']?></a>
        </td>     

        <td><a href='#' onclick='javascript:oplataDetail(<?= $model->dataArray[$i]['id']?>)'> <?= number_format($model->dataArray[$i]['oplateS']/1000,0,'.','&nbsp;') ?></a></td>     
        <td><?= number_format($model->dataArray[$i]['efficient'],0,'.','&nbsp;') ?></td>     
        <td><a href='#' onclick='javascript:clientDetail(<?= $model->dataArray[$i]['id']?>)'> <?= number_format($model->dataArray[$i]['uniqClient'],0,'.','&nbsp;') ?></a></td>     
        <td><?= number_format($model->dataArray[$i]['allActivity'],0,'.','&nbsp;') ?></td>     

        <td class='marked'><?= number_format($model->dataArray[$i]['inLeadP'],1,'.','&nbsp;') ?>%</td>     
        <td class='marked'><?= number_format($model->dataArray[$i]['inContactP'],1,'.','&nbsp;') ?>%</td>     
        <td class='marked'><?= number_format($model->dataArray[$i]['inZakazP'],1,'.','&nbsp;') ?>%</td>     
        
        <td><a href='#' onclick='javascript:schetDetail(<?= $model->dataArray[$i]['id']?>)'> <?= number_format($model->dataArray[$i]['schetN'],0,'.','&nbsp;') ?></a></td>     
        <td class='marked'><?= number_format($model->dataArray[$i]['contatToSchetP'],1,'.','&nbsp;') ?>%</td>     
        <td><?= number_format($model->dataArray[$i]['schetS']/1000,0,'.','&nbsp;') ?></td>     
        <td><?= number_format($model->dataArray[$i]['oplateS']/1000,0,'.','&nbsp;') ?></td>     
        <td class='marked'><?= number_format($model->dataArray[$i]['schetToOplataP'],1,'.','&nbsp;') ?>%</td>     
        <td><?= number_format($model->dataArray[$i]['mediumSchet']/1000,0,'.','&nbsp;') ?></td>     
        
        <td><?= number_format($model->dataArray[$i]['month'][0],0,'.','&nbsp;') ?></td>     
        <td><?= number_format($model->dataArray[$i]['month'][1],0,'.','&nbsp;') ?></td>     
        <td><?= number_format($model->dataArray[$i]['month'][2],0,'.','&nbsp;') ?></td>     
   
        
    </tr>

  <?php }

  
 if ($itogo['allActivity'] > 0)
 {
    $itogo['efficient'] = $itogo['oplateS']/$itogo['allActivity'];
    $itogo ['inLeadP'] =100*$itogo ['inLead']/$itogo['allActivity'] ;
    $itogo ['inContactP'] =100*$itogo ['inContact']/$itogo['allActivity'] ;
    $itogo ['inZakazP'] =100*$itogo ['inDeals']/$itogo['allActivity'] ;
    $itogo ['contatToSchetP'] =100*$itogo ['schetN']/$itogo['allActivity'] ;
 }
  else 
  {
    $itogo['efficient'] =0;
    $itogo ['inLeadP'] =0;
    $itogo ['inContactP'] =0;
    $itogo ['inZakazP'] =0;      
    $itogo['contatToSchetP'] = 0;
  }
 
    if ($itogo['schetS'] > 0 )  $itogo['schetToOplataP'] = 100*$itogo['oplateS']/$itogo['schetS'];
                           else $itogo['schetToOplataP'] = 0;
                           
    if ($itogo['schetN'] > 0)    $itogo['mediumSchet'] = $itogo['schetS']/$itogo['schetN'];
                           else $itogo['mediumSchet'] = 0;
   
 ?>    
  
        <tr>
        <th>&nbsp;</th>     
        <td class='itogo'>Итого</a></td>     
        
        <td class='itogo'><?= number_format($itogo['oplateS']/1000,0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['efficient'],0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['uniqClient'],0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['allActivity'],0,'.','&nbsp;') ?></td>     

        <td class='itogo marked'><?= number_format($itogo['inLeadP'],1,'.','&nbsp;') ?>% (<?= $itogo['inLead'] ?> )</td>     
        <td class='itogo marked'><?= number_format($itogo['inContactP'],1,'.','&nbsp;') ?>% (<?= $itogo['inContact'] ?>)</td>     
        <td class='itogo marked'><?= number_format($itogo['inZakazP'],1,'.','&nbsp;') ?>% (<?= $itogo['inDeals'] ?>)</td>     
        
        <td class='itogo'><?= number_format($itogo['schetN'],0,'.','&nbsp;') ?></td>     
        <td class='itogo marked'><?= number_format($itogo['contatToSchetP'],1,'.','&nbsp;') ?>%</td>     
        <td class='itogo'><?= number_format($itogo['schetS']/1000,0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['oplateS']/1000,0,'.','&nbsp;') ?></td>     
        <td class='itogo marked'><?= number_format($itogo['schetToOplataP'],1,'.','&nbsp;') ?>%</td>     
        <td class='itogo'><?= number_format($itogo['mediumSchet']/1000,0,'.','&nbsp;') ?></td>     
        
        <td class='itogo'><?= number_format($itogo['month0'],0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['month1'],0,'.','&nbsp;') ?></td>     
        <td class='itogo'><?= number_format($itogo['month2'],0,'.','&nbsp;') ?></td>     
   
        
    </tr>

  
  </tbody>      
</table> 
</div>     

<?php 
$nday= date('t',  mktime(0, 0, 0, $cur['m'], 1, $cur['y'])); 
$itogoActive = array();
for ($i=0; $i<=$nday; $i++ )$itogoActive [$i]=0;
$activityDayList = $model->prepareManagerDayActivityData();
?>

<div class="container" style="overflow: auto;">      
<table class='table table-bordered'>
<thead>
<tr>
 <th>Менеджер</th>     
 
 <?php for ($i=1; $i<=$nday; $i++ )
 {
 echo "<th>".$i."</th>\n";
 }
 ?>  
</tr>
</thead>     

  <tbody> 
  <?php for($i=0; $i<count($activityDayList); $i++ ) {   
  
//  if ($activityDayList[$i]['S'] == 0) continue;
  ?>    
    <tr>
        <td><a href='#' onclick='javascript:statDetail(<?= $activityDayList[$i]['id']?>)'>  <?= $activityDayList[$i]['userFIO'] ?> </a></td>     

    <?php for ($j=1; $j<=$nday; $j++ )
    {
       $itogoActive [$j]+= $activityDayList[$i][$j];
        $style="";
        if ($activityDayList[$i][$j] >0) $style=" style='background-color: LightGray;'";
        echo "<td ".$style.">".$activityDayList[$i][$j]."</td>\n";
    }
    ?>  
        
    </tr>

  <?php } /*End of data output*/ ?>    
  
  <tr>
   <td class='itogo'>Итого</a></td>  
   
   <?php for ($j=1; $j<=$nday; $j++ )
    {
        $style="";
        if ($itogoActive[$j] >0) $style=" style='background-color: LightGray;'";
        echo "<td class='itogo' ".$style.">".$itogoActive[$j]."</td>\n";
    }
    ?>  
    
    
   </tr>
  
  </tbody>      
</table> 
</div>     




<?php
/*
if(!empty($model->debug))
{
echo "<pre>";
print_r ($model->monthShift);
echo "/n";
print_r ($model->debug);
echo "/n";
print_r($activityDayList);
echo "</pre>";

}*/
?>


