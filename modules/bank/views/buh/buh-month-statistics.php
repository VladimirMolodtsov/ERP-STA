<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use yii\bootstrap\Collapse;

use yii\helpers\Url;

$this->title = 'Бухгалтерская статистика - помесячная';
$this->params['breadcrumbs'][] = $this->title;


$now =$model->timeList[6];
$prev=$now-24*3600;
$next=$now+24*3600;

$btnclass = [];

$N= count ($model->checkedList);
for ($i=0;$i< $N; $i++)
{

if ($model->checkedList[$i] == 1) 
{    
    $btnhead [$i]="btn btn-success";
    $btnfoot [$i]="btn btn-success";
    if ($model->syncedList[$i] == 0){
        $btnfoot [$i]="btn btn-warning"; 
    }        
 
}    
else
 {       
    $btnhead [$i]="btn";
    $btnfoot [$i]="btn btn-hide";
 }
 
 

}    

$chkList=$model->checkedList;
$timeList =$model->timeList;
$manual = $model->manual;



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
   openWin('bank/buh/show-month-calc&dtstart=<?=$model->dtstart?>&col='+col+'&idx='+idx,'info1'); 
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

/*Начинаем работу с колонкой*/
function setCheck(col)
{    
    openSwitchWin('bank/buh/set-month-checked&dtstart=<?=$model->dtstart?>&col='+col); 
}

/*Запускаем синхронизацию и подгрузку данных*/
function startSync(col)
{    
    openSwitchWin('bank/buh/start-month-sync&dtstart=<?=$model->dtstart?>&col='+col);     
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

function changeShowDate()
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=bank/buh/buh-month-statistics&dtstart='+showDate ;
}

function dayStat() 
{

document.location.href='index.php?r=bank/buh/buh-statistics';
}
</script>

  <h2><?php echo Html::encode($this->title); ?>  <div class='btn btn-default' style='margin-left:250px;' onclick='dayStat();'>Ежедневная </div> </h2>


<br>
<?php

/*$columns[]= [
                'attribute' => 'execTime',
                'label' => 'Время',
                'format' => 'raw',
            ];*/

$columns[]= [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
            ];


for ($i=1;$i<=12; $i++){
    
    
$columns[]=[
                'attribute' => 'v'.$i,
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'header' => '<div align="center"><input type="button" onclick="setCheck('.$i.');"  class="'.$btnhead[$i].'"
                value="'.date('M',$model->timeList[$i]).'"></div>',                
                'footer' => '<div align="center"><input type="button" onclick="startSync('.$i.');" class="'.$btnfoot[$i].'"
                value="Проверить"></div> ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList,$timeList, $manual, $i) {
                $col=$i;
                $v = 'v'.$col;
                $c = 'c'.$col;
                $s = 's'.$col;
                $val = number_format($model[$v],'2','.','&nbsp;');
                if ($chkList[$col]==0) return "&nbsp;";
                
                $id = $v.$model['idx'];
                $cl="";
                $ret="";
                if ($manual == 1){
                if ($model[$s] == false) {$cl='color:Gray;'; /*$val = "&nbsp;";*/}           
                                   else  {$cl='font-weight:bold;';}            
                $ret ="<div id='viewBox_".$id."' style='width:100px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value=''>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$col."','".$model['idx']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                }
                if ($chkList[$col]==1)
                {                    
                    $chk = number_format($model[$c],'2','.','&nbsp;');
                    if (empty($model['ref']))  $ret.="";//$ret.="<div class='viewcell'>".$chk. "</div>"; 
                    else $ret.="<div class='refcell' onclick='showRef(\"".$model['ref']."\",\"".date('Y-m-d',$timeList[$col])."\");'> ".$chk. "</div>"; 
                    
                }     
                return  $ret;
                },
            ];
}


echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        'showFooter' => true,
    'panel' => [
        'type'=>'success',
        'footer'=>false
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => $columns,

    ]
);
?>

<?php

$check_columns[]= [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList) {
                return $model['idx']." ".$model['titleTask'];
                }
            ];



for ($i=1;$i<=12; $i++){
    
    
$check_columns[]=  [
                'attribute' => 'v'.$i,
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'label' => date('M',$model->timeList[$i]),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($chkList, $i) {
                   $k = $i; 
                   $v = 'c'.$k;
                   if($chkList[$k] == 0 ) return "&nbsp;"; 
                   $c=""; 
                   if ($model['isMarkNonZero'] == 1 && ($model[$v]> 0.1)) $c="color:Crimson;"; 
                   return "<div class='chkview' style='$c' onclick='showCalc($k,".$model['idx'].");'>".number_format($model[$v],'2','.','&nbsp;')."</div>";
                }
                
            ];

}

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

        'columns' => $check_columns,
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


<pre>
<?php 
//print_r ($columns);
//print_r($model->debug);
//print_r($model->checkedList);
//print_r($model->dateList);
?>
</pre>
