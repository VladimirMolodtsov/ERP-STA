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
.orginfo {
  
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
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
                'attribute' => 'creationDateTieme',
                'label'     => 'Создан',
                'format' => 'raw',                            
                 'value' => function ($model, $key, $index, $column) {                 
      
                  $regDate =  date("d.m.y H:i",strtotime($model['creationDateTieme']));
                  
                 if ($model['haveDetail'] == 0){
                 $ref=Yii::$app->request->baseUrl."/../modules/bank/".$model['fname'];
                 $val = "<a target='_blank' href='".$ref."'>$regDate</a>";                  
                 return $regDate;                    
                 }
                 
                  $action =  "openDetail(".$model['id'].");";                   
                  $val = \yii\helpers\Html::tag( 'div', $regDate , 
                   [
                     'onclick' => $action,                   
                     'class'   => 'clickable',
                     'title'   => $regDate,
                   ]);
                 return $val;
               }

            ],  
            
           [
                'attribute' => 'totalSum',
                'filter' => false,
                'label'     => 'На сумму',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['totalSum'],2,',','&nbsp;');
               }
                
            ],            

           [
                'attribute' => 'fname',
                'filter' => false,
                'label'     => 'Скачать',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                
                 if ($model['haveDetail'] == 0){
                 $ref=Yii::$app->request->baseUrl."/../modules/bank/".$model['fname'];
                 $val = "<a target='_blank' href='".$ref."'><span class='glyphicon glyphicon-download-alt'></span></a>";                  
                 return $val;                    
                 }
                 
                 
                 
               $action =  "downloadData(".$model['id'].");"; 
               $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-download-alt'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Скачать',
                   ]);
                return $val;                    
                                              
    
               }
                
            ],            

            [
                'attribute' => 'isSend',                
                'label'     => 'Скачено',
                'format' => 'raw',  
                'filter' => ['0' => 'Все', '1' => 'Да', '2' => 'Нет',],    
                'value' => function ($model, $key, $index, $column) {                    
                
                $id = $model['id']."isSend";
                $style="";    
                $val ="&nbsp;";                
                           
               if ($model['isSend'] >0 ) {$style='background:Green;'; }                 
                                   else  {$style='background:White;';}    
                                  
               $val = \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'title'   => 'Скачено',
                     'style'   => $style,
                   ]);
                return $val;                    
               }                               
            ],            

           [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',            
                
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



