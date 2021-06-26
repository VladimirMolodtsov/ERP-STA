<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Активность менеджера - счета.';

$managerRecord= $model->getManagerRecord($userId);

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>
<?php
if(!empty($managerRecord)) 
{
   echo "<h4>".$managerRecord->userFIO."</h4>"; 
}
?>

<a href="#" onclick="openEditWin('index.php?r=site/manager-schet-activity&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['orgId']."'>".$model['orgTitle']."</a>";
                },
            ],			
			                 
            
     	    [
                'attribute' => 'schetDate',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                
                 $action=" onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');\"";                 
                 if (empty($model['ref1C'])) $ret= "<div class='child' ".$action." style='background-color:Yellow'>";
                                       else $ret= "<div class='child' ".$action." style='background-color:LightGreen'>";                                                                         
				 $ret.= "<b>Счёт</b> № ".$model['schetNum']."&nbsp;от&nbsp;". date("d.m.Y", strtotime($model['schetDate']))."<br> на: ";
                 $ret.=number_format($model['schetSumm'],2,'.','&nbsp;');				                           
                 $ret.="</div>";
                 return $ret;                 
                },
                
            ],		

            [
                'attribute' => 'Оплата',
				'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',			                
                'value' => function ($model, $key, $index, $column) {					
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();
                 
                 //return $model['schetId'];
                 if (count($listData)==0) return "&nbsp;";                 
                 if($listData[0]['sumOplata'] == 0)return "&nbsp;";                 
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div  style='padding:5px;background-color:LightGreen'>"; 
                                                            else $ret= "<div  style='padding:5px;background-color:Yellow'>";
                                                            
				  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));				                                              
                  $ret.="<br>&nbsp;</div>";                  
                 return $ret;                  
                },
            ],			
			
            [
                'attribute' => 'Поставка',
				'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',			                
                'value' => function ($model, $key, $index, $column) {					
                if (empty($model['schetId'])) return "&nbsp";    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();
                             
                if(count($listData)==0 || $listData[0]['sumSupply'] == 0)               
                { 
                                
                    $list = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet',
                    [':refSchet' => $model['schetId'] ])->queryAll();
                    if (count ($list) == 0 ) return "Нет запроса ";
                    $ret= "<div>Запрос на поставку № ".$list[0]['id']." от ".date ('d.m.Y', strtotime($list[0]['requestDate']))."</div>";                 
                    return $ret;
                }
                
                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div style='padding:5px;background-color:LightGreen'>"; 
                                                                 else $ret= "<div style='padding:5px;background-color:Yellow'>";
                                                            
				  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));				                                              
                  $ret.="<br>&nbsp;</div>";
                 return $ret;                  
                },
            ],			

            
            
        ],
    ]
	);
?>

