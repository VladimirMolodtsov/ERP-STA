<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Список заявок на закупку';
//$this->params['breadcrumbs'][] = $this->title;
$curUser=Yii::$app->user->identity;


?>
<p>Менеджер <b><?= Html::encode($curUser->userFIO)?></b></p>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<style>
.button {
    background-color: GhostWhite ;
    color: Gray ;
	border-color: Gray;
	text-align:right;
} 
</style>
  
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
                'attribute' => 'wareTitle',
				'label' => 'Товар',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'Заявка',
				'label' => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<nobr><a href='#' onclick=\"openWin('market/market-good-request&id=".$model['requestId']."','requestWin');\"> № ".$model['requestId']." от ".date("d.m.y", strtotime($model['formDate']))."</a></nobr>";
                },
            ],		

			[
                'attribute' => 'orgTitle',
				'label' => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				    if (empty($model['orgTitle'])) return $model['requestClient'];
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['orgId']."'>".$model['orgTitle']."</a>";
                },
            ],		

            [
                'attribute' => 'Сформирована',
				'label'     => 'Сформирована',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isFormed'] >0 ){ $isFlg = true;}
					else                       { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ?  'success':'danger'),
                        ]
						);
                },
            ],	

            [
                'attribute' => 'refPurchaseZakaz',
				'label'     => 'В работе',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
                    if ($model['refPurchaseZakaz'] >0 )     $isFlg = true;
                    else $isFlg = false;
                    
                    $ret = \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ?  'success':'danger'),
                        ]
						);
                        
                       return $ret;  
                },
            ],	

            [
                'attribute' => 'refPurchaseZakaz',
				'label'     => 'Статус',
                'format' => 'raw',
				//'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
                 $status = "";       

              if ($model['refPurchaseZakaz'] >0 ){ 
                    
                    
              $strSql = 'SELECT status, id, purchaseRef FROM {{%purchase_zakaz}}  where {{%purchase_zakaz}}.id = :refPurchaseZakaz LIMIT 1';              
              $statusZakaz = Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $model['refPurchaseZakaz'],])->queryAll();       
       
              if (count($statusZakaz) >0 )
              {
                if ($statusZakaz[0]['status'] < 8)
                {
                    $status = "<a href='#' onclick=\"openWin('store/purchase-zakaz&noframe=1&id=".$statusZakaz[0]['id']."','storeWin');\"> Запрос цены </a>";
                }
                else
                {                    
                    $statusPurchase = Yii::$app->db->createCommand($strSql, [':refPurchaseZakaz' => $model['refPurchaseZakaz'],])->queryAll();                                          
                    $status = "<a href='#' onclick=\"openWin('store/purchase&noframe=1&id=".$statusZakaz[0]['purchaseRef']."','storeWin');\"> В закупке </a>";
                }          
              }
              }
                       return $status;  
                },
            ],	

			
        ],
    ]
);
?>