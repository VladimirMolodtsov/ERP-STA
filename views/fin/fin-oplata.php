<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Оплаты по счетам.';



?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>

<a href="#" onclick="openEditWin('index.php?r=fin/fin-oplata&<?= Yii::$app->request->queryString  ?>&format=csv');"> Выгрузить</a> 

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
     	    /*[
                'attribute' => 'id',
				'label'     => 'id',
                'format' => 'raw',
            ],	*/	
                 
     	    [
                'attribute' => 'oplateSumm',
				'label'     => 'Сумма',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'oplateYear',
				'label'     => 'Год платежа',
                'format' => 'raw',
            ],		

       	    [
                'attribute' => 'oplateMonth',
				'label'     => 'Месяц платежа',
             	'filter'=>array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' ),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                 
                $monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    
			    return  $monthList[$model['oplateMonth']];
                },
     
            ],		

            
     	    [
                'attribute' => 'oplateDate',
				'label'     => 'Дата платежа',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'oplateNum',
				'label'     => 'Номер',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		

     	    [
                'attribute' => 'refSchet',
				'label'     => 'Привязан',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return  "счет № ".$model['schetNum']." от: ".$model['schetDate']." <br>клиент: <b>".$model['title']."</b><br> Менеджер: <b>".$model['userFIO']."</b>";
                },

            ],		
            
			[
                'attribute' => 'remove',
				'label' => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	
				$refSchet =0;
                if (empty($model['refSchet'])) $refSchet=0;				
                else $refSchet=$model['refSchet'];
				return "<button  class='btn btn-default'  type=button aria-label='Удалить' 
						onclick='javascript:if (confirm(\"Отменить удаление невозможно! Удалять?\"))
                        {
                            openSwitchWin(\"fin/fin-oplata-remove&oplataId=".$model['id']."&refSchet=".$refSchet."\");
                        }'><span class='glyphicon glyphicon-remove-circle' style='color:Crimson' aria-hidden='true'></span>
                        </button>"; 
				

                },
            ],		
            
            
            
            
            
        ],
    ]
	);
?>

