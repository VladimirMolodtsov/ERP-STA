<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Синхронизация с 1с';
?>
 
 <h2><?= Html::encode($this->title) ?></h2>
 
 <pre>
  <?php // print_r ($page); ?>
 </pre>
  
  <?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        //'filterModel' => $model,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],		    
            'title:raw:Заголовок',
			'schetINN:raw:INN',			 
            'have_phone:raw:Известно телефонов',
			'area:raw:Область',
			'city:raw:Город',
			'razdel:raw:Разделы',	
			'x:raw:X',
 			'y:raw:Y',			
            [
                'attribute' => 'Менеджер',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				return  Yii::$app->db->createCommand('SELECT userFIO from {{%user}} where id=:refManager  ', 
				[':refManager' => $model['refManager'],])->queryScalar();
                },
            ],		

		    [
                'attribute' => 'isNew',
				'label'     => 'Новый',
                'format' => 'raw',
//				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isNew'] >0 ) { $isFlg = true;}
					else                     { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : '',
                        [
                            'class' => 'label label-' . ($isFlg ?  'success' : 'danger'),
                        ]
						);
                },
            ],		
			'orgNote:raw:Доп. Инф.',			
			
        ],
		
    ]
);
?>
  
  
 