<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 


$this->title = 'Платежные поручения';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
//$this->registerJsFile('@web/js/modules/bank/store-oplata.js');



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

</style>


<script type="text/javascript">

function downloadData(id)
{
  url="/bank/buh/download-pay-order&id="+id;
  openWin(url,'download');  
  document.location.reload(true); 
}

function openDetail(id)
{
  url="/bank/buh/show-pay-order&id="+id;
  openWin(url,'childWin');  
}

</script> 




<h3><?= $this->title?></h3> 

<hr>
<?php
 
  echo GridView::widget(
    [
        'dataProvider' => $provider,
  //      'filterModel' => $model,
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
                'attribute' => 'docType',
                'filter' => false,
                'label'     => 'Документ',
                'format' => 'raw',                            
            ],            
            [
                'attribute' => 'docNum',
                'filter' => false,
                'label'     => '№/дата',
                'format' => 'raw',                            
                 'value' => function ($model, $key, $index, $column) {                 
      
                  $title="";
                  if (!empty($model['refDocOplata']) ) $refDocOplata =$model['refDocOplata'];
                  else $refDocOplata= $model['docNum']- 10000;
                  
                 $docData  =Yii::$app->db->createCommand("Select refDocument, docIntNum from  
                {{%doc_oplata}}, {{%documents}}  where {{%doc_oplata}}.refDocument= {{%documents}}.id 
                and {{%doc_oplata}}.id =:refDocOplata",                  
                 [':refDocOplata' => $refDocOplata,])->queryOne();                                
                 
                  $title="Документ № ".$docData['docIntNum']."\n";
                  $title.="Требование № ".$refDocOplata."\n Документ id ".$docData['refDocument'];
                  $regDate =  $model['docNum']."<br>".date("d.m.Y",strtotime($model['docDate']));
                  $val = \yii\helpers\Html::tag( 'div', $regDate , 
                   [
                   'title' => $title,
                   ]);
                 return $val;
                 }
            ],            
            
           [
                'attribute' => 'summ',
                'filter' => false,
                'label'     => 'Сумма/НДС',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['summ'],2,',','&nbsp;')."<br>".$model['NDS']."%";
               }
            ],            

           [
                'attribute' => 'beneficiaryTitle',
                'filter' => false,
                'label'     => 'Контрагент',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['beneficiaryTitle'];
               }
            ],   
            
           [
                'attribute' => 'beneficiaryInn',
                'filter' => false,
                'label'     => 'ИНН/КПП',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['beneficiaryInn']."<br>".$model['beneficiaryKpp'];
               }
            ],   

           [
                'attribute' => 'beneficiaryAccount',
                'filter' => false,
                'label'     => 'РС/КС',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                        return $model['beneficiaryAccount']."<br>".$model['beneficiaryCorrAccount'];
               }
            ],   

           [
                'attribute' => 'beneficiaryBank1',
                'filter' => false,
                'label'     => 'Банк/БИК',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                        return $model['beneficiaryBank1']."<br>".$model['beneficiaryBik'];
               }
            ],   
           [
                'attribute' => 'payPurpose',
                'filter' => false,
                'label'     => 'Назначение',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    

                $purpose = $model['payPurpose'];
                if ($model['NDS'] == 0)  $purpose.=', без НДС';
                          else 
                          { 
                            $ndsSum = $model['summ']/(1+$model['NDS']/100);
                            $ndsSum = $ndsSum*($model['NDS']/100);
                            $ndsShowSum = intval($ndsSum *100)/100;
                            if ($ndsShowSum<$ndsSum)$ndsShowSum+=0.01;
                            $purpose.=', В том числе НДС '.$model['NDS'].'% - '.number_format($ndsShowSum,2,'.',"").' руб.';
                          }

                        return $purpose;
               }
                
                
                
                
                
                
            ],   
        ],
    ]
); 

?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action'=>'index.php?r=/bank/buh/save-store-oplata']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>



