<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
//use kartik\grid\EditableColumn;
use yii\bootstrap\Collapse;

use yii\helpers\Url;

$this->title = 'Бухгалтерская статистика';
$this->params['breadcrumbs'][] = $this->title;


$now =$model->timeList[5];
$prev=$now-24*3600;
$next=$now+24*3600;

$btnclass = [];

$N= count ($model->checkedList);
for ($i=0;$i< $N; $i++)
{

$btnlbl1  [$i]="Проверить";
$btnlbl2  [$i]="Завершить";        

if ($model->checkedList[$i] == 1) 
{    
    $btnhead [$i]="btn btn-success";
    $btnfoot1 [$i]="btn ";
    $btnfoot2 [$i]="btn ";
    if ($model->finishedList[$i] == 1){
        $model->checkedList[$i] = 2;        
        $btnfoot1 [$i]="btn btn-success";
        $btnfoot2 [$i]="btn ";
        $btnlbl1  [$i]="Проверено";
 
    }        
       if ($model->finishedList[$i] == 2){
        $model->checkedList[$i] = 2;
        $btnlbl1  [$i]="Проверено"; 
        $btnlbl2  [$i]="Завершено";    
        $btnfoot1 [$i]="btn btn-success";
        $btnfoot2 [$i]="btn btn-success";
    }        
 
}    
   else
 {       
    $btnhead [$i]="btn";
    $btnfoot1 [$i]="btn btn-hide";
    $btnfoot2 [$i]="btn btn-hide";
 }
 
 

}    

$chkList=$model->checkedList;
$timeList =$model->timeList;
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.table-local {    
  font-size: 12px;
}

.gridcell {
    width: 120px;        
    height: 17px;
    display: block;
    font-size: 12px;    
    text-align: center;
    word-wrap: break-word;
    /*background:DarkSlateGrey;*/
}    
.gridcell:hover{
    background:Silver;
    cursor: pointer;
    color:#FFFFFF;
}
.editcell{
   width: 17px;
   display:none;
   white-space: nowrap;
   background:White;
}


.viewcell{
    width: 100px;        
    height: 17px;
    display: block;
    font-size: 12px;    
    text-align: right;
    word-wrap: break-word;
}

.refcell{
    width: 100px;        
    height: 17px;
    display: block;
    font-size: 12px;    
    text-align: right;
    word-wrap: break-word;
    color:blue;
}
.refcell:hover
{
   cursor: pointer;
   text-decoration: underline;
}

 .chkview{
     text-align: right;
 }

.chkview:hover
{
   cursor: pointer;
   text-decoration: underline;
}

  
.btn-hide{
   display:none;
}    
</style>


<script type="text/javascript">

var curId=0;

function showCalc(col,idx)
{
   openWin('bank/buh/show-calc&dtstart=<?=$model->dtstart?>&col='+col+'&idx='+idx,'info1'); 
}

function acceptEdit(col, order)
{    
    boxId = 'v'+col+order;
    
    editId = 'edit_'+boxId;   
    val= document.getElementById(editId).value;

    openSwitchWin('bank/buh/set-statistics&dtstart=<?=$model->dtstart?>&col='+col+'&order='+order+'&val='+val); 
    closeEditBox(boxId);
}

function acceptEventTitleEdit(timeStart,idx)
{

    boxId = 'title_'+idx;    
    editId = 'edit_'+boxId;   
    val= document.getElementById(editId).value;

    openSwitchWin('bank/buh/set-event-title&timestart='+timeStart+'&val='+val); 
    closeEditBox(boxId);

}

function acceptEventNoteEdit(timeStart,idx)
{

    boxId = 'note_'+idx;    
    editId = 'edit_'+boxId;   
    val= document.getElementById(editId).value;

    openSwitchWin('bank/buh/set-event-note&timestart='+timeStart+'&val='+val); 
    closeEditBox(boxId);

}


function setCheck(col)
{    
    openSwitchWin('bank/buh/set-checked&dtstart=<?=$model->dtstart?>&col='+col); 
}

function setFinish(col)
{    
    openSwitchWin('bank/buh/set-finished&dtstart=<?=$model->dtstart?>&col='+col); 
}

