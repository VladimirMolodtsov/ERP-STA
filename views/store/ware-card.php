<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$this->title = 'Карточка внутренней номенклатуры';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$model -> loadData();
?>

<style>
.btn-small{
margin:2px;
font-size: 10pt;
padding:2px;
height:20px;
width:20px;
}


</style>
  
<script>


function switchEdActive(val)
{
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataType').value='isEdActive';
    saveData(val);
}

/*************/
function switchEdMain(val)
{
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataType').value='isEdMain';
    saveData(val);
}

/*************/
function saveData(val)
{
    document.getElementById('dataVal').value=val;
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-nomenklatura-detail',
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


function saveWare (){
    $(document.body).css({'cursor' : 'wait'});
    var data = $('#mainForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-ware',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
            //document.location.reload(true);
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while retrive ware title!');
        }
    });

}

function createEd(){
    $('#newEdDialog').modal('show');
}
function saveEd(){

    $('#newEdDialog').modal('hide');
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataType').value='createEd';
    val = document.getElementById('newEdTitle').value;
    saveData(val);
}


function createSupplierGood(){
    $('#newSupplierGoodDialog').modal('show');
}

function saveSupplierGood(){

    $('#newSupplierGoodDialog').modal('hide');
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataType').value='createSupplierGood';
    val = document.getElementById('newSupplierGoodTitle').value;
    saveData(val);
}

function createRealize(){
    $('#newRealizeDialog').modal('show');
}

function saveRealize(){

    $('#newRealizeDialog').modal('hide');
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataType').value='createRealize';
    val = document.getElementById('newRealizeTitle').value;
    saveData(val);
}



</script>



<?php $form = ActiveForm::begin([
   'options' => ['class' => 'edit-form'],
   'action'  => 'index.php?r=store/save-ware',
   'id'=> 'mainForm'
]);

?>




 <table border=0 width=100% class='table table-small'>
 
  <tr>
  <td class='lbl'>  Номенклатурное название  </td>
  <td colspan=2><?= $form->field($model, 'wareTitle')->textarea(['rows' => 2,
         'style' => 'width:400px', 'class' => 'form-control form-small', 'cols' => 25])->label(false)?></td>

 <td rowspan='2' colspan=3 class='data'>

<?php
$wareId= $model->id;
echo GridView::widget(
    [
        'dataProvider' => $model->getEdListProvider(Yii::$app->request->get(), 1),
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
                'attribute' => 'edTitle',
                'label' => 'Единицы измерения',
                'format' => 'raw',
            ],

            [
                'attribute' => 'isActive',
                'label' => 'Доступна',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($wareId)  {


                  $strSql="SELECT ifnull(isActive,0) FROM {{%ware_ed_lnk}} where refWareEd =:refWareEd AND refWareList=:refWareList";
                  $isActive= Yii::$app->db->createCommand($strSql,
                        [
                        ':refWareEd' => $model['id'],
                        ':refWareList' => $wareId,
                        ])->queryScalar();

                   if ($isActive == 1) $style='background-color:Blue';
                                       else $style='background-color:White';
                   $action = "switchEdActive(".$model['id'].")";
                   $id = 'isActive'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', '',
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => 'Доступность для использования'
                   ]);
                   return $val;
                }

            ],

            [
                'attribute' => 'isMain',
                'label' => 'Основная ед.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($wareId)   {

                  $strSql="SELECT ifnull(isMain,0) FROM {{%ware_ed_lnk}} where refWareEd =:refWareEd AND refWareList=:refWareList";
                  $isMain= Yii::$app->db->createCommand($strSql,
                        [
                        ':refWareEd' => $model['id'],
                        ':refWareList' => $wareId,
                        ])->queryScalar();



                   if ($isMain == 1) $style='background-color:Blue';
                                         else $style='background-color:White';

                   $action = "switchEdMain(".$model['id'].")";
                   $id = 'isMain'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', '',
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => 'Основная единица учета'
                   ]);
                   return $val;
                }

            ],



    ],
  ]);

 $action ='createEd();';
 echo \yii\helpers\Html::tag( 'div', '',
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'onclick' => $action,
                     'style'   => 'font-size:15px;',
                   ]);
