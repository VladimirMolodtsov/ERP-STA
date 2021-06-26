<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\tabs\TabsX;

$this->title = 'Параметр контроля';
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/fin-control-cfg.js');
$requestId=$model->id;
?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />

<style>
.chkDiv{
  color:blue;      
}
.chkDiv:hover{
  text-decoration: underline;
  cursor: pointer;
}

</style>
  
<script>
function addNewBuhRow()
{  
     openSwitchWin('/managment/fin/add-buh-row&rowRef=<?= $model->id ?>');     
}

var curId=0;

function showEditBox(boxId)
{

 closeEditBox(curId);
 curId = boxId;
 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
 
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
    document.getElementById(editId).focus();  
    $(editId).focus();    
}

function closeEditBox(boxId)
{
if (boxId == "0") {return;}

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    

}
function acceptDtEdit(id)
{
 boxId = 'dt'+id;
 editId = 'edit_'+boxId;
 accdt = document.getElementById(editId).value;
 openSwitchWin('/managment/fin/set-dt&id='+id+'&accdt='+accdt);
}

function acceptKtEdit(id)
{
 boxId = 'kt'+id;
 editId = 'edit_'+boxId;
 var acckt = document.getElementById(editId).value;
 openSwitchWin('/managment/fin/set-kt&id='+id+'&acckt='+acckt);
}

function acceptNoteEdit(id)
{
 boxId = 'note'+id;
 editId = 'edit_'+boxId;
 var note = document.getElementById(editId).value;
 openSwitchWin('/managment/fin/set-note&id='+id+'&note='+note);
}

function chngDiv(id,val)
{
    alert(val);
}

function setDiv(id,div)
{
 
 openSwitchWin('/managment/fin/set-div&id='+id+'&div='+div);
}


</script>



<?php Pjax::begin(['id' => 'formEdit']);  
$form = ActiveForm::begin(); ?>
<div class='row'>
 <div class ='col-md-3'></div> 
 <div class ='col-md-4'><?= $form->field($model, 'rowTitle')->label(false) ?></div>
 <div class ='col-md-5'><?=  Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>  </div>
</div>
<div class='spacer'></div>

<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<?php ActiveForm::end(); Pjax::end(); ?>

<div class='spacer'></div>

<?php


$content[0] = GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'accdt',
                'label' => 'Счет дебет',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:155px;'],

                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['accdt'];

                $id = "dt".$model['id'];
                $cl="";
                $ret ="<div id='viewBox_".$id."' style='width:150px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:150px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:100px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptDtEdit('".$model['id']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
                
            ],
                            
            [
                'attribute' => 'acckt',
                'label' => 'Счет кредит',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:155px;'],

                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['acckt'];

                $id = "kt".$model['id'];
                $cl="";
                $ret ="<div id='viewBox_".$id."' style='width:150px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:150px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:100px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptKtEdit('".$model['id']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
 
                
            ],

            [
                'attribute' => 'div',
                'label' => '+/-',
                'contentOptions'   =>   ['style' => 'padding:2px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
     
                 $val=$model['div'];

                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#dataUse' onclick='setDiv(".$model['id'].",-1)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#dataUse' onclick='setDiv(".$model['id'].", 0)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#dataUse' onclick='setDiv(".$model['id'].", 1)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                               
                return  "<div align='center'>".$out."</div>";
                },               
            ],

            [
                'attribute' => 'note',
                'label' => 'Примечание',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],
                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['note'];

                $id = "note".$model['id'];
                $cl="";
                $ret ="<div id='viewBox_".$id."' style='width:100%; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100%;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:90%;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptNoteEdit('".$model['id']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
            ],
            
        ],
    ]
);


$content[0] .= "<div class ='row'>";
$content[0] .= "  <div class ='col-md-10'></div>";
$content[0] .= "  <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewBuhRow();'><span class='glyphicon glyphicon-plus'></span></a></div> ";  
$content[0] .= "</div>";

?>
<div class='spacer'></div>
<hr>

<?php

