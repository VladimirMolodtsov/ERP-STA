<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;
if (!($curUser->roleFlg & 0x0020)) {return;}

$this->title = 'Сводная статистика контактов с клиентами';
//$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>
<style>
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkGreen ;
	font-size: 14pt;
 }
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 } 
 
.phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
	font-size: 10pt;    
}
/* кликабельный текст */
.phones {
    color:#f70;
    cursor: help
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;

		
 
</style>

<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

   	        [
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
			],	

   	        [
                'attribute' => 'all_contacts',
				'label'     => 'Общее к-во контактов',
                'format' => 'raw',
			],	

   	        [
                'attribute' => 'prev_month',
				'label'     => 'Предыдущий месяц',
                'format' => 'raw',
			],	

   	        [
                'attribute' => 'cur_month',
				'label'     => 'Текущий месяц',
                'format' => 'raw',
			],	
			
			[
                'attribute' => 'Заказы',
				'label'     => 'Заказы',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
				
                 $year = date("Y",time());
				 $prv_year=$year;
				 $month = date("m",time());
				 $prv_month = $month -1;
				 if ($prv_month == 0){$prv_month = 12;$prv_year--;}
				 
				$prev = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%zakaz}} where (month(formDate) = '.$prv_month.' and year(formDate) = '.$prv_year.') 
				and ref_user=:ref_user', [':ref_user' => $model['id']])->queryScalar();
					
				$cur = Yii::$app->db->createCommand('SELECT COUNT(id) from {{%zakaz}} where (month(formDate) = '.$month.' and year(formDate) = '.$year.') 
				and ref_user=:ref_user', [':ref_user' => $model['id']])->queryScalar();
					
				 return "За пред. месяц: ".$prev."<br> За тек. месяц: ".$cur;
				}
			],	
			
			[
                'attribute' => 'Счета',
				'label'     => 'Счета',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
					
                 $year = date("Y",time());
				 $prv_year=$year;
				 $month = date("m",time());
				 $prv_month = $month -1;
				 if ($prv_month == 0){$prv_month = 12;$prv_year--;}
	
					
				$prev = Yii::$app->db->createCommand('SELECT COUNT({{%schet}}.id) from {{%schet}},{{%zakaz}} where {{%schet}}.refZakaz ={{%zakaz}}.id 
				AND (month(formDate) = '.$prv_month.' and year(formDate) = '.$prv_year.')  and ref_user=:ref_user',
				[':ref_user' => $model['id']])->queryScalar();
					
				$cur = Yii::$app->db->createCommand('SELECT COUNT({{%schet}}.id) from {{%schet}},{{%zakaz}} where {{%schet}}.refZakaz ={{%zakaz}}.id 
				AND (month(formDate) = '.$month.' and year(formDate) = '.$year.')  and ref_user=:ref_user',
				[':ref_user' => $model['id']])->queryScalar();
					
				 return "За пред. месяц: ".$prev."<br> За тек. месяц: ".$cur;
				}
			],	
			
			
			[
                'attribute' => 'Детально',
				'label'     => 'Детально',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
				 return "<a href='index.php?r=site/stat-detail&id=".$model['id']."'>Перейти</a>";
				}
			],	
			
			

		],
    ]
);
?>

