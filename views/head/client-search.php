<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Поиск клиентов';
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');



$wareTypeArray = $model->wareTypeArray;
$wareGrpArray = $model->wareGrpArray;
?>


<script type="text/javascript">

/*var wareType=[
    [2,3],
    [0,1]
];*/


function cngFilter(id, type, val)
{
 var url = '<?= Yii::$app->request->url ?>';

  url= url+'&setType='+type+'&setId='+id+'&setVal='+val;
  document.location.href=url;

}


function chngList()
{
 id = $('#selectOrgJobList').val();
 cngFilter(id, 'chngList', 0);
 
}

function openFltWareDialog()
{
    $('#fltWareDialog').modal('show'); 
}

function openAddListDialog()
{
    $('#addListDialog').modal('show'); 
}

function closeAddListDialog()
{
    $('#addListDialog').modal('hide'); 
    
    $('#dataType').val('addOrgJob');
    var dataVal = $('#editListName').val();
    $('#dataVal').val(dataVal);
 
    var dataNote = $('#editListNote').val();
    $('#dataNote').val(dataNote);
 
 saveData();
}

function markAll()
{    
   cngFilter(1, 'markAll',1);
}

function saveData()
{
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=head/save-org-job-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload==true)document.location.reload(true); 
            else showRes(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


function switchInJobList(refOrg)
{
    var curOrgJobList= $('#selectOrgJobList').val();
    $('#dataType').val('switchJobList');
    $('#recordId').val(curOrgJobList);
    $('#dataVal').val(refOrg);
    
    saveData();
}

function showRes(res)
{
 if (res.dataType == 'switchJobList'){
  var idx = res.dataVal+'switchJobList';
  if (res.val == 1)
      document.getElementById(idx).style.backgroundColor ='Green';
  else    
       document.getElementById(idx).style.backgroundColor ='LightGray';
 }
}

function fltFIO()
{
   var fltString= $('#fltManager').val();
   cngFilter(1, 'cngManagerFilter', fltString);

}

function fltOrg()
{
   var orgFltString= $('#fltOrgName').val();
   cngFilter(1, 'cngOrgFilter', orgFltString);
}

function fltSetForm()
{
 var id = $('#fltForm').val();
 cngFilter(1, 'fltForm', id); 
}


function openSupplyList(refOrg,refWare)
{
 var url ='head/org-ware-supply&refOrg='+refOrg+'&refWare='+refWare;
 openWin(url, 'supplyWin');

}
</script> 
 
<style>

.btn-small{
margin:2px;
padding:2px;
height:15px;
width:20px;
}

.fltTable {
 font-size:11px;
 padding: 2px;
}
</style>


<div class='row'>
    <div class='col-md-2'>
       Текущий список    
    </div>     
    <div class='col-md-5'>
      <?php      
        $orgJobList = $model->getOrgJobList();   
             
        echo Html::dropDownList( 
                       'selectOrgJobList', 
                        $model->curOrgJobList, 
                        $orgJobList,
                              [
                              'class' => 'form-control',
                              'style' => 'font-size:11px; padding:1px;', 
                              'id' => 'selectOrgJobList', 
                              'onchange' => 'chngList();'
                              ]);
      ?>
    </div> 
    <div class='col-md-1'>
    <?php
     $action = "openAddListDialog();";
      echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'title'   => 'Добавить список',
                     'onclick' => $action,     
                     'style'  => 'font-size:15px;'
                   ]);
    ?>
    </div>     
    <div class='col-md-2'>
    <?php
     $action = "openWin('head/org-job-list-reestr','orgListWin')";
     echo \yii\helpers\Html::tag( 'div', "Реестр Списков", 
                   [
                     'class'   => 'clickable',
                     'title'   => 'Открыть реестр списков',
                     'onclick' => $action,
     
                   ]);
    ?>
    </div>     
        
        
        
    <div class='col-md-2'>
    <?php
     $action = "markAll()";
     echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-small',
                     'title'   => 'Добавить в список все отфильтрованные',
                     'style'   => 'background-color:Green;',
                     'onclick' => $action,     
                   ]);
    ?>

    <?php
   /* $lastSync =	 Yii::$app->db->createCommand('SELECT MAX(syncTime) FROM {{%tmp_reestr}}')
     ->queryScalar(); 
    if (strtotime($lastSync) < time()-8*60*60) {$style='color:Crimson;'; }
                                  else     {$style=''; }
    echo \yii\helpers\Html::tag( 'span', date("d.m.y", strtotime($lastSync)), 
                   [                     
                     'title'   => 'Список клиентов актуален на ',
                     'style'   => $style,     
                   ])."&nbsp;";
         
     $action = "openWin('site/update-reestr-client','reestrWin')";
     echo \yii\helpers\Html::tag( 'span', "", 
                   [
                     'class'   => 'glyphicon glyphicon-refresh clickable',
                     'title'   => 'Обновить реестр',
                     'onclick' => $action,
     
                   ]);
  */  ?>
    </div>     
        
