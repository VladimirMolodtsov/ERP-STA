<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Товары поставщиков';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$model->getStat(); 
?>

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
function saveData(val)
{
    document.getElementById('dataVal').value=val;    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-warehouse-detail',
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
function opentTypeList(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareTypeName';
    $('#selectTypeDialog').modal('show');   
}

function setSelectedType(typeRef)
{
   $('#selectTypeDialog').modal('hide');   
   saveData(typeRef);   
}
/*************/
function opentGroupList(id)
{   
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareGrpTitle';
    $('#selectGroupDialog').modal('show');   
}

function setSelectedGroup(grpRef)
{
   $('#selectGroupDialog').modal('hide');   
   saveData(grpRef);   
}

/*************/
function openProducerList(id)
{   
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareProdTitle';
    $('#selectProducerDialog').modal('show');   
}

function setSelectedProducer(prodRef)
{
   $('#selectProducerDialog').modal('hide');   
   saveData(prodRef);   
}


/*************/
function opentWareList(id)
{       
  openWin('store/ware-set&refSclad='+id,'wareSetWin');  
}


function addSelectedWare(wareRef,edRef)
{
   $('#selectWareDialog').modal('hide');   
   saveData(wareRef);   
}


function openGoodCard(id)
{
    openWin('store/good-card&id='+id,'wareCard');

}


function showErr(err)
{
    document.location.href='index.php?r=store/ware-show&errOnly='+err;
}



</script>


<table border='0' width='100%' > 
        <tr>        
            <td width='350px'>
<h3><?= Html::encode($this->title) ?></h3>
<p>Товар в наименованиях документов закупки.</p>

            </td>
                        
                                                
            <td width='100px'> 
            <?php
if ( $errOnly == 0 ) {
    $action = "showErr(1)";                   
    $style ="";
    }
else {    
    $action = "showErr(0)";                   
    $style ="background-color:Blue;color:White;";
    }
    ?>

            
                <div  class='btn btn-primary leaf  <?PHP if ($model->errOnly==1) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'                  
                 onclick='<?= $action ?>'> 
                <div class='leaf-txt' >Нет<br>номенклат.</div>
                <div class='leaf-val' style='color:Crimson'><?= $model->wareStat['ErrNum'] ?></div> 
                <div class='leaf-sub' ></div>
                </div>
            </td>                

            <td align='right'>

        <?php
             echo \yii\helpers\Html::tag( 'span', '', 
              [
                  'class'   => 'clickable glyphicon glyphicon-cog',
                  'id'      => 'config',
                  'onclick' => "openWin('store/ware-config','cfgWin')",          
               ]);
            ?>
            
            
            </td>
            
    </tr>
</table>    

     
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
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'goodTitle',
                'label' => 'Товар по документам',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   
                   $action = "openGoodCard(".$model['id'].")";                   
                   $id = 'goodTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $model['goodTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                   
                   return $val;
                }                                
            ],
       
            [
                'attribute' => 'wareTitle',
                'label' => 'Номенклатура',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareName = $model['wareTitle'];
                    if ( empty($wareName) ) $wareName = "-----";
                    $style='';
                
                if(empty($model['wareListRef'])) {
                $wareName = "N/A";
                $style='color:Crimson';    
                }
                else{
               $strSql="SELECT ifnull(isConfirmed,0) FROM {{%ware_list}} where id =:refWareEd ";
               $isConfirmed = Yii::$app->db->createCommand($strSql,
                        [
                        ':refWareEd' => $model['wareListRef'],
                        ])->queryScalar();
                if ($isConfirmed == 1) $style='color:DarkGreen;font-weight:bold;';   
                }
                
                $action = "opentWareList(".$model['id'].")";                   
                $id = 'wareTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $wareName, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Номенклатура товара",
                     'style'   => $style,
                   ]);
                   
                   return $val;
            }

            ],