$content[1] = GridView::widget(
    [
        'dataProvider' => $buhProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
        
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
        
            [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
            ],

            [
                'attribute' => 'v5',
                'label' => 'Предыдущее значение',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {     
                    $val = number_format($model['v5'],'2','.','&nbsp;');
                    return "<div align='center'>".$val."</div>";
                },                
            ],
            [
                'attribute' => '-',
                'label' => 'Использовать предыдущее',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($requestId)  {
     
                 $list = Yii::$app->db->createCommand("Select id, rowRef, statRef, mult, isPrev FROM {{%fin_check_ut_cfg}} 
                 where rowRef=:rowRef AND isPrev = 1 AND statRef=:statRef LIMIT 1",
                 [
                 ':rowRef' => $requestId,
                 ':statRef'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
                 $out = "";
                 $id = 'utDivPrev'.$model['idx'].'_-1';
                 if ( $val == -1 ) { $style = 'color:White;background-color:Crimson'; $setVal = -1;  }                 
                             else  { $style = 'color:Blue;background-color:White';    $setVal = -1; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].",-1,1)";             
                 $out .=  Html::tag('span', 
                                        "-1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class'   => 'chkDiv',
                                        'style'   => $style, 
                                        'id'      => $id
                                        ]); 

                 $id = 'utDivPrev'.$model['idx'].'_0';                       
                 if ( $val == 0 )  { $style = 'color:Black;background-color:White'; $setVal = 0;  }                 
                             else  { $style = 'color:Blue;background-color:White'; $setVal = 0; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].", 0,1)";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;0&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style, 
                                        'id'      => $id                                        
                                        ]); 
               
                 $id = 'utDivPrev'.$model['idx'].'_+1';                       
                 if ( $val == 1 )  { $style = 'color:White;background-color:Green'; $setVal = 1;  }                 
                             else  { $style = 'color:Blue;background-color:White';  $setVal = 1; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].", 1,1)";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;+1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style,
                                        'id'      => $id                                        
                                        ]); 
                               
                return  "<div align='center'>".$out."</div>";
                },
                
                
            ],
            
                       

                                   
            [
                'attribute' => 'v6',
                'label' => 'Текущее значение',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {     
                    $val = number_format($model['v6'],'2','.','&nbsp;');
                    return "<div align='center'>".$val."</div>";
                },                
            ],
                       

            [
                'attribute' => '-',
                'label' => 'Использовать текущее ',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($requestId) {
     
     
                 $list = Yii::$app->db->createCommand("Select id, rowRef, statRef, mult, isPrev FROM {{%fin_check_ut_cfg}} 
                 where rowRef=:rowRef AND isPrev = 0 AND statRef=:statRef LIMIT 1",
                 [
                 ':rowRef' => $requestId,
                 ':statRef'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
             
                 $out = "";
                 $id = 'utDivCurrent'.$model['idx'].'_-1';
                 if ( $val == -1 ) { $style = 'color:White;background-color:Crimson'; $setVal = -1;  }                 
                             else  { $style = 'color:Blue;background-color:White';    $setVal = -1; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].",-1,0)";             
                 $out .=  Html::tag('span', 
                                        "-1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class'   => 'chkDiv',
                                        'style'   => $style, 
                                        'id'      => $id
                                        ]); 

                 $id = 'utDivCurrent'.$model['idx'].'_0';                       
                 if ( $val == 0 )  { $style = 'color:Black;background-color:White'; $setVal = 0;  }                 
                             else  { $style = 'color:Blue;background-color:White'; $setVal = 0; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].", 0,0)";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;0&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style, 
                                        'id'      => $id                                        
                                        ]); 
               
                 $id = 'utDivCurrent'.$model['idx'].'_+1';                       
                 if ( $val == 1 )  { $style = 'color:White;background-color:Green'; $setVal = 1;  }                 
                             else  { $style = 'color:Blue;background-color:White';  $setVal = 1; }                 
                 $action = "setDataUse(".$requestId.",".$model['idx'].", 1,0)";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;+1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style,
                                        'id'      => $id                                        
                                        ]); 
                               
                return  "<div align='center'>".$out."</div>";
                },
            ],
            
            
                                   
        ],
    ]
);

/*******************************/

$content['docCfgForm'] =  "<div class='row'>";
$content['docCfgForm'] .= "<div class ='col-md-2'>";
$content['docCfgForm'] .= "Тип данных";
$content['docCfgForm'] .= Html::radioList('docTypeControl', 
                          $model->docType, 
                          [
                           '0' => 'Ручной ввод',
                           '1' => 'Из выписки',
                           '2' => 'Из документов'
                          ],
                           [
                            'style' => 'width:130px;',
                            'onClick' => 'saveDocCfgType('.$model->id.')',
                            'id' =>'docTypeControl'
                           ]                          
                          );
$content['docCfgForm'] .=  "<p> Данные приведены на дату:".date("d.m.Y", $controlTime)."</p>";                          
$content['docCfgForm'] .=  "</div>";
    
$content['docCfgForm'] .= "<div class ='col-md-10'>";