?>
   </td>
 </tr>


   <tr>
  <td class='lbl'>  Описание товара  </td>
  <td colspan=2><?= $form->field($model, 'wareNote')->textarea(['rows' => 2,
         'style' => 'width:400px', 'class' => 'form-control form-small', 'cols' => 25])->label(false)?></td>

 </tr>









 <tr>
  <td class='lbl'>  Тип  </td> 
  <td class='data'> <?= $form->field($model, 'wareType')->dropDownList($model->getWareTypes(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px'])->label(false)?>
   </td>
            
  <td class='lbl'> Группа   </td> 
  <td class='data'> <?= $form->field($model, 'wareGroup')->dropDownList($model->getWareGroups(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px'])->label(false)?>
   </td>  

  <td class='lbl'> Производитель   </td>
  <td class='data'> <?= $form->field($model, 'wareProducer')->dropDownList($model->getWareProducer(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px'])->label(false)?>
   </td>

   
   <td align='right'>
        <?php
             echo \yii\helpers\Html::tag( 'div', '', 
              [
                  'class'   => 'clickable glyphicon glyphicon-cog',
                  'id'      => 'config',
                  'onclick' => "openWin('store/ware-config','cfgWin')",          
               ]);
            ?>    
</td>
   
</tr>
 

 <tr>
  <td class='lbl'>  Плотность  </td>
  <td class='data'><?php
    $wareDensity =$model->getWareDensity();
    echo  $form->field($model, 'wareDensitySel')
    ->dropDownList($wareDensity,
    [
        'id' => 'wareDensitySel',
        'style' => 'margin-top:0px;width:200px',
        'onchange' => 'cngDensity();',
        'prompt' => 'Плотность',

    ])->label(false);
    ?>
    </td>

   </td>

  <td class='lbl'> Формат   </td>
  <td class='data' align='right'>    <?php
    $formatTypes =$model->getWareFormat();

    echo  $form->field($model, 'wareFormatSel')
    ->dropDownList($formatTypes,
    [
        'id' => 'wareFormatSel',
        'style' => 'margin-top:0px;width:200px',
        'onchange' => 'cngFormat();',
        'prompt' => 'Формат',
        ])->label(false);
    ?>
   </td>
   <td></td>
   <td align='right'>  
    </td>

</tr>

<tr>
   <td class='lbl'>Марка</td>
   <td class='data'>
    <?php

    echo  $form->field($model, 'wareMark')
    ->textInput([
        'id' => 'wareMark',
        'style' => 'width:150px; font-size:11px;padding:1px;',
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>

   <td class='lbl'>Сорт</td>
   <td class='data'>
    <?php
    echo  $form->field($model, 'wareSort')
    ->textInput([
        'id' => 'wareSort',
        'style' => 'width:150px; font-size:11px;padding:1px;',
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>

   <td class='lbl'>Дополнение</td>
   <td class='data'>
       <?php
    echo  $form->field($model, 'addNote')
    ->textInput([
        'id' => 'addNote',
        'style' => 'width:150px; font-size:11px;padding:1px;',
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>

</tr>






<tr>
<td colspan=6 align='right'>

<?php
echo $form->field($model, 'id' )->hiddenInput(['id' => 'id' ])->label(false);
?>

<?= Html::submitButton('Сохранить',
    [
        'class' => 'btn btn-primary',
        'style' => 'width:150px;background-color: DarkGreen ;',
        'onclick' => 'saveWare()',
        ]) ?>
</td>
</tr>

<?php 
if (!empty($model->id)){

?>
<tr>
<td colspan=3 align='left' style='padding:3px;'>
<?php

echo GridView::widget(
    [
        'dataProvider' => $model->getWarehouseListProvider(Yii::$app->request->get(), 1),
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
                'attribute' => 'title',
                'label' => 'Связанные товары поставщика',
                'format' => 'raw',
            ],

            [
                'attribute' => 'ed',
                'label' => 'Ед.изм.',
                'format' => 'raw',
            ],

    ],
  ]);

 $action ='createSupplierGood();';
 echo \yii\helpers\Html::tag( 'div', '',
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'onclick' => $action,
                     'style'   => 'font-size:15px;',
                   ]);

?>
</td>
<td colspan=3 align='right'  style='padding:3px;'>

<?php
echo GridView::widget(
    [
        'dataProvider' => $model->getWareNameListProvider(Yii::$app->request->get(), 1),
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
                'label' => 'Связанные товары реализации',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                $action = "openWin('store/ware-card-realize&id=".$model['id']."', 'wareCardRealize')";
                $id = 'wareTitle'.$model['id'];

                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'],
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);

                   return $val;
                }

            ],

            [
                'attribute' => 'wareEd',
                'label' => 'Ед.изм.',
                'format' => 'raw',
            ],

    ],
  ]);



 $action ='createRealize();';
 echo \yii\helpers\Html::tag( 'div', '',
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'onclick' => $action,
                     'style'   => 'font-size:15px;',
                   ]);

?>


</td>

</tr>

<?php 
}
?>


</table>

<?php ActiveForm::end(); ?>







<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-nomenklatura-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end();
?>

<?php

Modal::begin([
    'id' =>'newEdDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>

    <div class='row'>
        <div class='col-md-2'>
            Единица измерения
        </div>

        <div class='col-md-4'>
            <?php

            echo Html::textInput(
                          'newEdTitle',
                          '',
                              [
                              'class' => 'form-control',
                              'style' => 'width:200px; font-size:11px;padding:1px;',
                              'id' => 'newEdTitle',
                              'placeHolder' => 'название ед. изм.'
                              ]);
            ?>
        </div>

    </div>

    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'saveEd();',
                     'title'   => 'Сохранить',
                   ]);
    ?>
    </div>

<?php
Modal::end();
/***************************/
?>


<?php

Modal::begin([
    'id' =>'newRealizeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>

    <div class='row'>
        <div class='col-md-4'>
            Наименование товара реализации
        </div>

        <div class='col-md-4'>
            <?php

            echo Html::textInput(
                          'newRealizeTitle',
                          '',
                              [
                              'class' => 'form-control',
                              'style' => 'width:300px; font-size:11px;padding:1px;',
                              'id' => 'newRealizeTitle',
                              'placeHolder' => 'Наименование товара реализации.'
                              ]);
            ?>
        </div>

    </div>

    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'saveRealize();',
                     'title'   => 'Сохранить',
                   ]);
    ?>
    </div>

<?php
Modal::end();
/***************************/
?>
<?php

Modal::begin([
    'id' =>'newSupplierGoodDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>

    <div class='row'>
        <div class='col-md-4'>
            Наименование товара поставщика
        </div>

        <div class='col-md-4'>
            <?php

            echo Html::textInput(
                          'newSupplierGoodTitle',
                          '',
                              [
                              'class' => 'form-control',
                              'style' => 'width:300px; font-size:11px;padding:1px;',
                              'id' => 'newSupplierGoodTitle',
                              'placeHolder' => 'Наименование товара поставщика'
                              ]);
            ?>
        </div>

    </div>

    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'saveSupplierGood();',
                     'title'   => 'Сохранить',
                   ]);
    ?>
    </div>

<?php
Modal::end();
/***************************/
?>
