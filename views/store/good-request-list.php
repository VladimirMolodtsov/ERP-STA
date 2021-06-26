<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Заявки на закупку';
//$this->params['breadcrumbs'][] = $this->title;

?>
 <script>
function openWin(url)
{
  window.open("index.php?r="+url,'edit','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1050,height=700'); 
}
</script> 

  <h2><?= Html::encode($this->title) ?></h2>

  
  
<br>
<?php
echo \yii\grid\GridView::widget(
    [
	

	
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
	
	
			[
                'attribute' => 'good',
				'label'     => 'Запрашиваемый товар',
                'format' => 'raw',
            ],			
			[
                'attribute' => 'sumCount',
				'label'     => 'Суммарное количество',
                'format' => 'raw',
            ],			
						
			[
                'attribute' => 'minDate',
				'label'     => 'Дата поставки',
                'format' => ['datetime', 'php:d-m-Y G:i'],
            ],			

			[
                'attribute' => 'goodSclad',
				'label'     => 'Закупаемый товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				    if ( empty($model['goodSchet']) ) return "&nbsp;";
                    return $model['goodSclad']." по счету ".$model['goodSchet'];
                },

            ],			
			
			[
                'attribute' => 'Изменить',
				'label' => 'Изменить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<input class='btn btn-primary' type='button' value='Данные по счету' onclick=\"javascript:openWin('store/fill-schet-request&good=".$model['good']."')\"/>";
                },
            ],		
			
			[
                'attribute' => 'Завершено',
				'label' => 'Завершено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					if ( empty($model['goodSchet']) ) return "&nbsp;";					
                    return "<a class='btn btn-primary' href='index.php?r=site/mark-request&good=".$model['good']."'>Отметить</a>";					
                },
            ],		
	
        ],
    ]
);
?>