//$controlTime = $this->controlTime;
$content['docCfgForm'] .= GridView::widget(
    [
        'dataProvider' => $docProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*    'panel' => [
        'type'=>'success',
    ],        */
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

                
        'columns' => [       
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
        
            [
                'attribute' => 'typeTitle',
                'label' => 'Статус',
                'format' => 'raw',
 //               'contentOptions'   =>   ['style' => 'padding:2px; width:155px;'],
                
            ],
                            
            [
                'attribute' => 'operationTitle',
                'label' => 'Операция',
                'format' => 'raw',
 //               'contentOptions'   =>   ['style' => 'padding:2px; width:155px;'],
          ],

            [
                'attribute' => 'div',
                'label' => '+/-',
  //              'contentOptions'   =>   ['style' => 'padding:2px; width:100px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($requestId)  {
     
                 $val=0;//$model['div'];
                 
                 $val = Yii::$app->db->createCommand("Select mult FROM {{%fin_check_doc_cfg}} 
                 where refOperation=:refOperation AND refRowReport=:refRowReport LIMIT 1",
                 [
                 ':refOperation'   => $model['refOperation'],
                 ':refRowReport'   => $requestId,
                 ])->queryScalar();
                 
                 if(empty($val))$val = 0;
                 
                 
                 $out = "";
                 $id = 'docCfgForm'.$model['refOperation'].'_-1';
                 if ( $val == -1 ) { $style = 'color:White;background-color:Crimson'; $setVal = -1;  }                 
                             else  { $style = 'color:Blue;background-color:White';    $setVal = -1; }                 
                 $action = "chngDiv(".$requestId.",".$model['refOperation'].",".$setVal.")";             
                 $out .=  Html::tag('span', 
                                        "-1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class'   => 'chkDiv',
                                        'style'   => $style, 
                                        'id'      => $id
                                        ]); 

                 $id = 'docCfgForm'.$model['refOperation'].'_0';                       
                 if ( $val == 0 )  { $style = 'color:Black;background-color:White'; $setVal = 0;  }                 
                             else  { $style = 'color:Blue;background-color:White'; $setVal = 0; }                 
                 $action = "chngDiv(".$requestId.",".$model['refOperation'].",".$setVal.")";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;0&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style, 
                                        'id'      => $id                                        
                                        ]); 
               
                 $id = 'docCfgForm'.$model['refOperation'].'_+1';                       
                 if ( $val == 1 )  { $style = 'color:White;background-color:Green'; $setVal = 1;  }                 
                             else  { $style = 'color:Blue;background-color:White';  $setVal = 1; }                 
                 $action = "chngDiv(".$requestId.",".$model['refOperation'].",".$setVal.")";             
                 $out .=  Html::tag('span', 
                                        "&nbsp;+1&nbsp;", 
                                        [
                                        'onclick' => $action,
                                        'class' => 'chkDiv',
                                        'style' => $style,
                                        'id'      => $id                                        
                                        ]); 
                               
                return  "<div align='center'>".$out."</div>";
                },               
            ],
            
            
            [
                'attribute' => '-',
                'label' => 'По документам',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($controlTime)  {
                     
               $strSql = "Select sum(docSum*mult) from {{%documents}}, {{%fin_check_doc_cfg}} 
                    where  {{%documents}}.operationType = {{%fin_check_doc_cfg}}.refOperation
                    and {{%fin_check_doc_cfg}}.refRowReport = :refRowReport
                    and {{%documents}}.docOrigDate  = :docDate";
      
      
               $val=  Yii::$app->db->createCommand(
                $strSql, [ 
                    ':refRowReport' => $model['refOperation'],
                    ':docDate' => date("Y-m-d", $controlTime),
                ])->queryScalar(); 
                
                if (empty($val))$val=0;
                
                return number_format($val,2,".","&nbsp;");
                 
                },               
            ],

            [
                'attribute' => '-',
                'label' => 'Из выписки',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($controlTime)  {
                     
               $strSql = "Select sum(debetSum*mult+creditSum*mult) from {{%bank_extract}}, {{%fin_check_doc_cfg}} 
                    where  {{%bank_extract}}.operationType = {{%fin_check_doc_cfg}}.refOperation
                    and {{%fin_check_doc_cfg}}.refOperation = :refRowReport
                    and DATE({{%bank_extract}}.recordDate)  = :docDate";
      
      
               $val=  Yii::$app->db->createCommand(
                $strSql, [ 
                    ':refRowReport' => $model['refOperation'],
                    ':docDate' => date("Y-m-d", $controlTime),
                ])->queryScalar(); 
                
                if (empty($val))$val=0;
                
                return number_format($val,2,".","&nbsp;");
                 
                },               
            ],
                        
   
                        
        ],
    ]
);
$content['docCfgForm'] .="</div>";
$content['docCfgForm'] .=  "</div>";

//$content['docCfgForm'] .=  "<div align='center'>". Html::submitButton('Сохранить', ['class' => 'btn btn-primary'])." </div>";


/*******************************/
$items = [

        [
            'label'=>'<i class="fas fa-home"></i> По документам',
            'content'=>$content['docCfgForm'],
            'active'=>false
        ],
        
        [
            'label'=>'<i class="fas fa-home"></i> Бух (авто)',
            'content'=>$content[0],
            'active'=>true
        ],
        
        [
            'label'=>'<i class="fas fa-home"></i> УТ (авто)',
            'content'=>$content[1],
            'active'=>false
        ],
        
/*        [
            'label'=>'<i class="fas fa-user"></i> Profile',
            'content'=>$content2,
            'linkOptions'=>['data-url'=>\yii\helpers\Url::to(['/site/tabs-data'])]
        ],
*/        
    ];


// Above
echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'bordered'=>true,
    'encodeLabels'=>false
]);


?>
<br>

<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/fin/save-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//  echo "<input type='submit'>";
ActiveForm::end(); 
?>
