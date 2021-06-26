<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Прайс';
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
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}

.suspicious {
  color:Crimson;  
}  


.notsuspicious {
  color:DarkGreen;  
}  
</style>
  
<script>

function selectPrice(id,row)
{
 window.opener.addWareFromPrice(id,row);
 window.close();

}
</script>

<div class='row'>

    <div class='col-md-6'>
        <h3><?= Html::encode($this->title) ?></h3>
    </div>

    <div class='col-md-6' style='font-size:15px;text-align:right;'>
        <?php

        echo \yii\helpers\Html::tag( 'div', '',
              [
                  'class'   => 'clickable glyphicon glyphicon-print',
                  'id'      => 'config',
                  'onclick' => "openWin('store/ware-price&format=print','printWin')",
                  'style'   => 'margin-top:25px; '
               ]);
        ?>
    </div>
</div>
<?php
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
  */      
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       

            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Вид товара',
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareTitle',
                'label' => 'Наименование',
                'format' => 'raw',
            ],
            [
                'attribute' => 'v1',
                'label' => 'до 100',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v1';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectPrice(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                

            ],

            [
                'attribute' => 'v2',
                'label' => 'до 400',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v2';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectPrice(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],

            [
                'attribute' => 'v3',
                'label' => '400-3000',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v3';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectPrice(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                

                
            ],

            [
                'attribute' => 'v4',
                'label' => '3000+',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v4';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectPrice(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                

                
            ],
                                    
        ],
    ]
);

?>


<pre>
<?php //print_r($model->debug); ?>
</pre>
