<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\date\DatePicker;

use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Сверка долга';
$curUser=Yii::$app->user->identity;


$now =strtotime($model->strDate);
$prev=$now-24*3600;
$next=$now+24*3600;

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.checkable {
text-align:center;
    visibility:visible;
    width:100px;
}

.checkable:hover{
    visibility:hidden;
    
}

</style>
  
<script>
function switchFilter(id)
{
     openSwitchWin('fin/switch-sverka-flt&strDate=<?= date("Y-m-d", $now) ?>&id='+id);
}

function openClient(orgRef)
{
     openWin('site/org-detail&noframe=1&orgId='+orgRef,'childWin');
}

function openCfg()
{
     openWin('managment/doc/doc-type-cfg','childWin');
}


function setUseAll(useAll)
{
    document.location.href='index.php?r=fin/sverka-use&noframe=1&strDate=<?= date("Y-m-d", $now) ?>&orgFlt=<?=$model->orgFlt?>&useAll='+useAll;
}

function changeShowDate()
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=fin/sverka-use&noframe=1&strDate='+showDate ;
}

function setStatFlt(fltType)
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=fin/sverka-use&noframe=1&ftType='+fltType+'&strDate='+showDate ;
}


function setAsUse(id, typeRef, sum, typeTitle)
{
    document.getElementById('sverkaDialogTitle').innerText=typeTitle;
    document.getElementById('dataId').value=id;
    document.getElementById("dataFix").checked = true;
    document.getElementById('dataRequestId').value=typeRef;
    document.getElementById('dataValue').value=sum;
    
    $('#sverkaDialog').modal('show');

}
function setExecSave()
{
    //document.getElementById('sverkaForm').submit();
    
   $('#sverkaDialog').modal('hide'); 
    var data = $('#sverkaForm').serialize();
    $.ajax({
        url: 'index.php?r=fin/save-sverka-record',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            refresh();
            console.log(res);          
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	    
   
   
}

function refresh()
{
  document.location.reload(false);    
  // $.pjax.reload({container:"#grdSverka"});    
     // срабатывает, но не обновляет данные 
}


</script>

<div class ='spacer'></div>
<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=fin/sverka-use&noframe=1&strDate=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-3' style='text-align:center'><h4><?= Html::encode($this->title) ?> на </h4></div>
    <div class ='col-md-3' style='text-align:center'>
   <?php   
   echo DatePicker::widget([
    'name' => 'show_date',
    'id' => 'show_date',
    'value' => date("d.m.Y",$now),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
]);
?>      
</div>

   
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=fin/sverka-use&noframe=1&strDate=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>

  <div class='col-md-1' style='text-align:right;'><a href='#' onclick='openCfg();'><span class='glyphicon glyphicon-cog'></span></a></div>    
  <div class='col-md-1' style='text-align:right;'><a href='index.php?r=data/sync-sverka&syncTime=<?= $now ?>&noframe=1'><span class='glyphicon glyphicon-refresh'></span></a></div>  
  <div class ='col-md-2' style='text-align:center'><?= $model->syncDateTime ?></div>
</div>




<div class ='row'>
   <div class ='col-md-8'>   
<?php
//number_format($model->sumValue,2,'.','&nbsp;');
$orgFlt = $model->orgFlt;



$columns[]= [
                'attribute' => 'owerOrgTitle',
                'label' => 'Баланс',
                'format' => 'raw',
            ];

$columns[]= [
                'attribute' => 'balanceSum',
                'label' => 'Баланс',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return "<div style='text-align:right'>".number_format($model['balanceSum'],2,'.','&nbsp;')."</div>";
                }                
            ];

$columns[]= [
                'attribute' => 'isFilter',
                'label' => 'Учитывать',
                'filter' => ['1' => 'Все', '2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['isFilter'] == 1 ){ $isUse = true;  $val=0;}
                   else                           { $isUse = false; $val=1;}
                    return "<a href='#' onclick='switchFilter(".$model['id'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                         ['class' => 'label label-' . ($isUse ? 'success' : 'default'),])."</a>";
                }                
                
            ];


$content =
     GridView::widget(
    [
        'dataProvider' => $fltProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,        
        
  /*  'panel' => [
        'type'=>'success',
        'footer'=>true,
    ],        */
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => $columns,
                
    ]
);
$fltStat = "";
if (empty($model->orgFlt))$fltStat = "in";
 echo Collapse::widget([
    'items' => [
        [
            'label' => $model->fltOrgTitle." ▼ ",                        
            'content' => $content,
            'contentOptions' => ['class' => $fltStat],
            'options' => []
        ]
    ]
]); 


?>
    </div>
    <div class ='col-md-4' >   

    </div>
</div>



<hr>

<div class='row'>
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isClientP') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isClientP");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Нам Клиент'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isClientM') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isClientM");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Мы Товар'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isWareP') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isWareP");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Нам Товар'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isWareM') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isWareM");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Мы за товар'>
  </div>  
   <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isBank') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isBank");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Банк'>
  </div>  

</div>

<div class='row'>
   <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isServiceP') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isServiceP");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Нам за услуги'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isServiceM') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isServiceM");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Мы за услуги'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isOtherP') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isOtherP");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Нам прочее'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isOtherM') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isOtherM");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Мы прочее'>
  </div>  
  <div class='col-md-2'>    
    <?php  $s='btn-default'; if($model->ftType == 'isMove') $s='btn-primary';?>
    <input type='button' onclick='setStatFlt("isMove");' class='btn <?= $s ?>' style='width:120px; padding:2px' value='Перемещение'>
  </div>  

</div>

<?php
//number_format($model->sumValue,2,'.','&nbsp;');
$columns=$model->getSverkaColumns();

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        'showFooter' => true,
        'id' => 'grdSverka',
    /*'panel' => [
        'type'=>'success',        
        'footer'=>"",
    ],*/        
        
        'pjax'=>true,
        'pjaxSettings'=>[        
        'neverTimeout'=>true,
        ],

        'columns' => $columns,
    ]
);

?>

<?php
Modal::begin([
    'id' =>'sverkaDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Перенос данных </h4>',
]);?><div style='width:500px'>
<?php $form = ActiveForm::begin(['id' => 'sverkaForm', 'action' => 'index.php?r=fin/save-sverka-record']); 
?>
<p>Перенести в <span id="sverkaDialogTitle" style='font-weight:bold;' >куда </span></p>
<?= $form->field($model, 'dataValue' )->textInput(['id' => 'dataValue' ])->label('Сумма сверки'); ?>
<?= $form->field($model, 'dataFix')   ->checkbox(['id' => 'dataFix', 'label' => 'Запомнить']); ?>
<?= $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false); ?>
<?= $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false); ?>
<?= $form->field($model, 'strDate' )->hiddenInput(['id' => 'strDate' ])->label(false); ?>
<div align='center'><input type='button' class='btn btn-primary' value='Сохранить' onclick='setExecSave();'></div>

<?php ActiveForm::end(); ?>

</div>
<?php Modal::end();?>


<?php
// echo "<pre>";
//print_r($model->debug);
// echo "</pre>";
?>