function startCheck(col)
{    
    
    openWin('bank/buh/start-check&dtstart=<?=$model->dtstart?>&col='+col, 'syncWin'); 
}

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





function showRef(ref, dt)
{
    openWin(ref+dt,'finWin');
}
</script>

  <h2><?php echo Html::encode($this->title); ?></h2>

<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=bank/buh/buh-statistics&dtstart=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-10' style='text-align:center'><h4><?= date("d.F.Y", $now) ?></h4></div>
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=bank/buh/buh-statistics&dtstart=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
</div>
<br>
<?php
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        'showFooter' => true,
    'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'before'=>Html::a('<i class="glyphicon glyphicon-plus"></i> Create Country', ['create'], ['class' => 'btn btn-success']),
        //'after'=>Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),
        'footer'=>false
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'attribute' => 'execTime',
                'label' => 'Время',
                'format' => 'raw',
            ],

            [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
            ],
       
            [
                'attribute' => 'v1',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                //'label' => ,
                'header' => '<div align="center"><input type="button" onclick="setCheck(1);"  class="'.$btnhead[0].'" value="'.$model->dateList[0].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(1);" class="'.$btnfoot1[0].'" value="'.$btnlbl1[0].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(1);"  class="'.$btnfoot2[0].'" value="'.$btnlbl2[0].'"></div>      ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v1'],'2','.','&nbsp;');

                if ($chkList[0]==0) return "&nbsp;";
                
                $col='1';
                $id = "v1".$model['idx'];
                $cl="";
                if ($model['s1'] == false) {$cl='color:Gray;'; /*$val = "&nbsp;";*/}           
                                 else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[0]==2)
                {                    
                    $chk = number_format($model['c1'],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                return  $ret;
                },
                
                
            ],
                       
            [
                'attribute' => 'v2',
                'header' => '<div align="center"><input type="button" onclick="setCheck(2);"  class="'.$btnhead[1].'" value="'.$model->dateList[1].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(2);" class="'.$btnfoot1[1].'" value="'.$btnlbl1[1].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(2);"  class="'.$btnfoot2[1].'" value="'.$btnlbl2[1].'"></div>      ',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v2'],'2','.','&nbsp;');
                if ($chkList[1]==0) return "&nbsp;";
                
                $col='2';
                $id = "v2".$model['idx'];    
                if ($model['s2'] == false) {$cl='color:Gray;'; /*$val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[1]==2)
                {                    
                    $chk = number_format($model['c2'],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                
                return  $ret;
                },
                
            ],

            [
                'attribute' => 'v3',
                'format' => 'raw',                
                'header' => '<div align="center"><input type="button" onclick="setCheck(3);"  class="'.$btnhead[2].'" value="'.$model->dateList[2].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(3);" class="'.$btnfoot1[2].'" value="'.$btnlbl1[2].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(3);"  class="'.$btnfoot2[2].'" value="'.$btnlbl2[2].'"></div>      ',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v3'],'2','.','&nbsp;');
                if ($chkList[2]==0) return "&nbsp;";
                $col='3';
                $id = "v3".$model['idx'];    
                if ($model['s3'] == false) {$cl='color:Gray;';/* $val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[2]==2 )
                {                    
                    $chk = number_format($model['c3'],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
               
                return  $ret;
                },
                
            ],

            [
                'attribute' => 'v4',
                'format' => 'raw',                
                'header' => '<div align="center"><input type="button" onclick="setCheck(4);"  class="'.$btnhead[3].'" value="'.$model->dateList[3].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(4);" class="'.$btnfoot1[3].'" value="'.$btnlbl1[3].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(4);"  class="'.$btnfoot2[3].'" value="'.$btnlbl2[3].'"></div>      ',

                'contentOptions'   =>   ['style' => 'padding:2px'],
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v4'],'2','.','&nbsp;');
                if ($chkList[3]==0) return "&nbsp;";                
                $col='4';
                $id = "v4".$model['idx'];    
                if ($model['s4'] == false)  {$cl='color:Gray;'; /*$val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[3]==2)
                {                    
                    $chk = number_format($model['c4'],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                
                return  $ret;
                },
                
            ],
            
            [
                'attribute' => 'v5',
                'format' => 'raw',                
                'header' => '<div align="center"><input type="button" onclick="setCheck(5);"  class="'.$btnhead[4].'" value="'.$model->dateList[4].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(5);" class="'.$btnfoot1[4].'" value="'.$btnlbl1[4].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(5);"  class="'.$btnfoot2[4].'" value="'.$btnlbl2[4].'"></div>      ',
                'contentOptions'   =>   ['style' => 'padding:2px; '],
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v5'],'2','.','&nbsp;');
                if ($chkList[4]==0) return "&nbsp;";                
                $col='5';
                $id = "v5".$model['idx'];    
                if ($model['s5'] == false)  {$cl='color:Gray;'; /*$val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[4]==2 )
                {                    
                    $chk = number_format($model['c5'],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                
                return  $ret;
                },
                
            ],

            [
                'attribute' => 'v6',
                'format' => 'raw',                
                'header' => '<div align="center"><input type="button" onclick="setCheck(6);"  class="'.$btnhead[5].'" value="'.$model->dateList[5].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(6);" class="'.$btnfoot1[5].'" value="'.$btnlbl1[5].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(6);"  class="'.$btnfoot2[5].'" value="'.$btnlbl2[5].'"></div>      ',
                'contentOptions'   =>   ['style' => 'padding:2px;background-color:LemonChiffon;'],
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v6'],'2','.','&nbsp;');
                if ($chkList[5]==0) return "&nbsp;";                
                $col='6';
                $id = "v6".$model['idx'];    
                if ($model['s6'] == false) {$cl='color:Gray;'; /*$val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[5]==2)
                {                    
                    $chk = number_format($model['c6'],'2','.','&nbsp;');
                    if (empty($model['ref'])) $ret.="";// $ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                
                return  $ret;
                },
                
            ],
            
            [
                'attribute' => 'v7',
                'format' => 'raw',                
                'header' => '<div align="center"><input type="button" onclick="setCheck(7);"  class="'.$btnhead[6].'" value="'.$model->dateList[6].'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startCheck(7);" class="'.$btnfoot1[6].'" value="'.$btnlbl1[6].'"></div>
                             <br>                
                             <div align="center"><input type="button" onclick="setFinish(7);"  class="'.$btnfoot2[6].'" value="'.$btnlbl2[6].'"></div>      ',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList) {
     
                $val = number_format($model['v7'],'2','.','&nbsp;');
                if ($chkList[6]==0) return "&nbsp;";                
                $col='7';
                $id = "v7".$model['idx'];    
                if ($model['s7'] == false) {$cl='color:Gray;'; /*$val = "&nbsp;";*/} 
                else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                if ($chkList[6]==2)
                {                    
                    $chk = number_format($model['c7'],'2','.','&nbsp;');
                    if (empty($model['ref'])) $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col-1])."\");'> ".$chk. "</div>"; 
                    
                }     
                
                return  $ret;
                },
                
            ],

            
        ],
    ]
);
?>

