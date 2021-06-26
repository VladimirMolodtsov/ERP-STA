<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 


$this->title = 'Документы на оплату';
//$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/store-oplata.js');



$from = strtotime($model->fromDate);
$to = strtotime($model->toDate);

$dataPP=$model->getStatPP ();

$puchaseRole=[
  0 => 'Товар',
  1 => 'Доставка',
  2 => 'Перемещение',
  3 => 'Резка',
  4 => 'Прочее',
];               

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
margin:1px;
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


<script type="text/javascript">
function    changeShowDate(id){

dtFrom = document.getElementById('from_date').value;    
dtTo = document.getElementById('to_date').value;    
    
document.location.href = 'index.php?r=/bank/buh/store-pay&noframe=<?=$noframe?>&toDate='+dtTo+'&fromDate='+dtFrom;
}

function    filterPaymentOrder()
{

overdueVal = document.getElementById('overdueVal').value;    
todayVal   = document.getElementById('todayVal').value;    
tomorrowVal= document.getElementById('tomorrowVal').value;    
furtherVal = document.getElementById('furtherVal').value;    

fltString ="&flt=pp";    
fltString+= "&overdueVal="+overdueVal;    
fltString+= "&todayVal="+todayVal;
fltString+= "&tomorrowVal="+tomorrowVal;
fltString+= "&furtherVal="+furtherVal;
    
document.location.href = 'index.php?r=/bank/buh/store-pay&noframe=<?=$noframe?>'+fltString;    
}
function    skipFilter()
{
overdueVal = document.getElementById('overdueVal').value;    
todayVal   = document.getElementById('todayVal').value;    
tomorrowVal= document.getElementById('tomorrowVal').value;    
furtherVal = document.getElementById('furtherVal').value;    

fltString ="&flt=all";    
fltString+= "&overdueVal="+overdueVal;    
fltString+= "&todayVal="+todayVal;
fltString+= "&tomorrowVal="+tomorrowVal;
fltString+= "&furtherVal="+furtherVal;
    
    
document.location.href = 'index.php?r=/bank/buh/store-pay&noframe=<?=$noframe?>'+fltString;        
}

