<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выберите или создайте новую организацию';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setOrg(id, title, phone) {    
	window.parent.setOrg(id, title, phone);
}

function newOrg() {    
    window.parent.setOrg(-2, document.getElementById('addOrg').value, '');
}

</script >

<!--<h3><?= Html::encode($this->title) ?></h3>-->
<!--<input class="btn btn-primary"  style="width: 150px;" type="button" value="Реклама/Прочее" onclick="javascript:setOrg(-1,'Реклама/Прочее');"/>&nbsp;&nbsp;&nbsp;-->
<!--<input class="btn btn-primary"  style="width: 150px;" type="button" value="Создать новую:" onclick="javascript:newOrg();"/>&nbsp;<input name='addOrg'  style="width: 350px;" id='addOrg' placeholder='Название новой организации'
>
<hr>-->
<h4>Выбор из списка:</h4>
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
                    //$title = Html::encode($model['contactOrgTitle']);
                    $title = preg_replace("/\'/"," ",$model['contactOrgTitle']);
					return "<a href='#' onclick=\"javascript:setOrg('".$model['id']."','".$title."','".$model['contactPhone']."'  );\" >".$model['contactOrgTitle']."</a>";
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
