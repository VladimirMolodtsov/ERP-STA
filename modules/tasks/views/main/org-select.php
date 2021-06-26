<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Alert;

?>
<style>
.button {
    background-color: GhostWhite ;
    color: Gray ;
	border-color: Gray;
	text-align:right;
} 
.small {
    
    font-size:12px;
}

</style>
 <script>
function setOrg(orgRef)
{
  document.location.href="index.php?r=tasks/main/set-task-org&orgRef="+orgRef+"&refManager=<?=$model->userId?>";   
}
 </script>  
  
<?php
$userArray = $model->getManagerList();
/*echo "<pre>";
print_r($userArray);
echo "</pre>";*/
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed small'],
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    $ret= "<a href='#' onclick='setOrg(".$model['id'].")' >".$model['orgTitle']."</a>";
                    return $ret;
                },
            ],		
				
            [
	
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
				'filter'    => $userArray,
                
            ],
			
        ],
    ]
);
?>
