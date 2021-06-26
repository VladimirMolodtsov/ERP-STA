<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */

$this->title = 'Регистрация счета';
?>
 
 <h2><?= Html::encode($this->title) ?></h2>

 <p>
 Выберите счет из списка. или перейдите к <a href="index.php?r=market/reg-manual-schet&orgId=<?=$refOrg?>&eventId=<?=$eventId?>&zakazId=<?=$zakazId?>">ручной регистрации счета</a>.
</p> 
  <?php 
  
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
	  	    [
                'attribute' => 'schetKey',
				'label'     => 'Уже синхрониз.',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {				
				
				if ($model['refSchet']>0 ){ $isFlg = true;}
				else                      { $isFlg = false;}
				
				 return  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ?  'success' : 'danger'),
                        ]
						);
				 
				}
			],	
			
	  	    [
                'attribute' => 'schetKey',
				'label'     => 'Сопоставить с 1С',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {				
				 return "<a class='btn btn-primary' href='index.php?r=data/create-sync-single-schet&schetKey=".$model['schetKey']."'>Синхронизировать</a>";
				}
			],	
			
			
        ],
		
    ]
);
?>
  
  
 