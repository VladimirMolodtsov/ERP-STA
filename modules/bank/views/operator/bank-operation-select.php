<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;


$this->title = 'Банк - операции согласно 1С';
//$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>


<script>
function changeShowDate(){
var fromDate = document.getElementById('from_date').value;
var toDate = document.getElementById('to_date').value;
document.location.href='index.php?r=/bank/operator/bank-operation-select&noframe=1&refExtract=<?=$refExtract?>&refOrg=<?=$model->refOrg ?>&fromDate='+fromDate+'&toDate='+toDate; 
    
}

function link(id) {
	window.parent.linkOperation(id);
}

function unLink(id) {
    window.parent.unlinkOperation(id);
}

function syncData()
{    

var fromDate = document.getElementById('from_date').value;
var toDate = document.getElementById('to_date').value;
var url ='index.php?r=/data/ajax-sync&actionid=5&fromDate='+fromDate+'&toDate='+toDate; 
    $('#showSyncProgress').modal('show');       
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#showSyncProgress').modal('hide');       
            document.location.reload(true);   
            
        },
        error: function(){
             $('html, body').css("cursor", "auto");
             $('#showSyncProgress').modal('hide');       
            alert('Error while retriving data!');
        }
    });	
    
}


</script>


<table border='0'><tr>
<td>
    <?php   
   echo DatePicker::widget([
    'name' => 'from_date',
    'id' => 'from_date',
    'value' => date("d.m.Y",$model->from),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>            
    <?php   
   echo DatePicker::widget([
    'name' => 'to_date',
    'id' => 'to_date',
    'value' => date("d.m.Y",$model->to),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
</<td>
<td width='20px;' valign='top'>
<?php
                $action = "syncData()";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-refresh clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:5px; margin-left:10px;',
                     'title' => 'Синхронизировать период с 1С',
                   ]);
    
?>
</td>
</tr>
</table>


<?= \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
            [
                'attribute' => 'orgTitle',
                'contentOptions' =>['style' => 'font-size:11px;'],
                'label'     => 'Контрагент',
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'regNote',
                'label'     => 'Регистратор',
                'contentOptions' =>['style' => 'font-size:10px;'],
                'format' => 'raw',            
            ],            
            [
                'attribute' => 'regDate',
                'label'     => 'Рег. инф.',
                'contentOptions' =>['style' => 'font-size:11px;'],
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column)use ($refExtract) {                    
                    $val = "№ ".$model['regNum']."<br>";
                    $val .= $model['regDate']."<br>";
                    return $val;
                }
            ],            
            [
                'attribute' => 'ppNumber',
                'label'     => 'Номер ПП',
                'contentOptions' =>['style' => 'font-size:11px;'],
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column)use ($refExtract) {                    
                    $val = "№ ".$model['ppNumber']."<br>";
                    $val .= $model['recordSum']." руб.<br>";
                    return $val;
                }                 
            ],            
                 
            [
                'attribute' => 'action',
				'label'     => '',
                'format' => 'raw',
                'contentOptions' =>['style' => 'font-size:11px;'],
                'value' => function ($model, $key, $index, $column)use ($refExtract) {                    
          
                $id = $model['id']."removeData"; 
                
                $val ="";
                
                if ($refExtract == $model['refBankExtract'] && $refExtract>0){
                    $action =  "unLink(".$model['id'].");";                    
                    $style="color:Crimson;";    
                    $title = "Отвязать ";                    
                    $val ="<span class='glyphicon glyphicon-remove'></span>";                
                }
                else
                {
                    $action =  "link(".$model['id'].");";                    
                    $style="color:Green;";    
                    $title = "Привязать ";
                    $val ="<span class='glyphicon glyphicon-plus'></span>";                
                     	
                }
                
                return \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => $title,
                     'style'   => "padding:5px;margin:0px;".$style,
                   ]);
                
                    
               }
                
            ],		


            /****/
        ],
    ]
); 

?>


<?php
Modal::begin([
    'id' =>'showSyncProgress',
    'header' => '<h4> Поиск в 1С </h4>',
]);?>
<div style='width:100%; text-align:center;'><img src='img/ajax-loader.gif'></div>
<?php
Modal::end();
?>
