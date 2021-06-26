<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;


$this->title = 'Банк - сверка выписки';

$unchecked = $model->getUnchecked();
 /*
echo "<pre>";
 print_r($model->dataArray);
 
 //print_r($model->extractArray);
 
// print_r($model->operationArray);
echo "</pre>"; 
exit(0); 
 */
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
/*.btn-act {color:Green;}*/

</style>


<script type="text/javascript">
var bankExtractRef;
function addOpRecord(id)
{
 bankExtractRef = id;   
 $('#addOpDialog').modal('show'); 
}

function setOperationLnk(opId)
{
    openSwitchWin('/bank/operator/extract-operation-lnk&extract='+bankExtractRef+'&operation='+opId);
}

function unlinkCheck(opId)
{
    openSwitchWin('/bank/operator/extract-operation-lnk&extract=0&operation='+opId);
}

function confirmCheck (bankExtractRef,operationId)
{
    openSwitchWin('/bank/operator/extract-operation-lnk&extract='+bankExtractRef+'&operation='+operationId);
}


function setChecked(bankExtractRef)
{
    openSwitchWin('/bank/operator/set-chk-status&status=1&extract='+bankExtractRef);
}

function setCanceled(bankExtractRef)
{
    openSwitchWin('/bank/operator/set-chk-status&status=-1&extract='+bankExtractRef);
}

function cancelChecked(bankExtractRef)
{
    openSwitchWin('/bank/operator/set-chk-status&status=0&extract='+bankExtractRef);
}

function finalizeCheck()
{
   var unchExtract=<?= $unchecked['extract'] ?>; 
   var unchOperation=<?= $unchecked['operation'] ?>;

   
   if (unchExtract != 0){
          alert("Обработаны не все записи из банковской выписки. Сверка не завершена!"); 
       }
   else   {
      openSwitchWin('/bank/operator/finalize-check&refBankHeader=<?= $model->refBankHeader ?>&refOpHeader=<?= $model->refOpHeader ?>');
   }
   
    window.opener.location.reload(false); 
    window.opener.focus();
    window.close();
}
</script> 

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
                'attribute' => 'recordDate',
                'label'     => 'Дата',
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['recordDate'])+4*3600);
               }

            ],            

            [
                'attribute' => 'debetOrgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',     
            ],            

            [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
            ],            

            [
                'attribute' => 'creditSum',
                'label'     => 'Приход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditSum'],2,',','&nbsp;');
               }
                
            ],            
            
                                    
            [
                'attribute' => 'debetSum',
                'label'     => 'Расход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['debetSum'],2,',','&nbsp;');
               }
                
            ],            

            
           [
                'attribute' => 'Сопоставлено 1C',
                'label'     => 'Сопоставлено 1C',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                                                    
                 $ret ="";   
                  $strSql="Select id, orgTitle, recordSum, regDate from {{%bank_operation}} where refBankExtract =:ref";                   
                  $list = Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $model['bankExtractRef'])                                               
                           ->queryAll();                                    
                $N = count($list);                                
                $s=0;              
                $ret .="<table width='200px'><tr>";
                if ($model['checkStatus'] != 0)
                {
                  for ($i=0; $i< $N ;$i++)
                  {
                   
                   $ret .="<td colspan='3'><b>".$list[$i]["orgTitle"]."</b></td></tr>\n";
                   $ret .="<tr><td> ".date("d.M", strtotime($list[$i]["regDate"]))."</td><td> ".$list[$i]["recordSum"]." руб.</td>\n";                                                                
                   $ret .="<td align='right'></td></tr>\n";                  
                   $s+=$list[$i]["recordSum"];
                  }  
                 $ret.="<tr><td  colspan=2 ><b>ВСЕГО:&nbsp;</b>".number_format($s,2,',','&nbsp;')."</td><td style='text-align:right'></td></tr>";   
                  
                }
                else {    
                  if ($N == 0 )
                  {     //Нет подтвержденных
                   if ($model["opRef"] !=0){
                   $ret .="<td colspan='3'>".$model["opOrgTitle"]."</td></tr>\n";
                   $ret .="<tr><td> ".date("d.M", strtotime($model["opRegDate"]))."</td><td>".$model["opSum"]." руб.</td>\n";                                
                   $ret .="<td align='right'><a href='#' class='btn-act'  title='Подтвердить' onclick=confirmCheck(".$model['bankExtractRef'].",".$model["operationId"].");><font color='Green'><span class='glyphicon glyphicon-ok-circle'></span></font></a></td></tr>\n";
                   $s+=$model["opSum"]; } else $ret .="<td></td>";
                  }else
                  {                
                   for ($i=0; $i< $N ;$i++){                   
                     $ret .="<td colspan='3'><b>".$list[$i]["orgTitle"]."</b></td></tr>\n";
                     $ret .="<tr><td> ".date("d.M", strtotime($list[$i]["regDate"]))."</td><td> ".$list[$i]["recordSum"]." руб.</td>\n";                                                                
                     $ret .="<td align='right'> <a href='#' title='Убрать' onclick=unlinkCheck(".$list[$i]["id"].");><font color='Crimson'><span class='glyphicon glyphicon-remove-circle'></span></font></a></td></tr>\n";                  
                     $s+=$list[$i]["recordSum"]; }                    
                  }                
                                
                 $ret.="<tr><td  colspan=2 ><b>ВСЕГО:&nbsp;</b>".number_format($s,2,',','&nbsp;')."</td><td style='text-align:right'><a href='#'  title='Добавить' onclick=addOpRecord(".$model['bankExtractRef'].");><span class='glyphicon glyphicon-plus-sign'></span></a></td></tr>";   
                } //$model['checkStatus'] != 0
               
                $ret .="</table>"; 
                return $ret;
               }
                
            ],            

            
            [
                'attribute' => 'refChecker',
                'label'     => 'Статус',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                 if ($model['checkStatus'] > 0)
                 {                 
                     return "Сверено ".$model['userFIO'];                 
                 }    
                 if ($model['checkStatus'] < 0)
                 {                 
                     return "Игнор. ".$model['userFIO'];                 
                 }    
                             
                   return "";          
                                    
               }
                
            ],                        
            [
                'attribute' => '',
                'label'     => 'Действие',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
               
                 if ($model['checkStatus'] != 0)
                 {                 
                     return "<a href='#' onclick='cancelChecked(".$model['bankExtractRef'].")' title='Отменить'><font color='Blue'><span class='glyphicon glyphicon-minus-sign'></span></font></a>";
                 }    

                  $strSql="Select sum(recordSum) from {{%bank_operation}} where refBankExtract =:ref";                   
                  $chkSum = Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $model['bankExtractRef'])                    
                           ->queryScalar();

                  if($chkSum < 0) $chkSum = -1*$chkSum;
                  $chkSum+=0.1;                   
                  if($chkSum >= ($model['creditSum']+$model['debetSum']))
                  {                   
                   return "<a href='#' onclick='setChecked(".$model['bankExtractRef'].")'  title='Сверено '><font color='Green'><span class='glyphicon glyphicon-ok-sign'></span></font></a> ";
                   }
                   
                   return "<a href='#' onclick='setCanceled(".$model['bankExtractRef'].")' title='Игнорировать'><font color='Crimson'><span class='glyphicon glyphicon-remove-sign'></span></font></a> ";                     
                   
               }
                
            ],                        
            
        ],
    ]
); 