<?php

$content = GridView::widget(
    [
        'dataProvider' => $controlprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'before'=>Html::a('<i class="glyphicon glyphicon-plus"></i> Create Country', ['create'], ['class' => 'btn btn-success']),
        //'after'=>Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),
        'footer'=>false
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                return $model['idx']." ".$model['titleTask'];
                }
            ],
       
            [
                'attribute' => 'v1',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'label' => $model->dateList[0],
                //'header' =>  '<div align="center"><input type="button" onclick="setCheck(1);" class="'.$btnclass[0].'" value="'.$model->dateList[0].'"></div>',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[0] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v1']> 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc(1,".$model['idx'].");'>".number_format($model['v1'],'2','.','&nbsp;')."</div>";
                   
                }
                
            ],
                       
            [
                'attribute' => 'v2',
                'label' => $model->dateList[1],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(2);" class="'.$btnclass[1].'" value="'.$model->dateList[1].'"></div>',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[1] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v2'] > 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc(2,".$model['idx'].");'>".number_format($model['v2'],'2','.','&nbsp;')."</div>";
                }
                
            ],

            [
                'attribute' => 'v3',
                'label' => $model->dateList[2],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(3);" class="'.$btnclass[2].'" value="'.$model->dateList[2].'"></div>',                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format'=>'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[2] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v3']> 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc(3,".$model['idx'].");'>".number_format($model['v3'],'2','.','&nbsp;')."</div>";

                }

            ],

            [
                'attribute' => 'v4',
                'label' => $model->dateList[3],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(4);" class="'.$btnclass[3].'" value="'.$model->dateList[3].'"></div>',                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format'=>'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[3] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v4']> 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc(4,".$model['idx'].");'>".number_format($model['v4'],'2','.','&nbsp;')."</div>";

                }
 
            ],
            
            [
                'attribute' => 'v5',
                'label' => $model->dateList[4],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(5);" class="'.$btnclass[4].'" value="'.$model->dateList[4].'"></div>',                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format'=>'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[4] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v5']> 0.1)) $c="color:Crimson;"; 
                   return "<div  class='chkview' style='$c' onclick='showCalc(5,".$model['idx'].");'>".number_format($model['v5'],'2','.','&nbsp;')."</div>";
                }

            ],

            [
                'attribute' => 'v6',
                'label' => $model->dateList[5],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(6);" class="'.$btnclass[5].'" value="'.$model->dateList[5].'"></div>',                'format' => 'raw',                
                'contentOptions'   =>   ['style' => 'padding:2px; background-color:LemonChiffon;'],                
                'format'=>'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[5] == 0 ) return "&nbsp;";
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v6']> 0.1)) $c="color:Crimson;" ;
                   return "<div  class='chkview' style='$c' onclick='showCalc(6,".$model['idx'].");'>".number_format($model['v6'],'2','.','&nbsp;')."</div>";

                }
                
            ],
            
            [
                'attribute' => 'v7',
                'label' => $model->dateList[6],
                 //'header' =>  '<div align="center"><input type="button" onclick="setCheck(7);" class="'.$btnclass[6].'" value="'.$model->dateList[6].'"></div>',                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format'=>'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                   if($chkList[6] == 0 ) return "&nbsp;";
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model['v7']> 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc(7,".$model['idx'].");'>".number_format($model['v7'],'2','.','&nbsp;')."</div>";

                }

                
            ],

            
        ],
    ]
);

