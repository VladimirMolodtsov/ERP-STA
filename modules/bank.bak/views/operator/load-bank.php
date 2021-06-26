<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - загрузка выписки';
$this->params['breadcrumbs'][] = $this->title;

$now=date("dmY");
$prev=date("dmY", time()-24*3600);
$next=date("dmY", time()+24*3600);
 
$zero=strtotime (date("Y-m-d")." 00:00:00"); //на начало дня
$shift = intval((time() - $zero)/3600)+4; // сколько прошло с начала дня


 
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.action_ref {    
    color:Green;
}
</style>


<script type="text/javascript">

function loadExtaract()
{
 $('#loadFileDialog').modal('show'); 
}

</script> 


<table class='table table-striped' style='width:900px;'>
<thead>
<tr>
    <td width="75px"><b>Дедлайн</b></td>
    <td width="250px"><b>Действие</b></td>
    <td width="75px"></td>
    <td width="175px">Дата/время</td>
    <td>Приход</td>
    <td>Расход</td>
</tr>

</thead>
<tbody>
<tr>
    <td>9:00</td>     <td>Войти в систему</td>  <td><?= $model->showAction(1, "5:00")?> </td>
    <td></td>
    <td colspan='3'></td>
</tr>

<tr>
    <td>9-10</td>
    <td>Загрузить выписку</td>
    <td><?php
      if ($shift >= 9 && $shift < 12)
      echo "<a href='#' class='action_ref' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>";
      else {  echo "<font color='LightGray'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></font>";  }         
      $ref= $model->getExtractRef("9:00", "12:00");  
      $extractId=$ref['id'];
      if ($ref['id'] == 0) echo "&nbsp;<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "&nbsp;<a href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";    
     ?></td>    
    
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>
    
</tr>
<tr>
    <td>10-10</td>
    <td>Синхронизировать данные с 1С</td>
    <td><a class='action_ref' href="index.php?r=/bank/operator/sync-bank-operation&sd=<?= $prev ?>&ed=<?= $next ?>" target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a>&nbsp;
     <?php     
     $ref= $model->getOperationRef("09:00", "12:00");   
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a  class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";  
     ?>
    </td>    
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>

</tr>
<tr>
    <td>10-30</td>
    <td>Провести сверку</td>
    <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='3'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='3' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>
</tr>

<tr>
    <td>12-10</td>
    <td>Загрузить выписку</td>    
    <td><?php
    if ($shift >= 12 && $shift < 15){
      echo "<a class='action_ref' href='#' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>";    
      } 
      else {  echo "<font color='LightGray'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></font>";  }         
    $ref= $model->getExtractRef("12:00", "15:00");  
    $extractId=$ref['id'];
    if ($ref['id'] == 0) echo "&nbsp;<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "&nbsp;<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";        
    ?></td>  
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>
</tr>

<tr>
    <td>13-10</td>
    <td>Синхронизировать данные с 1С</td>
    <td><a class='action_ref' href="index.php?r=/bank/operator/sync-bank-operation&sd=<?= $now ?>&ed=<?= $next ?>" target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a>
     <?php     
     $ref= $model->getOperationRef("12:00", "15:00"); 
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";  
     ?>
    </td>
        <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>

</tr>
<tr>
    <td>13-30</td>
    <td>Провести сверку</td>
    <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='3'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='3' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>
    <td colspan='3'></td>
</tr>


<tr>
    <td>15-10</td>
    <td>Загрузить выписку</td>
    <td><?php
      if ($shift >= 15 && $shift < 17)
        echo "<a class='action_ref' href='#' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>";
      else {  echo "<font color='LightGray'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></font>";  }         
      $ref= $model->getExtractRef("15:00", "17:00");  
      $extractId=$ref['id'];
      if ($ref['id'] == 0) echo "&nbsp;<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "&nbsp;<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";    
    ?></td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>
</tr>
<tr>
    <td>16-10</td>
    <td>Синхронизировать данные с 1С</td>
    <td><a class='action_ref' href="index.php?r=/bank/operator/sync-bank-operation&sd=<?= $now ?>&ed=<?= $next ?>" target='_blank'><span class="glyphicon  glyphicon-refresh" aria-hidden='true'></span></a>
     <?php     
     $ref= $model->getOperationRef("15:00", "17:00");  
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";  
     ?>
    </td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>

</tr>
<tr>
    <td>16-30</td>
    <td>Провести сверку</td>
        <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='3'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='3' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>
    
</tr>

<tr>
    <td>17-10</td>
    <td>Загрузить выписку</td>
    <td><?php
      if ($shift >= 17 && $shift < 19)
        echo "<a class='action_ref' href='#' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>";
      else {  echo "<font color='LightGray'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></font>";  }         
      $ref= $model->getExtractRef("17:00", "19:00");  
      $extractId=$ref['id'];
      if ($ref['id'] == 0) echo "&nbsp;<font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "&nbsp;<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";    
    ?></td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
    }
    else "<td colspan='3'></td>"
    ?>
</tr>


</tbody>
</table>


<p>Загружаемый файл должен быть в формате xlsx и содержать выписку в формате предоставлемом Сбербанк. 
<?php
    if ($shift < 9 ||  $shift > 19)
    echo "<a href='#' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>";
?>
</p>


<?php
Modal::begin([
    'id' =>'loadFileDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],    
]);?><div style='width:650px'>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
  <?php
    echo $form->field($model, 'xlsxFile')->fileInput()->label('Выберите файл с выпиской');
  ?>
  <?= Html::submitButton('Загрузить выписку', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>


</div><?php
Modal::end();
?>




<hr>
<div class="item-header">Загруженные выписки:</div> 
<?php 
echo
 \yii\grid\GridView::widget(
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
                //'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'format' => 'raw',     
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
                'attribute' => 'creditTurn',
                'label'     => 'Поступления',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditTurn'],2,',','&nbsp;');
               }
                
            ],            
            
                        
            [
                'attribute' => 'debetTurn',
                'label'     => 'Расходы',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['debetTurn'],2,',','&nbsp;');
               }
                
            ],            
                         
            [
                'attribute' => 'userFIO',
                'label'     => 'Оператор',
                'format' => 'raw',            
            ],            

        
        ],
    ]
); 

?>


