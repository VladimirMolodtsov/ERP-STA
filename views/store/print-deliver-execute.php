<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$curUser=Yii::$app->user->identity;
$this->title = 'Затраты на доставку';

?>
<style>
.table-locale{
  font-size:11px;   
  border: 2px #000 solid;
}

#main-table thead > tr > th, #main-table tbody > tr > td {  
  border: 2px #000 solid;
}

</style>

<script type="text/javascript">

</script>


<h3><?= Html::encode($this->title) ?></h3>

<?php
$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    
echo "<p> За период c ".date("d.m.Y",strtotime($model->dFrom))." по ".date("d.m.Y",strtotime($model->dTo))." </p>";
?>

<div style='position: absolute; left:5px; padding:5px;'>
<table  class='table table-bordered table-locale' id='main-table'>
<thead>
    <th width='50px'>Дата доставки</th>
    <th width='50px'>Заявка №</th>
    <th width='50px'>Счет</th>
    <th width='50px'>Затраты<br>водителя</th>
    <th width='50px'>Время<br>экспед.</th>
    <th width='50px'>Затраты<br>экспед.</th>
    <th width='50px'>Вес</th>
    <th width='50px'>Тип</th>
    <th width='150px'>Куда</th>
    <th width='150px'>Откуда</th>
    <th >Примечание</th>   
</thead>


<tbody>
</div>
<?php
//print_r($deliversListData);
$cnt = count($deliversListData); 
for ($i=0; $i < $cnt; $i++){
?>
<tr>
    <td ><?= date("d.m.y", strtotime($deliversListData[$i]['factDate'])) ?></td>
    <td ><?= $deliversListData[$i]['requestNum'] ?></td>           
    <td >
    <?php
         if (!empty($deliversListData[$i]['refSchet']))             
         {
           $strSql = "Select schetNum, schetDate from {{%schet}} where id =:refSchet";    
           $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $deliversListData[$i]['refSchet'],])->queryAll();                                                                        
           if (count($schetData) > 0)
           echo "клиент ". $schetData[0]['schetNum']  ;                    
         }

         if (!empty($deliversListData[$i]['refSupplierSchet']))
         {
           $strSql = "Select schetNum, schetDate from {{%supplier_schet_header}} where id =:refSchet";    
           $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $deliversListData[$i]['refSchet'],])->queryAll();                                                                        
           if (count($schetData) > 0)
           echo "поставщ. ". $schetData[0]['schetNum'] ;                     
         }
     ?>     
    </td>           
    <td ><?= $deliversListData[$i]['factValue'] ?></td>    
    <td ><?= $deliversListData[$i]['request_time'] ?></td>    
    <td ><?= $deliversListData[$i]['request_exp_value'] ?></td>    
    
    <td ><?= $deliversListData[$i]['factWeight'] ?></td>    
    
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
    <td ><?= $deliversListData[$i]['requestAdress'] ?></td>
    <td ><?= $deliversListData[$i]['requestScladAdress'] ?></td>
    <td ><?= $deliversListData[$i]['requestNote'] ?></td>    
          
    
    
</tr>
<?php
//<td ><?= $deliversListData[$i]['deliverSum'] </td>       
}
?>
    
</tbody>

</table>

<h4>Начисления</h4>
<div style='width:600px;'> 
<table class='table'> 
<body>
<tr>
    <td style='background:Silver;' colspan='4' ><b>Водитель</b></td>
</tr>
<tr>
    <td  align='right'>Суммарное время: <b><?= number_format(($model->itogoTime),0,'.','&nbsp;') ?></b> мин. </td>
    <td > Затраты: </td>   
    <td><?= $model->driveItog ?></td>    
</tr>

<tr>
    <td style='background:Silver;' colspan='4' ><b>Экспедитор</b></td>
</tr>

<tr>
    <td  align='right'> </td>   
    <td> Затраты </td>
    <td><?= $model->expCostItog ?></td>    
</tr>

<tr>
    <td align='right'>Суммарный вес: <b><?= number_format(($model->itogoWeight),0,'.','&nbsp;') ?></b> кг *
    <td><?= $model->valWeight ?> </td>     
    <td><?= $model->expWrkItog ?></td>        
</tr>

</table>
</div>
