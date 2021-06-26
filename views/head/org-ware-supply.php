<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\bootstrap\Collapse;
use yii\bootstrap\ActiveForm;
use yii\db\Query;


$this->title = 'Список отгрузок по организации';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
    
 ?>


<script type="text/javascript">



</script> 
 
<style>



</style>
<h4><?= $this->title?></h4>

<?php 

  echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],

        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => false,
        'panel' => ['type'=>'success',],

        'responsive'=>true,
        'hover'=>true,

        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [


             [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'format' => 'raw',
//                'contentOptions' => ['width' => '250px'],
            ],

             [
                'attribute' => 'schetDate',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return $model['schetNum']." от ".date("d.m.Y", strtotime($model['schetDate']));
                },


             ],   
            [
                'attribute' => 'supplyDate',
                'label' => 'Отгрузка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return $model['supplyNum']." от ".date("d.m.Y", strtotime($model['supplyDate']));
                },

            ],

            [
                'attribute' => 'supplyGood',
                'label' => 'Товар',
                'format' => 'raw',
            ],
            [
                'attribute' => 'supplyCount',
                'label' => 'К-во',
                'format' => 'raw',
            ],
            [
                'attribute' => 'supplyEd',
                'label' => 'Ед.',
                'format' => 'raw',
            ],
            [
                'attribute' => 'supplySumm',
                'label' => 'Сумма',
                'format' => 'raw',
            ],
            [
                'attribute' => 'ref1C',
                'label' => '1С',
                'format' => 'raw',
            ],


             
             
                                       
             

        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=head/save-org-job-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal', ])->label(false);
echo $form->field($model, 'dataNote' )->textArea(['id' => 'dataNote', 'style' =>'display:none' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