</div>

<hr>

<div class='row'>

    <div class='col-md-8'>

<?php 

echo ListView::widget([
    'dataProvider' => $provider,
    'itemView' => '_clspost',
    'viewParams' => [
    'curOrgJobList' => $model->curOrgJobList,
    'wareTypes' => $model->wareTypes,
    'wareGrp' => $model->wareGrp,
    ],
]);

?>
    </div>


    <div class='col-md-4'>
    <table border='0' width='100%'><tr>
    <td><?php
        $action =  "fltOrg();";
        echo Html::textInput( 
                          'fltOrgName', 
                          $model->orgFilter,                                
                              [
                              'class' => 'form-control',
                              //'style' => 'width:300px; font-size:11px;padding:1px;', 
                              'id' => 'fltOrgName',                               
                              'onChange' => $action,
                              'placeHolder' => 'фильтр по названию клиента'
                              ]);    
                              
    ?></td>
    <td width='20px' style='padding:5px;'>
    <?php
            echo \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'title'   => 'найти по названию',
                     'style'   => "font-size:15px;",
                   ]);         
    ?>
    </td>
    </table>

    <table border='0' width='100%'><tr>
    <td><?php
        $action =  "fltFIO();";
        
        $managerList = $model->getManagerList();   
             
        echo Html::dropDownList( 
                       'selectOrgJobList', 
                        $model->fltManager,                                 
                        $managerList,
                              [
                              'class' => 'form-control',
                      //        'style' => 'font-size:11px;', 
                              'id' => 'fltManager', 
                              'onChange' => $action,
                              'prompt'  => 'фильтр по менеджеру (сброшен)',
                              ]);
 
    ?></td>
    <td width='20px' style='padding:5px;'>
    <?php
            echo \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'title'   => 'найти по менеджеру',
                     'style'   => "font-size:15px;",
                   ]);         
    ?>
    </td>
    </table>

    
            
    <p><b>Товар - </b></p>
    <?php
    Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getWareTypeProvider(Yii::$app->request->get()),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [


              [
                'attribute' => 'id',
                'label'     => '',
                'contentOptions' =>['class' => 'fltTable', 'style' =>'padding: 2px;width:30px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($wareTypeArray) {
                 $style="";

                 $id = $model['id'].'wareGrp';


                 if (in_array($model['id'], $wareTypeArray)){
                    $style ="background-color:Blue;";
                    $action =  "cngFilter(".$model['id'].", 'wareType', 0);";
                    }
                 else {
                    $style ="background-color:White;";
                    $action =  "cngFilter(".$model['id'].", 'wareType', 1);";
                    }


                 $val = \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => '',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;
               }
            ],


            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],

        ],
    ]
);
Pjax::end();
    ?>

    <?php
    Pjax::begin();
    
    $action = "cngFilter(1, 'grpAll', 1);";
    $switchGrp = \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'onclick' => $action,
                     'title'   => 'Переключить фильтр',
                   ]);
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getWareGrpProvider(Yii::$app->request->get()),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [


              [
                'attribute' => '-',
                'label'     => $switchGrp,                
                'encodeLabel'     => false,
                'format' => 'raw',
                
                'contentOptions' =>['class' => 'fltTable', 'style' =>'padding-top: 2px;padding-bottom: 2px;'],
                'value' => function ($model, $key, $index, $column)use($wareGrpArray) {
                 $style="";

                 $id = $model['id'].'wareGrp';
                 if (in_array($model['id'], $wareGrpArray)){
                    $style ="background-color:Blue;";
                    $action =  "cngFilter(".$model['id'].", 'wareGrp', 0);";
                    }
                 else {
                    $style ="background-color:White;";
                    $action =  "cngFilter(".$model['id'].", 'wareGrp', 1);";
                    }



                 $val = \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => '',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;
               }
            ],


            [
                'attribute' => 'wareGrpTitle',
                'label'     => 'Вид',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],


            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],

        ],
    ]
);
Pjax::end();
    ?>
    
