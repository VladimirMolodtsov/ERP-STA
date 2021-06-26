<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$curUser=Yii::$app->user->identity;
$this->title = 'Адреса электронной почты';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/fin-control.js');

?>

<script>
function rmEmail(id)
{ 
   openSwitchWin("/site/rm-email&id="+id);    
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
                'attribute' => 'email',
                'label'  => 'Телефон',
                'format' => 'raw',
            ],

	        [
                'attribute' => 'orgTitle',
                'label'  => 'Контрагент',
                'format' => 'raw',
            ],
	/*        [
                'attribute' => 'phoneContactFIO',
                'label'  => 'ФИО',
                'format' => 'raw',
            ],
*/
	        [
                'attribute' => '-',
                'label'  => 'Дубликаты',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    $res = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%emaillist}} where email=:email',
                    [':email' => $model['email'],])->queryScalar();
                    if ($res <= 1) return "&nbsp;";
                    $res--;
                    return $res;
                },
            ],		

	        [
                'attribute' => '-',
                'label'  => 'Организаций',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    $res = Yii::$app->db->createCommand('SELECT COUNT(DISTINCT(ref_org) ) from {{%emaillist}} where email=:email',
                    [':email' => $model['email'],])->queryScalar();
                    if ($res <= 1) return "&nbsp;";
                    $res--;
                    return $res;
                },
            ],		

	        [
                'attribute' => '-',
                'label'  => 'Контактов',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    $res = Yii::$app->db->createCommand('SELECT COUNT(DISTINCT(id) ) from {{%contact}}
                    where contactEmail=:email',
                    [':email' => $model['email'],])->queryScalar();
                    if ($res <= 0) return "&nbsp;";
                    return $res;
                },
            ],		

	        [
                'attribute' => '-',
                'label'  => '<span class="glyphicon glyphicon-trash"></span>',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    $res = Yii::$app->db->createCommand('SELECT COUNT(DISTINCT(id) ) from {{%contact}}
                    where contactEmail=:email',
                    [':email' => $model['email'],])->queryScalar();
                    if ($res > 0) return "&nbsp;";
                                    
                    return "<a href='#' onclick='rmEmail(".$model['id'].")'><span class='glyphicon glyphicon-trash'></span></a>";
                },
            ],		

			        
        ],
    ]
);
?>

