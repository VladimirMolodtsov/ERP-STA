<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Единица измерения';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>
  
<script>

function setSelected(edValue) {    
	window.parent.setSelectedWareEd(edValue);
}

</script>


<?php

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
                
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
        
            [
                'attribute' => 'wareEd',
                'label' => 'Наименование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                                                            
                   $action = "setSelected('".$model['wareEd']."')";
                                    
                   $val = \yii\helpers\Html::tag( 'div', $model['wareEd'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
            //         'title'   => $title,                
                   ]);
                   return $val;
                }                                
           ],
       ]
    ]
);
?>
