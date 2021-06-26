<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\WarehouseForm;

$this->title = 'Наполнение склада';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>
<style>
.otves {
    background-color: Green ;
	//width: 50px;
	font-size: 10px;
	margin:4px;
	padding:4px;
} 

.small_btn {
    //background-color: Green ;
	//width: 50px;
	font-size: 10px;
	margin:4px;
	padding:4px;
} 

.inuse {
    background-color:  Brown;
	//width: 30px;
	font-size: 10px;
	margin:4px;
	padding:4px;
} 

.myuse {
    background-color:  GoldenRod ;
	//width: 30px;
	font-size: 10px;
	margin:4px;
	padding:4px;
} 

</style>
  
<script>
function ShowOtvesWin(id)
{
  wid=window.open("index.php?r=store/show-otves&id="+id,'addwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600');   
  window.wid.focus();
}
</script>

<?php


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'title',
				'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return $model['title'];
                },
            ],		

			[
                'attribute' => 'grpGood',
				'label' => 'Товарная группа',
                'format' => 'raw',
				'filter' => $model->getGrpGroup(),
				
            ],		
			
            [
	
                'attribute' => 'isValid',
				'label'     => 'Валидно',                
				'format' => 'raw',
				'filter'=>array("1"=>"Да","2"=>"Все"),
                'value' => function ($model, $key, $index, $column) {					
				
				    if ($model['isValid'] >0 ){ $isFlg = true;}
					else                      { $isFlg = false;}
                    return  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
				
				 
                },

            ],
			
			
            [
	
                'attribute' => 'isOtves',
				'label'     => 'Отвесы',                
				'format' => 'raw',
				//'filter'=>array("1"=>"с","2"=>"Все"),
                'value' => function ($model, $key, $index, $column) {					
				
				    if ($model['isOtves'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    $val=  \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },

            ],

            [
                'attribute' => 'amount',
				'label'     => 'Остаток',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
				
				  $val="<nobr>Всего: ".$model['amount']."</nobr>";
                  if ($model['isOtves'] == 0) {
	
					$reserved = Yii::$app->db->createCommand(
					'Select sum(reserved), good from {{%zakazContent}} join {{%zakaz}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
					 left join {{%schet}} on {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz where 
					 ({{%zakaz}}.isActive = 1 OR {{%schet}}.isSchetActive = 1) and good = :good
					 group by good')
					->bindValue(':good', $model['title'])										
					->queryScalar();
					
				  $val="<nobr>На складе: ".$model['amount']."</nobr>";
				  $val.="<br><nobr>В резервах: ".$reserved."</nobr>";
				  $val.="<br><nobr>Доступно: ".($model['amount'] - $reserved)."</nobr>";
	
					  
					  return $val;
					  }
										
				$sumOtv = Yii::$app->db->createCommand(
					'SELECT sum(size) FROM {{%otves_list}} where refWarehouse=:refWarehouse ')
					->bindValue(':refWarehouse', $model['id'])					
					->queryScalar();
										
				//$sumOtv = $model['reserved'];
					
				  $val.="<br><nobr>В отвесах: ".$sumOtv."</nobr>";
					
				  $val.="<br><nobr>Не распределено: ".($model['amount'] - $sumOtv)."</nobr>";

                return $val;
				}
				
            ],

            [
                'attribute' => 'ed',
				'label'     => 'Ед. изм',                
                'format' => 'raw',
            ],
            [
                'attribute' => 'price',
				'label'     => 'Цена',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				 return 1.2*$model['price'];
                }
            ],

            [
                'attribute' => 'isOtves',
				'label'     => 'Список выделенных отвесов',                
                'format' => 'raw',

                'value' => function ($model, $key, $index, $column) {
					if ($model['isOtves'] == 0) {return;}
					$val="";
					$curUser=Yii::$app->user->identity;
					$list = Yii::$app->db->createCommand(
					'SELECT id, size, refManager, inUse FROM {{%otves_list}} where refWarehouse=:refWarehouse  ORDER BY size')
					->bindValue(':refWarehouse', $model['id'])					
					->queryAll();
			
			
					for($i=0; $i < count ($list); $i++)
					{
						if ($list[$i]['inUse'] == 1 ) 
						{	
							$style='inuse';
							if ($curUser->id == $list[$i]['refManager']) $style='myuse';
							else				$style='inuse';
						}
						else                          $style='otves';
											
						 $val.=/*$list[$i]['id']. */" <input class='btn btn-primary ".$style."' type=button value='".$list[$i]['size']."' onclick='javascript:ShowOtvesWin(".$list[$i]['id'].");'>";		
					}
		 
					
                return $val;
				}
				
            ],

			
            
        ],
    ]
);

?>