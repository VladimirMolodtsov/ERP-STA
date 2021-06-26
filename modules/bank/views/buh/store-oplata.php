<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;


$this->title = 'Докуменеты на оплату';
//$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/store-oplata.js');

$from = strtotime($model->fromDate);
$to = strtotime($model->toDate);

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
.orginfo {
  
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
}

</style>


<script type="text/javascript">



</script> 




<h3>Документы на оплату</h3> 



<div class='row'>
<div class="col-md-6">
<?php
$payerList = $model->getPayerList();
echo Html::dropDownList( 
                       'selectPayer', 
                        $model->curPayer, 
                        $payerList,
                              [
                              'class' => 'form-control',
                              'style' => 'font-size:11px; padding:1px;', 
                              'id' => 'selectPayer', 
                              'onchange' => 'chngPayer();'
                              ]);

?>

</div>   


<div class="col-md-2">

</div>   

<div class="col-md-2">
    <a href="#" class='btn btn-primary'  onclick="preparePaymentOrder();">Сформировать ПП</a> 
</div>   
<div class="col-md-2">

</div>   
      
</div> 
<div class='spacer'></div>

<div class='row'>

<div class="col-md-2">
    
</div>   

<div class="col-md-2">
    <a href="index.php?r=/bank/buh/store-pay" >Кратко</a> 
</div>   

<div class="col-md-2">
    <a href="index.php?r=/bank/buh/pay-orders" >Список ПП</a> 
</div>   

<div class="col-md-6">
<b>Всего принято платежных поручений <?=$model->totalCount?> на сумму <?= number_format($model->totalSum,2,'.','&nbsp;') ?></b>
</div>   
      
</div> 

