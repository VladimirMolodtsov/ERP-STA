<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

$this->title = 'Контроль состояния задач';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>

table, th, td {
    border: 0px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
.leaf {
    height: 120px; /* высота нашего блока */
    width:  150px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    color: Blue;
    background:WhiteSmoke;
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
    background: Blue;
}

.leaf-txt {    
    font-size:15px;
}
.leaf-val {    
    font-size:25px;
}
.leaf-sub {    
    font-size:15px;
    text-align: center;
    /*color:DimGrey;*/
}
.child {
  padding:5px;
  text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}

</style>

<table border='0' width='1140px'>

<tr>
<th style='padding-left:50px;'> Деньги </th>
<th style='padding-left:50px;'> Товар  </th>
<th style='padding-left:50px;'> Продажи  </th>
<th style='padding-left:50px;'> Кадры  </th>
</tr>
<?php 
$bankState = $model->getBankState(); 
$color= "";
if ($bankState['status'] == 4 ) $color='color:White;background:Crimson;'; 
?>
<td>
<a  class='btn btn-primary leaf' style='<?= $color ?>' href='index.php?r=/bank/buh/buh-day-detail'>
        <div class='leaf-txt'>Работа с банком </div>
        <br>
        <div class='leaf-val'><?= $bankState['name'] ?></div> 
        <div class='leaf-sub'><?= $bankState['period'] ?></div>
</a>
</td>


<?php $purchaseState = $model->getPurchaseState(); ?>
<td>
<a  class='btn btn-primary leaf'  href='index.php?r=store/purchase-table&mode=37'>
        <div class='leaf-txt'>Закупки</div>
        <div class='leaf-val'><?= $purchaseState['requestInSogl']+ $purchaseState['purchaseInSogl']?></div> 
        <div class='leaf-sub'>Заявки:&nbsp;&nbsp;<?=$purchaseState['requestInSogl']?></div>
        <div class='leaf-sub'>Закупки:&nbsp;<?=$purchaseState['purchaseInSogl']?></div>
</a>
</td>


<td>
<?php
$marketState=$model->getMarketState();
?>
<a  class='btn btn-primary leaf' href='index.php?r=site/head-start'>
        <div class='leaf-txt'>Отдел продаж</div>
        <div class='leaf-val'><?=$marketState['d_activity']?></div> 
        <div class='leaf-sub'>Оплаты:&nbsp;&nbsp;<?=$marketState['d_oplata']?></div>
        <div class='leaf-sub'>Отгрузки:&nbsp;<?=$marketState['d_supply']?></div>
        
</a>
</td>

<td>
<a  class='btn btn-primary leaf' style='background:WhiteSmoke;' href='index.php?r=site%2Fpersonal-start'>
        <div class='leaf-txt'>Отдел  кадров</div>
        <div class='leaf-val'></div> 
        <div class='leaf-sub' ></div>
</a>
</td>


</tr>
<!----------------------->
<tr>

<?php 
$docState = $model->getDocState(); 
$color= "";
if ($docState['status'] == 4 ) $color='color:White;background:Crimson;'; 
?>
<td>
<a  class='btn btn-primary leaf' style='<?= $color ?>' href='index.php?r=/bank/buh/buh-day-detail'>
        <div class='leaf-txt'>Первичка </div>
        <br>
        <div class='leaf-val'><?= $docState['name'] ?></div> 
        <div class='leaf-sub'><?= $docState['period'] ?></div>
</a>


</td>
<td>
<a  class='btn btn-primary leaf'  href='index.php?r=store/head-sclad&detail=12'>
        <div class='leaf-txt'>Склад</div>
        <br>
        <div class='leaf-val' style='font-size:20px;' ><?= number_format($purchaseState['amount'],0, "." ,'&nbsp;') ?></div> 
        <div class='leaf-sub' ><?=number_format($purchaseState['storeStatus'],0, "." ,'&nbsp;')?>%</div>
</a>
</td>

<td></td>
<td></td>

</tr>

<!----------------------->

<?php 
$buhState = $model->getBuhState(); 
$color= "";
if ($buhState['status'] == 4 ) $color='color:White;background:Crimson;'; 
?>
<td>
<a  class='btn btn-primary leaf' style='<?= $color ?>' href='index.php?r=/bank/buh/buh-day-detail'>
        <div class='leaf-txt'>Бухгалтер </div>
        <br>
        <div class='leaf-val'><?= $buhState['name'] ?></div> 
        <div class='leaf-sub'><?= $buhState['period'] ?></div>
</a>
</td>

<td></td>
<td></td>
<td></td>

</tr>

</table>






</table>








