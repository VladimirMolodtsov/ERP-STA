<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;


$this->title = 'Контроль бухгалтерии. Управление';

$precision = 0;
$dateformat = "d.m";

?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>
.gridcell_ {
    display: block;
    font-size: 12px;	
    text-align: center;
}
.gridcell {
	width: 120px;		
	height: 100%;
    display: block;
    font-size: 12px;	
    text-align: center;
    word-wrap: break-word;
	/*background:DarkSlateGrey;*/
}	
.gridcell:hover{
	background:Silver;
    cursor: pointer;
	color:#FFFFFF;
}
.editcell{
   width: 120px;
   display:none;
   white-space: nowrap;
   background:White;
}
</style>

<script type="text/javascript">


</script>


<h3><?= Html::encode($this->title) ?></h3>
 <?php 
    $controlData   =$model->getTotalControlData();
    

    
?>

<table class='table table-striped table-small'>
<thead>
<tr>
    <th colspan=7>Текущий</th>
    <th colspan=6>Предшествующий</th>
</tr>

<tr>
    <th width='30px'></th>
    <th width='30px'></th>
    <th></th>
    <th>Дата</th>
    <th>Нам</th>
    <th>Мы</th>
    <th>Реальные</th>
    <th>Все</th>                
    
    <th></th>
    <th>Дата</th>
    <th>Нам</th>
    <th>Мы</th>
    <th>Реальные</th>
    <th>Все</th>                

</tr>
</thead>
<tbody>


</tbody>
<tr>
    <td><a href='#' onclick='openWin("head/cfg-sclad-control","childWin")' ><span class="glyphicon  glyphicon-cog" aria-hidden='true'></span></a></td>
    <td><a href='index.php?r=data/sync-sclad-control&isPrev=0' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a></td>
    <td>Склад</td>
    <td><?= date($dateformat, strtotime($controlData['scladDate'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['scladReal'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['scladAll'],$precision,'.','&nbsp;') ?></td>
    
    <td>&nbsp;</td>
    <td><?= date($dateformat, strtotime($controlData['scladDatePrev'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['scladRealPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['scladAllPrev'],$precision,'.','&nbsp;') ?></td>
    
</tr>

<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Отгрузки</td>
    <td><?= date($dateformat, strtotime($controlData['supplyDate'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supplyReal'],$precision,'.','&nbsp;') ?></td>

    <td></td>    
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supplyRealPrev'],$precision,'.','&nbsp;') ?></td>
    <td></td>

</tr>
<tr>
    <td><a href='#' onclick='openWin("head/cfg-sverka-control","childWin")' ><span class="glyphicon  glyphicon-cog" aria-hidden='true'></span></a></td>
    <td><a href='index.php?r=data/sync-sverka&isPrev=0' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a></td>
    <td>Долг Клиенты</td>
    <td><?= date($dateformat, strtotime($controlData['clientDate'])) ?></td>
    <td><?= number_format($controlData['clientDebet'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['clientCredit'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format(($controlData['clientCredit']+$controlData['clientDebet']),$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['clientAll'],$precision,'.','&nbsp;') ?></td>
    
    <td></td>
    <td><?= date($dateformat, strtotime($controlData['clientDatePrev'])) ?></td>
    <td><?= number_format($controlData['clientDebetPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['clientCreditPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format(($controlData['clientCreditPrev']+$controlData['clientDebetPrev']),$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['clientAllPrev'],$precision,'.','&nbsp;') ?></td>
    
    
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Приход денег</td>
    <td><?= date($dateformat, strtotime($controlData['oplateDate'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['oplateAll'],$precision,'.','&nbsp;') ?></td>
    <td></td>

    <td></td>
    <td><?= date($dateformat, strtotime($controlData['oplateDatePrev'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['oplateAllPrev'],$precision,'.','&nbsp;') ?></td>
    <td></td>
    
</tr>

<tr>
    <td><a href='#' onclick='openWin("head/cfg-bank-control","childWin")' ><span class="glyphicon  glyphicon-cog" aria-hidden='true'></span></a></td>
    <td><a href='index.php?r=data/sync-bank&isPrev=0' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a></td>
    <td>Деньги (Банк)</td>
    <td><?= date($dateformat, strtotime($controlData['cashDate'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['cashReal'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['cashAll'],$precision,'.','&nbsp;') ?></td>
    
    <td>&nbsp;</td>
    <td><?= date($dateformat, strtotime($controlData['cashDatePrev'] ))?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['cashRealPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['cashAllPrev'],$precision,'.','&nbsp;') ?></td>
    
    
</tr>

<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Оплаты поставщикам</td>
    <td><?= date($dateformat, strtotime($controlData['supOplateDate'] )) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supOplateReal'],$precision,'.','&nbsp;') ?></td>
    <td></td>

    <td>&nbsp;</td>
    <td><?= date($dateformat, strtotime($controlData['supOplateDatePrev'] )) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supOplateRealPrev'],$precision,'.','&nbsp;') ?></td>
    <td></td>

</tr>

<tr>
    <td><a href='#' onclick='openWin("head/cfg-sverka-control","childWin")' ><span class="glyphicon  glyphicon-cog" aria-hidden='true'></span></a></td>
    <td><a href='index.php?r=data/sync-sverka' target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a></td>
    <td>Долг поставщики</td>
    <td><?= date($dateformat, strtotime($controlData['supplierDate'])) ?></td>
    <td><?= number_format($controlData['supplierDebet'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['supplierCredit'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format(($controlData['supplierDebet']+$controlData['supplierCredit']),$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['supplierAll'],$precision,'.','&nbsp;') ?></td>
    
    <td></td>
    <td><?= date($dateformat, strtotime($controlData['supplierDate'])) ?></td>
    <td><?= number_format($controlData['supplierDebetPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['supplierCreditPrev'],$precision,'.','&nbsp;') ?></td>
    <td><?= number_format(($controlData['supplierDebetPrev']+$controlData['supplierCreditPrev']),$precision,'.','&nbsp;') ?></td>
    <td><?= number_format($controlData['supplierAllPrev'],$precision,'.','&nbsp;') ?></td>
    
</tr>

<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Приход товара</td>
    <td><?= date($dateformat, strtotime($controlData['supSupplyDate'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supSupplyReal'],$precision,'.','&nbsp;') ?></td>
    <td></td>

    <td>&nbsp;</td>
    <td><?= date($dateformat, strtotime($controlData['supSupplyDatePrev'])) ?></td>
    <td></td>
    <td></td>
    <td><?= number_format($controlData['supSupplyRealPrev'],$precision,'.','&nbsp;') ?></td>
    <td></td>
    
</tr>

</table>
    
    
    

