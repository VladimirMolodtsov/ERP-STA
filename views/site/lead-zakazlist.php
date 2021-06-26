<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Привязать лид к сделке';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setZakaz(zakazId, formDate, schetNum, schetDate ) {
	window.parent.closeZakazList(zakazId, formDate, schetNum, schetDate );
}

function newZakaz(orgId) {    
	window.parent.getNewZakaz(orgId);
}
</script >

<h3><?= Html::encode($this->title) ?></h3>

<p>Клиент: <b><?= $model->contactOrgTitle ?></b> </p>

<input class="btn btn-primary"  style="width: 150px;" type="button" value="Создать заявку" onclick="javascript:newZakaz(<?= $model->orgId ?>);"/>

<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			[
                'attribute' => 'id',
				'label'     => 'Сделка',
                'format' => 'raw',
                'filter'=>array("1"=>"Заказ","2"=>"Счет"),
                'value' => function ($model, $key, $index, $column) {
					$ret = "<a href='#' onclick='javascript:setZakaz(\"".$model['id']."\",\"".$model['formDate']."\",\"".$model['schetNum']."\",\"".$model['schetDate']."\" );' >";
                    $ret.= "Заказ № ".$model['id']." от ".$model['formDate'];
                    if (!empty($model['schetNum'])) $ret .= "<br> счет №".$model['schetNum']." от ".$model['schetDate']." на сумму ".number_format($model['schetSumm'],2,'.','&nbsp;');
                    $ret.="</a>";
                    return $ret;
                },
            ],
			[
                'attribute' => 'Наименования товаров',
				'label'     => 'Наименования товаров',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $goodList  = Yii::$app->db->createCommand(
                    'SELECT good, count, ed, value from {{%zakazContent}} where refZakaz=:zakazId LIMIT 3', 
                    [':zakazId' =>$model['id'],	])->queryAll();
                    $ret="";
                    for($i=0;$i<count($goodList); $i++)
                    {
                      $ret.= $goodList[$i]['good']." ".$goodList[$i]['count']."<br>";    
                    }            
                    return $ret;
                },
            ],		
            
        	[
                'attribute' => 'isSchetActive',
				'label'     => 'Счета в работе',
                'format' => 'raw',				
                'filter'=>array("1"=>"Да","2"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if (empty($model['isSchetActive']) ){ $isFlg = false;}
					else                        { $isFlg = true;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		

            
        ],
    ]
	);
?>