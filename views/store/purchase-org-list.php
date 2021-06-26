<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор организации';
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
	window.parent.closeOrgList(id, title);
}

function newOrg() {
    window.parent.closeOrgList(-2, document.getElementById('addOrg').value);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>
<input name='addOrg' id='addOrg'>&nbsp;<input 
 class="btn btn-primary"  style="width: 150px;" type="button" value="Добавить" onclick="javascript:newOrg();"/>


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
                'attribute' => 'contactOrgTitle',
				'label'     => 'Наименование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='#' onclick='javascript:setOrg(\"".$model['id']."\",\"".$model['contactOrgTitle']."\" );' >".$model['contactOrgTitle']."</a>";
                },
            ],		
        ],
    ]
	);
?>

<?php 

if (!empty($model->debug))
{
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>
