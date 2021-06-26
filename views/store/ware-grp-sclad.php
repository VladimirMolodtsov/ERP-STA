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
<p>Виды товара на складах.</p>

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
    <div class='col-md-8'></div>
    
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
</div>


<?php

$columns =  [

            [
                'attribute' => 'wareTypeName',
                'label' => 'Тип',
                'format' => 'raw',
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareTypes(),
            ],
        
            [
                'attribute' => 'wareGrpTitle',
                'label' => 'Вид',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],                
                'filter' => $model->getWareGroups(),
                'value' => function ($model, $key, $index, $column) {                  
                
                    $action = "openWin('/store/ware-sclad-detail&grpRef=".$model['grpRef']."','wareWin');";
                    return \yii\helpers\Html::tag( 'div', $model['wareGrpTitle'], 
                    [
                      'class'   => 'clickable',                      
                      'onclick' => $action,
                      'title'   => 'Вид товара',              
                    ]); 
                }                
                
                
          ],

            [
                'attribute' => 'goodAmount',
                'label' => 'К-во.',
                'format' => 'raw',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filter' => [4 => 'Все', 1 => '> 0', 2 => '= 0',  3 => '< 0'],
                'value' => function ($model, $key, $index, $column) {  
                if ($model['goodAmount'] <0)  return "<font color='Crimson'>".number_format($model['goodAmount'],2,'.','&nbsp;')."</font>";             
                    return number_format($model['goodAmount'],2,'.','&nbsp;');
                }                
                
            ],
           ];
           
           

$list = $model->getWareScladColumns();
$lastRef = $model->lastRef;

for ($i=0;$i<count($list);$i++)
    {
        $useInSum = $list[$i]['useInSum'];
        if ($useInSum != 1) continue;
        $lbl = preg_replace("/\s+/","<br>",$list[$i]['scladTitle']);
        $useRef = $list[$i]['id'];
        $columns[]= [      
                'attribute' => "sclad_".$i,
                'label' => "<div class='tb-head'>".$lbl."</div>",
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => [0 => 'Все', 1 => '> 0', 2 => '= 0',  3 => '< 0'],
                'encodeLabel' => false,
                //'contentOptions' => ['style' =>'font-size:11px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($lastRef,$useRef) {                

                   $val=Yii::$app->db->createCommand('Select Sum(goodAmount) from {{%ware_content}},{{%warehouse}}   where 
                   {{%ware_content}}.goodTitle = {{%warehouse}}.title and {{%warehouse}}.grpRef =:grpRef AND {{%warehouse}}.ed =:goodEd 
                   and goodAmount>0
                   AND {{%ware_content}}.useRef = :useRef AND {{%ware_content}}.headerRef =:headerRef',
                   [
                     ':grpRef' => $model['grpRef'],
                     ':headerRef' => $lastRef,
                     ':useRef'    => $useRef,
                     ':goodEd'    => $model['goodEd'],
                   ]
                   )                    
                    ->queryScalar(); 
                   
                   $id = 'sclad_'.$useRef.'_'.$model['grpRef'];
                   if ($val< 0) $c='color:Crimson;';
                           else $c='color:Black;';
                   return \yii\helpers\Html::tag( 'div', number_format($val,2,'.','&nbsp;'), 
                   [
                     'id'      => $id,
                     'style'   => 'text-align:center;'.$c,
                   ]);
                }                                
       ];
    }

  /*      $columns[]= [      
                'attribute' => "sclad_".$i,
                'label' => "<div class='tb-head'>Прочие</div>",
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => [0 => 'Все', 1 => '> 0', 2 => '= 0',  3 => '< 0'],
                'encodeLabel' => false,
                //'contentOptions' => ['style' =>'font-size:11px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($lastRef,$useRef) {                

                   $val=Yii::$app->db->createCommand('Select Sum(goodAmount) from {{%ware_content}},{{%warehouse}},{{%ware_use}}   where 
                   {{%ware_content}}.goodRef = {{%warehouse}}.id and {{%warehouse}}.grpRef =:grpRef AND {{%warehouse}}.ed =:goodEd 
                   and goodAmount>0 AND {{%ware_content}}.useRef = {{%ware_use}}.id AND {{%ware_use}}.useInSum=0
                  AND {{%ware_content}}.headerRef =:headerRef',
                   [
                     ':grpRef' => $model['grpRef'],
                     ':headerRef' => $lastRef,
   //                  ':useRef'    => $useRef,
                     ':goodEd'    => $model['goodEd'],
                   ]
                   )                    
                   ->queryScalar(); 
                    
                    
                   
                   $id = 'sclad_'.$useRef.'_'.$model['grpRef'];
                   if ($val< 0) $c='color:Crimson;';
                           else $c='color:Black;';
                   return \yii\helpers\Html::tag( 'div', number_format($val,2,'.','&nbsp;'), 
                   [
                     'id'      => $id,
                     'style'   => 'text-align:center;'.$c,
                   ]);
                }                                
       ];                
    */       
           
                      
           
