<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Наполнение склада';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>
<p>Товар на складах (в наименованиях документов закупки).</p>

<style>
.tb-head {    
    font-size:11px;
    //width:75px;
     word-wrap: break-word;
    word-break:  break-all;
    line-break: auto;  /* нет поддержки для русского языка */ 
    hyphens: manual;
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
    document.getElementById('recordId').value=id; 
    document.getElementById('dataType').value='wareTitle';
    $('#selectWareDialog').modal('show');   
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

function openOtvesEdit(id)
{
    openWin('store/otves-create&wareScladRef='+id,'otvesWin');
}

function syncData()
{    

    $('#showSyncProgress').modal('show');       
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: 'index.php?r=/data/sync-sclad-ajax',
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#showSyncProgress').modal('hide');       
            document.location.reload(true);   
            
        },
        error: function(){
             $('html, body').css("cursor", "auto");
             $('#showSyncProgress').modal('hide');       
            alert('Error while saving data!');
        }
    });	
    
}



</script>

<div class='row'>
    <div class='col-md-2'>
    <?php
                $action = "openSmallWin('store/ware-grp-sclad', 'wareGrpWin')";                   
                 echo \yii\helpers\Html::tag( 'div', 'Виды товара', 
                   [
                     'class'   => 'btn btn-primary',
                     'onclick' => $action,
                     'style'  => '',
                   ]);
    
    
    ?>
    </div>
    <div class='col-md-5'></div>
    
    
    
    
    
    <div class='col-md-3'>Синхронизировано на дату: <?= $model->onDate?></div>

    <div class='col-md-1'>
    <?php
                $action = "syncData()";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-refresh clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:0px;',
                     'title' => 'Текущая попытка синхронизации '.$model->syncDate,
                   ]);
    
    
    ?>
    </div>
    
    <div class='col-md-1'>
    <?php
                $action = "openWin('store/ware-use','scladWin')";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-cog clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:0px;',
                     'title' => 'Настроить склады',
                   ]);
    
    
    ?>
    </div>

</div>

<?php

$columns =  [

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
                'attribute' => 'goodEd',
                'label' => 'Ед.',
                'format' => 'raw',
                
            ],

            [
                'attribute' => '-',
                'label' => 'Отвесы',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   
                 if(empty($model['wareListRef'])) return;
                   
                 $action = "openOtvesEdit(".$model['id'].")";                   
                 $id = 'otves_'.$model['id'];
                 
                 $freeOtves=Yii::$app->db->createCommand('Select sum(size) from {{%otves_list}} where 
                   refWareList = :refWareList AND inUse = 0  ',
                   [
                     ':refWareList' => $model['wareListRef'],
                   ]
                   )                    
                    ->queryScalar(); 
                 if (empty($freeOtves))   $freeOtves = 0;
                 
                 $val = \yii\helpers\Html::tag( 'div', $freeOtves, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                   
                   return $val;
                }                                
            ],
            
            
                                    
];

$list = $model->getWareScladColumns();
$lastRef = $model->lastRef;

for ($i=0;$i<count($list);$i++)
    {
        $useInSum = $list[$i]['useInSum'];
        $lbl = preg_replace("/\s+/","<br>",$list[$i]['scladTitle']);
        $uid = $list[$i]['id'];
        $columns[]= [      
                'attribute' => "sclad_".$i,
                'label' => "<div class='tb-head'>".$lbl."</div>",
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => [0 => 'Все', 1 => '> 0', 2 => '= 0',  3 => '< 0'],
                'encodeLabel' => false,
                //'contentOptions' => ['style' =>'font-size:11px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($lastRef, $uid, $useInSum) {                

                   $val=Yii::$app->db->createCommand('Select goodAmount from {{%ware_content}} where 
                   useRef = :useRef AND goodRef=:goodRef AND headerRef =:headerRef',
                   [
                     ':useRef' => $uid,
                     ':goodRef' => $model['id'],
                     ':headerRef' => $lastRef
                   ]
                   )                    
                    ->queryScalar(); 


                       
                   //$action = "openGoodCard(".$model['id'].")";                   
                   $id = 'sclad_'.$uid.'_'.$model['id'];
                   if ($val< 0) $c='color:Crimson;';
                           else $c='color:Black;';
                           
                  if ($useInSum == 1) $c='color:DarkGreen;';
                   return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     //'class'   => 'clickable',
                     'id'      => $id,
                     'style'   => 'text-align:center;'.$c,
                     //'onclick' => $action,
                   ]);
                }                                
       ];
    }




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

        'columns' => $columns,
        
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
    <iframe width='550px' height='620px' frameborder='no' id='frameSelectGroupDialog'  src='index.php?r=store/ware-producer-select&noframe=1' seamless>
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

<?php
Modal::begin([
    'id' =>'showSyncProgress',
    //'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Поиск в 1С </h4>',
]);?>
<div style='width:100%; text-align:center;'><img src='img/ajax-loader.gif'></div>
<?php
Modal::end();
?>
