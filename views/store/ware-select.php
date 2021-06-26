<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Товарная номенклатура';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

//$model->loadWareSetPar();

$yfrom = date('Y')-1;
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

function chkSelectedWare(){
 //console.log("chk");
}

function selectWare(wareRef,wareEd ) {    
	window.parent.addSelectedWare(wareRef,wareEd);
}
function openSupply( wareNameRef) {       
    url = 'fin/supply-src&noframe=1&m_from=1&y_from=<?= $yfrom ?>&wareRef='+wareNameRef;    
    mode=document.getElementById('mode').value;
    if (mode ==1 ) url = url +'&orgRef=<?=$model->orgRef?>';
    openWin(url,'supplyWin'); 
}


function openOtvesList( wareListRef,  wareNameRef) {    

    url = 'store/ware-otves-list&wareRef='+wareListRef+'&wareNameRef='+ wareNameRef+'&refSchet=<?= $model->refSchet ?>&refZakaz=<?= $model->refZakaz ?>';    
    openWin(url,'otvesWin');
    window.parent.closeSelectWare();
}

function chngProd( ) {    
var saleType = $('input[id="saleType"]:checked').val();
document.getElementById('wareFormat').value =0;
document.getElementById('wareLength').value ='';
document.getElementById('wareWidth').value ='';

  switch (saleType)
  {
   case '1':
   document.getElementById('wareLengthCont').style.display = 'none';
   break;
   case '2':
   document.getElementById('wareLengthCont').style.display = 'block';   
   break;
  }

acceptFilter( );
}

function cngFormat () {
document.getElementById('wareLength').value ='';
document.getElementById('wareWidth').value ='';
acceptFilter( );    
}    

function setMode(mode){
document.getElementById('mode').value=mode;
acceptFilter( );
}

function acceptFilter( ) {    
var wareType = document.getElementById('wareTypeShow').value;
var wareGrp = document.getElementById('wareGrpShow').value;
var wareProd = document.getElementById('wareProdShow').value;
var format = document.getElementById('wareFormat').value;
var density = document.getElementById('wareDensityShow').value;
var showProdutcion = document.getElementById('showProdutcion').value;
var mode = document.getElementById('mode').value;
//var warePack= document.getElementById('warePackShow').value;

//var wareWidth = document.getElementById('wareWidth').value;
//var wareLength= document.getElementById('wareLength').value;

//var saleType = $('input[id="saleType"]:checked').val();

var wareMark= document.getElementById('wareMark').value;
var wareSort= document.getElementById('wareSort').value;

var url = 'index.php?r=store/ware-select&noframe=1&wareType='+wareType+'&wareGrp='+wareGrp+'&wareProd='+wareProd;
    url = url+'&showProdutcion='+showProdutcion;
    url = url+'&orgRef=<?= $model->orgRef ?>&mode='+mode;
    url = url+'&refSchet=<?= $model->refSchet ?>&refZakaz=<?= $model->refZakaz ?>';


  //  url = url+'&format='+format+'&density='+density+'&wareSort='+wareSort+'&wareMark='+wareMark;
  //  url = url+'&saleType='+saleType+'&wareWidth='+wareWidth+'&wareLength='+wareLength;
document.location.href=url;
}

function createWare(){
    document.getElementById('dataVal').value=document.getElementById('wareTitle').value;    
    document.getElementById('dataType').value='createWare';
    
    document.getElementById('wareTitle').value=document.getElementById('wareTitleShow').value;    
    document.getElementById('wareTypeRef').value=document.getElementById('wareTypeShow').value;        
    document.getElementById('grpRef').value=document.getElementById('wareGrpShow').value;    
    document.getElementById('producerRef').value=document.getElementById('wareProdShow').value;        
    
    document.getElementById('density').value=document.getElementById('wareDensityShow').value;    
    document.getElementById('format').value=document.getElementById('wareFormat').value;    

    document.getElementById('wareMarkGen').value= document.getElementById('wareMark').value;
    document.getElementById('wareSortGen').value= document.getElementById('wareSort').value;


    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-name-detail',
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


<table width='870px' border='0'>
<tr>
    <td style='padding:4px; width:160px;'>
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
          'id' => 'wareTypeShow', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор типа',
        ]);       
    ?>
    </td>
    
    <td  style='padding:4px; width:160px;'>
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
          'id' => 'wareGrpShow', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор вида товара',
        ]);       
    ?>
    </td>
        
    <td   style='padding:4px; width:160px;'>
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
          'id' => 'wareProdShow', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Выбор производителя',
        ]);       
    ?>
    </td>

    <td  style='padding:4px; width:160px;'>
    <?php

    //$wareProducer[0]='Все';
     echo  Html::dropDownList(
       'showProdutcion',
        intval($model->showProdutcion),
        [ 1=> 'Сырье', 2 => 'Продукция'] ,
        [
          'class' => 'form-control',
          'style' => 'width:150px;font-size:12px; padding:1px;',
          'id' => 'showProdutcion',
          'onchange' => 'acceptFilter();',
          'prompt' => 'Производство/сырье',
        ]);
    ?>
    </td>

    <td></td>
