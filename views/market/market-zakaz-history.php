<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'История согласования';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>
 
    <div class="part-header"> </div> 
    <br>
    <?php	
	echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getZakazHistoryProvider(),
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
		'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			'initialZakaz:raw:К начальному заказу',            
			'title:raw:Тип согласования',
			'proposal:raw:Предложение',

			[
                'attribute' => 'propDate',
				'label'     => 'Дата согласования ',
                'format' => ['datetime', 'php:d-m-Y G:i'],
            ],
			
        ],
    ]
	);	
	?>

