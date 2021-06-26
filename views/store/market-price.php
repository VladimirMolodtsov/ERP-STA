<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\WarehouseForm;

$this->title = 'Формирование заявки/резервирование';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

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
	font-size: 12px;
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

function setEnableWin(id, reserved)
{
  wid=window.open("index.php?r=store/reserve-otves&zakazId=<?= Html::encode($zakazId) ?>&id="+id+"&reserved="+reserved,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
 //window.wid.focus();
}

function setDisableWin(id, reserved)
{
  wid=window.open("index.php?r=store/unreserve-otves&zakazId=<?= Html::encode($zakazId) ?>&id="+id+"&reserved="+reserved,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
  //window.wid.focus();
}

function setReserveWin(id,zakazId,cnt)
{
  wid=window.open("index.php?r=store/set-reserve&id="+id+"&zakazId="+zakazId+"&cnt="+cnt,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600');   
  window.wid.focus();
}

</script>
 
<div class='row'>
  <div class='col-md-7 col-xs-5'>
  </div>
  
  <div class='col-md-2 col-xs-3'>    
	<input class="btn btn-primary"  style="width: 150px; background-color: ForestGreen;" type="button" value="Синхронизировать" onclick="javascript:openEditWin('market/sync-price');"/>
  </div>  

  <div class='col-md-2 col-xs-3'>  
	<a  class='btn btn-primary' href="index.php?r=market/market-zakaz&zakazId=<?=$zakazId?>&orgId=<?=$orgId?>" >Выйти и обновить заявку</a>    
  </div>
</div>
<div class='col-md-1 col-xs-1'>
 </div>
  
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
				if ($model['relizePrice'] > $model['marketPrice'])	 return $model['relizePrice'];
															else 	 return $model['marketPrice'];
                }
            ],

            [
                'attribute' => 'Зарезервировано',
				'label'     => 'Зарезер.',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($zakazId) {								
					
				$list = Yii::$app->db->createCommand(
					'Select reserved,count from {{%zakazContent}} join {{%zakaz}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
					 where good = :good and {{%zakaz}}.id = :zakazId
					 group by good')
					->bindValue(':good', $model['title'])										
					->bindValue(':zakazId', $zakazId)
					->queryAll();
					if (empty($list[0]['reserved']))
					{						
						if (empty($list[0]['count']))return "&nbsp;";
						return "Резерв 0 <br> Заявка: ".$list[0]['count'];
					}
					return $list[0]['reserved'];					
                }
            ],
			
            [
                'attribute' => 'Резервирование',
				'label'     => 'Резервирование',                
                'format' => 'raw',
				
				
				
                'value' => function ($model, $key, $index, $column) use ($zakazId) {
					
					
					$val="";
					
					$list = Yii::$app->db->createCommand(
					'Select reserved,count from {{%zakazContent}} join {{%zakaz}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
					 where good = :good and {{%zakaz}}.id = :zakazId
					 group by good')
					->bindValue(':good', $model['title'])										
					->bindValue(':zakazId', $zakazId)
					->queryAll();
		
					$count =0;
					if (empty($list[0]['reserved']))
					{
						if (empty($list[0]['count']))$count=0;
						else $count=$list[0]['count'];	
					}
					else
					{
						$count=	$list[0]['reserved'];
					}

					
					if ($model['isOtves'] == 0) 
					{			
				
					$val.=" <input class='btn btn-primary small_btn' type=button value='Резерв' onclick='javascript:setReserveWin(".$model['id'].",".$zakazId.",".$count." );'>";			
					return $val;
					}
					
					
					$list = Yii::$app->db->createCommand(
					'SELECT id, size, refZakaz, inUse FROM {{%otves_list}} where refWarehouse=:refWarehouse 
					AND (inUse =0 OR refZakaz=:refZakaz)
					ORDER BY size')
					->bindValue(':refWarehouse', $model['id'])					
					->bindValue(':refZakaz', $zakazId)					
					->queryAll();
			
					$free=0;
					for($i=0; $i < count ($list); $i++)
					{
						if ($list[$i]['inUse'] == 1 )
						{
							$style='inuse';
							$val.=" <input class='btn btn-primary ".$style."' type=button value='".$list[$i]['size']."' onclick='javascript:setDisableWin(".$list[$i]['id'].",".$count.");'>";								
						}
						else {
							$free++;
							$style='otves';
							$val.=" <input class='btn btn-primary ".$style."' type=button value='".$list[$i]['size']."' onclick='javascript:setEnableWin(".$list[$i]['id'].",".$count.");'>";								
						}																	
					}
					
					if ($free==0)
						$val.=" <input class='btn btn-primary small_btn' type=button value='Резерв' onclick='javascript:setReserveWin(".$model['id'].",".$zakazId.",".$count." );'>";								
                return $val;
				}
				
            ],

			
            
        ],
    ]
);

?>