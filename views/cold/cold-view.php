<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;



$this->title = 'Холодные звонки';
$this->params['breadcrumbs'][] = $this->title;


?>
  <h2><?= Html::encode($this->title) ?></h2>

  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel'  => $model,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		
			[
                'attribute' => 'title',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['id']."'>".$model['title']."</a>";
                },
            ],		


            'have_phone:raw:Известно телефонов',
			'razdel:raw:Раздел',		
/*			'isFirstContact:raw:isFirstContact',		*/
			[
                'attribute' => 'isFirstContact',
				'label'     => 'Был звонок',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
	
					if ($model['isFirstContact'] > 0 ){ $isFlg = true;}
					else                            { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
						
                },
            ],		

			[
                'attribute' => 'isNeedFinished',
				'label'     => 'Потребности ',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isNeedFinished'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
					
			[
                'attribute' => 'isPreparedForSchet',
				'label'     => 'Готов к сч.',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isPreparedForSchet'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		

			[
                'attribute' => 'isSchetFinished',
				'label'     => 'Счет есть',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isSchetFinished'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
       
			[
                'attribute' => 'isInWork',
				'label'     => 'В работе',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isInWork'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
            
            'userFio:raw:Оператор',
        ],
    ]
);
?>