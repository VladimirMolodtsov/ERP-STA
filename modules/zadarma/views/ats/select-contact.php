<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
$this->title = '';
?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>
.bound{
    background-color:LightGray; 
    width:10px;
}
td{
 font-size:11px;
}
</style>


<script type="text/javascript">

function showLead(leadId)
{
    openWin('site/lead-process&contactId='+leadId, 'contactWin');
}

function showContact(refContact, orgRef)
{
    openWin('site/reg-contact&id='+orgRef+'&contactId='+refContact, 'contactWin');
}

function newContact()
{
 window.parent.newContact(<?= $model->orgRef  ?>);
}


function setContact(selContactRef)
{
 window.parent.setContact(selContactRef);
}
</script> 

<div class='row'>
    <div class='col-md-6'>    

    </div>
    <div class='col-md-2'>
        <div class='btn btn-primary' onclick='newContact();'>Создать контакт</div>
    </div>    
</div>
<?php    
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed small'],
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'attribute' => 'phone',
				'label' => 'Телефон',
                'format' => 'raw',            
            ],		

			[
                'attribute' => 'contactDate',
				'label' => 'Дата/время',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                if ($model['eventType'] >=10 && $model['eventType'] < 100) $action="showLead(".$model['selContactRef'].");";
                                                                      else $action="showContact(".$model['selContactRef'].");";
                                                                      
                return \yii\helpers\Html::tag( 'div', date("d.m.Y H:i", strtotime($model['contactDate'])), 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);

                },
            ],		

            
			[
                'attribute' => '',
				'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                
                $action="setContact(".$model['selContactRef'].");";
                return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                
                },
            ],		
            
			[
                'attribute' => 'note',
				'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                $val = mb_substr($model['note'],0,250,'utf-8');
                
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'style' => 'font-size:10px;',
                   ]);
                
                },
            ],		
        ],
    ]
);
?>