$columns[]=        
            [
                'attribute' => 'wareEd',
                'label' => 'Ед.',
                'format' => 'raw',
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],
                'filter' => $model->getWareScladEdList(),
                'value' => function ($model, $key, $index, $column) {                  
                    return $model['goodEd'];
                }                
                
            ];
            
$columns[]=    [
                'attribute' => '-',
                'label' => 'Предложено',
                'format' => 'raw',
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],                
                'value' => function ($model, $key, $index, $column) {                  
                
                $val=Yii::$app->db->createCommand('Select Sum({{%schetContent}}.wareCount) from {{%schetContent}},{{%schet}},{{%warehouse}}  
                where {{%schetContent}}.refSchet = {{%schet}}.id AND {{%schetContent}}.warehouseRef = {{%warehouse}}.id
                AND docStatus in (1,2,3) and cashState=0 and grpRef=:wareGrpRef AND {{%schetContent}}.wareEd=:wareEd',
                   [
                     ':wareGrpRef' => $model['grpRef'],
                     ':wareEd' =>$model['goodEd'],
                   ]
                   ) ->queryScalar(); 
                
                $action = "openWin('store/ware-schet-detail&state=1&grpRef=".$model['grpRef']."','shetWin')";
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',                     
                     'onclick' => $action,
                   ]);
                }                
                
            ];

$columns[]=    [
                'attribute' => '-',
                'label' => 'Согласовано',
                'format' => 'raw',
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],                
                'value' => function ($model, $key, $index, $column) {                  
                
                $val=Yii::$app->db->createCommand('Select Sum({{%schetContent}}.wareCount) from {{%schetContent}},{{%schet}},{{%warehouse}}  
                where {{%schetContent}}.refSchet = {{%schet}}.id AND {{%schetContent}}.warehouseRef = {{%warehouse}}.id
                AND docStatus =4 and cashState=0 and grpRef=:wareGrpRef AND {{%schetContent}}.wareEd=:wareEd',
                   [
                     ':wareGrpRef' => $model['grpRef'],
                     ':wareEd' =>$model['goodEd'],
                   ]
                   ) ->queryScalar(); 
                
                $action = "openWin('store/ware-schet-detail&state=2&grpRef=".$model['grpRef']."','shetWin')";
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',                     
                     'onclick' => $action,
                   ]);
                }                
                
            ];
            
$columns[]=   [
                'attribute' => '-',
                'label' => 'Оплачено',
                'format' => 'raw',
                'contentOptions' => ['style' =>'text-align:right;width: 100px;'],
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 100px'],                
                'value' => function ($model, $key, $index, $column) {                  
                
                $val=Yii::$app->db->createCommand('Select Sum({{%schetContent}}.wareCount) from {{%schetContent}},{{%schet}},{{%warehouse}}  
                where {{%schetContent}}.refSchet = {{%schet}}.id AND {{%schetContent}}.warehouseRef = {{%warehouse}}.id
                AND cashState IN (1,2) 
                and wareGrpRef=:wareGrpRef AND {{%schetContent}}.wareEd=:wareEd',
                   [
                     ':wareGrpRef' => $model['grpRef'],
                     ':wareEd' =>$model['goodEd'],
                   ]
                   ) ->queryScalar(); 
                
             
                
                $action = "openWin('store/ware-schet-detail&state=3&grpRef=".$model['grpRef']."','shetWin')";
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',                     
                     'onclick' => $action,
                   ]);
                }                
                
            ];
            
                        

/*
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


*/

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
