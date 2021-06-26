<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;

$this->title = 'Выписки привязанные к счету';

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
function attachToSchet(oplataId) {
    openSwitchWin('fin/extract-attach&schetId=<?= $schetId ?>&oplataId='+oplataId);   
}
function detachFromSchet(oplataId) {
    openSwitchWin('fin/extract-detach&schetId=<?= $schetId ?>&oplataId='+oplataId);   
}

function  saveField(id, type) {
    
    var idx = id+type;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;    
    document.getElementById('dataVal').value=document.getElementById(idx).value;        
 
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/fin/save-extract-lnk-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);          
            window.opener.location.reload(false); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
   
}

</script >

<h3><?= Html::encode($this->title) ?></h3>



<p> Клиент:  <?= $schetData['title'] ?><br>
    Счет №  <?= $schetData['schetNum'] ?> от  <?= date('d-m-Y',strtotime($schetData['schetDate'])) ?> на сумму  <?= number_format($schetData['schetSumm'],2,'.','&nbsp;') ?><br>
    Сумма привязанных оплат:  <?= $schetData['summExtract'] ?>
</p>

<div class="part-header">  Привязанные оплаты </div>    
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
                'attribute' => 'lnkSum',
				'label'     => 'Сумма',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:1px; '],
                 'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['lnkRef'].'lnkSum';                 
                 $action =  "saveField(".$model['lnkRef'].", 'lnkSum');"; 
                 $val= Html::textInput( 
                          $id, 
                          $model['lnkSum'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
           
               return $val;               
               }             
                
                
                
            ],		
            
     	    [
                'attribute' => 'recordDate',
				'label'     => 'Дата платежа',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if(empty($model['recordDate'])) return ""; 
					return  date("d.m.Y", strtotime($model['recordDate']));
                },
                
            ],		

     	    [
                'attribute' => 'docNum',
				'label'     => 'П/П',
                'format' => 'raw',
            ],		
            
       	    [
                'attribute' => 'description',
				'label'     => 'Основание платежа',
                'format' => 'raw',
            ],		

            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ( empty($model['orgRef'])) return $model['creditOrgTitle'];
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		
            
            
     	    [
                'attribute' => 'id',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<div class='clickable glyphicon glyphicon-minus'  title='Убрать' onclick=\"javascript: detachFromSchet(".$model['id'].");\"/></div>";
                },

            ],		
            

        ],
    ]
	);
?>
<div class='btn btn-primary'  onclick='window.close();' >Завершиь</div>
<div class='spacer'></div>
<div class="part-header"> Оплаты доступные для привязки  </div>    

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
                'attribute' => 'oplateSumm',
				'label'     => 'Сумма',
                'format' => 'raw',
            ],		
            
     	    [
                'attribute' => 'recordDate',
				'label'     => 'Дата платежа',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if(empty($model['recordDate'])) return ""; 
					return  date("d.m.Y", strtotime($model['recordDate']));
                },
                
            ],		

     	    [
                'attribute' => 'docNum',
				'label'     => 'П/П',
                'format' => 'raw',
            ],		
            
       	    [
                'attribute' => 'description',
				'label'     => 'Основание платежа',
                'format' => 'raw',
            ],		

            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Плательщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if ( empty($model['orgRef'])) return $model['creditOrgTitle'];
					return  $model['orgTitle']." ИНН: ".$model['orgINN']." КПП:".$model['orgKPP'];
                },

            ],		
            
            
     	    [
                'attribute' => 'id',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($schetId) {
                    if ($model['schetRef'] == $schetId) return '&nbsp;';                    
					return "<div class='clickable glyphicon glyphicon-plus'  title='Привязать' onclick=\"javascript:attachToSchet(".$model['id'].");\"/></div>";
                },

            ],		
            

        ],
    ]
	);
?>

<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action'=>'index.php?r=/fin/save-extract-lnk-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);

echo "<input type='submit'>";
ActiveForm::end(); 
?>
