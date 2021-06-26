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
<p> Дата создания выписки от <?= date('d.m.Y H:i:s', strtotime($model->headerRec->creationDate)+4*3600) ?>
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

/*            [
                'attribute' => 'debetRS',
                'label'     => 'Дебет р/с, ИНН ',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['debetRS']."<br>".$model['debetINN'];
               }
                
            ],
*/                        
            [
                'attribute' => 'debetOrgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',     
            ],            

/*            [
                'attribute' => 'creditRs',
                'label'     => 'Кредит р/с, ИНН',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return $model['creditRs']."<br>".$model['creditINN'];
               }                
            ],            
*/            
            [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
            ],            

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
