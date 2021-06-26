<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Товарная номенклатура';


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

function selectWare(wareRef) {    
	window.parent.setSelectedWare(wareRef);
}

function acceptFilter( ) {    
var wareType = document.getElementById('wareType').value;
var wareGrp = document.getElementById('wareGrp').value;
var wareProd = document.getElementById('wareProd').value;
var url = 'index.php?r=store/ware-list-select&noframe=1&wareType='+wareType+'&wareGrp='+wareGrp+'&wareProd='+wareProd;
document.location.href=url;
}
</script>


<table width='470px' border='0'><tr>
    <td>
    <?php
    $wareTypes =$model->getWareTypes();
    //$wareTypes[0]='Все'; 
     echo  Html::dropDownList( 
       'wareTypeName', 
        $model->wareTypeName, 
        $wareTypes ,
        [
          'class' => 'form-control',
          'style' => 'width:150px;font-size:12px; padding:1px;', 
          'id' => 'wareType', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор типа',
        ]);       
    ?>
    </td>
    
    <td> 
    <?php
    $wareGroups =$model->getWareGroups();
   // $wareGroups[0]='Все'; 
     echo  Html::dropDownList( 
       'wareGrpTitle', 
        $model->wareGrpTitle, 
        $wareGroups ,
        [
          'class' => 'form-control',
          'style' => 'width:150px;font-size:12px; padding:1px;', 
          'id' => 'wareGrp', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор вида товара',
        ]);       
    ?>
    </td>
        
    <td> 
    <?php
    $wareProducer =$model->getWareProducer();
    //$wareProducer[0]='Все'; 
     echo  Html::dropDownList( 
       'wareProdTitle', 
        intval($model->wareProdTitle), 
        $wareProducer ,
        [
          'class' => 'form-control',
          'style' => 'width:150px;font-size:12px; padding:1px;', 
          'id' => 'wareProd', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор производителя',
        ]);       
    ?>
    </td>
</tr></table>

<div class='spacer'></div>
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
                'attribute' => 'wareTitle',
                'label' => 'Товар',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 200px'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";
                
                $strSql="SELECT title FROM {{%warehouse}} where wareListRef =:wareRef";
                $list=  Yii::$app->db->createCommand($strSql,[':wareRef' => $model['id'],])->queryAll();                        
                    for($i=0;$i<count($list); $i++) $title.=$list[$i]['title']."\n";                  
                $action = "selectWare(".$model['id'].", ".$model['refWareEd'].")";                
                $id = 'wareGrpTitle'.$model['id'];                   
                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,                
                   ]);
                   return $val;
                }                                
            ],
            [
                'attribute' => 'edTitle',
                'filter' => $model->getWareEdList(),
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 75px'],
                'label' => 'Ед.изм.',
                'format' => 'raw',
            ],
       ]
    ]
);
?>
