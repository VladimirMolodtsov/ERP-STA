<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Банк - операции согласно 1С';
$this->params['breadcrumbs'][] = $this->title;
    
?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<?= \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  

            [
                'attribute' => 'ownerTitle',
                'label'     => 'Организация',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'orgKPP',
                'label'     => 'КПП',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'regNote',
                'label'     => 'Регистратор',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'regDate',
                'label'     => 'Рег.дата',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'regNum',
                'label'     => 'Рег.номер',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'operationNote',
                'label'     => 'Сделка',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'operationDate',
                'label'     => 'Дата',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'operationNum',
                'label'     => 'Номер',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'recordSum',
                'label'     => 'Сумма',
                'format' => 'raw',            
            ],            
                 
            [
                'attribute' => 'article',
                'label'     => 'Статья ДДС',
                'format' => 'raw',            
            ],            

            /****/
        ],
    ]
); 

?>


