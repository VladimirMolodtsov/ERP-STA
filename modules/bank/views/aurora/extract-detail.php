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



<h3> Детализация по выписке </h3>
</p>

<hr>

  <?= \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
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
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['debetOrgTitle']."<br>".$model['debetINN']." ".$model['debetKPP'];
               }
                
            ],            

/*            [
                'attribute' => 'debetINN',
                'label'     => 'ИНН',
                'format' => 'raw',                     
            ],
            [
                'attribute' => 'debetKPP',
                'label'     => 'КПП',
                'format' => 'raw',                     
            ],*/
                        
            [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['creditOrgTitle']."<br>".$model['creditINN']." ".$model['debetKPP'];
               }                
            ],            

/*            [
                'attribute' => 'creditINN',
                'label'     => 'ИНН',
                'format' => 'raw',                     
            ],
            [
                'attribute' => 'debetKPP',
                'label'     => 'КПП',
                'format' => 'raw',                     
            ],*/


            [
                'attribute' => 'creditSum',
                'label'     => 'Приход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditSum'],2,',','&nbsp;');
               }
                
            ],            
            
                                    
            [
                'attribute' => 'debetSum',
                'label'     => 'Расход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['debetSum'],2,',','&nbsp;');
               }
                
            ],            

            
            [
                'attribute' => 'Основание',
                'label'     => 'Основание',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['reasonText'];
               }
            ],            
                                    
/*

            'debetRS', 
            '', 
            'debetOrgTitle', 
            'creditRs', 
            '', 
            'creditOrgTitle', 
            'debetSum', 
            'creditSum', 
            'contrAgentBank', 
            'description', 
            'reasonDocType', 
            'reasonDocNum', 
            'reasonDocDate', 
            'debetBIK', 
            'creditBIK',   
*/
        ],
    ]
); 

?>
