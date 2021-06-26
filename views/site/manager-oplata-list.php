<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Оплаты по счетам менеджера.';

$managerRecord= $model->getManagerRecord($userId);

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>
<?php
if(!empty($managerRecord)) 
{
   echo "<h4>".$managerRecord->userFIO."</h4>"; 
}
?>

<a href="#" onclick="openEditWin('index.php?r=site/manager-oplata-list&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 

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
                'attribute' => 'id',
				'label'     => 'id',
                'format' => 'raw',
            ],		
                 
     	    [
                'attribute' => 'oplateSumm',
				'label'     => 'Сумма',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'oplateDate',
				'label'     => 'Дата платежа',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'oplateNum',
				'label'     => 'Номер платежа',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		

        ],
    ]
	);
?>

