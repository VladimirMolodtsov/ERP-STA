<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;

$this->title = 'Результат поиска';
//$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');


if (empty($mode)){$mode = 1;}
?>
<style>
.button {
    background-color: MediumSeaGreen;
} 
 
</style>


  <h2><?= Html::encode($this->title) ?></h2>

  
<?php
//print_r ( $provider->sql);

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel'  => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [

            [
                'attribute' => 'id',
                'label' => '#',
                'format' => 'raw',
                ],        

        
            [
                'attribute' => 'title',
                'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                    $s=""; 
                    if ($model['isOrgActive'] == 0)  $s='font-weight:bold;text-decoration: line-through;color:Crimson';                                  
               
                $action="openWin('site/org-detail&orgId=".$model['id']."','orgDetail');"; 
                $val = \yii\helpers\Html::tag( 'div', $model['title'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,                     
                     'style'   => $s,
                   ]);
                 return $val;
                },
            ],        

            [
                'attribute' => '',
                'label' => 'Адрес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    

               $list = Yii::$app->db->createCommand('SELECT area, city from {{%adreslist}} where ref_org=:ref_org', 
                [':ref_org' => $model['id'],    ])->queryAll();
                $val="";
                for($i=0;$i<count($list);$i++ )
                {
                    $val.= $list[$i]['area']." ". $list[$i]['city']."<br>";
                }
                return $val;
                },
            ],        

            

            
            'razdel:raw:Раздел',        
            'contactPhone:raw:Контактный телефон',        
            'contactEmail:raw:Эл. почта',                    
            'schetINN:raw:ИНН',        
            'userFIO:raw:Оператор',
            

            
        ],
    ]
);



?>
