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

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>

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

function setDiv(id,div)
{
 
 openSwitchWin('/managment/fin/set-div&id='+id+'&div='+div);
}


function setDataUse(statRow,div,isPrev)
{

 openSwitchWin('/managment/fin/add-stat-row&rowRef=<?= $model->id ?>&statRow='+statRow+'&div='+div+'&isPrev='+isPrev );

}

</script>

<?php


echo  GridView::widget(
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


echo "<div class ='row'>";
echo "  <div class ='col-md-10'></div>";
echo "  <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewBuhRow();'><span class='glyphicon glyphicon-plus'></span></a></div> ";  
echo "</div>";

?>
