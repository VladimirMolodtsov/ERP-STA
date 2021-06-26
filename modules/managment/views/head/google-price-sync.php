<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Прайс - синхронизация';
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>

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

</script>

</script>

<h4>Товар</h4>
<?php
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
//        'filterModel' => $model,        
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
                'attribute' => 'wareGroup',
                'label' => 'Вид товара',
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareProducer',
                'label' => 'Производитель',
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareTitle',
                'label' => 'Наименование',
                'format' => 'raw',
                 'value' => function ($model, $key, $index, $column)  {               
                 $style='color:Black';
                 if($model['isFind']==0)$style='color:Crimson';
                 if($model['isFind']==2)$style='color:Green';
                 return  \yii\helpers\Html::tag( 'div', $model['wareTitle'], 
                   [
                     //'class'   => 'clickable',
//                     'id'      => $id,
  //                   'onclick' => $action,
    //                 'title'   => 'Подгрузить номенклатуру',
                     'style'   => $style
                   ]);

                 
                 }
                
            ],
            [
                'attribute' => 'v1',
                'label' => 'до 100',
                'format' => 'raw',
            ],

            [
                'attribute' => 'v2',
                'label' => 'до 400',
                'format' => 'raw',
            ],

            [
                'attribute' => 'v3',
                'label' => '400-3000',
                'format' => 'raw',
            ],

            [
                'attribute' => 'v4',
                'label' => '3000+',
                'format' => 'raw',
            ],
                                    
        ],
    ]
);

?>