</tr>


<?php if ($model->orgRef>0) { ?>
<tr>
    <td colspan=2 >
    <?php
    if ($model->mode ==1) $class='btn btn-primary';
    else $class='btn btn-default';
    $action="setMode(1)";
    echo \yii\helpers\Html::tag( 'div', $model->orgTitle,
                   [
                     'class'   => $class,
                     'onclick' => $action,
                     'style'   => 'font-size:11px; width:300px;'
                   ]);

    ?>
    </td>

    <td colspan=2 >
    <?php
    if ($model->mode ==0) $class='btn btn-primary';
    else $class='btn btn-default';
    $action="setMode(0)";
    echo \yii\helpers\Html::tag( 'div', 'Все организации',
                   [
                     'class'   => $class,
                     'onclick' => $action,
                     'style'   => 'font-size:11px; width:300px;'
                   ]);

    ?>
    </td>


    <td></td>
</tr>

<?php }?>


</table>


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

/*            [
                'attribute' => 'wareTypeName',
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
*/        

            [
                'attribute' => 'wareTitle',
                'label' => 'Товар',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 200px'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";
                
                /*$strSql="SELECT title FROM {{%warehouse}} where wareListRef =:wareRef";
                $list=  Yii::$app->db->createCommand($strSql,[':wareRef' => $model['id'],])->queryAll();                        
                    for($i=0;$i<count($list); $i++) $title.=$list[$i]['title']."\n";  */                
                if(!empty($model['wareListRef'])) $style='color:DarkGreen;font-weight:bold';
                                                else  $style='';
                $action = "openWin('store/ware-card&id=".$model['id']."', 'wareCard')";
                $id = 'wareTitle'.$model['id'];

                   $val = \yii\helpers\Html::tag( 'div', $model['wareTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,                
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
                $title= $model['v1']." ".$model['v2']." ".$model['v3']." ".$model['v4'];
                
                $action = "selectWare(".$model['id'].",'".$model['wareEd']."')";
                $id = 'add'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "Добавить в счет/заказ",
                   ]);

                   return $val;
                }                
                

                
            ],
            
            
            [
                'attribute' => 'wareEd',
                //'filter' => $model->getWareEdList(),
                'filterInputOptions' => ['style' => 'font-size:12px; padding:1px;width: 50px'],
                'label' => 'Ед.изм.',
                'format' => 'raw',
            ],

            [
                'attribute' => 'lastUse',
                'label' => 'Дата',
                'format' =>  ['date', 'php:d.m.y'],
            ],

            
            
            [
                'attribute' => 'useCount',
                'label' => 'Исп',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= "";
                
                if(empty($model['wareListRef'])) $class='';
                                           else  $class='clickable';
                $action = "openSupply(".$model['wareListRef'].")";
                $id = 'useCount'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', $model['useCount'], 
                   [
                     'class'   => $class,
                     'id'      => $id,
                     'onclick' => $action,
                   ]);

                   return $val;
                }                
                

                
            ],

            [
                'attribute' => '-',
                'label' => 'Остаток',
                'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 200px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $title= "";
                $otvesCnt =0;
                $remain =0;
                if(!empty($model['wareListRef']))
                {
                    $strSql="SELECT count(id) FROM {{%otves_list}} where refWareList =:wareRef";
                    $otvesCnt=Yii::$app->db->createCommand($strSql,[':wareRef' => $model['wareListRef'],])->queryScalar();
                    
                    $strSql="SELECT SUM(amount) FROM {{%warehouse}} where wareListRef =:wareRef";
                    $remain=Yii::$app->db->createCommand($strSql,[':wareRef' => $model['wareListRef'],])->queryScalar();
                    
                }
                else if(!empty($model['warehouseRef']))
                {
                    $strSql="SELECT SUM(amount) FROM {{%warehouse}} where wareListRef =:wareRef";
                    $remain=Yii::$app->db->createCommand($strSql,[':wareRef' => $model['warehouseRef'],])->queryScalar();

                }
            
                if ($otvesCnt==0) $class='';
                else  $class='clickable';
                $action = "openOtvesList(".$model['wareListRef'].", ".$model['id']." )";
                $id = 'remain'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', number_format($remain,0,'.',"&nbsp;")   , 
                   [
                     'class'   => $class,
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                  return $val;
              }
            ],

          [
                'attribute' => 'v3',
                'label' => 'Прайс',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            
                $title= $model['v1']." ".$model['v2']." ".$model['v3']." ".$model['v4'];
                
               // if(empty($model['wareListRef'])) $class='';
               //                            else  $class='clickable';
               // $action = "openSupply(".$model['wareListRef'].")";
                $id = 'v'.$model['id'];
                $val = \yii\helpers\Html::tag( 'div', $model['v3'], 
                   [
                 //    'class'   => $class,
                     'id'      => $id,
                 //    'title'   => $title,
                   ]);

                   return $val;
                }                
                

                
            ],

