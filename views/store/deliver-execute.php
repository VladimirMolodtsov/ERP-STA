<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Затраты на доставку';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script>

function recalcExpWrk()
{
    valWeight=document.getElementById('valWeight').value;	
    
    document.getElementById('expWrkItog').value = <?= $model->itogoWeight?> * valWeight ;
}

function recalcDrvWrk()
{    
    valTime  =document.getElementById('valTime').value;	    
    document.getElementById('expWrkItog').value = <?= $model->itogoTime?>*valTime;
}

function expWrkProceed()
{
    document.getElementById('actionType').value = 1;
    document.getElementById("oplateForm").submit(); 
}


function expCostProceed()
{
    document.getElementById('actionType').value = 2;
    document.getElementById("oplateForm").submit(); 
}

function expDriveProceed()
{
    document.getElementById('actionType').value = 3;
    document.getElementById("oplateForm").submit(); 
}

</script>


<h3><?= Html::encode($this->title) ?></h3>
<div style='text-align:right;'>
<a href="#" onclick="openWin('index.php?r=store/deliver-execute&<?= Yii::$app->request->queryString  ?>&noframe=1&format=print','printWin');">
<span class="glyphicon glyphicon-print" aria-hidden="true"></span></a> 
</div>
<br>
<div>
<form name='fltForm' method='get' action='index.php'>
<input type='hidden' name='r' value='store/deliver-execute'>
<input name='noframe'  type='hidden'  value='0'> 

<div class='row'>
<div class='col-md-2'><div style='padding-top: 10px; text-align:right;'>От </div></div>
<div class='col-md-2'><input type='date' class='form-control' name='dFrom' id='dFrom' value='<?= $model->dFrom ?>' > </div>
<div class='col-md-1'><div style='padding-top: 10px; text-align:right;'>до </div></div>
<div class='col-md-2'><input type='date' class='form-control' name='dTo' id='dTo'  value='<?= $model->dTo ?>' > </div>
<div class='col-md-3' style='text-align:left;'>
<input type='submit' class='btn btn-primary' value='Отфильтровать'>
</div>
</div><div class='spacer'></div>
</form>
</div>
</br>

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


     	    [
                'attribute' => 'factDate',
				'label'     => 'Дата',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return date ('d.m.y',strtotime( $model['factDate']));
                }                
            ],		
            
     	    [
                'attribute' => 'requestNum',
				'label'     => 'Номер',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                
                $id='requestNum'.$model['id'];
                $action = "openWin('store/deliver-zakaz&id=".$model['id']."','deliverZakazWin')";
                return    \yii\helpers\Html::tag( 'div', $model['requestNum'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                return $val;
                
                
                }
            ],		

            
                             
     	    [
                'attribute' => 'Счет',
				'label'     => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    if (!empty($model['refSchet']))
                    {
                      $strSql = "Select schetNum, schetDate from {{%schet}} where id =:refSchet";    
                      $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryAll();                                                                        
                      if (count($schetData) > 0)
                      return "клиент ". $schetData[0]['schetNum']  ;                    
                    }

                    if (!empty($model['refSupplierSchet']))
                    {
                      $strSql = "Select schetNum, schetDate from {{%supplier_schet_header}} where id =:refSchet";    
                      $schetData = Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet'],])->queryAll();                                                                        
                      if (count($schetData) > 0)
                      return "поставщ. ". $schetData[0]['schetNum'] ;                     
                    }
                }                
                
                
            ],		

            [
                'attribute' => 'ffactValue',
				'label'     => 'Затраты <br> водителя ',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return $model['factValue'];
                }
            ],		

            [
                'attribute' => 'frequest_time',
				'label'     => 'Время <br> экспед.',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return $model['request_time'];
                }
                
            ],		
            
            [
                'attribute' => 'frequest_exp_value',
				'label'     => 'Затраты <br> экспед.',
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return $model['request_exp_value'];
                }
                
            ],		
            
            
            [
                'attribute' => 'ffactWeight',
				'label'     => 'Вес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return $model['factWeight'];
                }
                
            ],		
        
          [
                'attribute' => 'fsupplyType',
				'label'     => 'Тип',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                

                switch ($model['supplyType'])
                {
                  case 1:
                    return 'Доставка клиенту';
                  break;
                    
                  case 2:
                    return 'Перемещение';
                  break;
                  case 4:
                    return 'Документы';
                  break;
                  case 5:
                    return 'Доставка от поставщика';
                  break;
    
                }
                
                }                
            ],		

        
        
            [
                'attribute' => 'requestAdress',
				'label'     => 'Куда',
                'format' => 'raw',
            ],		
                
            [
                'attribute' => 'requestScladAdress',
				'label'     => 'Откуда',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'requestNote',
				'label'     => 'Примечание',
                'format' => 'raw',
            ],		

    
        
        
            [
                'attribute' => 'refOplateWrkExp',
				'label'     => 'Оплата <br> работ <br> эксп',
                'encodeLabel' => false,                
                'format' => 'raw',
                'filter' => ['1' => 'Все', '2' => 'Нет', '3' => 'В реестре'],
                'value' => function ($model, $key, $index, $column) {					
					if ($model['refOplateWrkExp'] > 0 ){ $isOp = true;}
					else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
						)."<br>".number_format($model['sumOplateWrkExp'],2,'.','&nbsp;');
                },

                
            ],		
            
            [
                'attribute' => 'refOplateExpCost',
				'label'     => 'Оплата <br> затрат <br> эксп',
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => ['1' => 'Все', '2' => 'Нет', '3' => 'В реестре'],
                'value' => function ($model, $key, $index, $column) {					
					if ($model['refOplateExpCost'] > 0 ){ $isOp = true;}
					else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
						)."<br>".number_format($model['sumOplateExpCost'],2,'.','&nbsp;');
                },

                
            ],		

            [
                'attribute' => 'refOplateDrive',
				'label'     => 'Оплата <br> водит.',
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => ['1' => 'Все', '2' => 'Нет', '3' => 'В реестре'/*, '4' => 'Не Оплачен'*/,],
                'value' => function ($model, $key, $index, $column) {					
					if ($model['refOplateDrive'] > 0 ){ $isOp = true;}
					else                            { $isOp = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
						)."<br>".number_format($model['sumOplateDrive'],2,'.','&nbsp;');
						
                },

                
            ],		
            
            
        ],
    ]
	);
