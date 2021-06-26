<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\tabs\TabsX;
use yii\bootstrap\Modal;

$this->title = 'Выбор номенклатуры';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$model->loadWareSetPar();
?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

.form-group {
margin-bottom:0px;

}
.help-block{
display:none;
}

.help-block-error{
display:none;
}
</style>
  
<script>
function copyCurrentWare(id){
    window.parent.copyCurrentWare(id);

}

function setCurrentWare(id){
    window.parent.setCurrentWare(id);
}

</script>
   
<?php

/*Список номенклатуры - одобренные*/
$tabs[0]= GridView::widget(
    [
        'dataProvider' => $model->getWareSetProvider(Yii::$app->request->get(), 1),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
    //    'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
                
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
               
            [
                'attribute' => 'wareTitle',
                'label' => 'Номенклатура',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'value' => function ($model, $key, $index, $column) {                
                   
                   $action = "setCurrentWare(".$model['id'].")";                   
                   $id = 'wareTitle_'.$model['id'];
                   if ($model['isConfirmed'] == 1) $style='color:DarkGreen';
                   else $style='color:Black';
                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Подгрузить номенклатуру',
                     'style'   => $style
                   ]);
                   
                   return $val;
                }                                
                
            ],
            
            [
                'attribute' => '-',
                'label' => '',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'value' => function ($model, $key, $index, $column) {                
                   
                   $action = "copyCurrentWare(".$model['id'].")";                   
                   $id = 'wareTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-duplicate clickable'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Копировать параметры'
                   ]);                   
                   return $val;
                }                                
                
            ],
                        
            [
                'attribute' => 'wareFormat',
                'label' => 'Формат',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
            [
                'attribute' => 'wareDensity',
                'label' => 'Плотность',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
                       
            [
                'attribute' => 'wareSort',
                'label' => 'Сорт',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
            [
                'attribute' => 'wareMark',
                'label' => 'Марка',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],            
            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],            
    ],    
  ]);  


/*Список номенклатуры - Не одобренные*/
$tabs[1]= GridView::widget(
    [
        'dataProvider' => $model->getWareSetProvider(Yii::$app->request->get(), 2),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
    //    'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
                
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
               
            [
                'attribute' => 'wareTitle',
                'label' => 'Номенклатура',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'value' => function ($model, $key, $index, $column) {                
                   
                   $action = "setCurrentWare(".$model['id'].")";                   
                   $id = 'wareTitle_'.$model['id'];
                   if ($model['isConfirmed'] == 1) $style='color:DarkGreen';
                   else $style='color:Black';
                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Подгрузить номенклатуру',
                     'style'   => $style
                   ]);
                   
                   return $val;
                }                                
                
            ],            
            [
                'attribute' => '-',
                'label' => '',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'value' => function ($model, $key, $index, $column) {                
                   
                   $action = "copyCurrentWare(".$model['id'].")";                   
                   $id = 'wareTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-paste clickable'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Копировать параметры'
                   ]);
                   
                   return $val;
                }                                
                
            ],                       
            [
                'attribute' => 'wareFormat',
                'label' => 'Формат',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
            [
                'attribute' => 'wareDensity',
                'label' => 'Плотность',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
                       
            [
                'attribute' => 'wareSort',
                'label' => 'Сорт',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
            [
                'attribute' => 'wareMark',
                'label' => 'Марка',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],            
            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],           
    ],    
  ]);  


/*Список связанных товаров поставщика*/
if ($model->saleType<2) $prov=$model->getWarehouseSetProvider(Yii::$app->request->get());
                   else $prov=$model->getWarehouseProdProvider(Yii::$app->request->get());

$tabs[2]= GridView::widget(
    [
        'dataProvider' => $prov,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
    //    'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
                
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
               
            [
                'attribute' => 'wareTitle',
                'label' => 'Товар поставщика',
                'format' => 'raw',                
            ],            
            [
                'attribute' => 'wareTypeName',
                'label' => 'Тип',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],           
            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Вид',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],           
            
            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],           
            
            [
                'attribute' => 'wareFormat',
                'label' => 'Формат',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
            [
                'attribute' => 'wareDensity',
                'label' => 'Плотность',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],                       
    ],    
  ]);  


$items = [

        [
            'label'=>'<i class="fas fa-home"></i>Номенклатура одобренная',
            'content'=>$tabs[0],
            'active'=>true
        ],

        [
            'label'=>'<i class="fas fa-home"></i>Номенклатура не одобренные',
            'content'=>$tabs[1],
            'active'=>false
        ],


    ];

if (!empty($model->id)){
if ($model->saleType<2)
{
$items[]=[
            'label'=>'<i class="fas fa-home"></i>Товары поставщика',
            'content'=>$tabs[2],
            'active'=>false
         ];
}
else
{
$items[]=[
            'label'=>'<i class="fas fa-home"></i>Товары для переработки',
            'content'=>$tabs[2],
            'active'=>false
         ];
}
}

echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'bordered'=>true,
    'encodeLabels'=>false
]);
        
?>





