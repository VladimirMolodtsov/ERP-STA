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
$this->registerJsFile('@web/js/modules/managment/fin-control.js');

?>

<script>
function rmPhone(id)
{ 
   openSwitchWin("/site/rm-phone&id="+id);    
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
            ],

	        [
                'attribute' => 'orgTitle',
                'label'  => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                $s=""; 
                if ($model['isOrgActive'] == 0) 
                {
                 $s="style='font-weight:bold;text-decoration: line-through;color:Crimson'";                                  
                }
				 return "<a $s href='index.php?r=site/org-detail&orgId=".$model['ref_org']."'>".$model['orgTitle']."</a>";
				//  return $model['orgTitle'];       
                },
                
            ],
	        [
                'attribute' => 'phoneContactFIO',
                'label'  => 'ФИО',
                'format' => 'raw',
            ],

	        [
                'attribute' => '-',
                'label'  => 'Дубликаты',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    $res = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%phones}} where phone=:phone',
                    [':phone' => $model['phone'],])->queryScalar();
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
                    $res = Yii::$app->db->createCommand('SELECT COUNT(DISTINCT(ref_org) ) from {{%phones}} where phone=:phone',
                    [':phone' => $model['phone'],])->queryScalar();
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
                    where ref_phone=:ref_phone',
                    [':ref_phone' => $model['id'],])->queryScalar();
                    if ($res == 0) return "&nbsp;";                    
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
                    where ref_phone=:ref_phone',
                    [':ref_phone' => $model['id'],])->queryScalar();
                    
                    if ($res > 0) return "&nbsp;";
                    
                    return "<a href='#' onclick='rmPhone(".$model['id'].")'><span class='glyphicon glyphicon-trash'></span></a>";
                },
            ],		

			        
        ],
    ]
);
?>

