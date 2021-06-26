<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

$this->title = 'Периодические задачи- шаблон';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<div class="item-header">Банковские операции:</div> 

 <?php
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
    

            [
                'attribute' => 'moduleTitle',
                'label'     => 'Модуль',
                'format' => 'raw',     
            ],            
                    
            [
                'attribute' => 'templateTitle',
                'label'     => 'Название',
                'format' => 'raw',     
            ],            

            
            [
                'attribute' => 'weekDay',
                'label'     => 'Дни недели',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['weekDay'];
               }
                
            ],            
            

           /****/
        ],
    ]
); 
Pjax::end(); 
?>


<a href='index.php?r=/tasks/main/new-template' class='btn btn-primary'>Новый шаблон</a>
