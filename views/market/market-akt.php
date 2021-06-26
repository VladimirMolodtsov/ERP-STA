<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Collapse;
use kartik\grid\GridView;

/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Акт выполненных работ';
//$this->params['breadcrumbs'][] = $this->title;


//$this->registerCssFile('@web/tcal.css');
//$this->registerJsFile('@web/tcal.js');

$this->registerCssFile('@web/phone.css');
//$this->registerCssFile('@web/css/zvonki-common.css');


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/css/market/market-schet.css');         



$model->loadData();
?>


<style>
.page-title
{    
  font-size: 14pt;
  font-weight:bold;
}
.panel-heading
{    
 padding:2px;
}

.panel-body
{    
 padding:2px;
}
.summary
{
   display:none; 
}
.collapse-toggle
{
  font-size:12px;  
}
/***/
/*label
{
font-size:11px;
vertical-align:middle;
}*/
</style>

<script type="text/javascript">

function printAct(stamp){
  //showTransport = $('input[name="showTransport"]:checked').val();  
  var Url = 'market/print-act&id=<?=$model->id?>';                   
  openWin(Url,'printWin');                     
}

function saveField(id, type)
{   
    idx = type+id;
    document.getElementById('recordId').value = id;
    document.getElementById('dataType').value = type;
    document.getElementById('dataVal').value = document.getElementById(idx).value;
    saveData();
}

function switchField(id, type)
{
    document.getElementById('recordId').value = id;
    document.getElementById('dataType').value = type;
    saveData();
}

function addNewWare()
{   
    document.getElementById('recordId').value = <?= $model->id ?>;
    document.getElementById('dataType').value = 'addNewWare';
    saveData();
}


function saveNote()
{   
    
    document.getElementById('recordId').value = <?= $model->id ?>;
    document.getElementById('dataType').value = 'actNote';
    document.getElementById('dataVal').value = document.getElementById('actNote').value;
    saveData();
}


function saveData()
{
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-act-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload==true)document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

</script>

<h3><?= $this->title ?></h3>


<table border='0' width='1160px'>
 <tr>
  <td width='350px' valign='top'> <?php/*левый блок*/?>  
   <table border='0' width='100%'>
    <tr>
       <td width="100px"> Номер:</td>
       <td>
          <?php        
                  echo Html::textInput( 
                          'actNum', 
                          $model->actNum,                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:200px;', 
                              'id' => 'actNum', 
                              //'onchange' => $action,
                              ]);
         ?>  
      </td>
   </tr>

    <tr>
       <td width="100px"> Дата:</td>
       <td>
          <?php        
                  echo Html::textInput( 
                          'actDate', 
                          date("d.m.Y", strtotime($model->actDate)),                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:200px;', 
                              'id' => 'actDate', 
                              //'onchange' => $action,
                              ]);
         ?>  
      </td>
   </tr>

   
   <tr>
     <td colspan='2'> <b>По счету:</b></td>       
   </tr>
    <tr>
       <td width="100px"> Номер:</td>
       <td><b><?= $model->schetNum ?></b></td>
   </tr>   
    <tr>
       <td width="100px"> Дата:</td>
       <td><b><?= date("d.m.Y", strtotime($model->schetDate)) ?></b></td>
   </tr>
   
        
  </table>

   <div>  <b>  Контрагент: </b>
    <br><u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($model->orgRef)?>"><?= Html::encode($model->orgTitle)?></a></strong></u>   
  </div>
 
<div style='margin-bottom:5px; text-align:right;'>
 
                    
  
        <?php 
                 echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-print'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'print',
                     'onclick' => 'printAct(0);',
                     'title'   => 'без печати'     
                   ]);
        ?>
        
 </div>       
    
  </td>

 
  <td width='5px' ></td>
  <td valign='top'>
  <?php
  $providerWare = $model->getWareInActProvider(Yii::$app->request->get());    

  $contentWare= GridView::widget(
    [
        'dataProvider' => $providerWare,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'showFooter' => false,
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'attribute' => 'wareTitle',
                'label'     => 'Номенклатура',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:400px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = "wareTitle".$model['id'];
                 $action =  "saveField(".$model['id'].", 'wareTitle');"; 
                 return Html::textInput( 
                          $id, 
                          $model['wareTitle'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:400px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => 'wareCount',
                'label'     => 'К-во',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "wareCount".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareCount');"; 
                     return Html::textInput( 
                          $id, 
                          $model['wareCount'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
            
            
            [
                'attribute' => 'wareEd',
                'label'     => 'Ед.изм',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "wareEd".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareEd');"; 
                     return Html::textInput( 
                          $id, 
                          $model['wareEd'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
            
            [
                'attribute' => 'warePrice',
                'label'     => 'Цена',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "warePrice".$model['id'];
                    $action =  "saveField(".$model['id'].", 'warePrice');"; 
                     return Html::textInput( 
                          $id, 
                          $model['warePrice'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => '-',
                'label'     => 'Сумма',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id= 'wareSum'.$model['id'];
                    $sum = $model['warePrice']*$model['wareCount'];
                    return \yii\helpers\Html::tag( 'div', $sum , 
                   [
                     'id'      => $id,
                     'style'   => 'padding:6px;'
                   ]);
                },
            ],        

            [
                'attribute' => 'isActive',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;width:75px;',  'align' => 'center'],
                'value' => function ($model, $key, $index, $column) {
                    
                    if ($model['isActive'] == 1){
                        $style = 'color:Green';
                        $class = 'glyphicon glyphicon-ok';
                    } else {
                        $style = 'color:Grey';
                        $class = 'glyphicon glyphicon-remove';
                    }
                    
                    $action="switchField(".$model['id'].", 'isActive');";  
                    $id= 'isActive'.$model['id'];
                    return \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable '.$class,
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'margin-top:5px;'.$style,
                     'title'   => 'активность',                
                   ]);
                },                
            ],        
            
            
           
        ],
     ]
     );



    $contentWare.="<div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>
    ";
    
    
    $id = 'btnAddNewWare';
    $action = 'addNewWare()';
    $contentWare.=\yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:12px;',
                     'title'   => 'Добавить произвольный',                
                   ]);                   
    $contentWare.="&nbsp;&nbsp;";                       
        
    
    $contentWare.="</div>";
                   

                   

                   
                   
    $contentWare.="    
     </div>   
  </div>
   ";

         
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Содержание акта: ▼",
            'content' => $contentWare,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 

  ?>
 

<?php
 $action = "saveNote();";
 $contentWare =  Html::textArea( 
                          $id, 
                          $model->actNote,                                
                              [
                              'class' => 'form-control',
                              'style' => ' font-size:11px;padding:1px;', 
                              'id' => 'actNote', 
                               'rows' => 5, 
                               'cols' => 35,
                               'onchange' => $action,
                              ]);

 
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Комментарий к акту: ▼",
            'content' => $contentWare,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 
?>
   
  
  </td>
  
  
  
</tr>
</table>  
  
  
<hr>

<!--- Контакт финиш--->    

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r ($model->debug);
    echo "</pre>";
}

?>  


   
<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=market/save-act-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

