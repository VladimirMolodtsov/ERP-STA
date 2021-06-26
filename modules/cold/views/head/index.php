<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Холодная база - загрузки';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>



 
</style>


<script type="text/javascript">

</script> 

<div class='row'>
<div class='col-md-8'>     </div>
<div class='col-md-4'>    <a href='index.php?r=cold/head/load-by-url'  class='btn btn-primary'>Загрузить</a> </div>
</div>
<div class="spacer"> </div>    
<?php
echo \yii\grid\GridView::widget(
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
                'attribute' => 'decription',
                'label'     => 'Тематическая группа',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $r="<a href='#' onclick=\"openWin('cold/head/detail-load&id=".$model['id']."','childwin');\" >".$model['decription']."</a>";
                return $r;
                },

             ],        

             
             [
                'attribute' => 'importDate',
                'label'     => 'Раздел',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                      return date("d.m.Y H:i", strtotime($model['importDate'])+4*3600);
                }
                      
             ],        
                         
          
            [
                'attribute' => 'userFIO',
                'label'     => 'Загрузил',
                'format' => 'raw',

            ],        

        ],
    ]
    );
?>

