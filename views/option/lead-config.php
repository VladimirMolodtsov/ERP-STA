<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;


$this->title = 'Настройка сценария работы с лидами';
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<style>

</style>

<script type="text/javascript">

function saveField(id, type)
{
    
    idx= type+id;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=option/save-config',
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

<h3><?= Html::encode($this->title) ?></h3>

<hr>
<h4>Сроки обработки:</h4>
<p>Срок выполнения задачи, от момента регистрации лида (дни) </p>


<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered', 'style' => 'width:600px;' ],
        'columns' => [
			[
                'attribute' => 'keyTitle',
				'label'     => 'Наименование',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'keyValue',
				'label'     => 'Значение',
				'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "keyValue".$model['id'];
                    $action =  "saveField(".$model['id'].", 'keyValue');"; 
                     return Html::textInput( 
                          $id, 
                          $model['keyValue'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:75px; font-size:13px;padding:1px;', 
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

$form = ActiveForm::begin(['id' => 'saveDataForm']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
ActiveForm::end(); 



if (!empty($model->debug))
{
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}




?>
