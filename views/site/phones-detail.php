<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;


$curUser=Yii::$app->user->identity;
$this->title = 'Телефоны';
//$this->params['breadcrumbs'][] = $this->title;
$record=$model->loadOrgRecord();

?>


<div class="part-header"> Телефоны</div> 
 
<?php

$phoneCount = Yii::$app->db->createCommand(
            'SELECT count(id) from {{%phones}} where ref_org=:ref_org ', 
            [':ref_org' => $model->orgId])->queryScalar();
			
$phoneProvider = new SqlDataProvider(['sql' => 
            'Select  id, phone, status from {{%phones}}
			 where {{%phones}}.ref_org=:ref_org',
			'params' => [':ref_org' => $model->orgId],
			'totalCount' => $phoneCount,
			'pagination' => [
			'pageSize' => 5,
			],
			'sort' => [
			'attributes' => [
			'status',
			'phone',	
			],
			],
		]);

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $phoneProvider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],	
            'phone:raw:Телефон',
	        [
                'attribute' => 'Последний контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_phone=:ref_phone order by id  DESC LIMIT 1 ', 
				[':ref_phone' => $model['id'],])->queryAll();
				$ret="&nbsp;";				
				if (empty ($resList) ) {return "&nbsp;";}
				for($i=0;$i<count($resList);$i++){					
					if ( ($resList[$i]['contactFIO'] =="-" || $resList[$i]['contactFIO'] =="") && ($resList[$i]['note']=="")) {continue;}
					$ret= date("d.m.Y",strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']." ".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		
			        
        ],
    ]
);
?>

