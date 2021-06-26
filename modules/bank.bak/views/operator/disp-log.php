<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - контроль загрузки выписки';
$this->params['breadcrumbs'][] = $this->title;

$now=$model->showDate;

$prev=$model->showDate-24*3600;
$next=$model->showDate+24*3600;
 
 
$zero=strtotime (date("Y-m-d", $model->showDate)." 00:00:00"); //на начало дня
$shift = ((time() - $zero)/3600)+4; // сколько прошло с начала дня
//echo $shift;
//$loginArray = $borderArray['login'];
$loadArray  = $borderArray['load'];
$syncArray  = $borderArray['sync'];
$checkArray = $borderArray['check'];

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

<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=bank/operator/disp-log&showDate=<?= $prev ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-10' style='text-align:center'><?= date("d.F.Y", $now) ?></div>
   <div class ='col-md-1' style='text-align:right'>
    <?php if ($next < time()) { ?>  
       <a href="index.php?r=bank/operator/disp-log&showDate=<?= $next ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   <?php } ?>  
   </div>
</div>


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

    <td></td>     <td>Войти в систему</td>  <td> </td>
    <td></td>
    <td colspan='3'></td>
</tr>

<tr>
    <td><?= $loadArray [0][1] ?></td>
    <td>Загрузить выписку</td>
    <td><?php
      if     ($shift < $loadArray [0][0])                                $color= "LightGray";
      elseif ($shift >= $loadArray [0][1] && $shift < $loadArray [0][2]) $color= "Orange";
      else                                                               $color= "Crimson";
      $ref= $model->getExtractRef($loadArray [0][0], $loadArray [0][2]);  
      if ($ref['id'] == 0)       
           echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";        
      $extractId=$ref['id'];      
     ?></td>    
    
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"
    ?>
    
</tr>
<tr>
    <td><?= $syncArray[0][1] ?></td>
    <td>Синхронизировать данные с 1С</td>
    <td>
     <?php     
     
      if     ($shift <  $syncArray [0][0])                               $color= "LightGray";
      elseif ($shift >= $syncArray [0][1] && $shift < $syncArray [0][2]) $color= "Orange";
      else                                                               $color= "Crimson";

     
     if ($shift >= 9  && $shift < 11) $color= "LightGray";
     if ($shift >= 11 && $shift < 12) $color= "Orange";
     else                             $color= "Crimson";
     $ref= $model->getOperationRef($syncArray[0][0], $syncArray[0][2]);   
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a  class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";       
     ?>
    </td>    
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"
    ?>

</tr>
<tr>
    <td>10-30</td>
    <td>Провести сверку</td>
    <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='4'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='4' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>
</tr>

<tr>
    <td><?= $loadArray [1][1] ?></td>
    <td>Загрузить выписку</td>    
    <td><?php
      if     ($shift < $loadArray  [1][0])                               $color= "LightGray";
      elseif ($shift >= $loadArray [1][1] && $shift < $loadArray [1][2]) $color= "Orange";
      else                                                               $color= "Crimson";
      $ref= $model->getExtractRef($loadArray [1][0], $loadArray [1][2]);  
      if ($ref['id'] == 0)       
           echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";        
      $extractId=$ref['id'];      
    ?></td>  
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"

    ?>
</tr>

<tr>
    <td>13-10</td>
    <td>Синхронизировать данные с 1С</td>
    <td>
     <?php   
//     echo $syncArray[1][0]." ".$syncArray[1][2];  
      if     ($shift <  $syncArray [1][0])                               $color= "LightGray";
      elseif ($shift >= $syncArray [1][1] && $shift < $syncArray [1][2]) $color= "Orange";
      else                                                               $color= "Crimson";

      $ref= $model->getOperationRef($syncArray[1][0], $syncArray[1][2]);   
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a  class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";  
     ?>
    </td>
        <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"
    ?>

</tr>
<tr>
    <td>13-30</td>
    <td>Провести сверку</td>
    <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='4'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='4' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>

</tr>


<tr>
    <td><?= $loadArray [2][1] ?></td>
    <td>Загрузить выписку</td>
    <td><?php
      if     ($shift < $loadArray  [2][0])                               $color= "LightGray";
      elseif ($shift >= $loadArray [2][1] && $shift < $loadArray [2][2]) $color= "Orange";
      else                                                               $color= "Crimson";
      $ref= $model->getExtractRef($loadArray [2][0], $loadArray [2][2]);  
      if ($ref['id'] == 0)       
           echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";        
      $extractId=$ref['id'];      
    ?></td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"

    ?>
</tr>
<tr>
    <td>16-10</td>
    <td>Синхронизировать данные с 1С</td>
    <td>
     <?php     
//      echo $syncArray[2][0]." ".$syncArray[2][2];
      if     ($shift <  $syncArray [2][0])                               $color= "LightGray";
      elseif ($shift >= $syncArray [2][1] && $shift < $syncArray [2][2]) $color= "Orange";
      else                                                               $color= "Crimson";
     
     
     $ref= $model->getOperationRef($syncArray[2][0], $syncArray[2][2]);   
     $syncId=$ref['id'];
     if ($ref['id'] == 0) echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
     else echo "<a  class='action_ref' href='index.php?r=/bank/operator/show-bank-operation&id=".$ref['id']."' target='_blank'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";  
     ?>
    </td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"
    ?>

</tr>
<tr>
    <td>16-30</td>
    <td>Провести сверку</td>
    <?php 
    if ($syncId*$extractId == 0)
    {
        echo "<td align='left'><font color='LightGray'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font></td><td colspan='4'></td>";
        
    }
    else
    {
     echo "<td><a  class='action_ref' href='#' onclick='openWin(\"/bank/operator/bank-check&refBankHeader=".$extractId."&refOpHeader=".$syncId."\");'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a></td>";  
    
        $sverka= $model->getSverka($syncId, $extractId );  
     if ($sverka['extract'] == 0) $ecolor="Green";
                             else $ecolor="Crimson";
     
     if ($sverka['operation'] == 0) $ocolor="Green";
                               else $ocolor="Crimson";
             
     echo "<td colspan='4' align='center'> <font color='".$ecolor."'>".$sverka['extract']."</font>/<font color='".$ocolor."'>".$sverka['operation']."</font></td>";
       
    }
    $syncId=$extractId == 0;
    ?>
</tr>

<tr>
    <td><?= $loadArray [3][1] ?></td>
    <td>Загрузить выписку</td>
    <td><?php
      if     ($shift <  $loadArray [3][0])                               $color= "LightGray";
      elseif ($shift >= $loadArray [3][1] && $shift < $loadArray [3][2]) $color= "Orange";
      else                                                               $color= "Crimson";
      $ref= $model->getExtractRef($loadArray [3][0], $loadArray [3][2]);  
      if ($ref['id'] == 0)       
           echo "<font color='".$color."'><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></font>";
      else echo "<a class='action_ref' href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$ref['id']."','childWin');\" ><span class='glyphicon glyphicon-th-list' aria-hidden='true'></span></a>";        
      $extractId=$ref['id'];      

    ?></td>
    <?php
    if ($ref['id'] > 0) 
    {
      echo "<td>".date("d.m.Y H:i:s", strtotime($ref['creationDate'])+4*3600)."</td>";
      echo "<td>".number_format($ref['credit'],2,'.','&nbsp;')."</td>";
      echo "<td>".number_format($ref['debet'],2,'.','&nbsp;')."</td>";
      echo "<td>".$ref['userFIO']."</td>";
    }
    else echo "<td colspan='4'></td>"
    ?>
</tr>


</tbody>
</table>


<p>Загружаемый файл должен быть в формате xlsx и содержать выписку в формате предоставлемом Сбербанк. 
<?php    
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


