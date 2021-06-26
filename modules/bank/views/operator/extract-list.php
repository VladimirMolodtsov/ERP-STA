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
document.location.href='index.php?r=/bank/operator/extract-list&fromDate='+fromDate+'&toDate='+toDate; 
    
}   
</script>
<div class='row'>

<div class ='col-md-3' style='text-align:left'>
<div class="item-header">Загруженные банковские выписки:</div> 
</div>
    <div class ='col-md-3' style='text-align:left'>
    Звгружено за период:
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
    
   </div>   

    <div class="col-md-2" style='text-align:right'>
    
    </div>       


</div>  

<div class='spacer'></div>

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
/*$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/bank/operator/save-extraction-lnk']);
echo $form->field($detailModel, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($detailModel, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($detailModel, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);*
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
