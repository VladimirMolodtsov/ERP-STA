<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Параметры контроля';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/monitor-row-cfg.js');

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}
</style>
  
<script>
function cfgRowProfit(rowRef)
{    
 openWin("managment/head/monitor-profit-cfg&rowRef="+rowRef,'cfgWin');      
}

function cfgRowBDDS(rowRef)
{    
 openWin("managment/head/monitor-bank-op-cfg&rowRef="+rowRef,'cfgWin');      
}

function cfgRowDolg(rowRef)
{    
 openWin("managment/head/monitor-dolgi-cfg&rowRef="+rowRef,'cfgWin');      
}


function cfgRowWare(rowRef)
{    
 openWin("managment/head/monitor-ware-cfg&rowRef="+rowRef,'cfgWin');      
}

function addNewRow  (rowId)
  {
    document.getElementById('rowType').value = rowId;  
    $('#frmEditRowDialog').modal('show'); 
  } 

function removeRow(rowRef)
{    
 openSwitchWin("managment/head/remove-row&rowRef="+rowRef);      
}


</script>

<p>Числовые значения приведены для периода <b><?= $model->stDate ?> - <?= $model->enDate?></b></p>
<?php
$m=$model; 

echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
      //  'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        */
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       
            [
                'attribute' => 'rowTitle',
                'label' => 'Параметр',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;'],

                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['rowTitle'];
                if ($model['isMark']==1)$val = "<b>".$val."</b>";

                $id = "rowTitle".$model['id'];
                $cl="";
                $ret ="<div id='viewBox_".$id."' style='width:175px; text-align:right;$cl'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$val."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:175px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:150px;' value='$val'>";
                $ret.="<a href ='#' onclick=\"javascript:acceptEdit('".$model['id']."','rowTitle'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                return  $ret;
                },
                
            ],
                          
            [
                'attribute' => 'isMark',
                'label' => "<span class='glyphicon glyphicon-bold'></span>",
                'encodeLabel' => false,
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:40px;'],

                'value' => function ($model, $key, $index, $column)  {
     
               $ret = "";
               $id = "isMark".$model['id'];                       
               $action = "switchMark(".$model['id'].", ".$model['isMark'].");";                                                                  
               $class = 'btn btn-small';
               if ($model['isMark'] == 1) $style  = 'background:Green;';
                  else                    $style  = 'background:LightGray;';
                  
               $ret = \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => $class,
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Выделить жирным',
                   ]);
       
                return "<div style='text-align:center'>".$ret."</div>";
                },
            ],
            [
                'attribute' => '-',
                'label' => 'Прибыль',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:195px;'],

                'value' => function ($model, $key, $index, $column) use($m) {
                
                $list = Yii::$app->db->createCommand( " SELECT typeTitle ,ifnull(b.mult,0 ) as mult
                from {{%monitor_val_type}} left join 
                (SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
                WHERE srcType = 7 and rowHeaderRef = ".intval($model['id']).") as b
                on b.filterRef = {{%monitor_val_type}}.id WHERE ifnull(b.mult,0 ) <> 0")->queryAll();
                $ret ="";
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] > 0) $ret .= "<div class='plus'>". $list[$i]['typeTitle']."</div>";
                  
                }                
                if ( count($list)> 0)$ret .= "<hr>";
                
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] < 0) $ret .= "<div class='minus'>".$list[$i]['typeTitle']."</div>";                  
                }                
                
                $action ='openWin("/managment/head/show-profit-by-row&noframe=1&stDate='.$m->stDate.'&enDate='.$m->enDate.'&rowRef='.$model['id'].'","detailWin");';                          
                $ret .= "<div>";                
                $ret .="<span onclick='".$action."' title='Прибыль' class='clickable'>";
                $ret .= number_format($m->calcRowProfit($model['id'],$m->rowType),2,'.',"&nbsp;");
                $ret .= "</span>\n";
                $ret .= "<span style='float:right;' class='glyphicon glyphicon-cog clickable' onclick='cfgRowProfit(".$model['id'].")' style='text-align:right'></span>";                               
                $ret .= "</div>";
                return  $ret;
                },
            ],

            [
                'attribute' => '-',
                'label' => 'ДДС',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:195px;'],

                'value' => function ($model, $key, $index, $column)  use($m)  {
     
                $list = Yii::$app->db->createCommand( " SELECT article,ifnull(b.mult,0 ) as mult
                from {{%bank_op_article}} left join 
                (SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
                WHERE srcType = 2 and rowHeaderRef = ".intval($model['id']).") as b
                on b.filterRef = {{%bank_op_article}}.id WHERE ifnull(b.mult,0 ) <> 0")->queryAll();
                $ret ="";
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] > 0) $ret .= "<div class='plus'>". $list[$i]['article']."</div>";
                }                
                if ( count($list)> 0)$ret .= "<hr>";
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] < 0) $ret .= "<div class='minus'>".$list[$i]['article']."</div>";
                }          


                
                $action ='openWin("/managment/head/show-bank-op-by-row&noframe=1&stDate='.$m->stDate.'&enDate='.$m->enDate.'&rowRef='.$model['id'].'","detailWin");';          
                $ret .= "<div>";                
                $ret .="<span onclick='".$action."' title='Прибыль' class='clickable'>";
                $ret .= number_format($m->calcRowDDS($model['id'],$m->rowType),2,'.',"&nbsp;");
                $ret .= "</span>\n";
                $ret .= "<span style='float:right;' class='glyphicon glyphicon-cog clickable' onclick='cfgRowBDDS(".$model['id'].")' style='text-align:right'></span>";                               
                $ret .= "</div>";
                
                return  $ret;
                },
            ],

            /*[
                'attribute' => '-',
                'label' => 'Долги',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:195px;'],

                'value' => function ($model, $key, $index, $column)  {
     
                $list = Yii::$app->db->createCommand( " SELECT title as orgTitle,ifnull(b.mult,0 ) as mult
                from {{%orglist}} left join 
                (SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
                WHERE srcType = 4 and rowHeaderRef = ".intval($model['id']).") as b
                on b.filterRef = {{%orglist}}.id WHERE ifnull(b.mult,0 ) <> 0")->queryAll();
                $ret ="";
                $N =count($list);
                $n=0;
                for ($i=0;$i< $N; $i++)
                {
                  if ($list[$i]['mult'] > 0)
                  {
                    if($n<2) $ret .= "<div class='plus'>". $list[$i]['orgTitle']."</div>";
                    $n++;
                  }
                }                
                if($n>2) $ret .= "еще ".($n-2)." ...";
                $n=0;
                if ( count($list)> 0)$ret .= "<hr>";
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] < 0 ){
                    if($n<2)  $ret .= "<div class='minus'>".$list[$i]['orgTitle']."</div>";
                    $n++;
                  }                  
                }                                
                if($n>2) $ret .= "еще ".($n-2)." ...";
                
                $ret .= "<div class='clickable' onclick='cfgRowDolg(".$model['id'].")' style='text-align:right'><span class='glyphicon glyphicon-cog'></span></div>";                              
                return  $ret;
                return  $ret;
                },
            ],*/

            [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:195px;'],

                'value' => function ($model, $key, $index, $column)  use($m)  {
     
                $list = Yii::$app->db->createCommand( " SELECT typeTitle,ifnull(b.mult,0 ) as mult
                from {{%control_purch_type}} left join 
                (SELECT rowHeaderRef, srcType, mult, filterRef FROM {{%monitor_row_cfg}}
                WHERE srcType = 6 and rowHeaderRef = ".intval($model['id']).") as b
                on b.filterRef = {{%control_purch_type}}.id WHERE ifnull(b.mult,0 ) <> 0")->queryAll();
                $ret ="";
                $N =count($list);
                $n=0;
                for ($i=0;$i< $N; $i++)
                {
                  if ($list[$i]['mult'] > 0)
                  {
                    if($n<2) $ret .= "<div class='plus'>". $list[$i]['typeTitle']."</div>";
                    $n++;
                  }
                }                
                if($n>2) $ret .= "еще ".($n-2)." ...";
                $n=0;
                if ( count($list)> 0)$ret .= "<hr>";
                for ($i=0;$i< count($list); $i++)
                {
                  if ($list[$i]['mult'] < 0 ){
                    if($n<2)  $ret .= "<div class='minus'>".$list[$i]['typeTitle']."</div>";
                    $n++;
                  }                  
                }                                
                if($n>2) $ret .= "еще ".($n-2)." ...";
                
                $action ='openWin("/managment/head/show-purch-by-row-ref&noframe=1&stDate='.$m->stDate.'&enDate='.$m->enDate.'&rowRef='.$model['id'].'","detailWin");'; 
                $ret .= "<div>";                
                $ret .="<span onclick='".$action."' title='Прибыль' class='clickable'>";
                $ret .= number_format($m->calcRowPurch($model['id'],$m->rowType),2,'.',"&nbsp;");
                $ret .= "</span>\n";
                $ret .= "<span style='float:right;' class='glyphicon glyphicon-cog clickable' onclick='cfgRowWare(".$model['id'].")' style='text-align:right'></span>";                               
                $ret .= "</div>";
                
                return  $ret;
                },
            ],


            [
                'attribute' => 'mult',
                'label' => '+/-',
                'contentOptions'   =>   ['style' => 'padding:2px; width:95px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
     
                 $val=$model['mult'];

                 $val=$model['mult'];                 

                 $out = "";
                 $id = 'minus'.$model['id'];
                 $action="setDiv(".$model['id'].", -1)";
                 if ( $val == -1 ) $style = "color:White;background-color:Crimson;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' -1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               $out .= "&nbsp;";
                 $id = 'zero'.$model['id'];
                 $action="setDiv(".$model['id'].",0)";
                 if ( $val == 0 )  $style = "color:Black;background-color:LightGray;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' 0 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
            $out .= "&nbsp;";
                 $id = 'plus'.$model['id'];
                 $action="setDiv(".$model['id'].",1)";
                 if ( $val == 1 )  $style = "color:White;background-color:Green;";
                              else $style = "color:Blue ;background-color:White;";
                 $out .= \yii\helpers\Html::tag( 'span', ' +1 ', 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                   ]);
               
                 return $out;        
                 
                },               
            ],

            [
                'attribute' => '-',
                'label' => '',
                'contentOptions'   =>   ['style' => 'padding:2px; width:30px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                                       
                 $action="removeRow(".$model['id'].")";                 
                 $out = \yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'glyphicon glyphicon-trash clickable',
                     //'id'      => $id,
                     'onclick' => $action,
                   ]);
               
                 return $out;        
                 
                },               
            ],

                     
        ],
    ]
);


?>

    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class='col-md-2' style='text-align:right;'><a href='#' onclick='addNewRow(<?= $model->rowType ?>);'><span class='glyphicon glyphicon-plus'></span></a></div>  
    </div>


<?php
Modal::begin([
    'id' =>'frmEditRowDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],    
]);?>

<?php $form = ActiveForm::begin(['id'=> 'frmEditRow']) ?>
  <?php
    echo $form->field($model, 'rowTitle')->textInput(['id'=>'rowTitle'])->label('Название колонки');
    echo $form->field($model, 'rowType')->textInput(['id'=>'rowType'])->label(false);
  ?>
  <?= Html::submitButton('Добавить параметр', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>


<?php
Modal::end();
?>

<input type='button' class='btn btn-primary' onclick='window.opener.location.reload(false); window.close();' value='Завершить'>


<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/managment/head/save-cfg-data']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataRowId' )->hiddenInput(['id' => 'dataRowId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
  //echo "<input type='submit'>";
ActiveForm::end(); 
?>

<?php 
/*
echo "<pre>\n";
  print_r ($model->debug);
echo "</pre>\n";
*/
 ?>

