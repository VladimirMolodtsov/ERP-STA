<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 
use kartik\date\DatePicker;


$this->title = 'Банк - выписки';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/operator.js');

$from = strtotime($detailModel->fromDate);
$to = strtotime($detailModel->toDate);
$dataPP=$detailModel->getStatPP ();
 ?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<style>
.table-small {
padding: 2px;
font-size:12px;
}
.action_ref {    
    color:Green;
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

.btn-smaller{
margin:0px;
margin-top:-2px;
padding:1px;
height:15px;
width:15px;
}
.orginfo {
  
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
}

</style>

<script>
function changeShowDate(){
fromDate = document.getElementById('from_date').value;
toDate = document.getElementById('to_date').value;
document.location.href='index.php?r=/bank/operator/index&fromDate='+fromDate+'&toDate='+toDate; 
    
}
</script>
<div class='row'>

<div class ='col-md-3' style='text-align:left'>
<div class="item-header">Банковские операции (Выписки):</div> 
</div>
    <div class ='col-md-3' style='text-align:left'>
    Проведено за период:
    <div class='spacer'></div>     
    <?php   
   echo DatePicker::widget([
    'name' => 'from_date',
    'id' => 'from_date',
    'value' => date("d.m.Y",$from),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>            
    <?php   
   echo DatePicker::widget([
    'name' => 'to_date',
    'id' => 'to_date',
    'value' => date("d.m.Y",$to),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
   </div>       
   
    <div class="col-md-4" >
    <div class='spacer' style='height:30px;'></div>
    <table border=0 width=100%>
    <tr>    
    <td style='width:25px;'> <?php   
    
                   if ($detailModel->overdueVal == 1) 
                      {$action="switchPP(0,".$detailModel->yesterdayVal.", ".$detailModel->todayVal.");";
                       $style = 'background:DarkBlue';}
                   else 
                      {$action="switchPP(1,".$detailModel->yesterdayVal.", ".$detailModel->todayVal.");";
                       $style = 'background:White';}
                  
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'overdue',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
         ?>     
         
    </td>   
    <td>Просрочено  <?=$dataPP['overdue']['N']?> на сумму <?= number_format($dataPP['overdue']['S'],2,'.','&nbsp;') ?> руб.</td>
    </tr>
    
    <tr>    
    <td style='width:25px;'> <?php    
            
                   if ($detailModel->yesterdayVal == 1) 
                      {$action="switchPP(".$detailModel->overdueVal.",0, ".$detailModel->todayVal.");";
                       $style = 'background:DarkBlue';}
                   else 
                      {$action="switchPP(".$detailModel->overdueVal.",1, ".$detailModel->todayVal.");";
                       $style = 'background:White';}

                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'yesterday',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
         ?>          
    </td>   
    <td>Вчера <?=$dataPP['yesterday']['N']?> на сумму <?= number_format($dataPP['yesterday']['S'],2,'.','&nbsp;') ?> руб. </td>
    </tr>

    
    <tr>    
    <td style='width:25px;'> <?php    
                   if ($detailModel->todayVal == 1)
                      {$action="switchPP(".$detailModel->overdueVal.",".$detailModel->yesterdayVal.",0);";
                       $style = 'background:DarkBlue';}
                   else 
                      {$action="switchPP(".$detailModel->overdueVal.",".$detailModel->yesterdayVal.",1);";
                       $style = 'background:White';}

            
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'today',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
         ?>         
    </td>   
    <td>Сегодня <?=$dataPP['today']['N']?> на сумму <?= number_format($dataPP['today']['S'],2,'.','&nbsp;') ?> руб. </td>
    </tr>
    </table>
    

   </div>   

    <div class="col-md-2" style='text-align:right'>
    
    <a href='index.php?<?=Yii::$app->request->queryString?>&format=csv' target='_blank' >Скачать</a> 
    </div>       


</div>  

<div class='spacer'></div>

 <?php
$typeArray = $model->getTypeArray();
$typeArray[0]='не задан'; 
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $detailProvider,
        'filterModel' => $detailModel,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        
        'columns' => [           
            [
                'attribute' => 'recordDate',
                'label'     => 'Проведено',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                 $d = date("d.m.Y", strtotime($model['recordDate'])+4*3600);
                 $title = 'Запись '.$model['id']." дата-время проводки: ".date("d.m.Y H:i:s", strtotime($model['recordDate'])+4*3600);   
                 $val= \yii\helpers\Html::tag( 'div', $d, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
                   
                 return $val;
               }                
            ],            

            [
                'attribute' => 'docNum',
                'label'     => 'П/П',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                 $pp = $model['docNum'];
                 $title = 'Платежное поручение №'.$pp;   
                 
                 if (!empty($model['reasonText']))
                     $title .= "\n".$model['reasonText'] ;
                 $val= \yii\helpers\Html::tag( 'div', $pp, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
                 return $val;  
               }                
            ],            

           [
                'attribute' => 'debetOrgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                 $val = $model['debetOrgTitle'];
                 $title = 'ИНН '.$model['debetINN']." Р/С ".$model['debetRS'];   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
                }
            ],            


           [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                 $val = $model['creditOrgTitle'];
                 $title = 'ИНН '.$model['creditINN']." Р/С ".$model['creditRs'];   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
                }
            ],            
        
                        
            [
                'attribute' => 'debetSum',
                'label'     => 'Расход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                 $val = number_format($model['debetSum'],2,',','&nbsp;');                 
                 $title = '';   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
               }
            ],            

            [
                'attribute' => 'creditSum',
                'label'     => 'Приход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                 $val = number_format($model['creditSum'],2,',','&nbsp;');
                 $title = '';   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
               }
            ],            
           /****/
           
            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    if (empty($model['orgRef'])) return "&nbsp;";
                 $action="openWin(\"site/org-detail&orgId=".$model['orgRef']."\", \"childwin\")";
                 return \yii\helpers\Html::tag( 'div', $model['orgTitle'], 
                   [
//                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
//                    'style'  => $style,
//                     'title'   => $docOrigStatus,
                   ]);
                    
                    
                },
            ],    
           
            
            [
                'attribute' => 'contragentType',
                'label' => 'Статус',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '120px'] ,  
                'value' => function ($model, $key, $index, $column) use($typeArray) {                        
                  
                $c = "";
                if ($model['contragentType'] == 0) $c = 'color:Crimson;';                 
                  $id = "contragentType".$model['id'];
                if (empty($model['contragentType'])) $contragentType = '0';
                                               else $contragentType = $model['contragentType'];
                  
                  return Html::dropDownList( 
                          $id, 
                          $contragentType, 
                              $typeArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveData('.$model['id'].',"contragentType");'
                              ]);
                },
            ],    
           
            [
                'attribute' => 'operationType',
                'label' => 'Операция',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

  
                if (empty($model['contragentType'])) $contragentType = 0;
                                               else $contragentType = $model['contragentType'];
                
                $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$contragentType." ORDER BY id"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','operationTitle');       
                $operationArray[0]='не задан';
                 $c = "";
                 if ($model['contragentType'] == 0) $c = 'color:Crimson;';                  
                  $id = "operationType".$model['id'];
                  return Html::dropDownList( 
                          $id, 
                          $model['operationType'], 
                              $operationArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveData('.$model['id'].',"operationType");'
                              ]);
                },
            ],    
                
            [
                'attribute' => 'extractRef',                
                'label'     => 'Связано с',
                'format' => 'raw',  
                  
               'value' => function ($model, $key, $index, $column) {                    
                
                
                $list= Yii::$app->db->createCommand("Select 
                docTitle, docURI,docOrigNum,docOrigDate,{{%doc_oplata}}.id as refDocOplata
                from  
                {{%documents}},{{%doc_oplata}},{{%doc_extract_lnk}} 
                 where {{%documents}}.id={{%doc_oplata}}.refDocument
                 AND  {{%doc_extract_lnk}}.docOplataRef={{%doc_oplata}}.id
                 And extractRef =:extractRef AND isLnk =1 AND {{%documents}}.isOplate=1",                  
                 [':extractRef' => $model['id'],])->queryAll(); 
                $N = count($list); 
                if ($N == 0){
                    $action =  "openDocumentList(".$model['id'].", 'showAll');";                                    
                    $val = \yii\helpers\Html::tag( 'div',                                
                    '--',                 
                   [
                     'class'   => 'clickable',
                //     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Связанные документы ",
                     'style'   => "padding:5px;margin:5px;font-size:15px;color:Crimson",
                   ]);                   
                }
                else{
                  $action =  "openDocumentList(".$model['id'].", 'showSel');";                                    
                  $val ="";                
                  for ($i=0;$i<$N;$i++){                                               
                   $val .= \yii\helpers\Html::tag( 'div',                                
                   $list[$i]['docTitle']."&nbsp".$list[$i]['docOrigNum']."<br>от&nbsp;".date("d.m.Y", strtotime($list[$i]['docOrigDate'])),                 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "Связанные документы ",
                     'style'   => "padding:5px;margin:5px;font-size:12px;",
                   ]);                   
                   }    
                }
                return $val;   
               }
            ],


                
                      
        ],
    ]
); 
Pjax::end(); 
?>




<div class="item-header">Загруженные выписки:</div> 
<?php
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $extractProvider,
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
                    return "<a href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$model['id']."','childWin');\" >".date("d.m.Y h:i", strtotime($model['creationDate'])+4*3600)."</a>";
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

            /****/
        ],
    ]
); 

Pjax::end(); 
?>

<a href='index.php?r=bank/operator/load-bank' class='btn btn-primary'>Загрузить</a>




<?php
Modal::begin([
    'id' =>'selectDocLnkDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectDocLnkDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/bank/operator/doc-extract&noframe=1&refExtract=0' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/bank/operator/save-extraction-lnk']);
echo $form->field($detailModel, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($detailModel, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($detailModel, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo "<input type='submit'>";
ActiveForm::end(); 
?>
