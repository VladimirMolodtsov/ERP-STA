<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Поставки привязанные к счету';

$schetData = $model->loadSchetData($schetId);
if (empty ($schetData))
{
    echo 'Данные по счету не найдены';
    return;
}

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function attachToSchet(supplyId) {
    openSwitchWin('fin/supply-attach&schetId=<?= $schetId ?>&supplyId='+supplyId);   
}
function detachFromSchet(supplyId) {
    openSwitchWin('fin/supply-detach&supplyId='+supplyId);   
}

</script >

<h3><?= Html::encode($this->title) ?></h3>



<p> Клиент:  <?= $schetData['title'] ?><br>
    Счет №  <?= $schetData['schetNum'] ?> от  <?= date('d-m-Y',strtotime($schetData['schetDate'])) ?> на сумму  <?= number_format($schetData['schetSumm'],2,'.','&nbsp;') ?><br>
    Стоимость привязанных поставок:  <?= $schetData['summSupply'] ?>
</p>

<div class="part-header">  Привязанные поставки </div>    
<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $linkedListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

     	    [
                'attribute' => 'supplyGood',
				'label'     => 'Товар',
                'format' => 'raw',
            ],		
     	    [
                'attribute' => 'supplyCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
                 
     	    [
                'attribute' => 'supplySumm',
				'label'     => 'На сумму',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'supplyDate',
				'label'     => 'Дата поставки',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'supplyNum',
				'label'     => 'Номер поставки',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Получатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		
            
            
     	    [
                'attribute' => 'id',
				'label'     => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<input class='btn btn-primary'  type='button' value='Убрать' onclick=\"javascript: detachFromSchet(".$model['id'].");\"/>";
                },

            ],		
            

        ],
    ]
	);
?>

<div class="part-header"> Поставки доступные для привязки  </div>    

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $freeListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
                 
     	    [
                'attribute' => 'supplyGood',
				'label'     => 'Товар',
                'format' => 'raw',
            ],		
     	    [
                'attribute' => 'supplyCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
                 
     	    [
                'attribute' => 'supplySumm',
				'label'     => 'На сумму',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'supplyDate',
				'label'     => 'Дата поставки',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'supplyNum',
				'label'     => 'Номер поставки',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Получатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		
            
       	    [
                'attribute' => 'id',
				'label'     => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<input class='btn btn-primary'  type='button' value='Привязать' onclick=\"javascript:attachToSchet(".$model['id'].");\"/>";
                },

            ],		
            

        ],
    ]
	);
?>
