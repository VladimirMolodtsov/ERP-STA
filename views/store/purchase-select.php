<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;
$this->title = 'Товары в закупках';

if ($curUser->roleFlg & 0x0020) 
{$this->title .= ' - управление';}


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

?>
<style>

.btn-small {	
	padding: 2px;	 
	font-size: 10pt;	
} 
 
.gridcell {
	width: 100%;		
	height: 100%;
	/*background:DarkSlateGrey;*/
}	

.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.gridcell:hover{
	background:DarkSlateGrey;
	color:#FFFFFF;
}

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.table-local
{
    padding: 2px;
    font-size: 10pt;
}
</style>

<script type="text/javascript">
function setPurch(purchaseId){
     window.parent.setPurch(purchaseId);
}
</script>	

<?php  
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            
            [
                'attribute' => 'creationDate',
				'label'     => 'Дата',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                 return date("d.m.Y",strtotime($model['creationDate'])); 
                }
            ],		
        
            [
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                /*Нет запроса - это только товар*/
                if (empty ($model['variantWareTitle']))  $showTitle = $model['wareTitle'];
                else $showTitle = "<b>".$model['variantWareTitle']."</b><br>(".mb_substr($model['wareTitle'],0,25,'utf-8')."..)"; 
                
                $id='wareTitle_'.$model['purchaseId'];
                $action = "setPurch(".$model['purchaseId'].");" ;                
                $val = \yii\helpers\Html::tag( 'div', $showTitle, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => 'width:200px;',
                   ]);

                 return $val;
               }
            ],		

            [
                'attribute' => 'sumWareCount',
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
                $strSql = 'SELECT formDate, isFormed, isActive, userFIO, title, 
                {{%schet}}.schetNum, {{%schet}}.schetDate, ifnull({{%schet}}.isReject,0) as schetReject 
                FROM ({{%zakaz}},{{%user}},{{%orglist}}) 
                LEFT JOIN {{%schet}} ON {{%schet}}.refZakaz = {{%zakaz}}.id  where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
                if(empty($dataList)) return "";                 
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret .= $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                if ( ($dataList[0]['isFormed'] == 0 && $dataList[0]['isActive'] == 0) || $dataList[0]['schetReject']) $ret = "<s>".$ret."</s>"; 
                
                
                
                return $ret;                
                }
            ],		

        
  
     
        ],
    ]
	);

 

?>   
<br>   
   
	

