<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор запроса';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setZapros(id) {
	window.parent.closeZaprosList(id);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>


<P>Запросы доступные для включения в закупку. Запрос можно включить только в одну закупку!</P>
<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
			[
                'attribute' => 'wareTitle',
				'label'     => 'Номенклатура',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<a href='#' onclick='javascript:setZapros(\"".$model['refPurchaseZakaz']."\");' >".$model['wareTitle']."</a>";
                },
            ],		
                        
			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
    
			[
                'attribute' => 'wareEd',
				'label'     => 'Ед.',
                'format' => 'raw',
            ],		
        
    
            [
                'attribute' => 'refZakaz',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                $strSql = 'SELECT formDate, userFIO, title FROM {{%zakaz}},{{%user}},{{%orglist}} where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
                
                if (empty($dataList))return "";
               
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret = $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                return $ret;                
                }
            ],		

          /*  [
                'attribute' => 'purchaseRef',
				'label'     => 'Связан с закупкой',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['purchaseRef'])) return "Свободен";
                 return $model['purchaseRef'];
                }
            ],	*/	
    

    /**/
        ],
    ]
	);
?>