"";

 echo Collapse::widget([
    'items' => [
        [
            'label' => "Значения проверки:",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 


?>
<hr>
<h4><a name="eventList"></a>Задачи на <?= date("d F Y", $now) ?></h4>
<?php
echo GridView::widget(
    [
        'dataProvider' => $eventprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'before'=>Html::a('<i class="glyphicon glyphicon-plus"></i> Create Country', ['create'], ['class' => 'btn btn-success']),
        //'after'=>Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),
        'footer'=>false
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'strStart',
                'label' => 'от',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'width:75px;'],                
            ],
       
            [
                'attribute' => 'strEnd',
                'label' => 'до',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'width:75px;'],                
            ],

            [
                'attribute' => 'eventTitleЗаголовок',
                'label' => 'Заголовок',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'width:190px;'],                
                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['eventTitle'];
                $valEdit ="";
                $id = "title_".$model['idx'];                                                             
                $ret ="<div id='viewBox_".$id."' style='width:180px; text-align:right;'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>";                 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:175px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:170px;' value='".$valEdit."'>";
                $ret.="<a href ='#eventList' onclick=\"javascript:acceptEventTitleEdit(".$model['timeStart'].",'".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#eventList' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
            ],

            [
                'attribute' => 'eventNote',
                'label' => 'Исполнение',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['eventNote'];
                $valEdit ="";
                $id = "note_".$model['idx'];                                                             
                $ret ="<div id='viewBox_".$id."' style='width:100%; text-align:right;'   class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>";                 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100%;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:90%;' value='".$valEdit."'>";
                $ret.="<a href ='#eventList' onclick=\"javascript:acceptEventNoteEdit(".$model['timeStart'].",'".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#eventList' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
                                
            ],
            
                        
        ],
    ]
);
?>




<pre>
<?php 
//print_r($model->debug)
?>
</pre>
