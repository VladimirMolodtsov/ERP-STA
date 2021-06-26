<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$curUser=Yii::$app->user->identity;
$this->title = 'Телефонная книга';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<script>
function setPhone(id){
     window.parent.setSelectedPhone(id);
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
                'attribute' => 'phone',
                'label'  => 'Телефон',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                
                $action = "setPhone(".$model['id'].");" ;                
                $val = \yii\helpers\Html::tag( 'div', $model['phone'], 
                   [
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => 'font-size:11px;',
                   ]);

                 return $val;
               }                
            ],

	        [
                'attribute' => 'phoneContactFIO',
                'label'  => 'ФИО',
                'format' => 'raw',
            ],

        ],
    ]
);
?>

