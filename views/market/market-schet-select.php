<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Состояние счета ';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>

 <script>
function openWin(url)
{
  wid=window.open("index.php?r="+url,'zakazwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}
</script>  

<p>Незавершенные счета менеджера</p>
  
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
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['orgId']."'>".$model['orgTitle']."</a>";
                },
            ],			

			[
                'attribute' => 'schetNumber',
				'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$ret = "№ ".$model['schetNum']." от ".date ('d.m.Y', strtotime($model['schetDate']))."<br>На сумму:".$model['schetSumm']; 				
				return $ret;
                },
            ],		
			
		    [
                'attribute' => 'schetStatus',
				'label'     => 'Текущий статус',
				'filter'=>array(
					"0" => "Все незавершенные",
					"1" => "Оформление документов",				
					"2" => "Оплата счета",				
					"3" => "Поставка по счету",				
				),
				
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					$lastOp = Yii::$app->db->createCommand(
					'SELECT max(refOp) as max_refOp from {{%schet_status}}
					  where refSchet=:refSchet', 
					[':refSchet' => $model['id'] ])->queryOne();

				
				$retVal="";
				if ($model['docStatus']>0)
				{
				  if ($model['supplyState']>0)	
				  {
					$list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =3 order BY razdelOrder')->queryAll();		
					$retVal .="<div>";
					$retVal .= "Выполнено: ".$list[$model['supplyState']-1]['Title'];
					if (count ($list) > $model['supplyState']) { $retVal.="<br> Ожидается:  ".$list[$model['supplyState']]['Title'];}
					$retVal .="</div>";
				  }			  					
				  if ($model['cashState']>0)	
				  {
					 $retVal .="<div>"; 
					$list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =2 order BY razdelOrder')->queryAll();		
					$retVal .= "Выполнено: ".$list[$model['cashState']-1]['Title'];
					if (count ($list) > $model['cashState']) { $retVal.="<br> Ожидается:  ".$list[$model['cashState']]['Title'];}					
					$retVal .="</div>";
				  }			  					
				    $retVal .="<div>";
					$list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =1 order BY razdelOrder')->queryAll();		
					$retVal .= "Выполнено: ".$list[$model['docStatus']-1]['Title'];
					if (count ($list) > $model['docStatus']) { $retVal.="<br> Ожидается:  ".$list[$model['docStatus']]['Title'];}					
					$retVal .="</div>";
					
				   return $retVal;	
				}					
				 $titleOpList = Yii::$app->db->createCommand(
				'SELECT id, opTitle, opAlter from {{%schetop}} where id>=:refOp  ORDER BY id LIMIT 2', 
					[':refOp' => $lastOp['max_refOp'] ])->queryAll();
                 				
				if (empty ($titleOpList))  {return "&nbsp;";}
							 
				 if ($model['isAlter'] == 1 )				
				 {
				 $retVal = "Выполнено: ".$titleOpList[0]['opAlter'];
				 if (count ($titleOpList) > 1) { $retVal.="<br> Ожидается:  ".$titleOpList[1]['opAlter']; }	 				
				 }
				 else
				 {
				 $retVal = "Выполнено: ".$titleOpList[0]['opTitle'];
				 if (count ($titleOpList) > 1) { $retVal.="<br> Ожидается:  ".$titleOpList[1]['opTitle']; }	 				
				 }
				 
				 return $retVal;
                },
            ],		
			
			[
                'attribute' => 'ref1C',
				'label'     => 'Счета в 1С',
                'format' => 'raw',				
                'value' => function ($model, $key, $index, $column) {
					
					if (empty($model['ref1C']) ){ $isFlg = false;}
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

			[
                'attribute' => 'ref1C',
				'label'     => 'Оплата/Поставка в 1C',
                'format' => 'raw',				
                'value' => function ($model, $key, $index, $column) {
		

				  
		
                  $ret="";				
					if ($model['schetSumm'] > 0 && $model['schetSumm'] <= $model['summOplata']){ $isFlg = true;}
					else                        { $isFlg = false;}
					
                    $ret = \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
					if ($model['summOplata'] > 0) $ret .= "&nbsp; ".$model['summOplata'];
					$ret .= "<br>";
					if ($model['schetSumm'] > 0 && $model['schetSumm'] <= $model['summSupply']){ $isFlg = true;}
					else                        { $isFlg = false;}
					
                    $ret .= \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
					if ($model['summSupply'] > 0) $ret .= "&nbsp; ".$model['summSupply'];
				return $ret;		
                },
            ],		
			
			
			[
                'attribute' => 'Продолжить',
				'label' => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
					return "<input class='btn btn-primary' style='width: 75px;'  type='button' value='Счет'  onclick='javascript:openWin(\"market/market-schet&id=".$model['id']."\")'/>";						
                    //return "<a  class='btn btn-primary' href='index.php?r=market/market-schet&id=".$model['id']."'>Счет</a>";
                },
            ],		
			
        ],
    ]
);
?>

<script type="text/javascript">
window.opener.location.reload(false); 
</script>
