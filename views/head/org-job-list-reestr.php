<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Реестр списков организаций';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

function openJobListReestr(id)
{
    openWin('head/org-job-list&curOrgJobList='+id,'jobList');
}
</script> 
 
<style>



</style>

<?php 

  echo  \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
//        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [

             [
                'attribute' => 'id',
                'label' => '#',
                'format' => 'raw',                
                
            ],   
            
            [
                'attribute' => 'jobTitle',
                'label' => 'Название списка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                
                  $action = "openJobListReestr(".$model['id'].");";
                  return \yii\helpers\Html::tag( 'div', $model['jobTitle'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,     
                   ]);
                
                },
             ],   

            [
                'attribute' => 'jobNote',
                'label' => 'Описание',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {                        
                  return "<pre>".$model['jobNote']."</pre>";  
                },
                
            ],    

            [
                'attribute' => 'dateCreation',
                'label' => 'Создан',
                'format' => 'raw',                
                
            ],   

            [
                'attribute' => 'isActive',
                'label' => 'Активен',
                'format' => 'raw',                
                
            ],    

        ],
    ]
);

