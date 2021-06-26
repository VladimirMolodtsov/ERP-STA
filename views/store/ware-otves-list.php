<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Отвесы';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


$refSchet = $model->refSchet;
$refZakaz = $model->refZakaz;

$model->loadSdelkaData();

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

function addOtves (id)
{
 var refZakaz = <?= $model->refZakaz ?>;
 var refSchet = <?= $model->refSchet ?>;

 if (refSchet > 0){
     document.getElementById('dataVal').value=refSchet;
     document.getElementById('dataType').value='addInSchet';
 }
 else if (refZakaz > 0){
     document.getElementById('dataVal').value=refZakaz;
     document.getElementById('dataType').value='addInZakaz';
 }

 document.getElementById('recordId').value=id;
 saveData ();
}



function rmOtves (id)
{
 var refZakaz = <?= $model->refZakaz ?>;
 var refSchet = <?= $model->refSchet ?>;

 document.getElementById('dataType').value='unLinkOtves';
 document.getElementById('recordId').value=id;
 saveData ();
}

function saveData ()
{
    var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});
    $.ajax({
        url: 'index.php?r=store/save-otves-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
            document.location.reload(true);
        },
        error: function(){
            alert('Error while save data!');
            $(document.body).css({'cursor' : 'default'});
        }
    });

}

/*************/
function syncOtves()
{
    //document.getElementById('dataVal').value=val;
    //var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});
    $.ajax({
        url: 'index.php?r=data/sync-otves',
        type: 'GET',
        dataType: 'json',
        //data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
            document.location.reload(true);

        },
        error: function(){
            alert('Error while sync data!');
            $(document.body).css({'cursor' : 'default'});
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

function endJob ()
{
window.opener.location.reload(false); 
//window.parent.location.reload(false); 
window.opener.focus();
window.close();
}

</script>


<table border='0' width='100%' > 
        <tr>        
            <td width='350px'>


<div class='row'>
    <div class='col-md-2' align='left'><h4><?= Html::encode($this->title) ?></h4></div>

    <div class='col-md-10' align='left'>
    <?php
    if(!empty($model->wareParam))
    {
      echo "<div>";
        echo $model->wareParam['wareTitle'];
      echo "</div>";
    }

    if(!empty($model->schetParam))
    {
      echo "<div>";
      echo "Счет № ";
        echo $model->schetParam['schetNum']." от ".date("d.m.Y",strtotime($model->schetParam['schetDate']));
        echo " ";
        echo $model->schetParam['title'];

      echo "</div>";
    }

    if(!empty($model->zakazParam))
    {
      echo "<div>";
      echo "Заказ № ";
        echo $model->zakazParam['id']." от ".date("d.m.Y",strtotime($model->zakazParam['formDate']));
        echo " ";
        echo $model->zakazParam['title'];
      echo "</div>";
    }
    ?>

    </div>

</div>

<div class='spacer'></div>

<div class='row'>    
    <div class='col-md-8' align='center'>
    <?php
    $size  = $model->getLnkOtves( $model->wareRef, $model->refZakaz, $model->refSchet);
    $price = $model->getPriceOtves( $model->wareRef, $size);
    $sum = $size*$price;
    ?>
    <b>
    Всего привязано к сделке: <?= $size ?>, 
    по цене  <?= $price ?>    
    на сумму <?= $sum ?> 
    </b>
    </div>        
    
    <div class='col-md-2' align='right'>
    <?php    
     $action="endJob();";
     echo \yii\helpers\Html::tag( 'div', 'Завершить',
                   [
                     'class'   => 'btn btn-primary',
                     'onclick' => $action,
                   ]);

    ?>

    </div>        
</div>

     
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
                'attribute' => 'id',
                'label' => '#',
                'format' => 'raw',
            ],

            [
                'attribute' => 'wareTitle',
                'label' => 'Номенклатура',
     //           'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'filter' => $model->getWareInOtves(),
                'format' => 'raw',
               

            ],

            [
                'attribute' => 'size',
                'label' => 'Размер',
                'format' => 'raw',
            ],
            
            [
                'attribute' => 'note',
                'label' => 'Использование',
                'format' => 'raw',
            ],            

            
            [
                'attribute' => 'inuse',
                'label' => 'Исп',
                'format' => 'raw',
                
               'value' => function ($model, $key, $index, $column)use ($refSchet, $refZakaz) {                                            
                
                $action='';
                $v='';
                if ($model['inUse'] == 0)
                {
                    $style='background-color:LightGray;';
                    $action="addOtves(".$model['id'].")";
                }
                else
                {
                    $style='background-color:Brown;';
                    $action='';
                if ($model['refZakaz'] == $refZakaz && $refZakaz!=0) {
                    $style='background-color:Green;';
                    $action="rmOtves(".$model['id'].")";
                    } 
                if ($model['refSchet'] == $refSchet  && $refSchet!=0) {
                    $style='background-color:Green;';              
                    $action="rmOtves(".$model['id'].")";  
                    } 
                }

                   $id = 'lnkOtves'.$model['id'];
                   $val = \yii\helpers\Html::tag( 'div', $v, 
                   [
                     'class'   => 'btn btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
                   return $val;
                }                
            ],            

            [
                'attribute' => '',
                'label' => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($refSchet, $refZakaz) {

                 if (!empty($model['refZakaz'])) {
                  $list = Yii::$app->db->createCommand('SELECT {{%zakaz}}.id, {{%zakaz}}.formDate FROM  {{%zakaz}}
                  WHERE  {{%zakaz}}.id =:refZakaz',
                    [
                    ':refZakaz' =>$model['refZakaz'],
                    ])->queryAll();
                 if(!empty($list))
                 $val = "№".$list[0]['id']." от ".$list[0]['formDate'];
                 return $val;
                 }
                }

            ],

            [
                'attribute' => '',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($refSchet, $refZakaz) {

                 if (!empty($model['refSchet'])) {
                  $list = Yii::$app->db->createCommand('SELECT {{%schet}}.id, schetNum, {{%schet}}.schetDate FROM {{%schet}}
                  WHERE  {{%schet}}.id =:refSchet',
                    [
                    ':refSchet' =>$model['refSchet'],
                    ])->queryAll();
                 if(!empty($list))
                 $val = "№".$list[0]['schetNum']." от ".$list[0]['schetDate'];
                 return $val;
                 }
                }

            ],



            [
                'attribute' => '',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($refSchet, $refZakaz) {

                  $title ="";
                 if (!empty($model['refZakaz'])) {
                  $title = Yii::$app->db->createCommand('SELECT {{%orglist}}.title FROM {{%orglist}}, {{%zakaz}}
                  WHERE  {{%orglist}}.id = {{%zakaz}}.refOrg and {{%zakaz}}.id =:refZakaz',
                    [
                    ':refZakaz' => $model['refZakaz'],
                    ])->queryScalar();
                 }


                 if (!empty($model['refSchet'])) {
                  $title = Yii::$app->db->createCommand('SELECT {{%orglist}}.title FROM {{%orglist}}, {{%schet}}
                  WHERE  {{%orglist}}.id = {{%schet}}.refOrg and {{%schet}}.id =:refSchet',
                    [
                    ':refSchet' => $model['refSchet'],
                    ])->queryScalar();
                 }

                    return $title;

                }

            ],


            [
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],            
            
            
            
            
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-otves-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'wareNameRef' )->hiddenInput(['id' => 'wareNameRef' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
