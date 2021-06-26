<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;



$this->title = 'Склады отгрузки';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script >

function saveField(id, type)
{
    idx= type+id;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    if(id > 0 && type != 'rmSclad') document.getElementById('dataVal').value=document.getElementById(idx).value;

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-deliver-sclad',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            if(res.reload == true) document.location.reload(true);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });
}
</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
    [

        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'sladTitle',
				'label'     => 'Склад',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "sladTitle".$model['id'];
                    $action =  "saveField(".$model['id'].", 'sladTitle');";
                     return Html::textInput(
                          $id,
                          $model['sladTitle'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:265px; font-size:11px;padding:1px;',
                              'id' => $id,
                              'onchange' => $action,
                              ]);
                },
            ],

			[
                'attribute' => 'scladAdress',
				'label'     => 'Адрес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "scladAdress".$model['id'];
                    $action =  "saveField(".$model['id'].", 'scladAdress');";
                     return Html::textInput(
                          $id,
                          $model['scladAdress'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:465px; font-size:11px;padding:1px;',
                              'id' => $id,
                              'onchange' => $action,
                              ]);
                },
			],

			[
                'attribute' => '',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $id = "rmSclad".$model['id'];
                    $action =  "saveField(".$model['id'].", 'rmSclad');";
                 return \yii\helpers\Html::tag( 'span', '',
                   [
                     'class'   => 'glyphicon glyphicon-remove clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:14px;color:Crimson',
                     'title'   => 'Удалить',
                   ]);


                },
			],


        ],
    ]
	);

?>

     <div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>


        <?php
        $id = 'btnAddNewWare';
        $action =  "saveField(0, 'addSclad');";
        echo \yii\helpers\Html::tag( 'span', '',
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:14px;',
                     'title'   => 'Добавить ',
                   ]);
        ?>
        &nbsp;
     </div>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=market/save-schet-param']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end();
?>
