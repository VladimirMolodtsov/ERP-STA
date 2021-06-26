<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - выписка';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<h3> Детализация по выпискам </h3>
</p>
<hr>

<form action="index.php" method ='get'>
<input type='hidden' name='r' value='/bank/operator/show-income'>
<input type='hidden' name='noframe' value='1'>
<div class='row'>
<div class='col-md-3'>

</div>

<div class='col-md-3'>
<input type='date' class='form-control' name='from' id='deliverDate' value='<?= $model->from ?>' >
</div>
<div class='col-md-1'>

</div>

<div class='col-md-3'>
<input type='date' class='form-control' name='to' id='deliverDate' value='<?=  $model->to ?>' >
</div>

<div class='col-md-2'>
<input type='submit' value='Фильтр' class='form-control'></input>
</div>

</div>
</form>

<hr>

  <?= \yii\grid\GridView::widget(
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
                'attribute' => 'recordDate',
                'label'     => 'Дата',
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['recordDate'])+4*3600);
               }

            ],            

            [
                'attribute' => 'debetOrgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',     
            ],            

            [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
            ],            
                        
            [
                'attribute' => 'creditSum',
                'label'     => 'Сумма поступления',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditSum'],2,',','&nbsp;');
               }
                
            ],            

            
            [
                'attribute' => 'Основание',
                'label'     => 'Основание',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['description'];
               }
                
            ],            
                                    
/*           'docNum', 
            'contrAgentBank', 
            'description', 
            'VO', */

            /****/
        ],
    ]
); 
?>
