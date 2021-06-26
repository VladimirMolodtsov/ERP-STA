<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор организации';
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<style>
.table-small {
padding: 2px;
font-size:12px;
}

</style>

<script type="text/javascript">
function setOrg(id, title, orgINN) {
	window.parent.closeOrgList(id, title);
}

function newOrg() {
    window.parent.closeOrgList(-2, '');
}

function openOrg(id) {   
  var url  ='site/org-detail&orgId='+id;
  openWin(url,'orgWin');
}
</script >

<h3><?= Html::encode($this->title) ?></h3>

<div align='right'>
<?php
 $action = "javascript:newOrg();";    
 echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
             [
               'class'   => 'btn btn-default',
               'onclick' => $action,
               'title'   => 'Создать на основе выписки',
             ]);
?>
</div>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
   			[
                'attribute' => 'id',
				'label'     => '#',
                'format' => 'raw',
            ],		

            
			[
                'attribute' => 'title',
				'label'     => 'Контрагент',
                'filterOptions' => ['style' =>'padding:2px; font-size:10px;'],
                'filterInputOptions' => ['style' =>'padding:2px; font-size:10px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 $val = $model['title'];
                 $action = "javascript:openOrg('".$model['id']."');";    
                 return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);

                },
            ],		
 
 			[
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $title = preg_replace("/\'/"," ",$model['title']);				   
                 $action = "javascript:setOrg('".$model['id']."','".$title."','".$model['orgINN']."'  );";    
                 return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);
                },
            ],		
 
			[
                'attribute' => 'orgINN',
                'filterOptions' => ['style' =>'padding:2px; font-size:11px;'],
                'filterInputOptions' => ['style' =>'padding:2px; font-size:10px; width:75px'],
				'label'     => 'ИНН',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'orgKPP',
                'filterOptions' => ['style' =>'padding:2px; font-size:11px;'],
                'filterInputOptions' => ['style' =>'padding:2px; font-size:10px; width:75px'],
				'label'     => 'КПП',
                'format' => 'raw',
            ],		
            
            
			[
                'attribute' => 'sdelok',
				'label'     => 'Сделок',
                'format' => 'raw',
            ],		
			[
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
            ],		
            
            
        ],
    ]
	);
?>

