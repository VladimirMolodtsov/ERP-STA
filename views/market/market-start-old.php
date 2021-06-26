<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\SqlDataProvider;


$this->title = 'Активные продажи';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;

$currentlyInWork= $model->getCurrentlyInWork();
?>
<style>
.button {
    background-color: #e7e7e7;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px px;
	width: 150px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 5px;
}
 .button_menu{
    padding: 15px;	 
 }
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: SlateBlue;
	font-size: 14pt;
 }
 
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 }
 
 
  .detail_title {
    margin:5px 0px;
    padding:10px;
	font-size: 10pt;    
}
  .detail_text {
    margin:5px 0px;
    padding:10px;
    border:1px solid #ffbc80;
    background: Beige;
	font-size: 10pt;    
}
 
</style>


 <h2><?= Html::encode($this->title) ?></h2>
 <p>Менеджер <?= Html::encode($curUser->userFIO)?></p> 
 <div class="part-header"> В работе </div>	
 <br>
 <p>Всего в работе<sup>*</sup> <?= Html::encode($currentlyInWork) ?></p>
 <div class="detail_text" ><sup>*</sup> Число организаций взятых в работу менеджером, которым еще не выставлен счет.</div>
 <br>
 <div>
 
 <?php
 if ($currentlyInWork >0){
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getInWorkProvider(),
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            'title:raw:Организация',
            [
                'attribute' => 'contactDate',
				'label'     => 'Дата контакта',
                'format' => ['datetime', 'php:d-m-Y'],
            ],			
			'note:raw:Комментарий',
			[
                'attribute' => 'id',
				'label'     => 'Продолжить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=market/market-schet&id=".$model['id']."'>Продолжить</a>";
                },
            ],		
			[
                'attribute' => 'Отказаться',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=market/market-schet-reject&id=".$model['id']."'>Отказаться</a>";
                },
            ],		
			
        ],
    ]
);
 }
?>
   
 </div> 
 <div class="part-header"> Доступно</div>	
 <br>
 <p>Не взято в работу: <?= Html::encode($model->getCurrentlyNotInWork()) ?>, &nbsp;
 <input class="button" type="button" value="Взять в работу" onclick="javascript:window.location='index.php?r=market/market-select'"/></p>

