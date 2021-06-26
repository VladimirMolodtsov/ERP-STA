<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Настройка классификации закупок';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/purch-classify-cfg.js');

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}
</style>
  
<script>
function addNewMask ()
  {
    openSwitchWin("managment/head/add-purch-mask");      
  } 

function removeRow(rowRef)
{    
 openSwitchWin("managment/head/remove-purch-mask&maskRef="+rowRef);      
}


</script>

<div class ='row'>
   
   <div class ='col-md-10'>   
   </div>
     
  <div class='col-md-2' style='text-align:right;'>
  <a href='index.php?r=managment/head/purch-classify'><span class='glyphicon glyphicon-save'></span></a></div>  
</div>

<div class ='spacer'></div>
<?php
$typeArray = $model->getClassifyTypes();
                      
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
      //  'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],
        
        'columns' => [       
            [
                'attribute' => 'typeTitle',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],

                'value' => function ($model, $key, $index, $column) use( $typeArray)  {

                $id = "typeRef".$model['id'];
                $action = "saveField(".$model['id'].",'typeRef')";
                return Html::dropDownList($id , $model['typeRef'], $typeArray,
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:100%;',
                'onChange' => $action,                
                ]);
                },
                
            ],
                          
            [
                'attribute' => 'mask',
                'label' => 'Маска',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],

                'value' => function ($model, $key, $index, $column)  {
     
                $c = "";
                $id = "mask".$model['id'];
                $action = "saveField(".$model['id'].",'mask')";
                if ($model['typeTitle'] == '%') $c = 'color:Crimson';
                return Html::textInput($id , $model['mask'], 
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:100%;'.$c,
                'onChange' => $action,                
                ]);
                },
                
            ],

            [
                'attribute' => 'useOrder',
                'label' => 'Порядок применения',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],

                'value' => function ($model, $key, $index, $column)  {
                $id = "useOrder".$model['id'];
                $action = "saveField(".$model['id'].",'useOrder')";                
                return Html::textInput($id , $model['useOrder'], 
                [
                'id'    => $id,
                'class' => 'form-control',
                'style' => 'width:100%;',
                'onChange' => $action,                
                ]);
                },
                
            ],

            [
                'attribute' => '-',
                'label' => '',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:4px;'],
                'value' => function ($model, $key, $index, $column) {                     
                $action = "removeRow(".$model['id'].");";
                return Html::tag('span', '', 
                [
                'class' => 'glyphicon glyphicon-remove-circle',
                'style' => 'color:Crimson',
                'onclick' => $action,
                ]);
                }               
            ],                            
                     
        ],
    ]
);


?>

    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewMask();'><span class='glyphicon glyphicon-plus'></span></a></div>  
    </div>



<input type='button' class='btn btn-primary' onclick='window.opener.location.reload(false); window.close();' value='Завершить'>


<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/head/save-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
  //echo "<input type='submit'>";
ActiveForm::end(); 
?>
