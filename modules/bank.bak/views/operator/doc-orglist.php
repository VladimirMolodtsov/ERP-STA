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
function setOrg(id, title, orgINN) {
	window.parent.closeOrgList(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>


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
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='#' onclick='javascript:setOrg(\"".$model['id']."\",\"".$model['title']."\",\"".$model['orgINN']."\"  );' >".$model['title']."</a>";
                },
            ],		
			[
                'attribute' => 'orgINN',
				'label'     => 'ИНН',
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

