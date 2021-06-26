<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Товар в сделках';

 switch ($model->state)
   {
     case 1:
        $this->title .= " - Предложено (до согласования счета)";    
        break;     
     case 2:
        $this->title .= " - Согласовано (до оплаты счета)";    
        break;     
     
     case 3:
        $this->title .= " - Оплачено (до начала отгрузки)";    
        break;     
   }   

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>
<p>Виды товара на складах.</p>

<style>
.tb-head {    
    font-size:11px;
    //width:75px;
     word-wrap: break-word;
    word-break:  break-all;
    line-break: auto;  /* нет поддержки для русского языка */ 
    hyphens: manual;
}
</style>
  
<script>
</script>



<?php
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],        
//        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [        
        
           [
                'attribute' => 'wareTitle',
                'label' => 'Товар',
                'format' => 'raw',
            ],

           [
                'attribute' => 'wareCount',
                'label' => 'К-во',
                'format' => 'raw',
            ],
           [
                'attribute' => 'wareEd',
                'label' => 'Ед.',
                'format' => 'raw',
            ],
                    
           [
                'attribute' => 'warePrice',
                'label' => 'Цена',
                'format' => 'raw',
            ],
           [
                'attribute' => 'schetNum',
                'label' => 'счет №',
                'format' => 'raw',
            ],
           [
                'attribute' => 'schetDate',
                'label' => 'Счет дата',
                'format' => 'raw',
            ],
        
           [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
            ],
        
        
        
        ]
        
    ]
);
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectTypeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectTypeDialog'  src='index.php?r=store/ware-type-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectGroupDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectGroupDialog'  src='index.php?r=store/ware-group-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectProducerDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectGroupDialog'  src='index.php?r=store/ware-producer-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectWareDialog'  src='index.php?r=store/ware-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-warehouse-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

<?php
Modal::begin([
    'id' =>'showSyncProgress',
    //'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Поиск в 1С </h4>',
]);?>
<div style='width:100%; text-align:center;'><img src='img/ajax-loader.gif'></div>
<?php
Modal::end();
?>
