<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Необработанные лиды';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setOrg(id, title) {
   
	//window.parent.document.getElementById('contactOrgTitle').value=title;    
    //window.parent.document.getElementById('orgId').value=id;    
	window.parent.closeOrgList(id, title);
}
</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $leadListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

     	    [
                'attribute' => 'contactDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'userFIO',
				'label'     => 'Оператор',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'note',
				'label'     => 'Текст лида',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    $ret= mb_substr($model['note'],0, 50)." ...";
					return $ret;
                },

            ],		
            
			[
                'attribute' => 'contactOrgTitle',
				'label'     => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ($model['ref_org'] == -1) return "Реклама/Прочее";
					return $model['contactOrgTitle'];
                },
            ],		
            
            
     	    [
                'attribute' => 'id',
				'label'     => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='index.php?r=site/new-lead&noframe=1&contactId=".$model['id']."'> Обработать </a>";
                },

            ],		
            

        ],
    ]
	);
?>