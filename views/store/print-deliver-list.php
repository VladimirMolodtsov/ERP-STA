<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$curUser=Yii::$app->user->identity;
$this->title = 'Список заданий на доставку';

?>
<style>
.table-locale{
  font-size:10px;   
  border: 2px #000 solid;
}

#main-table thead > tr > th, #main-table tbody > tr > td {  
  border: 2px #000 solid;
}

</style>

<script type="text/javascript">

</script>

<h3><?= Html::encode($this->title) ?></h3>

<div style='position: absolute; left:5px; padding:5px;'>
<table  class='table table-bordered table-locale' id='main-table'>
<thead>
    <th width='50px'>Дата доставки</th>
    <th width='50px'>Заявка №</th>
    <th width='150px'>Контрагент</th>    
    <th width='150px'>Адрес отгрузки</th>
    <th width='150px'>Адрес доставки</th>    
    <th width='50px'>Что делать</th>
    <th width='50px'>Номер УПД</th>
    <th width='50px'>Счет</th>
    <th width='50px'>Оплата</th>
    
    <th width='250px'>Товар</th>    
    <th width='50px'>Вес, кг</th>    
    <th >Комментарий</th>
   
</thead>


<tbody>
</div>
<?php
//print_r($deliversListData);
$cnt = count($deliversListData); 
$itogoWeight=0;

for ($i=0; $i < $cnt; $i++){
$itogoWeight+=floatval($deliversListData[$i]['requestTotalWeight']);
?>
<tr>
    <td ><?= date("d.m.y", strtotime($deliversListData[$i]['requestDateReal'])) ?></td>
    <td ><?= $deliversListData[$i]['requestNum'] ?></td>           
    <td ><?= $deliversListData[$i]['title'] ?></td>       
    <td ><?= $deliversListData[$i]['requestScladAdress'] ?></td>
    <td ><?= $deliversListData[$i]['requestAdress'] ?></td>    
        <td ><?php 
                    switch ($deliversListData[$i]['supplyType'])
                    {
                      case 1:
                        echo "Доставка клиенту";
                      break;
                      case 2:
                        echo "Перемещение";
                      break;
                      
                      case 4:
                        echo "Документы";
                      break;
                      case 5:
                        echo "Доставка от поставщика";
                      break;

                      default:
                        echo "&nbsp;";
                      break;
                    }
        ?>
    </td>                        

    <td ><?PHP echo $deliversListData[$i]['requestUPD']; 
        if($deliversListData[$i]['isRefSupply'] ==1) 
            echo "&nbsp;<font color='Black'><span class='glyphicon glyphicon-ok-sign' aria-hidden='true'></span></font>";    
    ?>
    
    </td>

    <td ><?PHP 

    echo $deliversListData[$i]['schetNum']; 
        if(!empty ($deliversListData[$i]['schetDate'])) 
            echo "&nbsp;от&nbsp;".date("d.m.Y",strtotime($deliversListData[$i]['schetDate']));    
    ?>
    
    </td>
    
    
    <td ><?PHP
            if (!empty($deliversListData[$i]['refSchet']))
                {
                $oplataList=Yii::$app->db->createCommand(
                'SELECT MAX(oplateNum) as oplateNum,  sum(oplateSumm) as oplateSumm, max(oplateDate) as oplateDate  from {{%oplata}} where refSchet=:refSchet ', 
                    [':refSchet' => $deliversListData[$i]['refSchet'] ])->queryOne();
                
                if (!empty($oplataList) && $oplataList['oplateSumm'] > 0) $v = "Оплачено ".number_format($oplataList['oplateSumm'], 2, '.', '&nbsp;')."  № ".$oplataList['oplateNum']." ".date("d.m.Y", strtotime($oplataList['oplateDate'])) ; 
                                    else $v = "";
                                    
                echo $v;                    
                }  
         ?>
    </td>
    
    <td ><?= $deliversListData[$i]['wareList'] ?></td>
    
    
    <td ><?= $deliversListData[$i]['requestTotalWeight'] ?></td>       
    <td ><?= $deliversListData[$i]['requestNote'] ?></td>    
          
    
    
</tr>
<?php
//<td ><?= $deliversListData[$i]['deliverSum'] </td>       
}
?>


<tr>
    <td colspan='8'align='right'><b>Итого вес:</b></td>    
    <td ><b><?= $itogoWeight ?></b></td>
    <td ></td>                      
</tr>

    
</tbody>
</table>
