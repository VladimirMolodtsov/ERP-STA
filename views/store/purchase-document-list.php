<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\tabs\TabsX;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper; 

$this->title = 'Выбор документа поставки';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setControl(id, docType) {
    lnkType = document.getElementById('shetType').value;    
	window.opener.closeSchetList(id, docType, shetType);
}

function changeShowDate(){

fromDate = document.getElementById('from_date').value;
toDate = document.getElementById('to_date').value;
document.location.href='index.php?r=store/purchase-control-list&noframe=1&fltOrgTitle=<?= $model->fltOrgTitle ?>&supplierRef=<?=$model->supplierRef?>&fromDate='+fromDate+'&toDate='+toDate; 
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<div class='row'>
<div class ='col-sm-3'>      
Документы за период:
</div>
  <div class ='col-sm-4'>      
    <?php   
   echo DatePicker::widget([
    'name' => 'from_date',
    'id' => 'from_date',
    'value' => $model->fromDate,    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>
   </div>    
   <div class ='col-sm-4'>
    <?php   
   echo DatePicker::widget([
    'name' => 'to_date',
    'id' => 'to_date',
    'value' => $model->toDate,    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
   </div>       

</div>
<div class='spacer'></div>

<div class='row'>
<div class ='col-sm-3'>      
Связывать как:
</div>
   <div class ='col-sm-4'>
<?php  echo Html::dropDownList( 
       'shetType', 
       0, 
       $model->getPuchaseRoles(),
       [
       'class' => 'form-control',
       'style' => 'width:220px;font-size:12px; padding:1px;', 
       'id' => 'shetType', 
       ]);
?>
 </div>       
</div>
<div class='spacer'></div>

<?php
$roles = $model->getPuchaseRoles();
$content[0]= GridView::widget(
    [
        'dataProvider' => $controlProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
          
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'options' =>
            ['id' => 'pjax1',],
        ],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'ref1C',
				'label'     => 'Документ в 1С',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                       
					return "<a href='#' onclick='javascript:setControl(\"".$model['id']."\",\"purchaseControl\");' >".$model['ref1C']."</a>";
                },
            ],		
			[
                'attribute' => 'purchDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => '-',
				'label'     => 'Связан',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) use ($roles) {
                    
                   $strSql = "SELECT purchRef, purchRole FROM {{%purch_control_lnk}} where controlRef = :controlRef LIMIT 4";                   
                   $lnkList =  Yii::$app->db->createCommand($strSql, [':controlRef' => $model['id'],])->queryAll();                    
                  $val ="";
                  for ($i=0; $i< count($lnkList); $i++)
                  {
                      $val .= "Закупка № ".$lnkList[$i]['purchRef']." ".$roles[$lnkList[$i]['purchRole']]."<br>";
                  }
                                       
                    return $val;
                },
                
                
            ],		
            
			[
                'attribute' => 'purchTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $ret = $model['purchTitle']." ".$model['purchCount']." ".$model['purchEd'];
					return $ret;
                },
            ],		

  			[
                'attribute' => 'syncDate',
				'label'     => 'Синхронизация',
                'format' => 'raw',
            ],		
            
            
            
        ],
    ]
	);
    
    
/*******************************/
$items = [
        
        [
            'label'=>'<i class="fas fa-home"></i> Поступление ',
            'content'=>$content[0],
            'active'=>true
        ],
        
    ];


// Above
echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'bordered'=>true,
    'encodeLabels'=>false
]);
    
?>
