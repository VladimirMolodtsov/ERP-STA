<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;



$this->title = 'Холодные звонки - отказы';
//$this->params['breadcrumbs'][] = $this->title;




?>
  <h2><?= Html::encode($this->title) ?></h2>

  
<?php
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
			/*
            [
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
                'attribute' => 'Комментарий',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by id  DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="&nbsp;";				
				if (empty ($resList) ) {return "$nbsp;";}
				for($i=0;$i<count($resList);$i++){					
					if ( ($resList[$i]['contactFIO'] =="-" || $resList[$i]['contactFIO'] =="") && ($resList[$i]['note']=="")) {continue;}
					$ret= date("d.m.Y",strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']." ".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

			[
                'attribute' => 'Сбросить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a class='btn btn-primary' href='index.php?r=cold/cold-rejectdel&id=".$model['id']."'>Сбросить</a>";
                },
            ],		
			
			
			
        ],
		
    ]
);
?>