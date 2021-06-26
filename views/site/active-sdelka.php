<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

use kartik\date\DatePicker;
use kartik\time\TimePicker;



$curUser=Yii::$app->user->identity;
$this->title = 'Контакт с контрагентом';
//$this->params['breadcrumbs'][] = $this->title;
$record=$orgModel->loadOrgRecord();

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style> 

.child {
  //padding:5px;
 // text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 background-color: LightGreen ;
 cursor:pointer;
}
.part-header
{
    padding: 2px;	 
	color: Black;
	text-align: right;    
	background-color: LightBlue ;
	font-size: 11pt;
    font-weight: Bold;
}

</style>

<script type="text/javascript">
</script>
<div class="part-header"> Список активных сделок</div>   
<?php Pjax::begin(); ?>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $activityProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'attribute' => 'zakazId',
                'label'     => 'Заказ',
                'format' => 'raw',            
                'contentOptions' =>['style'=>'padding:0px;'],
                
                'value' => function ($model, $key, $index, $column) {                    

                $action=" onclick=\"openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['zakazId']."','zakazWin');\"";
                return "<div class='child' ".$action." >".$model['zakazId']." от ". date("d.m.y", strtotime($model['formDate']))."</div>";                                           
                },
            ],            

            [
                'attribute' => 'schetId',
                'label'     => 'Счет',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {                                    
                 
                 $action=" onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');\"";
                 
                 if (empty($model['schetId'])) return "&nbsp;";                 
                 if (empty($model['ref1C'])) $ret= "<div class='child' ".$action." style='background-color:Yellow'>";
                                        else $ret= "<div class='child' ".$action." style='background-color:LightGreen'>";
                 
                 $ret.= $model['schetNum']."&nbsp;от&nbsp;". date("d.m.y", strtotime($model['schetDate']));                                           
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
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div class='child' style='background-color:LightGreen'>"; 
                                                            else $ret= "<div class='child' style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.y", strtotime($listData[0]['lastOplate']));                                                              
                  $ret.="</div>";
                 return $ret;                  
                },
            ],            
            
            [
                'attribute' => 'lastSupply',
                'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();

                if (count($listData)==0) return "&nbsp;";                 
                if($listData[0]['sumSupply'] == 0)return "&nbsp;";                 

                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div class='child' style='background-color:LightGreen'>"; 
                                                                 else $ret= "<div class='child' style='background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.y", strtotime($listData[0]['lastSupply']));                                                              
                  $ret.="</div>";
                 return $ret;                  
                },
            ],            

            
            /****/
        ],
    ]
); 
?>

<?php Pjax::end(); ?>
