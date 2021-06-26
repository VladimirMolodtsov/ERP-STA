<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Активность менеджера по клиентам.';

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
<a href="#" onclick="openEditWin('index.php?r=site/manager-org-activity&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 

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
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['id']."'>".$model['orgTitle']."</a>";
                },
            ],			
			                 
                
     	    [
                'attribute' => 'C',
				'label'     => 'Число контактов',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'Z',
				'label'     => 'Число заказов',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'S',
				'label'     => 'Число счетов',
                'format' => 'raw',
            ],		
            
            
        ],
    ]
	);
?>

