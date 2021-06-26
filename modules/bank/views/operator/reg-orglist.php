<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор организации';
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
function setOrg(id) {
	window.parent.closeOrgList(id, '');
}

function newOrg() {
    window.parent.closeOrgList(-2, document.getElementById('addOrg').value);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>


<input class="btn btn-primary"  style="width: 100px;" type="button" value="Создать :" onclick="javascript:newOrg();"/>&nbsp;<input name='addOrg'  style="width: 270px;" id='addOrg' placeholder='Название новой организации'
>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small', 'style' => 'width:470px;' ],
        'columns' => [
             
			[
                'attribute' => 'title',
				'label'     => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                      
              
                  $action ="setOrg(".$model['id'].",'');";
                  $val = \yii\helpers\Html::tag( 'div',$model['title'], 
                   [
                     'class'   => 'clickable',
                    // 'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => 'font-size:11px;padding:1px;', 
                   ]);   
                    
                    
                 return $val;           
                },
            ],		
			[
                'attribute' => 'orgINN',
				'label'     => 'ИНН/КПП',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  
                  $action ='setOrg(\"'.$model['id'].'\");';
                  $val = \yii\helpers\Html::tag( 'div',$model['orgINN']."<br>".$model['orgKPP'], 
                   [
                     'class'   => 'clickable',
                    // 'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'В оплату',
                     'style'   => 'font-size:11px;padding:1px;', 
                   ]);   
                    
                    
                 return $val;           
                },
            ],		
            
         	[
                'attribute' => 'sdelok',
				'label'     => 'Сделок',
                'format' => 'raw',
            ],	
            
        ],
    ]
	);
?>