/*          [
                'attribute' => 'showProdutcion',
                'label' => 'Продукция',
                'filter' => [0 => 'Все', 1=> 'Сырье', 2 => 'Продукция'],
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
*/

       ]
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-ware-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);


echo $form->field($model, 'wareTitle' )->hiddenInput(['id' => 'wareTitle' ])->label(false);
echo $form->field($model, 'wareGrpRef' )->hiddenInput(['id' => 'grpRef' ])->label(false);
echo $form->field($model, 'producerRef' )->hiddenInput(['id' => 'producerRef' ])->label(false);
echo $form->field($model, 'density' )->hiddenInput(['id' => 'density' ])->label(false);
echo $form->field($model, 'format' )->hiddenInput(['id' => 'format' ])->label(false);
//echo $form->field($model, 'warePack' )->hiddenInput(['id' => 'warePack' ])->label(false);
echo $form->field($model, 'wareTypeRef' )->hiddenInput(['id' => 'wareTypeRef' ])->label(false);
echo $form->field($model, 'mode' )->hiddenInput(['id' => 'mode' ])->label(false);

/*echo $form->field($model, 'wareWidthGen' )->hiddenInput(['id' => 'wareWidthGen' ])->label(false);
echo $form->field($model, 'wareLengthGen' )->hiddenInput(['id' => 'wareLengthGen' ])->label(false);
echo $form->field($model, 'saleTypeGen' )->hiddenInput(['id' => 'saleTypeGen' ])->label(false);
echo $form->field($model, 'wareMarkGen' )->hiddenInput(['id' => 'wareMarkGen' ])->label(false);
echo $form->field($model, 'wareSortGen' )->hiddenInput(['id' => 'wareSortGen' ])->label(false);
*/

//echo "<input type='submit'>";
ActiveForm::end(); 
?>


<?php
if(!empty($model->debug)){
echo "<pre>";    
print_r($model->debug);
echo "</pre>";
}
?>




<!--
    <td>
    <?php
/*     echo  Html::textInput( 
       'wareWidth', 
       $model->wareWidth,                                
      [
         'class' => 'form-control',
         'style' => 'width:70px; font-size:11px;padding:1px;', 
         'id' => 'wareWidth', 
         'placeholder' => 'ширина',
         'onchange' => 'acceptFilter();',
      ]);             
*/    ?>
    </td>

    <td><div id='wareLengthCont' style='<?php if($model->saleType == 1) echo "display:none;" ?>' >
    <?php
