<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выберите связанный документ';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setDoc(id, title) {
    window.parent.closeDocList(id, title);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>
<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
   			[
                'attribute' => 'docIntNum',
				'label'     => '#',
                'format' => 'raw',         
            ],		

   			[
                'attribute' => 'orgTitle',
				'label'     => 'Контрагент',
                'format' => 'raw',         
            ],		
            
            
			[
                'attribute' => '',
				'label'     => 'Документ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                if (empty($model['docClassifyRef'])) $v =  $model['docTitle'];
                else $v = $model['docType'];
                $v .= " ".$model['docOrigNum'];                 
                $v .= " ".$model['orgTitle'];
                return "<a href='#' onclick='javascript:setDoc(\"".$model['id']."\",\"\");' >".$v."</a>";
                },
            ],		
        ],
    ]
	);
?>