$chkSum=$model->getCheckedSum();
?>
<div class='row'>
<div class='col-md-5'>
<p>
    Итого приход <b><?= number_format($chkSum[0]['income'],2,',','&nbsp;') ?></b> расход <b><?= number_format($chkSum[0]['outcome'],2,',','&nbsp;') ?></b> 
</p>
</div>

<div class='col-md-5'>
<p>
    Не сопоставлено записей в выписке: <b><?= $unchecked['extract'] ?></b> в 1С: <b><?= $unchecked['operation'] ?></b> 
</p>
</div>

<div class='col-md-2'>
<input type='button' class='btn btn-primary' value='Завершить сверку' onclick='finalizeCheck();'>
</div>
</div>

<?php
Modal::begin([
    'id' =>'addOpDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],    
]);?><div style='width:550px'>


<?php
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $freeOpProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
    
           
            [
                'attribute' => 'op_regDate',
                'label'     => 'Дата',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y", strtotime($model['op_regDate']));
               }
                
            ],            
            [
                'attribute' => 'op_ownerTitle',
                'label'     => 'Организация',
                'format' => 'raw',     
            ],            
           [
                'attribute' => 'op_orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',     
            ],            

            [
                'attribute' => 'op_orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',     
            ],            
                        
            [
                'attribute' => 'op_recordSum',
                'label'     => 'Сумма',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return "<a href='#' onclick='setOperationLnk(".$model['operationId'].")';>".number_format($model['op_recordSum'],2,',','&nbsp;')."</a>\n";
               }
                
            ],            

           /****/
        ],
    ]
); 
Pjax::end(); 
?>


</div><?php
Modal::end();
?>

