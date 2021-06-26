<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;


$this->title = 'Наименования реализации';


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

function openWareList(id)
{
  openWin('store/ware-set&refName='+id,'wareSetWin');  
  //openWin('store/ware-card&id='+id,'wareSetWin');
}


function openCard(id)
{
  openWin('store/ware-card-realize&id='+id,'wareSetWin');
}

function setMode(mode)
{
  document.location.href='index.php?r=store/ware-names&mode='+mode;

}

function switchActive(id)
{
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value='isActive';
    saveData(0);
}


function switchPrice(id)
{
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value='isInPrice';
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
function openWareEdList(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareEd';
    $('#selectWareEdDialog').modal('show');   
}

function setSelectedWareEd(edValue)
{
   $('#selectWareEdDialog').modal('hide');   
   saveData(edValue);   
}
/************/

/*************/
function openWareFormList(id)
{ 
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareForm';
    $('#selectWareFormDialog').modal('show');   
}

function setSelectedForm(formRef)
{
   $('#selectWareFormDialog').modal('hide');   
   saveData(formRef);   
}
/************/


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
/************/
function openGroupList(id)
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
function saveData(val)
{
    document.getElementById('dataVal').value=val;
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-ware-name-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            if (res.isReload==true) document.location.reload(true);
            else showRes (res);            
        },
        error: function(){
            alert('Error while saving data!');
        }
    });
}


function showRes (res){
   console.log(res);

}

</script>




<table border='0' width='100%' >
        <tr>
            <td width='350px'>
<h3><?= Html::encode($this->title) ?></h3>
<p>Список товаров в номенклатуре реализации.</p>

            </td>


            <td width='100px'>
                <div  class='btn btn-primary leaf  <?PHP if ($model->mode==0) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'
                 onclick='setMode(0)'>
                <div class='leaf-txt' >Активных</div>
                <div class='leaf-val' style='color:Blue'><?= $model->wareStat['Active'] ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>
            <td width='10px'>&nbsp;</td>

            <td width='100px'>
                <div  class='btn btn-primary leaf  <?PHP if ($model->mode==1) echo "leaf-selected"; ?>' style='background:LightYellow ; color:Blue;'
                 onclick='setMode(1)'>
                <div class='leaf-txt' >Нет<br>номенклат.</div>
                <div class='leaf-val' style='color:Crimson'><?= $model->wareStat['ErrNum'] ?></div>
                <div class='leaf-sub' ></div>
                </div>
            </td>

            <td></td>

            
            <td width='150px' align='right'>
           <?php
             echo \yii\helpers\Html::tag( 'span', 'Прайс',
              [
                  'class'   => 'btn btn-default',
                  'id'      => 'sync',
                  'onclick' => "openWin('store/ware-price','priceWin')",
               ]);
            ?>
           </td> 
                        
            <td width='50px' align='right'>
           <?php
             echo \yii\helpers\Html::tag( 'span', '',
              [
                  'class'   => 'clickable glyphicon glyphicon-refresh',
                  'id'      => 'sync',
                  'onclick' => "openWin('managment/head/google-price-sync','syncWin')",
               ]);
            ?>
           </td> 
            <td width='50px' align='right'>
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
        'type'=>'success',
   //     'footer'=>true,
    ],

        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'wareTitle',
                'label' => 'Название в реализации',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 220px'],
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {


                $action = "openCard(".$model['id'].")";
                $id = 'wareTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'],
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Название товара в реализации",
                   ]);

                   return $val;
                }

            ],


            [
                'attribute' => 'nomTitle',
                'label' => 'Внутренняя номенклатура',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                $style='';
                $wareName = $model['nomTitle'];

                if(empty($model['wareListRef'])) {
                $wareName = "N/A";
                $style='color:Crimson';
                } else{
               $strSql="SELECT ifnull(isConfirmed,0) FROM {{%ware_list}} where id =:refWareEd ";
               $isConfirmed = Yii::$app->db->createCommand($strSql,
                        [
                        ':refWareEd' => $model['wareListRef'],
                        ])->queryScalar();
                if ($isConfirmed == 1) $style='color:DarkGreen;font-weight:bold;';   
                }
                
                
                $action = "openWareList(".$model['id'].")";
                $id = 'nomTitle_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $wareName,
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Внутренняя номенклатура товара",
                     'style'   => $style,
                   ]);

                   return $val;
                }
            ],

            
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
                
                if(empty($model['wareGrpRef'])) {
                $wareGrpTitle = "N/A";
                $style='color:Crimson';    
                }
                $action = "openGroupList(".$model['id'].")";                   
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
            ],




            [
                'attribute' => 'wareEd',
                'label' => 'Ед.изм.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareEd = $model['wareEd'];
                    
                $style='';                
                if(empty($model['wareEd'])) {
                    $wareEd = "-----";
                    $style='color:Crimson';    
                }
                $action = "openWareEdList(".$model['id'].")";                   
                $id = 'wareEd_'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', $wareEd, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Ед. изм",
                     'style'   => $style,
                   ]);
                   return $val;
            }
            ],

            [
                'attribute' => 'formTitle',
                'label' => 'Форма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                    $wareForm = $model['formTitle'];
                    
                $style='';                
                if(empty($wareForm)) {
                    $wareForm = "-----";
                    $style='color:Crimson';    
                }
                $action = "openWareFormList(".$model['id'].")";                   
                $id = 'wareForm_'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', $wareForm, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Форма товара",
                     'style'   => $style,
                   ]);
                   return $val;
            }
            ],            
            
            [
                'attribute' => 'lastUse',
                'label' => 'Дата исп',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                if (!empty($model['lastUse']))
                    return date("d.m.Y", strtotime($model['lastUse']));
                
                }
            ],

            [
                'attribute' => 'useCount',
                'label' => 'Исп',
                'format' => 'raw',
            ],

          [
                'attribute' => 'showProdutcion',
                'label' => 'Прод.',
                'filter' => [0 => 'Все', 1=> 'Сырье', 2 => 'Продукция'],
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 if ($model['showProdutcion'] == 1) $style = 'background:DarkBlue';
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

          [
                'attribute' => 'isInPrice',
                'label' => 'В прайсе',
                //'filter' => ['0'=> 'Все','1' => 'Да', '2' => 'Нет'],
                //'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 55px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 if ($model['isInPrice'] == 1) $style = 'background:DarkBlue';
                                         else $style = 'background:White';

                 $action = "switchPrice(".$model['id'].")";

                   $id = 'isInPrice_'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', "",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В прайс-листе',
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



<?php
/********** Диалог с добавлением типа *****************/
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
/********** Диалог с добавлением группы *****************/
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
/********** Диалог с добавлением производителя *****************/
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
/********** Диалог с выбором единиц измерения *****************/
Modal::begin([
    'id' =>'selectWareEdDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectWareEdDialog'  src='index.php?r=store/ware-name-ed-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>

<?php
/********** Диалог с выбором формы товара *****************/
Modal::begin([
    'id' =>'selectWareFormDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectWareFormDialog'  src='index.php?r=store/ware-form-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>

