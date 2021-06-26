<?php

use kartik\grid\GridView;
//use yii\grid\GridView;
//use yii\widgets\Pjax;
//use kartik\grid\EditableColumn;
//use yii\helpers\Url;
//use yii\helpers\Html;

$this->title = 'Cписок выданных задач';

?>

<?php
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        
        'responsive'=>true,
        'hover'=>true,
        'showFooter' => false,
        'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'before'=>Html::a('<i class="glyphicon glyphicon-plus"></i> Create Country', ['create'], ['class' => 'btn btn-success']),
        //'after'=>Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),        
         ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'attribute' => 'creationDate',
                'label' => 'Выдана',
                'format' => ['date', 'php:d.m.Y H:i'],
            ],

            [
                'attribute' => 'startDate',
                'label' => 'Начало',
                'format' => ['date', 'php:d.m.Y H:i'],
            ],

            
            [
                'attribute' => 'planDate',
                'label' => 'План',
                'format' => ['date', 'php:d.m.Y H:i'],
            ],
            
            [
                'attribute' => 'deadline',
                'label' => 'Дедлайн',
                'format' => ['date', 'php:d.m.Y H:i'],
            ],
            
                        
            [
                'attribute' => 'userFIO',
                'label'     => 'Исполнитель',
                'format'    => 'raw',
            ],

            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format'    => 'raw',
            ],

            [
                'attribute' => 'event_date',
                'label'     => 'Принята',
                'format'    => 'raw',
                'value' => function ($model, $key, $index, $column){
                  if (empty($model['refCalendar'])) return "&nbsp;";                  
                  return date("d.m.Y", strtotime($model['event_date']))." ".$model['eventTime'];  
                }
            ],

            [
                'attribute' => 'eventStatus',
                'label'     => 'Исполнение',
                'filter'    => ['1' => 'Не выполненны', '2' => 'Выполненны'],
                'format'    => 'raw',
                'value' => function ($model, $key, $index, $column) {                  
                  if ($model['eventStatus'] == 1) return "&nbsp;";                  
                  if ($model['eventStatus'] == 2) {
                      if (empty($model['refExecute'])) return "<span class='glyphicon glyphicon-ok'></span>";                                        
                      $note= Yii::$app->db->createCommand(   'SELECT  note from {{%contact}} where id = :refExecute  ', 
                                                         [   ':refExecute' => $model['refExecute'], ]
                                                         )->queryScalar();
                      if (empty($note)) return "<span class='glyphicon glyphicon-ok'></span>";                                      
                      return mb_substr($note,0,150,'utf-8');                     
                   }
                  return "&nbsp;";                  

                }
            ],
                           
        ],
    ]
);
?>