?>


<form name='oplateForm' id='oplateForm' method='get' action='index.php'>
<input type='hidden' name='r' value='store/deliver-execute'>
<input name='noframe'  type='hidden'  value='0'> 
<input name='action'   type='hidden'  value='actOplate'> 
<input name='actionType' id='actionType'  type='hidden'  value='actOplate'> 
<input type='hidden' name='dFrom' id='dFrom' value='<?= $model->dFrom ?>' >
<input type='hidden' name='dTo' id='dTo'  value='<?= $model->dTo ?>' > 
<h4>Начисления</h4>
<div style='width:600px;'> 
<table class='table'> 
<body>
<tr>
    <td style='background:Silver;' colspan='4' ><b>Водитель</b></td>
</tr>
<tr>
    <td  align='right'>Суммарное время: <b><?= number_format(($model->itogoTime),0,'.','&nbsp;') ?></b> мин. </td>
    <td > Затраты:
    <input type='hidden'  class='form-control' name='valTime' id='valTime' onchange='recalcExpWrk();' value='0' > 
    <!-- <?= $model->valTime ?> --></td>   
    <td><input class='form-control' readonly name='driveItog' id='driveItog'  value='<?= $model->driveItog ?>' ></td>    
    <td align='right'><input type='button' class='btn btn-primary' value='В оплату' onclick='expDriveProceed();' ></td>   
</tr>

<tr>
    <td style='background:Silver;' colspan='4' ><b>Экспедитор</b></td>
</tr>

<tr>
    <td  align='right'> </td>   
    <td> Затраты </td>
    <td><input class='form-control' readonly name='expCostItog' id='expCostItog'  value='<?= $model->expCostItog ?>' ></td>    
    <td align='right'><input type='button' class='btn btn-primary' value='В оплату' onclick='expCostProceed();' ></td>   
</tr>

<tr>
    <td align='right'>Суммарный вес: <b><?= number_format(($model->itogoWeight),0,'.','&nbsp;') ?></b> кг *
    <td><input  class='form-control'  style='width:50px;' name='valWeight' id='valWeight' onchange='recalcExpWrk();'  value='<?= $model->valWeight ?>' ></td>     
    <td><input class='form-control' readonly name='expWrkItog' id='expWrkItog'  value='<?= $model->expWrkItog ?>' ></td>    
    <td align='right'><input type='button' class='btn btn-primary' value='В оплату' onclick='expWrkProceed();' ></td>   
</tr>


</table>
</div>
</form>

