<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;


if ($mode==1){$this->title = 'Регестрировать заказ';}
if ($mode==2){$this->title = 'Регестрировать контакт';}
//$this->params['breadcrumbs'][] = $this->title;

if (empty($mode)){$mode = 1;}
?>
<style>
.button {
    background-color: MediumSeaGreen;
} 
 
</style>


  <h2><?= Html::encode($this->title) ?></h2>

  
<?php
if ($mode==1)
{
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel'  => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		
			'title:raw:Название',
			'area:raw:Область',
			'city:raw:Город',            
			'razdel:raw:Раздел',		
			'contactPhone:raw:Контактный телефон',		
			'schetINN:raw:ИНН',		
            'userFIO:raw:Оператор',
			[
                'attribute' => 'id',
				'label' => 'Cоздать заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				 return "<a class='btn btn-primary' href='index.php?r=market/market-zakaz-create&id=".$model['id']."'>Заказ</a>";
				          
                },
            ],		
			

			
        ],
    ]
);
}

else {
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel'  => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		
			'title:raw:Название',
			'area:raw:Область',
			'city:raw:Город',            
			'razdel:raw:Раздел',		
			'contactPhone:raw:Контактный телефон',		
			'schetINN:raw:ИНН',		
            'userFIO:raw:Оператор',
			[
                'attribute' => 'id',
				'label' => 'Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				           return "<a class='btn btn-primary' href='index.php?r=site/reg-contact&id=".$model['id']."'>Контакт</a>";
                },
            ],		
			

			
        ],
    ]
);
}


?>
<br>
<input class="btn btn-primary button" style="width: 175px;"  type="button" value="Добавить организацию"    onclick="javascript:window.location='index.php?r=market/market-new'"/>