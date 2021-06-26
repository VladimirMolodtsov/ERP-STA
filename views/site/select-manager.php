<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}

$this->title = 'Выбор клиентов ';

?>
  <h2><?= Html::encode($this->title) ?></h2>

  <link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


  <style>

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 75px;	
	float:right;
}
		
 
</style>


 <script>


 </script>  


<pre>
<?php print_r($model->debug); ?>
</pre>

<?php
			
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			
   	        [
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					return "<a href='index.php?r=site/set-org-manager&managerId=".$model['id']."' >".$model['userFIO']."</a>";
			   },

			],	
	     ]
    ]		 
);
?>
