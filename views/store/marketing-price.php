<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\WarehouseForm;

$this->title = 'Формирование заявки/резервирование';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
</style>
  
<script>

</script>
 
<div class='row'>
  <div class='col-md-9 col-xs-8'>
  </div>
  
  <div class='col-md-2 col-xs-3'>    
	<input class="btn btn-primary"  style="width: 150px; background-color: ForestGreen;" type="button" value="Синхронизировать" onclick="javascript:openEditWin('market/sync-price&noframe=1');"/>
  </div>  
  
<div class='col-md-1 col-xs-1'>
</div>
</div>

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
                'attribute' => 'title',
				'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return $model['title'];
                },
            ],		

			[
                'attribute' => 'grpGood',
				'label' => 'Товарная группа',
                'format' => 'raw',
				'filter' => $model->getGrpGroup(),
				
            ],		
			
            [
	
                'attribute' => 'isValid',
				'label'     => 'Валидно',                
				'format' => 'raw',
				'filter'=>array("1"=>"Да","2"=>"Все"),
                'value' => function ($model, $key, $index, $column) {					
				
				    if ($model['isValid'] >0 ){ $isFlg = true;}
					else                      { $isFlg = false;}
                    return  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
				
				 
                },

            ],
			
            [
                'attribute' => 'ed',
				'label'     => 'Ед. изм',                
                'format' => 'raw',
            ],

            [
                'attribute' => 'price',
				'label'     => 'Цена закупки',                
                'format' => 'raw',                
            ],
			
            [
                'attribute' => 'marketPrice',
				'label'     => 'Цена рыночная',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {				
					return "<a href='#' onclick='javascript:openEditWin(\"store/set-marketprice&noframe=1&id=".$model['id']."\");'>". $model['marketPrice']."</a>";					
                }
            ],

      	
   		
            
        ],
    ]
);

?>

