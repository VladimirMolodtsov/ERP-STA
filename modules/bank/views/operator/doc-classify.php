<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use kartik\grid\GridView;

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Классификатор документов';
//$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');



?>

<style> 

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
.localLabel {
width:65px;
padding:2px;
font-size:10px;
color:black;
word-wrap: normal;
top:0px;
}

th{
word-wrap: normal;
width:65px;
top:0;

}


</style>

<script>

function switchBox (classRef, grpRef)
{

var url = 'index.php?r=bank/operator/switch-classify&classRef='+classRef+'&grpRef='+grpRef;
console.log(url);   
    var data = new Array();
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSync(res);    
        },
        error: function(){
            alert('Error while retreving data!');
        }
    });	
}

function showSync(res)
{

   console.log(res);   
   if (res['res'] == false) return;           
   idx=res['classRef']+'_'+res['grpRef'];
   if (res.grpRef == 'DEL') {
       document.location.reload(true);  
       return;
       }
      
   switch (res['value'])
   {
     case 0:
      document.getElementById(idx).style.background='White';
     break;    
     case 1:
      if (res.grpRef == 'ref1C') document.getElementById(idx).style.background='Blue';
                            else document.getElementById(idx).style.background='LightGreen';
     break;
     case 2:     
      document.getElementById(idx).style.background='Blue';
      document.location.reload(true);  
     break;   
   }
  
   console.log(res);   
}

function addClass()
{
  $('#editClassDialog').modal('show'); 
}

</script>

<div class='row'>
 <div class='col-md-9'></div>
 <div class='col-md-1'><span onclick='addClass()' class='glyphicon glyphicon-plus clickable'></span></div> 
<!-- <div class='col-md-1'><a href='index.php?r=/bank/operator/doc-grp-cfg&noframe=1'><span class='glyphicon glyphicon-cog'></span></a></div> -->
</div>

<?php
$columns[]= [
                'attribute' => 'docType',
                'label'     => 'Класс',
                'format' => 'raw',   
                'contentOptions' => ['style' => 'width:190px;', ],                
                'value' => function ($model, $key, $index, $column)  {	                                   
                    return "<div  style='width:185px'>".$model['docType']."</a>";
                },                
            ];

/*
метка требования связи с 1С
*/

$columns[]=[
                'attribute' => 'isRef1C',
                'label'     => '1С',
                 'contentOptions' => [ 'align' =>'center'], 
                'format' => 'raw',   
               'value' => function ($model, $key, $index, $column){                                                
                $id=$model['classRef'].'_ref1C';
                
                if ($model['isRef1C'] > 0) $bg="background-color:Blue; color:White";
                                       else $bg="background-color:White";
                $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'onclick' => "switchBox(".$model['classRef'].",'ref1C')",
                     'style'  => 'font-size:11px;padding:1px;'.$bg,
                     'id'  => $id,
                     'title' => 'Есть связь с 1С',
                   ]);                   
                $val.="&nbsp;";   
                  
                 return $val;  
                },                                
            ];

  

$model->loadGrpList();
$columns=array_merge ($columns, $model->createColumns());      


$columns[]=[
                'attribute' => 'classRef',
                'label'     => '',
                 'contentOptions' => [ 'align' =>'center'], 
                'format' => 'raw',   
               'value' => function ($model, $key, $index, $column){                                                
                $id=$model['classRef'].'_Del';
                
                $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-trash',
                     'onclick' => "switchBox(".$model['classRef'].",'DEL')",
                     'style'  => 'font-size:11px;padding:1px;',
                     'id'  => $id,
                     'title' => 'Удалить',
                   ]);                   
                $val.="&nbsp;";   
                  
                 return $val;  
                },                                
            ];




 echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
      //  'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
      
        'responsive'=>true,
        'hover'=>false,
        
        /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
         ], */       
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
         ],


        'columns' => $columns
    ]
); 
?>

<?php
Modal::begin([
    'id' =>'editClassDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?>

<?php
$form = ActiveForm::begin(['id' => 'editClassForm']);
echo $form->field($model, 'id' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'docType' )->textInput(['id' => 'docType' ])->label(false);
echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform', ]);        
ActiveForm::end(); 
?>

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

