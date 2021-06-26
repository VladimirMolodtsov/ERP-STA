<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор реквезитов';
$curUser=Yii::$app->user->identity;

?>
<style>
.table-small {
padding: 2px;
font-size:12px;
}

</style>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setAccount(id) {
	window.parent.closeAccList(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<div class='btn btn-default' onclick='setAccount(-1);'>Создать реквезиты</div>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $accountProvider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small', 'style' => 'width:470px;' ],
        'columns' => [
			[
                'attribute' => 'orgRS',             
				'label'     => 'счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  
                  $action ="setAccount(".$model['id'].");";
                  $delemiter="-";
                  $orgRS = mb_substr($model['orgRS'],0,3,'utf-8');
                  $orgRS .= $delemiter;
                  $orgRS .= mb_substr($model['orgRS'],3,2,'utf-8');
                  $orgRS .= $delemiter;
                  $orgRS .= mb_substr($model['orgRS'],5,3,'utf-8');
                  $orgRS .= $delemiter;
                  $orgRS .= mb_substr($model['orgRS'],8,1,'utf-8');
                  $orgRS .= $delemiter;
                  $orgRS .= mb_substr($model['orgRS'],9,4,'utf-8');
                  $orgRS .= $delemiter;
                  $orgRS .= "<b>".mb_substr($model['orgRS'],13,7,'utf-8')."</b>";
                  
                  $val = \yii\helpers\Html::tag( 'div',$orgRS, 
                   [
                     'class'   => 'clickable',
                    // 'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Счет',
                     'style'   => 'font-size:11px;padding:1px;', 
                   ]);   
                    
                    
                 return $val;           
                },
            ],		
        
        
			[
                'attribute' => 'orgBank',
				'label'     => 'Банк',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  
                  $action ="setAccount(".$model['id'].");";
         
                  $val = \yii\helpers\Html::tag( 'div',$model['orgBank'], 
                   [
                     'class'   => 'clickable',
                    // 'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Счет',
                     'style'   => 'font-size:11px;padding:1px;', 
                   ]);   
                    
                    
                 return $val;           
                },

            ],		
            
        ],
    ]
	);
?>