function openDoc(id, docUri){ 
  url = 'index.php?r=bank/operator/reg-doc&noframe=1&id='+id;
  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  if (docUri != ''){
  wid=window.open(docUri, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();
  }
  window.wreg.focus();
}

</script> 

<div class='row'>
<div class="col-md-3">
<h3>Документы на оплату</h3>     
</div>   

<div class="col-md-2">
<div class='spacer' style='height:25px;'></div>
<a   class='btn btn-default' href="index.php?r=/bank/buh/store-oplata" >Детально</a>
</div>   

<div class="col-md-5">
<div class='spacer' style='height:25px;'></div>
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



<div class="col-md-2" style='text-align:right;'>
<div class='spacer' style='height:25px;'></div>
    <div  class='btn btn-default' onclick='openWin("/bank/buh/pay-orders","downloadWin")' >Список ПП</div> 
    
</div>   
      
</div> 


<div class='spacer'></div>

<div class='row'>

    <div class ='col-md-3' style='text-align:left'>
    Зарегестрировано за период:
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
    <div class="col-md-2" style='text-align:right'>
    </div>       
   
    <div class="col-md-5" >
    <table border=0 width=100%>
    <tr>    
    <td style='width:25px;'> <?php    
                   if ($model->overdueVal == 1) $style = 'background:DarkBlue';
                                          else $style = 'background:White';
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'overdue',
                     'onclick' => "switchPP('overdue')",
                     'style'   => $style,
                   ]);
         ?>     
         
    </td>   
    <td>Просрочено  <?=$dataPP['overdue']['N']?> на сумму <?= number_format($dataPP['overdue']['S'],2,'.','&nbsp;') ?> руб.</td>
    </tr>
    
    <tr>    
    <td style='width:25px;'> <?php    
                   if ($model->todayVal == 1) $style = 'background:DarkBlue';
                                          else $style = 'background:White';
            
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'today',
                     'onclick' => "switchPP('today')",
                     'style'   => $style,
                   ]);
         ?>         
    </td>   
    <td>Сегодня <?=$dataPP['today']['N']?> на сумму <?= number_format($dataPP['today']['S'],2,'.','&nbsp;') ?> руб. </td>
    </tr>
    
    <tr>    
    <td style='width:25px;'> <?php    
            
                   if ($model->tomorrowVal == 1) $style = 'background:DarkBlue';
                                           else $style = 'background:White';
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'tomorrow',
                     'onclick' => "switchPP('tomorrow')",
                     'style'   => $style,
                   ]);
         ?>          
    </td>   
    <td>Завтра <?=$dataPP['tomorrow']['N']?> на сумму <?= number_format($dataPP['tomorrow']['S'],2,'.','&nbsp;') ?> руб. </td>
    </tr>
    
    <tr>    
    <td style='width:25px;'> <?php    
            
                   if ($model->furtherVal == 1) $style = 'background:DarkBlue';
                                           else $style = 'background:White';
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'further',
                     'onclick' => "switchPP('further')",
                     'style'   => $style,
                   ]);
         ?>          
    </td>   
    <td>Далее  <?=$dataPP['further']['N']?> на сумму <?= number_format($dataPP['further']['S'],2,'.','&nbsp;') ?> руб. </td>
    </tr>
    
    </table>
    
    <div class='spacer' style='height:10px;'></div>
      <span id='ppTxt' style='font-weight:bold;'>Всего оплат <?=$dataPP['itogo']['N']?> на сумму <?= number_format($dataPP['itogo']['S'],2,'.','&nbsp;') ?> руб.</span>
      <?php
      if ($model->flt=='pp'){ $action = "skipFilter();"; $bg="background:LightGreen;"; $title='Сбросить';}
                      else  { $action = "filterPaymentOrder();"; $bg=""; $title ='Показать';} 
      ?>
      <div class='btn btn-default'  style='margin-left:30px;margin-top:-10px;<?= $bg ?>' onclick="<?= $action ?>"><?=$title?></div> 
   </div>   
    <div class="col-md-2" style='text-align:right;'>
     <div class='spacer' style='height:77px;'></div>   
     <a href="#" class='btn btn-primary'  onclick="preparePaymentOrder();">Сформировать</a> 
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
               // 'filter' => [ '1' => 'За 3 дня',  '2' => 'За декаду', '3' => 'За месяц', '4' => 'За квартал' ],
                'label'     => 'Вх.№',
                'format' => 'raw',                            
                 'value' => function ($model, $key, $index, $column) {                 
                   $regTime = strtotime($model['regDateTime']);
  
                   if ($regTime > 100) $regDate = "Зарегестрирован: ". date("d.m.y H:i", $regTime);
                                  else $regDate =  "";
                
                  $docUri = preg_replace("/\"/","",$model['docURI']);
                  $style="";
                  if(empty($docUri)) $style='color:Crimson';
                  $action = "openDoc(".intval($model['id']).",\"".$docUri ."\")";                 
                 $title = $regDate.' ';
                 $title .= 'Оператор '.$model['userFIO'];;

                  $val = \yii\helpers\Html::tag( 'div', $model['docIntNum'], 
                   [
                     'class'   => 'clickable',
                     'title'   => $title,
                     'onclick' => $action,
                     'style'   => $style
                   ]);
                   $val .= \yii\helpers\Html::tag( 'div', date("d.m.y", $regTime), 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                   ]);
                   
                 return $val;
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
                     $action = "selectOrg(".$model['id'].",'".$model['orgINN']."')";                     
                 } else {
                     $action = "openWin('site/org-detail&orgId=".$model['refOrg']."','childWin')";
                 
                 }
                 
                 
                 $id = 'refOrg'.$model['id'];
                 $val = \yii\helpers\Html::tag( 'div', $model['orgTitle']."<br>".$model['orgINN'], 
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
                'attribute' => 'docOrigDate',
                'label'     => 'Документ',
                'filter' => [ '1' => 'За 3 дня',  '2' => 'За декаду', '3' => 'За месяц', '4' => 'За квартал' ],
                
                'format' => 'raw',                            
                'contentOptions'   =>   ['padding' => '0px'] ,                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                // $class = 'clickable';
                 $id = 'docTitle'.$model['id'];
                 if (empty ($model['docURI'])){                    
                 $action="alert('Не задана ссылка на скан документа!')";   
                 $style='color:Crimson;';                  
                 }                 
                 else                
                  $action="openExtWin('".$model['docURI']."','childWin');";

                  switch ($model['docOrigStatus']){
                        case  1: $docOrigStatus= "Копия"; break;
                        case  2: $docOrigStatus= "Скан"; break;
                        default: $docOrigStatus= "Оригинал"; break;
                      }
                 $val="";     
                $style_utr="";      
                if(empty($model['refSupplierSchet'])){$style_utr='color:Gray;';}
                $action_utr="openWin('fin/supplier-schet-src&id=".$model['refSupplierSchet']."','childWin')";   
                $val .= \yii\helpers\Html::tag( 'div', $model['ref1C_input'], 
                   [
                     'class'   => 'clickable',
                     //'id'      => $id,
                     'onclick' => $action_utr,
                     'title'   => "Закупка",
                     'style'  => $style_utr,
                ]);

                
                $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','docType');       
                $operationArray[0]='Не задан';
                $v=$operationArray[$model['docClassifyRef']];
                if (empty($model['docClassifyRef'])) $v =  $model['docTitle'];

                 
                 $val.= \yii\helpers\Html::tag( 'div', $v."&nbsp;№&nbsp;".$model['docOrigNum'], 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => $style,
                     'title'   => $docOrigStatus,
                   ]);
                            

                $docOrigTime = strtotime($model['docOrigDate']);
                if ($docOrigTime  > 100) 
                    $border="";
                if (time()-$docOrigTime > 90*24*3600) $border ="border: 1px  solid Crimson;";   
                $val .= \yii\helpers\Html::tag( 'div', date("d.m.Y", $docOrigTime), 
                   [
                     //'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => $border.$style,
                     'title'   => $docOrigStatus,
                   ]);
                $val .= \yii\helpers\Html::tag( 'div', number_format($model['docSum'],2,',','&nbsp;')."&nbsp;руб",
                   [
                     //'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => 'text-align:right;'.$style,
                     'title'   => $docOrigStatus,
                   ]);    
                
                 return $val;
               }
            ],            

            [
                'attribute' => '-',                
                'label'     => 'Закупка',
                'format' => 'raw',  
                'contentOptions'   =>   ['style' => 'width:100px;'] ,   
                  
               'value' => function ($model, $key, $index, $column) use ($puchaseRole){ 
                
                if (empty($model['refSupplierSchet'])) return "Нет счета";
                              
               $purchList= Yii::$app->db->createCommand("Select {{%purchase}}.id, {{%purchase}}.dateCreation, purchRole 
                 from  {{%purch_schet_lnk}},{{%purchase}}  
                 where {{%purch_schet_lnk}}.purchRef={{%purchase}}.id 
                 and   {{%purch_schet_lnk}}.schetRef = :schetRef",                  
                 [':schetRef' => $model['refSupplierSchet'],])->queryAll();
                 
               if (count($purchList) == 0) // для совместимости
               $purchList= Yii::$app->db->createCommand("Select id, dateCreation, 0 as purchRole  
                 from  {{%purchase}}
                 where {{%purchase}}.supplierShetRef = :supplierShetRef",                  
                 [':supplierShetRef' => $model['refSupplierSchet'],])->queryAll();

                $val="";    
                for ($i=0;$i<count($purchList); $i++)
                {
                 $rec= $puchaseRole[$purchList[$i]['purchRole']]." ";
                 $rec.=$purchList[$i]['id']." ";
                 $rec.=date("d.m", strtotime($purchList[$i]['dateCreation']));
                 $action="openWin('store/purchase&id=".$purchList[$i]['id']."','childWin')";   
                 $val.= \yii\helpers\Html::tag( 'div', $rec, 
                   [
                     'class'   => 'clickable',
                     //'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Закупка",
                   ]); 
                    
                }
                
                $action= "addPurch(".$model['id'].",".$model['refDocOplata'].");";
                 $val.= \yii\helpers\Html::tag( 'div', "<span class=' 	glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "Добавить",
                     'style'   => 'width:15px;float:right;',
                   ]); 
                    
                return $val;
                    
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
               if  ($model['extractStatus'] > 1) $action = "alert('Оплата сформирована!')";
               if (empty($model['refOrg'])) $action ="alert('Организация не распознана!')";
               else{
               $alert ="";
                    if(empty($model['orgFullTitle'])) $alert .="Не заполнено полное название организации \\n";
                    if(empty($model['orgINN'])) $alert .="Не заполнено поле ИНН \\n";        
                    if(empty($model['orgKPP']) && mb_strlen($model['orgINN'],'utf-8') != 12) $alert .="Не заполнено поле КПП \\n";        
                    if(empty($model['orgBIK'])) $alert .="Не заполнено поле БИК \\n";        
                    if(empty($model['orgAccount'])) $alert .="Не заполнено поле расчетный счет \\n";        
                    if(empty($model['orgBank'])) $alert .="Не заполнено поле Банк \\n";        
                    if(empty($model['orgKS']) && empty($model['flgKS'])) $alert .="Не заполнено поле Корсчет";                

                    if(empty($model['refAccount'])) $alert ="Не выбран счет организации \\n";

               if(!empty($alert)) $action ="alert('".$alert."')";
               }
            
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => $style,
                   ]);
                   
               $action = "if ( confirm('Вы уверены, что хотите исключить документ из оплаты?') ) removeDocFromOplata(".$model['id'].");" ;   
               $style='background:White;color:Crimson;font-size:11px;';    
               $val .= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-remove'></span>", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Исключить из оплаты',
                     'style'   => $style,
                   ]);
                   
                return $val;
                    
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
                 if  ($model['extractStatus'] > 1) $readonly=true;
                                              else $readonly=false;
                 return Html::textInput( 
                          $id, 
                          $model['sumToOplate'],                                
                              [
                              'readonly' => $readonly,
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            
            [
                'attribute' => 'dateToOplata',                
                'label'     => 'Когда',
                'filter' => [ '0' => 'Все', '1' => 'Просроченные',  '2' => 'На сегодня', '3' => 'На завтра', '4' => 'Далее', '5' => 'Не задан' ],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                   $regTime = strtotime($model['dateToOplata']);
                   if ($regTime > 100) $val=date("d.m.Y", $regTime);
                   else $val= "";
                   $action =  "saveField(".$model['id'].", 'dateToOplata',".$model['refDocOplata'].");"; 
                   
                  $id = $model['id']."dateToOplata".$model['refDocOplata']; 
                  if  ($model['extractStatus'] > 1) {$readonly=true;$action ="alert('Оплата принята. Изменения не будут сохранены.')";}
                                              else  {$readonly=false;}
                   
                   
                   return    DatePicker::widget([
                        'name' => $id,
                        'id'   => $id,
                        'value' => $val,    
                        'type' => DatePicker::TYPE_INPUT,
                        'options' => [
                        'onchange' => $action,
                        'readonly' => $readonly,
                        'style' => 'width:65px;font-size:11px; padding:1px',
                        ],
                        'pluginOptions' => [    
                        'autoclose'=>true,
                        'format' => 'dd.mm.yyyy'        
                        ]
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
                 if  ($model['extractStatus'] > 1) $readonly=true;
                                              else $readonly=false;
   
                 return Html::textInput( 
                          $id, 
                          $model['NDS'],                                
                              [
                              'readonly' => $readonly,
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
                 if  ($model['extractStatus'] > 1) $readonly=true;
                                              else $readonly=false;
                                   
                
                                   
                 $val= Html::textInput( 
                          $id, 
                          $model['payPurpose'],                                
                              [
                              'title'    => $model['docNote']." ".$model['payPurpose'],
                              'readonly' => $readonly,
                              'class' => 'form-control',
                              'style' => 'width:150px;font-size:11px; padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                 if (!empty($model['refManager'])){             
                 $userFIO= Yii::$app->db->createCommand("Select userFIO from  {{%user}}
                 where id = :refManager",[':refManager' => $model['refManager'],])->queryScalar();
                 $val.="<br>".$userFIO;
                 }                     
                              
                return $val;              
               }                
            ],            
            
            
            [
                'attribute' => 'extractStatus',                
                'label'     => 'Принять',
                'format' => 'raw',    
                'filter' => ['0' => 'Все', '1' => 'Не принято', '2' => 'Принято', '3' => 'В оплате', '4' => 'Оплачено'],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."extractStatus".$model['refDocOplata'];
                $style="";    
                $val ="&nbsp;";                
               
               $alert ="";
                    if(empty($model['orgFullTitle'])) $alert .="\n Не заполнено полное название организации \n";
                    if(empty($model['orgINN'])) $alert .="Не заполнено поле ИНН \n";        
                    if(empty($model['orgKPP']) && mb_strlen($model['orgINN'],'utf-8')!=12) $alert .="Не заполнено поле КПП \n";        
                    if(empty($model['orgBIK'])) $alert .="Не заполнено поле БИК \n";        
                    if(empty($model['orgAccount'])) $alert .="Не заполнено поле расчетный счет \n";        
                    if(empty($model['orgBank'])) $alert .="Не заполнено поле Банк \n";        
                    if(empty($model['orgKS']) && empty($model['flgKS'])) $alert .="Не заполнено поле Корсчет";                

               
               $action =  "switchData(".$model['id'].", 'extractStatus',".$model['refDocOplata'].");";                    
               $status =$model['extractStatus'];
               if (empty($model['refOrg'])) $status = 11;
               if (!empty($alert)) $status = 11;
               if (!empty($model['refDocOplata'])) $ppNum = 10000+$model['refDocOplata'];
               else $ppNum = "";
               $mark ="&nbsp;";
               switch ($status) 
               {
                 case 1:    
                   $style='background:LightGreen;color:LightGreen;'; 
                   $title = 'Принято';
                 break;
                 case 11:    
                   $style='background:Yellow;'; 
                   if ($model['extractStatus'] == 4)$style .="color:Green;";
                                               else $style .="color:Yellow;";
                   $title = 'Организация не распознана или реквезиты не полны';
                 break;

                 case 3:    
                   $style='background:LightBlue;color:Green;'; 
                   $title = 'Сформировано. П/П № '.$ppNum;
                   $action =  "if (confirm('Платежное поручение уже было сформировано. Сформировать повторно?') == true) {
                   switchData(".$model['id'].", 'extractStatus',".$model['refDocOplata'].");}";                                       
                 break;                 
                 case 4:    
                   $style='background:Blue;color:White;'; 
                   $title = 'Оплачено П/П № '.$ppNum;
                   $action = "alert('Платежное поручение уже оплачено!');";
                 break;                 
                 case 5:    
                   $style='background:Green;color:White;'; 
                   $title = 'Оплачено П/П № '.$ppNum;
                   $action = "alert('Платежное поручение уже оплачено!');";
                 break;                                  
                 case 6:    
                   $style='background:DarkGreen;color:White;'; 
                   $title = 'Оплачено П/П № '.$ppNum;
                   $action = "alert('Платежное поручение уже оплачено!');";
                 break;                 
                case -1:    
                   $style='background:Crimson;color:Crimson;'; 
                   $title = 'Отказано';
                   $action = "extrsctDenied();";
                 break;                 
                 
                 default: 
                   $style='background:White;';
                   $title = 'Принять';
               }     
               
                 $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) 
                 from  {{%supplier_oplata}}, {{%doc_supplier_lnk}}
                 where {{%supplier_oplata}}.id = {{%doc_supplier_lnk}}.supplierOplataRef
                 AND   {{%doc_supplier_lnk}}.isLnk =1
                 AND {{%doc_supplier_lnk}}.docOplataRef =:docOplataRef",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryScalar();
                        
               if ($oplSum >= $model['sumToOplate'])                   
                   $mark = "<span class='glyphicon glyphicon-ok'></span>";
               
               $title .= $alert;
               $val = \yii\helpers\Html::tag( 'div',$mark, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => "font-size:10px;".$style,
                   ]);
                   
                if ($status == 0){   
                $id = $model['id']."removeData".$model['refDocOplata']; 
                $action =  "switchData(".$model['id'].", 'removeData',".$model['refDocOplata'].");";                    
                $style="";                              
                $val .= \yii\helpers\Html::tag( 'span', "", 
                   [
                     'class'   => 'glyphicon glyphicon-trash clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Сбросить оплату",
                     'style'   => "padding:5px;padding-left:15px;top:3px;".$style,
                     
                ]);}
                                
                return "<nobr>".$val."</nobr>".$ppNum;
                    
               }
                               
            ],            
  


            /*[
                'attribute' => 'removeData',                
                'label'     => 'Очистить',
                'format' => 'raw',    
                'value' => function ($model, $key, $index, $column) {                    
                
                $action =  "switchData(".$model['id'].", 'removeData',".$model['refDocOplata'].");";                    
                if (!empty($model['refSupplierOplata'])) return "&nbsp;";
                  //  $action = "alert('Зарегестрирована оплата в 1С!')";
                $id = $model['id']."removeData".$model['refDocOplata']; 
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
                               
            ],*/            
            
            [
                'attribute' => 'extractSum',                
                'label'     => 'Выписка',
                'format' => 'raw',  
                  
               'value' => function ($model, $key, $index, $column) {                    
                if (empty($model['refDocOplata'])) return "&nbsp;";    
                
                $docNum = $model['id']+5000; //ожидаемый номер платежки
                $oplSum= Yii::$app->db->createCommand("Select Sum(creditSum+debetSum) as N  from  
                {{%bank_extract}},{{%doc_extract_lnk}} 
                 where {{%bank_extract}}.id={{%doc_extract_lnk}}.extractRef
                 And docOplataRef =:docOplataRef AND isLnk =1",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryScalar();                                
                $id = $model['id']."extractSum".$model['refDocOplata'];

                $oplN= Yii::$app->db->createCommand("Select count({{%bank_extract}}.id) as N  from  
                {{%bank_extract}},{{%doc_extract_lnk}} 
                 where {{%bank_extract}}.id={{%doc_extract_lnk}}.extractRef
                 And docOplataRef =:docOplataRef AND isLnk =1",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryScalar();   
                $val =""; 
                if ($oplN == 1){
                $oplDate= Yii::$app->db->createCommand("Select recordDate  from  
                {{%bank_extract}},{{%doc_extract_lnk}} 
                 where {{%bank_extract}}.id={{%doc_extract_lnk}}.extractRef
                 And docOplataRef =:docOplataRef AND isLnk =1",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryScalar();   
                 $val="<br>(".date("d.m.Y",strtotime($oplDate)).")";                       
                }
                if ($oplN > 1){$val="<br>(".$oplN.")";}
                 

                
                $action =  "openExtractList(".$model['refDocOplata'].", 'showSel');";                    
                $style="";
                if ($oplSum == 0) {
                    $style = "color:Crimson;";
                    $action =  "openExtractList(".$model['refDocOplata'].", 'showAll');";                    
                    }                
                               
                return \yii\helpers\Html::tag( 'div', number_format($oplSum,2,'.','&nbsp;').$val, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Оплаты по выпискам",
                     'style'   => "padding:5px;margin:5px;".$style,
                   ]);                   
               }
            ],

            [
                'attribute' => 'oplata1c',                
                'label'     => 'по 1С',
                'format' => 'raw',  
                  
               'value' => function ($model, $key, $index, $column) { 

              //  return $model['refDocOplata'];
               
                if (empty($model['refDocOplata'])) return "&nbsp;";    
                $oplN = 0;
                //if ($model['refSupplierSchet'] <= 0) $oplSum = 0;
                //else
                {
                 $oplSumL= Yii::$app->db->createCommand("Select Sum(oplateSumm) as S, Count({{%supplier_oplata}}.id) as N 
                 from  {{%supplier_oplata}}, {{%doc_supplier_lnk}}
                 where {{%supplier_oplata}}.id = {{%doc_supplier_lnk}}.supplierOplataRef
                 AND   {{%doc_supplier_lnk}}.isLnk =1
                 AND {{%doc_supplier_lnk}}.docOplataRef =:docOplataRef",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryOne();
                    $oplSum = $oplSumL['S'];
                    $oplN = $oplSumL['N'];
                }
                if ($oplN == 1)
                {
                 $oplDate= Yii::$app->db->createCommand("Select oplateDate
                 from  {{%supplier_oplata}}, {{%doc_supplier_lnk}}
                 where {{%supplier_oplata}}.id = {{%doc_supplier_lnk}}.supplierOplataRef
                 AND   {{%doc_supplier_lnk}}.isLnk =1
                 AND {{%doc_supplier_lnk}}.docOplataRef =:docOplataRef",                  
                 [':docOplataRef' => $model['refDocOplata'],])->queryScalar();
                }
                $action =  "openOplataList(".$model['refDocOplata'].", 'showSel');";                    
                $id = $model['id']."oplata1c".$model['refDocOplata'];
                $style="";
                if ($oplSum == 0) {
                    $style = "color:Crimson;";
                    $action =  "openOplataList(".$model['refDocOplata'].", 'showAll' );";                    
                    }                
                $val ="";   
                if ($oplN == 1) $val.="<br>(".date("d.m.Y",strtotime($oplDate)).")";
                if ($oplN > 1) $val.="<br>(".$oplN.")";
                return \yii\helpers\Html::tag( 'div', number_format($oplSum,2,'.','&nbsp;').$val, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Открыть список оплат",
                     'style'   => "padding:5px;margin:5px;".$style,
                   ]);
                
                    
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
Modal::begin([
    'id' =>'selectPayOrderDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectPayOrderDialogFrame' width='570px' height='720px' frameborder='no'   src='/bank/buh/supplier-oplata&noframe=1&refSuppSchet=0' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectExtractDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectExtractDialogFrame' width='570px' height='720px' frameborder='no'   src='/bank/buh/extract-oplata&noframe=1&refDocOplata=0&flt=showAll' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
Modal::begin([
    'id' =>'selectPurchDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectPurchDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/store/purchase-select&noframe=1' seamless>
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

echo $form->field($model, 'overdueVal' )->hiddenInput(['id' => 'overdueVal' ])->label(false);
echo $form->field($model, 'todayVal' )->hiddenInput(['id' => 'todayVal' ])->label(false);
echo $form->field($model, 'tomorrowVal' )->hiddenInput(['id' => 'tomorrowVal' ])->label(false);
echo $form->field($model, 'furtherVal' )->hiddenInput(['id' => 'furtherVal' ])->label(false);    

echo "<input type='submit'>";
ActiveForm::end(); 
?>

<pre>
<?php
//print_r($dataPP);

?>
</pre>

