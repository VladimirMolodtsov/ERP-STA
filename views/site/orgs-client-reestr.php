<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}

$this->title = 'Реестр работы с клиентом';

$this->registerCssFile('@web/phone.css');
$this->registerJsFile('@web/phone.js');
?>
  <h2><?= $this->title ?> <a href=index.php?r=site/org-detail&orgId=<?=$orgRecord->id?> > "<?=$orgRecord->title ?>" </a></h2>


<style>
.btn-local
{
  padding: 2px;
  font-size: 10pt;
  width: 75px;	
  float:right;
}
.child {
  padding:0px;
  text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}
</style>



<?php
			$orgRef = $orgRecord->id;
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
       // 'filterModel' => $model,	
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            
   	        [
                'attribute' => 'operation',
				'label'     => 'Операция',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  if ( empty($model['refSchet']) ) return $model['operation'];
                  return $model['operation']."<br>(ID#&nbsp;".$model['refSchet'].")";  
                }
			],	
            

   	        [
                'attribute' => 'creditDate',
				'label'     => 'Кредит/<br> Мы должны',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                    if (empty($model['credit']) )return "";
                    if ($model['operation'] == 'Продажа' ) 
                    {    
                    if ( !empty($model['refSchet']) ) 
                    {                    
                        $action=" onclick=\"openWin('fin/oplata-list&schetId=".$model['refSchet']."','detailWin');\"";
                        $r ="<div class='child' ".$action.">";                        
                    }
                    else $r ="<div>";                                            
                    $r .= "Оплачено: <br> ";
                    $r .= number_format($model['credit'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['creditDate']));
                    $r .= "</div>";
                    return $r;
                    }
            

                    if ($model['operation'] == 'Закупка' ) {$r = "Получено товара: <br>";
                    
					return $r.number_format($model['credit'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['creditDate']));
                    }
                    return;
			   },

			],	

   	        [
                'attribute' => 'debetDate',
				'label'     => 'Дебет/<br> Нам должны',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                    if (empty($model['debet']) )return "";                    
                    if ($model['operation'] == 'Продажа' ) 
                    {    
                    if ( !empty($model['refSchet']) ) 
                    {                    
                        $action=" onclick=\"openWin('fin/supply-list&schetId=".$model['refSchet']."','detailWin');\"";
                        $r ="<div class='child' ".$action.">";                        
                    }
                    else $r ="<div>";                                            
                    $r .= "Отгружено: <br> ";
                    $r .= number_format($model['debet'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['debetDate']));
                    $r .= "</div>";
                    return $r;
                    }
            
                    
                    
                    if ($model['operation'] == 'Закупка' ) {$r = "Оплачено товара: <br>";                    
					return $r.number_format($model['debet'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['debetDate']));
                    }
                return;                    
			   },
			],	
	        
   	        [
                'attribute' => 'zakazDate',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($orgRef) {				
                    if (empty($model['zakazNum']) )return "<font color='Crimson'>N/A</font><br>";
                    
                    $action=" onclick=\"openWin('market/market-zakaz&orgId=".$orgRef."&zakazId=".$model['zakazNum']."','schetWin');\"";
                        
                    $retValue= "<div class='child' ".$action.">".date('d.m.Y', strtotime($model['zakazDate']))."<br> №".$model['zakazNum']."</div>";
                     if ($model['zakazIsActive']==0 && $model['zakazIsFormed']==0) $retValue.="<br><span class='label label-danger'>Отказ по счету</span>";                        
					    return $retValue;
				}
            ],
            
			
   	        [
                'attribute' => 'schetDate',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {				
                    if (empty($model['refSchet']) )
                        return "<font color='Crimson'>N/A</font><br>".date('d.m.Y', strtotime($model['schetDate']))."<br> №".$model['schetNum'];
                    
                    $action=" onclick=\"openWin('market/market-schet&id=".$model['refSchet']."','schetWin');\"";
                    
                    if (empty($model['schetSumm']))
                    {
                    $strSql  = "SELECT sum(count*value) from {{%schet}}, {{%zakazContent}} ";
                    $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND  {{%schet}}.id = :refSchet ";
                    $sum = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryScalar();
                     $sum ="<font color='Brown'>".number_format($sum,2,'.','&nbsp;')."&nbsp;руб.</font><br>";
                    }
                        else $sum =number_format($model['schetSumm'],2,'.','&nbsp;')."&nbsp;руб.<br>";
                        
                        $retValue= "<div class='child' ".$action.">".$sum.date('d.m.Y', strtotime($model['schetDate']))."<br> №".$model['schetNum']."</div>";
                        if ($model['isReject']==1) $retValue.="<br><span class='label label-danger'>Отказ по счету</span>";                        
					    return $retValue;
				}

			],	
						

    	    [
                'attribute' => 'refSchet',
				'label'     => 'Привязан <br> к счету',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                    if (empty($model['refSchet']) ){ $isFlg = false;}
                    else                        { $isFlg = true;}
                     $retVal= \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
                        );								
                        
                return $retVal;
			   },
			],	

    	    [
                'attribute' => 'ref1C',
				'label'     => 'Синхрон. <br> с 1С',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                    if (empty($model['ref1C']) ){ $isFlg = false;}
                    else                        { $isFlg = true;}
                     $retVal= \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
                        );								

                 if ($isFlg) $retVal.= "<br>&nbsp;<br> <input class='btn btn-primary btn-local' type='button' value='Сбросить' 
                onclick=\"javascript:openSwitchWin('site/schet-rm-ref&schetRef=".$model['refSchet']."');\"/>";                        
                return $retVal;
			   },
			],	

            
			[
	            'attribute' => 'showGood',
				'label'     => 'Товары',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                    
                
                /**/
                if ($model['operation'] == 'Продажа')
                {
                if (empty($model['refSchet']) )return "N/A";    
                $strSql  = "SELECT DISTINCT good from {{%schet}}, {{%zakazContent}} ";
                $strSql .= "where {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz AND {{%zakazContent}}.isActive =1  AND  {{%schet}}.id = :refSchet ";
				$resList = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++)
                {
                    if (empty($resList[$i]['good'])) continue;
                    $ret.= $resList[$i]['good']."<br>\n";
                }
                        
                 return $ret;
				}
              }
            ],
			
			[
	            'attribute' => 'Отгрузка',
				'label'     => 'Отгрузка',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {

                if (empty($model['refSchet']) )return "N/A";   
                $ret="";
                $list = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet',
                [':refSchet' => $model['refSchet'] ])->queryAll();
                if (count ($list) == 0 ) return "Нет запроса ";
                
                $action=" onclick=\"openWin('store/supply-request-new&viewMode=acceptRequest&id=".$list[0]['id']."','detailWin');\"";
                $ret.= "<div class='child' ".$action.">Запрос на поставку № ".$list[0]['id']." от ".date ('d.m.Y', strtotime($list[0]['requestDate']))."</div>";   
                
                /**/    
                                
                $list = Yii::$app->db->createCommand('SELECT id, requestNum, creationDate, requestDatePlanned, deliverSum, isFinished  FROM {{%request_deliver}} where refSchet =:refSchet',
                [':refSchet' => $model['refSchet'] ])->queryAll();
                if (count ($list) == 0 ) return $ret."<div>Нет отгрузки</div>";
                for ($i=0; $i < count ($list ); $i++ ) 
                {                    
                   $action=" onclick=\"openWin('store/deliver-zakaz&id=".$list[0]['id']."','detailWin');\"";

                    $ret.="<div class='child' ".$action.">Задание № ".$list[$i]['requestNum']." от ".date ('d.m.Y', strtotime($list[$i]['creationDate']))."<br> на сумму ".$list[$i]['deliverSum'];
                    if ($list[$i]['isFinished'] == 1 ) $ret.=" <font color='ForestGreen' >Выполнено </font>";                    
                    $ret.="</div>";                     
                }
                return $ret;                
                                 
				}
                /**/

              
            ],

			
	     ]
    ]		 
);

if (!empty($model->debug))
{
	echo "<pre>";
	print_r ($model->debug);
	echo "</pre>";
}

?>

