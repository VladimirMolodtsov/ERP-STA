<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Резервирование товара';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>

<p> 
Клиент: <?= Html::encode($zakazRecord['title']) ?> <br>
Заказ №: <?= Html::encode($zakazRecord['id']) ?> 
от: <?= date("d.m.Y", strtotime($zakazRecord['formDate'])) ?> 
</p>
<style>
.otves {
    background-color: Green ;
	//width: 50px;
	font-size: 10px;
	margin:2px;
	padding:2px;
} 

.small_btn {
    //background-color: Green ;
	//width: 50px;
	font-size: 10px;
	margin:2px;
	padding:2px;
} 

.inuse {
    background-color:  Brown;
	//width: 30px;
	font-size: 10px;
	margin:2px;
	padding:2px;
} 

</style>
  
<script>
function setEnableWin(zakazContentId,id)
{
  wid=window.open("index.php?r=store/reserve-otves&zakazContentId="+zakazContentId+"&zakazId=<?= Html::encode($zakazRecord['id']) ?>&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
 //window.wid.focus();
}

function setDisableWin(zakazContentId,id)
{
  wid=window.open("index.php?r=store/unreserve-otves&zakazContentId="+zakazContentId+"&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50');   
  //window.wid.focus();
}

function setReserveWin(id,zakazContentId,cnt)
{
  wid=window.open("index.php?r=store/set-reserve&id="+id+"&zakazContentId="+zakazContentId+"&cnt="+cnt,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600');   
  window.wid.focus();
}
</script>
 
  
  
<?php


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
	//	'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
						
			[
                'attribute' => 'good',
				'label' => 'Товар в заказе',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
				{					
				   return $model['good'];
                },
            ],		

			[
                'attribute' => 'count',
				'label'     => 'Количество в заказе',                
                'format' => 'raw',
            ],

			[
                'attribute' => 'zakazReserved',
				'label'     => 'Зарезирвировано',                
                'format' => 'raw',
            ],

            [
                'attribute' => 'amount',
				'label'     => 'Остаток на складе',                
                'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
				
				if(empty($model['price'])){return "<font color='crimson'>Товар не найден</font>";}
				
				
					$reserved = Yii::$app->db->createCommand(
					'Select sum(reserved), good from {{%zakazContent}} join {{%zakaz}} on {{%zakaz}}.id = {{%zakazContent}}.refZakaz
					 left join {{%schet}} on {{%schet}}.refZakaz = {{%zakazContent}}.refZakaz where 
					 ({{%zakaz}}.isActive = 1 OR {{%schet}}.isSchetActive = 1) and good = :good
					 group by good')
					->bindValue(':good', $model['good'])										
					->queryScalar();
					
				  $val="<nobr>На складе: ".$model['amount']."</nobr>";
				  $val.="<br><nobr>В резервах: ".$reserved."</nobr>";
				  $val.="<br><nobr>Доступно: ".($model['amount'] - $reserved)."</nobr>";
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
				 if(empty($model['price'])){return "-";}
				 return $model['price']." за ".$model['edPrice'];
                }
            ],

            [
                'attribute' => 'Резервирование',
				'label'     => 'Резервирование',                
                'format' => 'raw',
				
				
				
                'value' => function ($model, $key, $index, $column) {
					
					if(empty($model['price'])){return "&nbsp;";}
					
					$val="";
					if ($model['isOtves'] == 0) {						
					$val.=" <input class='btn btn-primary' type=button value='Изменить' onclick='javascript:setReserveWin(".$model['scladId'].",".$model['zakazContentId'].",".$model['count']." );'>";			
					return $val;}
					
					
					$list = Yii::$app->db->createCommand(
					'SELECT id, size, refZakaz, inUse FROM {{%otves_list}} where refWarehouse=:refWarehouse 
					AND (inUse =0 OR refZakaz=:refZakaz)
					ORDER BY size')
					->bindValue(':refWarehouse', $model['scladId'])					
					->bindValue(':refZakaz', $model['refZakaz'])					
					->queryAll();
			
					for($i=0; $i < count ($list); $i++)
					{
						if ($list[$i]['inUse'] == 1 )
						{
							$style='inuse';
							$val.=" <input class='btn btn-primary ".$style."' type=button value='".$list[$i]['size']."' onclick='javascript:setDisableWin(".$model['zakazContentId'].",".$list[$i]['id'].");'>";								
						}
						else {
							$style='otves';
							$val.=" <input class='btn btn-primary ".$style."' type=button value='".$list[$i]['size']."' onclick='javascript:setEnableWin(".$model['zakazContentId'].",".$list[$i]['id'].");'>";								
						}											
						
					}
					
                return $val;
				}
				
            ],

			
            
        ],
    ]
);

?>