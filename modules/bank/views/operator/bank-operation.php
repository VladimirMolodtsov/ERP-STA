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
                'attribute' => 'articleRef',
                'label'     => 'Тип статьи',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                  $strSql="Select grpTitle from {{%bank_op_article}}, {{%bank_op_grp}} where 
                  {{%bank_op_article}}.actionType = {{%bank_op_grp}}.flg and
                  {{%bank_op_article}}.id =:ref";                   
                  $article = Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $model['articleRef'])                                               
                           ->queryScalar();                                    
                    return $article;
               }                
            ],            
            
            [
                'attribute' => 'articleRef',
                'label'     => 'Статья ДДС',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                  $strSql="Select article from {{%bank_op_article}} where id =:ref";                   
                  $type = Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $model['articleRef'])                                               
                           ->queryScalar();                                    
                    return $type ;
               }
                
            ],  
            
           [
                'attribute' => 'refBankExtract',
                'label'     => 'П/П (Выписка)',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                if (empty($model['refBankExtract']))  
                return \yii\helpers\Html::tag( 'div', 'N/A', 
                   [
                     'title'   => 'Нет соответствия',
                     'style'   => 'color:Crimson;font-weight:bold',
                   ]);
                
                
                  $strSql="Select id, docNum, recordDate from {{%bank_extract}} where id =:ref";                   
                  $type = Yii::$app->db->createCommand($strSql)
                           ->bindValue(':ref', $model['refBankExtract'])                                               
                           ->queryOne();                                    
                  if (empty($type))                 
                  return \yii\helpers\Html::tag( 'div', 'Invalid', 
                   [
                     'title'   => 'Не верная ссылка',
                     'style'   => 'color:Crimson;font-weight:bold',
                   ]);
                          
                    
                  return \yii\helpers\Html::tag( 'div', $type['docNum'] , 
                   [
                     'title'   => 'Соответствие выписки',                     
                   ]);
                
                
                
               }
                       
            ],   
            
            /****/
        ],
    ]
); 

?>


