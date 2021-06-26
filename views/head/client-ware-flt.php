<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;

$this->title = 'Поиск клиентов';
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');



$wareTypeArray = $model->wareTypeArray;
$wareGrpArray = $model->wareGrpArray;
?>


<script type="text/javascript">



function selectWare(id)
{
  window.parent.selectWare(id);
}


</script> 
 
<style>

.btn-small{
margin:2px;
padding:2px;
height:15px;
width:20px;
}

.fltTable {
 font-size:11px;
 padding: 2px;
}
</style>

<?php
    Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
   
              [
                'attribute' => 'wareTitle',
                'encodeLabel'     => false,
                'format' => 'raw',                
                'contentOptions' =>['class' => 'fltTable', 'style' =>'padding-top: 2px;padding-bottom: 2px;'],
                'value' => function ($model, $key, $index, $column) {
                $action="selectWare(".$model['id'].")";
                 $val = \yii\helpers\Html::tag( 'div',$model['wareTitle'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                return $val;
               }
            ],


            [
                'attribute' => 'wareGrpTitle',
                'label'     => 'Вид',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],


            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],

        ],
    ]
);
Pjax::end();
    ?>
    </div>
</div>


