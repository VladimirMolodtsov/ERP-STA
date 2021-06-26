<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор счета';
$curUser=Yii::$app->user->identity;

?>
<style>
.table-local {    
  font-size: 12px;
}

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setSchet(id) {
	window.parent.closeSchetList(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'schetNum',
				'label'     => 'Счет №',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $strSql = "SELECT SUM(goodSumm) FROM {{%supplier_schet_content}} where schetRef = :schetRef";                   
                   $sum =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['id'],])->queryScalar();                    
                   
					return "<a href='#' onclick='javascript:setSchet(\"".$model['id']."\");' >".$model['schetNum']."<br>на&nbsp;сумму&nbsp;".number_format($sum,2,".","&nbsp;")."</a>";
                    
                },
            ],		
			[
                'attribute' => 'schetDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
			[
                'attribute' => 'Товар',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $strSql = "SELECT goodTitle FROM {{%supplier_schet_content}} where schetRef = :schetRef LIMIT 4";                   
                   $wareList =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['id'],])->queryAll();                    
                   $ret="";
                    for ($i=0; $i<count($wareList); $i++ )
                    {
                     $ret.=$wareList[$i]['goodTitle'];
                     if ($i>= 2) break;
                     $ret.="<br>";
                    }
                    if ($i<count($wareList)) $ret.="<br>...";                    
					return $ret;
                },
            ],		
            
        ],
    ]
	);
?>
