<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\SqlDataProvider;
$this->title = 'Холодные звонки - Первый контакт';
//$this->params['breadcrumbs'][] = $this->title;

$count = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%orglist}} where isFirstContact =:flg ', 
            [':flg' => 0])->queryScalar();
				
?>
  <h2><?= Html::encode($this->title) ?></h2>
  
<?php
/*if (isset($sort)){
     $provider->setSort([ 
	 'defaultOrder'=>[
             'have_phone'=>SORT_DESC,
            ]   
		]);
	}*/
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
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
			'area:raw:Область',
			'city:raw:Город',
			'razdel:raw:Разделы',	
			'x:raw:X',
 			'y:raw:Y',

/*			
            [
                'attribute' => 'Разделы',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$razdelList = Yii::$app->db->createCommand('SELECT DISTINCT razdel from {{%razdellist}} where ref_org=:ref_org  ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="";
				for($i=0;$i<count($razdelList);$i++){$ret= $razdelList[$i]['razdel']." ";}
                    return "$ret";
                },
            ],		
*/
			
			[
                'attribute' => 'В работу',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a class='btn btn-primary' href='index.php?r=cold/cold-init&id=".$model['id']."'>Взять</a>";
                },
            ],		
			
			
			
        ],
		
    ]
);
?>