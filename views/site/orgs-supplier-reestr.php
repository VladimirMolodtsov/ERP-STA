<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}

$this->title = 'Реестр работы с поставщиком';

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
                'attribute' => 'dateCreation',
				'label'     => 'Закупка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($orgRef) {				
                    if (empty($model['refPurchase']) )return "<font color='Crimson'>N/A</font><br>";
                    
                    $action="onclick=\"openWin('store/purchase&id=".$model['refPurchase']."','purchaseWin');\"";
                        
                    $retValue= "<div class='child' ".$action.">".date('d.m.Y', strtotime($model['dateCreation']))."<br> №".$model['refPurchase']."</div>";
    			    return $retValue;
				}
            ],
            

   	        [
                'attribute' => 'creditDate',
				'label'     => 'Приход товара/<br> Мы должны',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                    if (empty($model['credit']) )return "";
                    $r = "На сумму: <br>";                    
					return $r.number_format($model['credit'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['creditDate']));
                    
			   },

			],	

   	        [
                'attribute' => 'debetDate',
				'label'     => 'Оплата товара/<br> Нам должны',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {				
                    if (empty($model['debet']) )return "";                                        
                    $r = "На сумму: <br>";                    
					return $r.number_format($model['debet'],2,'.','&nbsp;')."&nbsp;руб<br>".date('d.m.Y', strtotime($model['debetDate']));
                                    return;                    
			   },
			],	
	        
            
			
   	        [
                'attribute' => 'schetDate',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {				
                    if (empty($model['refSchet']) )
                        return "<font color='Crimson'>N/A</font><br>".date('d.m.Y', strtotime($model['schetDate']))."<br> №".$model['schetNum'];
                    
                    //$action=" onclick=\"openWin('market/market-schet&id=".$model['refSchet']."','schetWin');\"";
                    
                    if (empty($model['schetSumm']))
                    {
                    $strSql  = "SELECT sum(goodSumm) from {{%supplier_schet_content}}";
                    $strSql .= "where schetRef = :refSchet ";
                    $sum = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryScalar();
                    $sum ="<font color='Brown'>".number_format($sum,2,'.','&nbsp;')."&nbsp;руб.</font><br>";
                    }
                        else $sum =number_format($model['schetSumm'],2,'.','&nbsp;')."&nbsp;руб.<br>";                        
                        $retValue= "<div class='child' ".$action.">".$sum.date('d.m.Y', strtotime($model['schetDate']))."<br> №".$model['schetNum']."</div>";                        
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
                'attribute' => 'supplierRef1C',
				'label'     => 'счет в 1С',
                'encodeLabel' => false,
                'format' => 'raw',               
			],	

            

            
			[
	            'attribute' => 'showGood',
				'label'     => 'Товары',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                if (!empty($model['refSchet']) ){
                $strSql  = "SELECT DISTINCT goodTitle from {{%supplier_schet_content}}";
                $strSql .= "where {{%supplier_schet_content}}.schetRef = :refSchet ";
				$resList = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++)
                {
                    if (empty($resList[$i]['goodTitle'])) continue;
                    $ret.= $resList[$i]['goodTitle']."<br>\n";
                }
                        
                 return $ret;
                }
                
                
                

              }
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