<table border='0' width='100%'><tr>
    <td><?php
        $action =  "fltSetForm();";
        $fltFormList = $model -> getFltFormList();
        
         echo Html::dropDownList( 
                       'fltForm', 
                        $model->fltForm,                                 
                        $fltFormList,
                              [
                              'class' => 'form-control',
                      //        'style' => 'font-size:11px;', 
                              'id' => 'fltForm', 
                              'onChange' => $action,
                              'prompt'  => 'фильтр по форме товара (сброшен)',
                              ]);
                               
    ?></td>
    <td width='20px' style='padding:5px;'>
    <?php
            echo \yii\helpers\Html::tag( 'div',"",
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'title'   => 'найти по форме',
                     'style'   => "font-size:15px;",
                   ]);         
    ?>
    </td>
    </table>    
    
    
<?php
    Pjax::begin();
   if(!empty($model->wareList))
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getWareFilterProvider(Yii::$app->request->get(), 1),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
   
              [
                'attribute' => 'wareTitle',
                'encodeLabel'     => false,
                'label'     => 'Товар',
                'format' => 'raw',                
                'contentOptions' =>['class' => 'fltTable', 'style' =>'padding-top: 2px;padding-bottom: 2px;'],
                'value' => function ($model, $key, $index, $column) {
                $action="selectWare(".$model['id'].")";
                 $val = \yii\helpers\Html::tag( 'div',$model['wareTitle'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                return $val;
               }
            ],


            [
                'attribute' => 'wareGrpTitle',
                'label'     => 'Вид',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],


            [
                'attribute' => 'wareTypeName',
                'label'     => 'Тип',
                'contentOptions' =>['class' => 'fltTable',  'style' =>'padding: 2px;'],
                'format' => 'raw',
            ],

        ],
    ]
);
Pjax::end();
    ?>
<!-- <div class='btn btn-default' onclick='openFltWareDialog()'>Добавить фильтр по товару</div>-->    
    
    </div>
</div>


<?php
Modal::begin([
    'id' =>'fltWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='fltWareDialogFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=head/client-ware-flt&wareGrp=<?=$model->wareGrp?>&wareTypes=<?=$model->wareTypes?>&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
Modal::begin([
    'id' =>'addListDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>
<div style='width:450px'>

<table class='table'>
<tr>
<td width='150px'>Название списка</td> 
<td> 
<?php
    echo Html::textInput( 
                          'editListName', 
                          '',                                
                              [
                              'class' => 'form-control',
                              //'style' => 'width:300px; font-size:11px;padding:1px;', 
                              'id' => 'editListName',                               
                              ]);
?>
</td>
</tr>

<tr>
<td>Комментарий</td> 
<td> 
<?php
    echo Html::textArea( 
                          'editListNote', 
                          '',                                
                              [
                              'class' => 'form-control',
                              'id' => 'editListNote',                               
                              ]);
?>
</td>
</tr>

</table>
<?php

    $action="closeAddListDialog();";
    echo \yii\helpers\Html::tag( 'div','Добавить список',
           [
             'class'   => 'btn btn-primary',
             'onclick' => $action,
           ]);
?>

</div>
<?php Modal::end();?>


<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>

<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=head/save-org-job-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal', ])->label(false);
echo $form->field($model, 'dataNote' )->textArea(['id' => 'dataNote', 'style' =>'display:none' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