/*       
            [
                'attribute' => 'scladTitle',
                'filter' => $model->getWareUseList(),
                'label' => 'Склад',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                
            ],

            [
                'attribute' => 'goodEd',
                'label' => 'Ед.изм.',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'value' => function ($model, $key, $index, $column) {                
                
                 if ($model['wareEdRef'] == 0) $style = 'color:Crimson';
                                         else $style = 'color:DarkGreen';
                   
                  $action = "";                   
                   $id = 'isActive_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $model['goodEd'], 
                   [
                     //'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $model['edTitle'],
                     'style'   => $style,
                   ]);
                   
                   return $val;
                }                
            ],

            [
                'attribute' => 'goodAmount',
                'label' => 'К-во.',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => [0 => 'Все', 1 => '> 0', 2 => '= 0',  3 => '< 0'],
                'value' => function ($model, $key, $index, $column) {  
                if ($model['goodAmount'] <0)  return "<font color='Crimson'>".number_format($model['goodAmount'],2,'.','&nbsp;')."</font>";             
                    return number_format($model['goodAmount'],2,'.','&nbsp;');
                }                
                
            ],
            
            [
                'attribute' => 'initPrice',
                'label' => 'Себестоимость',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                if ($model['initPrice'] <0)  return "<font color='Crimson'>".number_format($model['initPrice'],2,'.','&nbsp;')."</font>";             
                    return number_format($model['initPrice'],2,'.','&nbsp;');
                }                
                
            ],
*/            
            [
                'attribute' => 'wareFormat',
                'label' => 'Формат',
                'format' => 'raw',
            ],
            
            
            [
                'attribute' => 'wareDensity',
                'label' => 'Плотность',
                'format' => 'raw',
            ],
       
/*            [
                'attribute' => 'warePack',
                'label' => 'Упаковка',
                'format' => 'raw',
            ],*/
        
            [
                'attribute' => 'wareTypeName',
                'label' => 'Тип',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareTypes(),
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareTypeName = $model['wareTypeName'];
                    if ( empty($wareTypeName) ) $wareTypeName = "-----";
                    $style='';
                
                if(empty($model['wareTypeRef']) ) {
                $wareTypeName = "N/A";
                $style='color:Crimson';    
                }
                $action = "opentTypeList(".$model['id'].")";                   
                $id = 'wareTypeName_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $wareTypeName, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Тип товара",
                     'style'   => $style,
                   ]);
                   
                   return $val;
            }
                
            ],
        
            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Вид',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareGroups(),
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareGrpTitle = $model['wareGrpTitle'];
                    if ( empty($wareGrpTitle) ) $wareGrpTitle = "-----";
                    $style='';
                
                if(empty($model['grpRef'])) {
                $wareGrpTitle = "N/A";
                $style='color:Crimson';    
                }
                $action = "opentGroupList(".$model['id'].")";                   
                $id = 'wareTypeName_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $wareGrpTitle, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Вид товара",
                     'style'   => $style,
                   ]);
                   
                   return $val;
            }

            ],

            [
                'attribute' => 'wareProdTitle',
                'label' => 'Производитель',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareProducer(),
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareProdTitle = trim($model['wareProdTitle']);
                    if ( empty($wareProdTitle) ) $wareProdTitle = "-----";
                    $style='';
                
                if(empty($model['producerRef'])) {
                    $wareProdTitle = "N/A";
                    $style='color:Crimson';    
                }
                $action = "openProducerList(".$model['id'].")";                   
                $id = 'wareProdTitle'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $wareProdTitle, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Производитель товара",
                     'style'   => $style,
                   ]);
                   
                   return $val;
            }
                
            ],

            
          [
                'attribute' => 'isActive',
                'label' => 'Активен',
                'filter' => [0 => 'Все', 1=> 'Да', 2 => 'Нет'],
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

            
          [
                'attribute' => 'isProduction',
                'label' => 'Продукция',
                'filter' => [0 => 'Все', 1=> 'Нет', 2 => 'Да'],
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                 if ($model['isProduction'] == 1) $style = 'background:DarkBlue';
                                         else $style = 'background:White';
                   
                 $action = "switchProduct(".$model['id'].")";
                   
                   $id = 'isActive_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Продукция',
                     'style'   => $style,
                   ]);
                   
                   return $val;
                }                
                
            ],
            
       /*             
            [
                'attribute' => 'orgTitle',
                'label' => 'Организация',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
            ],
   */


        ],
    ]
);
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectTypeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectTypeDialog'  src='index.php?r=store/ware-type-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectGroupDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectGroupDialog'  src='index.php?r=store/ware-group-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectProducerDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectProducerDialog'  src='index.php?r=store/ware-producer-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'selectWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectWareDialog'  src='index.php?r=store/ware-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-warehouse-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