/*     echo  Html::textInput( 
       'wareLength', 
       $model->wareLength,                                
      [
         'class' => 'form-control',
         'style' => 'width:70px; font-size:11px;padding:1px;', 
         'id' => 'wareLength', 
         'placeholder' => 'длинна',
         'onchange' => 'acceptFilter();',
      ]);             
*/ ?></div>
    </td>

    

    <td  width='20px' align='left'>     
    <?php
/*        $action ="chngProd();";
    
    echo Html::radio(
    'saleType',($model->saleType==1),    
    ['label' => '', 
    'value' => 1, 
    'uncheck' => null, 
    'id' => 'saleType',
    'style' => 'font-size:12px;',  
    'onchange' => $action]
    ); 
*/        ?>
    </td>
    <td align='left'><div  style='margin-top:-7px;padding:2px;'> В сырье</div></td>
    <td  width='20px' align='left'>&nbsp;</td>     
    <td  width='20px' align='left'>     
    <?php
/*        $action ='';//"chngProd();";
    echo Html::radio(
    'saleType',($model->saleType==2),    
    ['label' => '', 
    'value' => 2, 
    'uncheck' => null, 
    'id' => 'saleType', 
    'style' => 'font-size:12px;',  
    'onchange' => $action]
    );    
*/     ?>
    </td>
    <td align='left'><div  style='margin-top:-7px;padding:2px;'> Продукция</div></td>
-->


<div id='additionOpt' style='display:none'>
<table border='0'  width='470px'>
<tr>

    <td> 
    <?php
    $wareFormats =$model->getWareFormat();
   // $wareGroups[0]='Все'; 
     echo  Html::dropDownList( 
       'wareFormat', 
        $model->format, 
        $wareFormats ,
        [
          'class' => 'form-control',
          'style' => 'width:110px;font-size:12px; padding:1px;', 
          'id' => 'wareFormat', 
          'onchange' => 'acceptFilter();',
          'prompt' => 'Формат',
        ]);       
    ?>
    </td>

<td> 
    <?php
     echo  Html::textInput( 
       'density', 
       $model->density,                                
      [
         'class' => 'form-control',
         'style' => 'width:100px; font-size:11px;padding:1px;', 
         'id' => 'wareDensityShow', 
         'placeholder' => 'плотность',
         'onchange' => 'acceptFilter();',
      ]);       
    ?>
    </td>    
    
    <td>
    <?php
     echo  Html::textInput( 
       'wareSort', 
       $model->wareSort,                                
      [
         'class' => 'form-control',
         'style' => 'width:100px; font-size:11px;padding:1px;', 
         'id' => 'wareSort', 
         'placeholder' => 'сорт',
         'onchange' => 'acceptFilter();',
      ]);             
    ?>
    </td>

    <td>
    <?php
     echo  Html::textInput( 
       'wareMark', 
       $model->wareMark,                                
      [
         'class' => 'form-control',
         'style' => 'width:100px; font-size:11px;padding:1px;', 
         'id' => 'wareMark', 
         'placeholder' => 'марка',
         'onchange' => 'acceptFilter();',
      ]);             
    ?>
    </td>
    
     <td> 
    </td>

    <td>
    <?php
 /*   echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-search',
                     'id'      => 'btn-search',
                     'onclick' => 'acceptFilter();',
                     'title'   => 'найти',
                   ]);    
  */  ?>               
    </td>
</tr>
</table>


<table width='470px' border='0'>
<tr>
    <td>
    
    <div style='margin-top:4px;margin-bottom:4px;height:2px;border-bottom:2px LightGray solid;'></div>
    <?php
       echo  Html::textInput( 
       'wareTitle', 
       $model->wareTitleShow,                                
      [
         'class' => 'form-control',
         'style' => 'width:432px; font-size:11px;padding:1px;', 
         'id' => 'wareTitleShow', 
         'placeholder' => 'Номенклатурное название',
      ]);       
    ?>
    </td>
    <td>    
    <div style='margin-top:-10px;margin-bottom:4px;height:2px;border-bottom:2px LightGray solid;'></div>
    <?php
    echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'id'      => 'btn-search',
                     'onclick' => 'createWare();',
                     'title'   => 'Создать',
                   ]);    
    ?>               
    </td>    
</tr>
</table>

</div>
