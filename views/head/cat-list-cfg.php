<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;

$this->title = '';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');    

?>


<script type="text/javascript">

function saveField(id, type)
{   
    idx= type+id;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=head/save-cat-cfg',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

</script> 
 
 
<style>



</style>

<?php 

  echo  \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
//        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

       
            [
                'attribute' => 'id',
                'label' => 'Код',
                'format' => 'raw',
             ],   
       
            [
                'attribute' => 'catTitle',
                'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $id = "catTitle".$model['id'];
                 $action =  "saveField(".$model['id'].", 'catTitle');"; 
                 return Html::textInput( 
                          $id, 
                          $model['catTitle'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:300px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },                
             ],   
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=head/save-cat-cfg']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