<hr>
<?php
 
  echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
      
        'responsive'=>true,
        'hover'=>false,
        
        /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
         ], */       
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'id' => 'reestrGrid'
        ],


        'columns' => [
            [
                'attribute' => 'docIntNum',
                'label'     => 'Вх.№',
                'format' => 'raw',                            
                 'value' => function ($model, $key, $index, $column) {                 
                   $regTime = strtotime($model['regDateTime']);
  
                   if ($regTime > 100) $regDate = "Зарегестрирован: ". date("d.m.y H:i", $regTime);
                                  else $regDate =  "";
   
                  $val = \yii\helpers\Html::tag( 'div', $model['docIntNum'], 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $regDate,
                   ]);
                 return $val;
               }

            ],  

            [
                'attribute' => 'docNote',
                'label'     => 'Предмет оплаты',
                'format' => 'raw',                            
                'contentOptions'   =>   ['padding' => '0px'] ,                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 $id = 'docNote'.$model['id'];
                 return Html::textInput( 
                          $id, 
                          $model['docNote'], 
                              [
                              'class' => 'form-control',
                              'style' => 'width:150px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => 'saveField('.$model['id'].',"docNote");'
                              ]);
               }

            ],            
           
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',                            
                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";
                 
                 if (empty($model['refOrg'])) {
                     $style='color:Crimson;'; 
                     $action = "selectOrg(".$model['id'].",".$model['orgINN'].")";                     
                 } else {
                     $action = "openWin('site/org-detail&orgId=".$model['refOrg']."','childWin')";
                 
                 }
                 
                 
                 $id = 'refOrg'.$model['id'];
                 
                 
                 $val = \yii\helpers\Html::tag( 'div', $model['orgTitle'], 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => $style,
                   ]);

                 return $val;
               }

            ],            
            
          
            
           [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',                            
                'contentOptions'   =>   ['padding' => '0px'] ,                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 $id = 'orgINN'.$model['id'];
                 return \yii\helpers\Html::tag( 'div', $model['orgINN'], 
                         [
                          'class' => 'cellInfo',
                          'style' => 'width:85px;', 
                          'id' => $id,                            
                         ]);
               }
            ],            
            

           [
                'attribute' => 'docTitle',
                'label'     => 'Документ',
                'format' => 'raw',                            
                'contentOptions'   =>   ['padding' => '0px'] ,                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 $id = 'docTitle'.$model['id'];
                 $action="openExtWin('".$model['docURI']."','childWin');";
                 $val = \yii\helpers\Html::tag( 'div', $model['docTitle'], 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style='  => $style,
                   ]);

                 return $val;

               }
            ],            
                        
            [
                'attribute' => 'docOrigNum',
                'label'     => '№',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                                
                 $style="";                 
                 $id = 'docOrigNum'.$model['id'];
                 $action="openExtWin('".$model['docURI']."','childWin');";
                      switch ($model['docOrigStatus']){
                        case  1: $docOrigStatus= "Копия"; break;
                        case  2: $docOrigStatus= "Скан"; break;
                        default: $docOrigStatus= "Оригинал"; break;
                      }
                 
                 $val = \yii\helpers\Html::tag( 'div', $model['docOrigNum'], 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style='  => $style,
                     'title'   => $docOrigStatus,
                   ]);

                 return $val;
               }
                       
            ],            

            [
                'attribute' => 'docOrigDate',
                'label'     => 'Дата',
                'filter' => [ '1' => 'За сегодня',  '2' => 'За декаду', '3' => 'За месяц', '4' => 'За квартал' ],
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                $docOrigTime = strtotime($model['docOrigDate']);
                if ($docOrigTime  > 100) return date("d.m.y", $docOrigTime);
                else  return "&nbsp;";
               }                       
            ],            
            
           [
                'attribute' => 'docSum',
                'filter' => false,
                'label'     => 'На сумму',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['docSum'],2,',','&nbsp;');
               }
                
            ],            
                        
          /* [
                'attribute' => 'isOplate',
                'filter' => [0 => 'Все', 1 => 'Да',  2 => 'Нет'],
                'label'     => 'Оплата',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['isOplate'] == 1) return "Да";
               }                
            ],  */          
                                     
            [
                'attribute' => 'ref1C_input',
                'label'     => 'Вх. № в 1C',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                            
                $id = "ref1C_input".$model['id'];
                $style="";    
                $val ="";                
               
               
               $action =  "openSupplierSchet(".$model['refSupplierSchet'].");";                    
               
               if ($model['refSupplierSchet'] == 0) $style='color:Crimson;'; 
               
               
               return \yii\helpers\Html::tag( 'div', $model['ref1C_input'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Счет поставщика",
                     'style'   => "padding:5px;margin:5px;".$style,
                   ]);

                }   
            ],            
           
            [
                'attribute' => 'refDocOplata',                
                'label'     => 'К оплате',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."refDocOplata".$model['refDocOplata'];
                $style="";    
                $val ="&nbsp;";                
                           
               if ($model['docOplateStatus'] >0 ) {$style='background:Green;color:White;'; }                 
                                            else  {$style='background:White;';}    
                     
               $action =  "switchData(".$model['id'].", 'refDocOplata',".$model['refDocOplata'].");"; 
               if (empty($model['refOrg'])) $action ="alert('Организация не распознана!')";
            //(recordId, dataType, dataId)               
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],            

            [
                'attribute' => 'dateToOplata',                
                'label'     => 'Когда',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                   $regTime = strtotime($model['dateToOplata']);
                   if ($regTime > 100) $val=date("d.m.y H:i", $regTime);
                   else $val= "";
                   $action =  "saveField(".$model['id'].", 'dateToOplata',".$model['refDocOplata'].");"; 
                   
                   $id = $model['id']."dateToOplata".$model['refDocOplata'];
                   return    DatePicker::widget([
                        'name' => $id,
                        'id'   => $id,
                        'value' => $val,    
                        'type' => DatePicker::TYPE_INPUT,
                        'options' => [
                        'onchange' => $action,
                        'style' => 'width:75px;',
                        ],
                        'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd.mm.yy'        
                        ]
                    ]);
               }                
            ],            
            [
                'attribute' => 'sumToOplate',                
                'label'     => 'Сколько',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'sumToOplate'.$model['refDocOplata'];                 
                 $action =  "saveField(".$model['id'].", 'sumToOplate',".$model['refDocOplata'].");"; 
                                     
                 return Html::textInput( 
                          $id, 
                          $model['sumToOplate'],                                
                              [
                              'readonly' => false,
                              'class' => 'form-control',
                              'style' => 'width:75px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            
            [
                'attribute' => 'NDS',                
                'label'     => 'НДС',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'NDS'.$model['refDocOplata'];                 
                 $action =  "saveField(".$model['id'].", 'NDS',".$model['refDocOplata'].");"; 
                                     
                 return Html::textInput( 
                          $id, 
                          $model['NDS'],                                
                              [
                              'readonly' => false,
                              'class' => 'form-control',
                              'style' => 'width:40px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            

            [
                'attribute' => 'payPurpose',                
                'label'     => 'Назначение',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'payPurpose'.$model['refDocOplata'];                 
                 $action =  "saveField(".$model['id'].", 'payPurpose',".$model['refDocOplata'].");"; 
                                     
                 return Html::textInput( 
                          $id, 
                          $model['payPurpose'],                                
                              [
                              'title'    => $model['docNote'],
                              'readonly' => false,
                              'class' => 'form-control',
                              'style' => 'width:150px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            
            
            
            [
                'attribute' => 'extractStatus',                
                'label'     => 'Принять',
                'format' => 'raw',    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."extractStatus".$model['refDocOplata'];
                $style="";    
                $val ="&nbsp;";                
               
               $action =  "switchData(".$model['id'].", 'extractStatus',".$model['refDocOplata'].");";                    
               $status =$model['extractStatus'];
               if (empty($model['refOrg'])) $status = 11;
               switch ($status) 
               {
                 case 1:    
                   $style='background:LightGreen;color:White;'; 
                   $title = 'Принято';
                 break;
                 case 11:    
                   $style='background:Yellow;color:White;'; 
                   $title = 'Организация не распознана';
                 break;

                 case 3:    
                   $style='background:Green;color:White;'; 
                   $title = 'Сформировано';
                   $action =  "if (confirm('Платежное поручение уже было сформировано. Сформировать повторно?') == true) {
                   switchData(".$model['id'].", 'extractStatus',".$model['refDocOplata'].");}";                                       
                 break;                 
                 case 4:    
                   $style='background:DarkGreen;color:White;'; 
                   $title = 'Оплачено';
                   $action = "alert('Платежное поручение уже оплачено!');";
                 break;                 
                 case 5:    
                   $style='background:Crimson;color:White;'; 
                   $title = 'Отказано';
                   $action = "extrsctDenied();";
                 break;                 
                 
                 default: 
                   $style='background:White;';
                   $title = 'Принять';
               }     
               
               
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => $style,
                   ]);
                return $val;
                    
               }
                               
            ],            
  


            [
                'attribute' => 'removeData',                
                'label'     => 'Очистить',
                'format' => 'raw',    
                'value' => function ($model, $key, $index, $column) {                    
                
                $action =  "switchData(".$model['id'].", 'removeData',".$model['refDocOplata'].");";                    
                $id = "removeData".$model['id'];
                $style="";    
                $val ="";                
                return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Сбросить оплату",
                     'style'   => "padding:5px;margin:5px;".$style,
                   ]);
                
                    
               }
                               
            ],            
            


            [
                'attribute' => 'oplata1c',                
                'label'     => 'пп в 1C',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                }       
            ],            
            
                                                                                  
        ],
    ]
); 

?>

<?php
Modal::begin([
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectOrgDialogFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/doc-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action'=>'index.php?r=/bank/buh/save-store-oplata']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>



