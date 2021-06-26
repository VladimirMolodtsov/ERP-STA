<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Внутренняя номенклатура';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>

<p>
    Список внутренней номенклатуры.
</p>

<style>

.btn-small{
margin:2px;
font-size: 10pt;
padding:2px;
height:20px;
width:20px;
}

.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-txt {    
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}



</style>

  
<script>

/*************/
function switchActive(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='isActive';
    saveData(0);   
}

/*************/
function switchProduct(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='isProduction';
    saveData(0);   
}

/*************/
function saveData(val)
{
    document.getElementById('dataVal').value=val;    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-ware-list-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

</script>

<div class='btn btn-primary' onclick="openWin('store/ware-set','wareSetWin')"><span class='glyphicon glyphicon-plus'></span></div>




<?php


echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'wareTypeName',
                //'filter' => $model->getWareTypeList(),
                'label' => 'Тип',                
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareTypes(),                
                'format' => 'raw',
            ],
        
            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Вид',              
                'format' => 'raw',                
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareGroups(),

            ],

            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareProducer(),
            ],
        
        
            [
                'attribute' => 'goodTitle',
                'label' => 'Номенклатурное название',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 300px'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";
                
                $strSql="SELECT title FROM {{%warehouse}} where wareListRef =:wareRef";
                $list=  Yii::$app->db->createCommand($strSql,[':wareRef' => $model['id'],])->queryAll();                        
                    for($i=0;$i<count($list); $i++) $title.=$list[$i]['title']."\n";                  
                $action = "openWin('store/ware-card&id=".$model['id']."', 'wareCard')";
                $id = 'wareGrpTitle'.$model['id'];
                $style='';
                if ($model['isConfirmed'] == 1) $style='color:DarkGreen;font-weight:bold;';                                 
                   
                   $val = \yii\helpers\Html::tag( 'div', $model['goodTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title, 
                     'style'   => $style,
                   ]);
                   return $val;
                }                
                
            ],

            [
                'attribute' => 'isConfirmed',
                'label' => 'Подт.',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";  

                //$action = "openWin('store/ware-set&id=".$model['id']."', 'wareSetWin')";
                $id = 'isConfirmed'.$model['id'];
                
                if ($model['isConfirmed'] == 1) {$style='color:DarkGreen;font-weight:bold;'; $v="<span class='glyphicon glyphicon-ok'></span>";}                                
                                          else  {$style='color:Crimson;'; $v="<span class='glyphicon glyphicon-alert'></span>";}                                
                   
                   $val = \yii\helpers\Html::tag( 'div', $v, 
                   [
                     //'class'   => 'clickable',
                     'id'      => $id,
                     //'onclick' => $action,
                     'title'   => 'подтверждено',                
                     'style'   => $style
                   ]);
                   
                   return $val;
                }                
                
            ],

            [
                'attribute' => '',
                'label' => '',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";  

                $action = "openWin('store/ware-set&id=".$model['id']."', 'wareSetWin')";
                $id = 'wareGrpTitle'.$model['id'];
                   
                   $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-pencil'></span>", 
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
                'filter' => false,
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 75px'],
                'label' => 'Ед.изм.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                  $strSql = "SELECT edTitle, isMain FROM {{%ware_ed}}, {{%ware_ed_lnk}} WHERE
                  {{%ware_ed_lnk}}.refWareEd= {{%ware_ed}}.id and {{%ware_ed_lnk}}.refWareList=:wareRef
                  and {{%ware_ed_lnk}}.isActive = 1
                  ORDER BY edTitle
                  ";

                  $list =  Yii::$app->db->createCommand($strSql,[':wareRef' => $model['id'],])->queryAll();

                  $val = "";
                  for($i=0;$i<count($list); $i++)
                  {
                    if ($list[$i]['isMain'] == 1) $val.= "<b>";
                    $val.=$list[$i]['edTitle']." ";
                    if ($list[$i]['isMain'] == 1) $val.= "</b>";

                  }

                  return $val;
              }
            ],

            [
                'attribute' => 'wareDensity',
                'label' => 'Плотность',
                'format' => 'raw',
            ],
            [
                'attribute' => 'wareFormat',
                'label' => 'Формат',
                'format' => 'raw',
            ],
            
            [
                'attribute' => 'isProduction',
                'label' => 'Продукция',
                'filter' => ['0'=> 'Все', '1' => 'Сырье', '2' => 'Продукция'],
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";
                  if ($model['isProduction'] == 1) {                  
                    $strSql="SELECT wareTitle, cost FROM {{%ware_prod_lnk}}, {{%ware_list}} where 
                    {{%ware_list}}.id={{%ware_prod_lnk}}.srcRef and {{%ware_prod_lnk}}.resRef =:wareRef";
                    $list=  Yii::$app->db->createCommand($strSql,[':wareRef' => $model['id'],])->queryAll();                        
                    for($i=0;$i<count($list); $i++) $title.=$list[$i]['wareTitle']." ".$list[$i]['cost']."\n";                  
                  $style = 'background:Blue';                  
                  }
                    else $style = 'background:White';                   
                   $action = "switchProduct(".$model['id'].")";
                   $id = 'isProduction'.$model['id'];
                   
                   $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => $style,
                   ]);
                   return $val;
                }                
                
            ],

          [
                'attribute' => 'isActive',
                'label' => 'Активен',
                'filter' => ['0'=> 'Все','1' => 'Да', '2' => 'Нет'],
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                 if ($model['isActive'] == 1) $style = 'background:DarkBlue';
                                         else $style = 'background:White';
                   
                 $action = "switchActive(".$model['id'].")";
                   
                   $id = 'isActive_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Доступен к продаже',
                     'style'   => $style,
                   ]);
                   
                   return $val;
                }                
                
            ],
            
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-ware-list-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

