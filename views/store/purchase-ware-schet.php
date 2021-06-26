<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор товара из счета';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setSchet(id) {
	window.parent.closeWareSchet(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='#' onclick='javascript:setSchet(\"".$model['id']."\");' >".$model['wareTitle']."</a>";
                },                
            ],		
            
			[
                'attribute' => 'schetNum',
				'label'     => 'Счет №',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='#' onclick='javascript:setSchet(\"".$model['id']."\");' >".$model['schetNum']."</a>";
                },
            ],		
			[
                'attribute' => 'schetDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
            

			[
                'attribute' => 'wareValue',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ($model['goodCount'] == 0) return "";
					return number_format($model['goodSumm']/$model['goodCount'],2,'.','&nbsp;');
                },
                
            ],		

            
        ],
    ]
	);
?>
