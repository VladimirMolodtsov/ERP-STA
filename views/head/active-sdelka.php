<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;


$this->title = 'Не завершенные счета';



?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>

</style>

<script type="text/javascript">

</script>


<h3><?= Html::encode($this->title) ?></h3>


<div class='row'>
<div class="col-md-10">
</div>   

<div class="col-md-2">
    <a href="#" onclick="openWin('index.php?r=head/active-sdelka&<?= Yii::$app->request->queryString  ?>&format=csv&noframe=1','childWin');">Выгрузить</a> 
</div>   
   
   
</div>      

<table border='0' width='100%'>
<tr>
    <td width='600px'>
 <?php 
    
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,
        //'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
     

            [
                'attribute' => 'zakazdate',
                'label'     => 'Заявка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                //refZakaz
                 return date("d.m.Y", strtotime($model['zakazdate']));
                }                                
                
            ],        

            [
                'attribute' => 'schetDate',
                'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                //refSchet
                 if (empty ($model['refSchet'])) return "";
                 return date("d.m.Y", strtotime($model['schetDate']));
                }                                
                
            ],        

            [
                'attribute' => 'oplDate',
                'label'     => 'Оплата',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                 if ($model['oplSum'] < 0.1) return "&nbsp;";   
                 return date("d.m.Y", strtotime($model['oplDate']));
                }                                
                
            ],        

            [
                'attribute' => 'oplSum',
                'label'     => 'Приход',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {   
                if ($model['oplSum'] < 0.1) return "&nbsp;";                
                 return number_format($model['oplSum'],2,".","&nbsp;");
                }                                
                
            ],        

            [
                'attribute' => 'requestDate',
                'label'     => 'Заяв. отгр.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                $requestTime=strtotime($model['supDate']);
                if ($requestTime < 1000 ) return "&nbsp;";
                
                 return date("d.m.Y", $requestTime);               
                }                                
                
            ],        
            
            
            [
                'attribute' => 'supDate',
                'label'     => 'Отгрузка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                if ($model['supSum'] < 0.1) return "&nbsp;";
                 return date("d.m.Y", strtotime($model['supDate']));
                }                                
                
            ],        

            [
                'attribute' => 'supSum',
                'label'     => 'Отгружено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                if ($model['supSum'] < 0.1) return "&nbsp;";    
                 return number_format($model['supSum'],2,".","&nbsp;");
                }                                
                
            ],        

            [
                'attribute' => '-',
                'label'     => 'Договор',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
               
                $list = Yii::$app->db->createCommand(" SELECT internalNumber, dateStart
                FROM {{%contracts}}  where refOrg = :refOrg ORDER BY internalNumber DESC",
                [':refOrg' => $model['refOrg']]
                )->queryAll();       
                                                
                if (count ($list) == 0 ) return "&nbsp;";                    
                $stTime = strtotime($list[0]['dateStart']);
                if ($stTime < 1000) return $list[0]['internalNumber']." n/a ";                
                return $list[0]['internalNumber']." ".date("d.m.Y", strtotime($list[0]['dateStart']));                
                }                                
                
            ],        

            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',
                
            ],        

            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',
                
            ],        
         
            
      ]//columns            
    ]
    );
?>
    
<pre>
<?php
//print_r($model->debug);
?>
</pre>
