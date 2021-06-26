<br>&nbsp;<br>
<div class="part-header"> Счета и заявки</div>     
<?php Pjax::begin(); ?>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $activityProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

  	       [
                'attribute' => 'id',
				'label'     => 'Заявка',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
				 $rval="№ ".$model['id'].' от '.date("d.m.Y", strtotime($model['formDate']));
				 return $rval;
				}
			],	
			
			[
                'attribute' => 'schetNum',
				'label'     => 'Счет',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
				 $rval="№ ".$model['schetNum'].' от '.date("d.m.Y", strtotime($model['schetDate']));
				 return $rval;
				}
			],	
							
			[
                'attribute' => 'Товар',
				'label'     => 'Товар',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
					
				$ret =  Yii::$app->db->createCommand('SELECT good, count from {{%zakazContent}}  where  {{%zakazContent}}.isActive=1 AND refZakaz=:refZakaz '
		                                     ,[':refZakaz'=>$model['id']] )->queryAll();       
	            $rval="";
				for ($i=0; $i<count($ret); $i++)	
				{
					if ($i> 2){$rval.="..."; break;}
					if ($i > 0) $rval.="<br>";
					$rval.=$ret[$i]['good'];
				}								 
				 return $rval;
				}
			],	
			
			
			[
                'attribute' => 'isSchetActive',
				'label'     => 'В работу',
                'format' => 'raw',
				
				'value' => function ($model, $key, $index, $column) {
					
					/*счета нет*/
					if(empty($model['schetNum']))
					{
						if ($model['isActive'] == 0)
							return "Заявка не активна";							
												
						/*Товар в резерве но счета нет - регистрация счета*/
 						if ($model['isGoodReserved'] == 1)
							return "<a  class='btn btn-primary' href='index.php?r=market/market-reg-schet&orgId=".$model['refOrg']."&zakazId=".$model['id']."'>Регистрировать</a>";						

						if ($model['isFormed'] == 1)
							return "<a class='btn btn-primary' href='index.php?r=market/market-reserve-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['id']."'>Резервирование</a>";						
						
							return "<a class='btn btn-primary' href='index.php?r=market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['id']."'> Заявка</a>";												
					}
				
					if($model['isSchetActive'] == 0)
					{
					    return "Работа по счету завершена";							
					}
				 								
				 return "<a href='index.php?r=market/market-schet&id=".$model['schetId']."'> К Счету </a>";
				}
			],	

			
			

		],
    ]
);
?>
<?php Pjax::end(); ?>


<br>&nbsp;<br>
<?php Pjax::begin(); ?>
<div class="part-header"> Список предыдущих контактов</div>   
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $contactProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            [
                'attribute' => 'grd_contactDate',
				'label' => 'Дата контакта',
                'format' => ['datetime', 'php:Y-m-d h:i:s'],
            ],
		
            'grd_contactFIO:raw:Контактное лицо',
            'grd_phone:raw:Телефон',
			'grd_note:raw:Комментарий',
			'grd_userFIO:raw:Менеджер',
        ],
    ]
);
?>
<?php Pjax::end(); ?>
