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



<?php Pjax::begin(['id' => 'formEdit']);  $form = ActiveForm::begin(); ?>
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

$idx=$model->id;
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
                'value' => function ($model, $key, $index, $column) use($idx)  {
     
                 $list = Yii::$app->db->createCommand("Select id, rowRef, statRef, mult, isPrev FROM {{%fin_check_ut_cfg}} 
                 where rowRef=:rowRef AND isPrev = 1 AND statRef=:statRef LIMIT 1",
                 [
                 ':rowRef' => $idx,
                 ':statRef'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].",-1,1)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 0,1)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 1,1)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                 
                 
                               
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
                'value' => function ($model, $key, $index, $column) use($idx) {
     
     
                 $list = Yii::$app->db->createCommand("Select id, rowRef, statRef, mult, isPrev FROM {{%fin_check_ut_cfg}} 
                 where rowRef=:rowRef AND isPrev = 0 AND statRef=:statRef LIMIT 1",
                 [
                 ':rowRef' => $idx,
                 ':statRef'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].",-1,0)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 0,0)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 1,0)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                 
                               
                return  "<div align='center'>".$out."</div>";
                },
            ],
            
            
                                   
        ],
    ]
);



$items = [
        [
            'label'=>'<i class="fas fa-home"></i> УТ (авто)',
            'content'=>$content[0],
            'active'=>true
        ],
        
        [
            'label'=>'<i class="fas fa-home"></i> Бух (авто)',
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

