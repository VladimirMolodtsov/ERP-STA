<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 
use kartik\date\DatePicker;


$this->title = 'Банк - выписки (сверка)';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/operator.js');

$from = strtotime($detailModel->fromDate);
$to = strtotime($detailModel->toDate);
$dataPP=$detailModel->getStatPP ();
 ?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<style>

.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}

.leaf:hover {
    /*box-shadow: 0.4em 0.4em 5px #696969;*/
    border: 1px solid Blue; /* размер и цвет границы блока */
    background-color:#eaf2f8;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-selected:hover {        
    border: 1px solid Blue; /* размер и цвет границы блока */
}



.leaf-txt {    
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}



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
document.location.href='index.php?r=/bank/operator/extract-check&fromDate='+fromDate+'&toDate='+toDate; 
    
}


var errWin;
function selectError()
{
  $('#selectErrDialog').modal('show');       
  
  /*if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();*/    
}
function setErrFilter (d,month,year){
 $('#selectErrDialog').modal('hide');     
 
 var fltDate=year+'-'+month+'-'+d;
 
 document.location.href="index.php?r=bank/operator/extract-check&detail=1&fromDate="+fltDate+"&toDate="+fltDate;
 
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
   
   <?php $err= $detailModel->getBankExtractErrors(); ?>
    <div class="col-md-4" >
    <br>
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectError();'>
        <div class='leaf-txt' style='color:Crimson' > Ошибок  </div>
        <div class='leaf-val' style='color:Crimson'><?= $err['all'] ?></div> 
        <div class='leaf-sub'></div>
    </a>

    
        
    </div>   

    <div class="col-md-2">    
    <div  style='text-align:right'>
        <a href='index.php?<?=Yii::$app->request->queryString?>&format=csv' target='_blank' >Скачать</a> 
    </div>
    
    <div  style='text-align:right; margin-top:10px;'>
        <a class='btn btn-primary' style="width:130px; margin:2px;" href='index.php?<?=Yii::$app->request->queryString?>&format=print' target='_blank' ><span>
        Печать списка</span></a>
               <a class='btn btn-default'  style="width:130px; margin:2px;" href='index.php?<?=Yii::$app->request->queryString?>&format=short' target='_blank' ><span>
        Краткий формат</span></a>
    </div>
    
    
    </div>       


</div>  

<div class='spacer'></div>

 <?php
$typeArray = $model->getTypeArray();
$orgTypeArray= $detailModel->getOrgTypeArray();
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
                'contentOptions' => ['width' => '75px'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $pp = $model['docNum'];
                 $title = 'Платежное поручение №'.$pp." ".$model['description'];   
                 
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
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                 $id = "orgTitle_".$model['id'];
                
                $title = 'Плательщик '.$model['debetOrgTitle']."\n";
                $title .= 'Получатель '.$model['creditOrgTitle']."\n";
                
                 if (!empty($model['orgRef'])) $s = '';
                 else $s='color: Crimson;';
                 /*$action="openWin(\"site/org-detail&orgId=".$model['orgRef']."\", \"childwin\")";
                 return \yii\helpers\Html::tag( 'div', $model['orgTitle'], 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',                                        
                     'title'   => $title,
                   ]);
                   }
                   */
                   
                if ( $model['creditSum'] > 0 ) {
                    $orgTitle = $model['debetOrgTitle'];
                    $title = 'ИНН '.$model['debetINN']." Р/С ".$model['debetRS'];
                    $orgINN = $model['debetINN']; }
                else {
                    $orgTitle = $model['creditOrgTitle'];
                    $title = 'ИНН '.$model['creditINN']." Р/С ".$model['creditRs'];
                    $orgINN = $model['creditINN']; }
                
                 $action = "selectOrg(".$model['id'].",".$orgINN.")";                 
                 
                 return \yii\helpers\Html::tag( 'div', $orgTitle, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => $s,
                     'title'   => $title,
                   ]);
                   
                   
                    
                    
                },
            ],    
           
                                    
            [
                'attribute' => 'debetSum',
                'label'     => 'Расход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                 if (empty($model['debetSum']))$val="&nbsp;";
                 else  $val = "-".number_format($model['debetSum'],2,',','&nbsp;');                 
                 $title = '';   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                     'style'  => 'color: Brown;',
                   ]);
               }
            ],            

            [
                'attribute' => 'creditSum',
                'label'     => 'Приход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                 if (empty($model['creditSum']))$val="&nbsp;";
                 else  $val = number_format($model['creditSum'],2,',','&nbsp;');
                 $title = '';   
                 return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'cellInfo',
                     'title'   => $title,
                     'style'  => 'color: DarkGreen;',
                   ]);
               }
            ],            
           /****/
           
            
            [
                'attribute' => '_extractRef',                
                'label'     => 'Связано с',
                'format' => 'raw',  
                  
               'value' => function ($model, $key, $index, $column) {                    
                
                 if ($model['extractType'] == 1)
                 {                     
                  $list= Yii::$app->db->createCommand("Select 
                        schetNum, schetDate, ref1C, schetSumm
                        from  {{%schet}} where {{%schet}}.id=:refSchet",                  
                        [':refSchet' => $model['refSchet'],])->queryAll(); 
                       $N = count($list); 
                       if ($N == 0){
                       $action =  "openSchetList(".$model['id'].", 'showAll');";                                    
                        $val = \yii\helpers\Html::tag( 'div', '---',                 
                        [
                        'class'   => 'clickable',
                        'onclick' => $action,
                        'title'   => "Связанные счета ",
                        'style'   => "padding:5px;margin:5px;font-size:15px;color:Crimson",
                        ]);                   
                        }else{
                         $action =  "openSchetList(".$model['id'].", 'showSel');";                                    
                        $val ="";                
                        for ($i=0;$i<$N;$i++){                                               
                        $val .= \yii\helpers\Html::tag( 'div',                                
                        "Кл.счет&nbsp".$list[$i]['schetNum']."<br>от&nbsp;".date("d.m.Y", strtotime($list[$i]['schetDate'])),                 
                        [
                        'class'   => 'clickable',
                        'onclick' => $action,
                        'title'   => "Связанные счета ",
                        'style'   => "padding:5px;margin:5px;font-size:12px;",
                        ]);}    
                        }

                 }else 
                 {
                     
                    $list= Yii::$app->db->createCommand("Select 
                        docTitle, docURI,docOrigNum,docOrigDate,{{%doc_oplata}}.id as refDocOplata
                        from  {{%documents}},{{%doc_oplata}},{{%doc_extract_lnk}} 
                        where {{%documents}}.id={{%doc_oplata}}.refDocument
                        AND  {{%doc_extract_lnk}}.docOplataRef={{%doc_oplata}}.id
                        And extractRef =:extractRef AND isLnk =1 AND {{%documents}}.isOplate=1",                  
                        [':extractRef' => $model['id'],])->queryAll(); 
                        $N = count($list); 
                        if ($N == 0){
                       $action =  "openDocumentList(".$model['id'].", 'showAll');";                                    
                        $val = \yii\helpers\Html::tag( 'div', '---',                 
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
                 } 
                
                
                return $val;   
               }
            ],
            


            
            [
                'attribute' => 'orgDeal',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                             
                $strSql = "SELECT {{%bank_op_article}}.id, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id"; 
                        
                
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','grpTitle');       
                $operationArray[0]='не задан';

                 $c = "";
                 if ($model['orgDeal'] == 0) $c = 'color:Crimson;';                  
                 $id = "orgType".$model['id'];
                                
                 $action = "selectDeal(".$model['id'].",".$model['orgRef'].", ".$model['orgDeal']." )";                 
                 $v=$operationArray[$model['orgDeal']];
                 return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  =>  $c,
                   ]);
                   
                
                },
            ],    
                                        
                     
            [
                'attribute' => 'orgDeal',
                'label' => 'Статья',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                    
                $strSql = "SELECT {{%bank_op_article}}.id, article from {{%bank_op_article}}"; 
                    
                
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','article');       
                $operationArray[0]='не задан';

                 $c = "";
                 if ($model['orgDeal'] == 0) $c = 'color:Crimson;';                  
                 $id = "orgDeal".$model['id'];
                                
                 $action = "selectDeal(".$model['id'].",".$model['orgRef'].", ".$model['orgDeal']." )";                 
                 $v=$operationArray[$model['orgDeal']];
                 return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  =>  $c,
                   ]);
                   
                
                },
            ],    
                            
            
                   
            [
                'attribute' => '-',
                'label' => '№ в 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '120px'] ,  
                'value' => function ($model, $key, $index, $column) use($typeArray) {                        
                    
                $id = "bankOperation".$model['id'];
                $c="";
                
                $strSql = "SELECT regNum from {{%bank_operation}} where refBankExtract =".$model['id']; 
                $val = Yii::$app->db->createCommand($strSql)->queryScalar();                    
                if (empty($val)){
                    $val = "---";
                    $c = 'color:Crimson;';                  
                }
    
                $action = "openBankOperationList(".$model['id'].",".$model['orgRef'].")";                 
                 return \yii\helpers\Html::tag( 'div',$val, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  =>  $c,
                   ]);

                
                },
            ],    
           
            [
                'attribute' => '-',
                'label' => 'Статья по 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                $strSql = "SELECT article from {{%bank_operation}} where refBankExtract =".$model['id']; 
                $val = Yii::$app->db->createCommand($strSql)->queryScalar();                    
                 return $val;
                },
            ],                   

            [
                'attribute' => '-',
                'label' => 'Сделка по 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                $strSql = "SELECT operationNum from {{%bank_operation}} where refBankExtract =".$model['id']; 
                $val = Yii::$app->db->createCommand($strSql)->queryScalar();                    
                 return $val;
                },
            ],                   
            
                                  
        ],
    ]
); 
Pjax::end(); 
?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/bank/operator/save-extraction-param']);
echo $form->field($detailModel, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($detailModel, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($detailModel, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>




<?php
Modal::begin([
    'id' =>'selectDocLnkDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='selectDocLnkDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/bank/operator/doc-extract&noframe=1&refExtract=0' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='selectOrgDialogFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/doc-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectOrgDeal',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='selectOrgDealFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/site/org-deal-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectBankOperation',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],    
]);?><div style='width:600px'>
    <iframe id='selectBankOperationFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/bank-operation-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectErrDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectErrDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/bank/operator/extract-error-calendar&noframe=1&month=<?=date('m',strtotime($detailModel->toDate))?>&year=<?=date('Y',strtotime($detailModel->toDate))?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


   <?php /* 
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
    <td>Не совпадает <?=$dataPP['today']['N']?> на сумму <?= number_format($dataPP['today']['S'],2,'.','&nbsp;') ?> руб. </td>
        
    </tr>

    </table>
    */
    ?>
    
    
<?php
 if(!empty($detailModel->debug))
 {
  echo "<pre>";
 print_r ($detailModel->debug);
 echo "</pre>";
 }
?>

    
    
