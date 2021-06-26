<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$curUser=Yii::$app->user->identity;
$this->title = 'Адресная книга';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<script>
function setAdress(id){
     window.parent.setSelectedAdress(id);
}

</script>


<h3><?= $this->title?> </h3>
  
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
                'attribute' => 'adress',
                'label'  => 'Адрес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                
                $action = "setAdress(".$model['id'].");" ;                
                $val = \yii\helpers\Html::tag( 'div', $model['adress'], 
                   [
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => 'font-size:11px;',
                   ]);

                 return $val;
               }                
            ],

	        [
                'attribute' => 'city',
                'label'  => 'Город',
                'format' => 'raw',
            ],

	        [
                'attribute' => 'index',
                'label'  => 'Индекс',
                'format' => 'raw',
            ],


        ],
    ]
);
?>

