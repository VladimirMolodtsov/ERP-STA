<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */

$this->title = 'Сопоставление счета';
?>
 
 <h2><?= Html::encode($this->title) ?></h2>
 
 <pre>
  <?php 
  /*Добавим идентификатор синхронизируемого счета*/
/*  for ($i=0;$i<count($schetList); $i++)
  {	  
	$schetList[$i]['schetId']=$schetId;	
  }
  */
//  print_r($schetList);
  
  
		$provider = new ArrayDataProvider([
            'allModels' => $schetList,
			'sort' => [ 
                'attributes' => [
				'schetNum', 
				'schetINN', 
				'orgTitle', 
				'sum',
				'date'
				],
            ],
            'pagination' => [ 
                'pageSize' => 10, 
            ],
        ]);
  ?>
 </pre>
  
  <?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],		    
            'schetNum:raw:Счет №',
			'date:raw:Дата счета',			 
            'orgTitle:raw:Клиент',
			'schetINN:raw:ИНН',
			'sum:raw:На сумму',
			'schetKey:raw:ключ',
	  	    [
                'attribute' => 'schetKey',
				'label'     => 'Сопоставить с 1С',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {				
				 return "<a href='index.php?r=data/sync-single-schet&schetKey=".$model['schetKey']."'>Синхронизировать</a>";
				}
			],	
			
			
        ],
		
    ]
);
?>
  
  
 