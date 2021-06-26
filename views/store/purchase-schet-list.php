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

$this->title = 'Выбор счета';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setDoc(id, docType) {
    lnkType = document.getElementById('shetType').value;    
	window.opener.closeSchetList(id, docType, shetType);
}

function changeShowDate(){

fromDate = document.getElementById('from_date').value;
toDate = document.getElementById('to_date').value;
document.location.href='index.php?r=store/purchase-schet-list&noframe=1&supplierRef=<?=$model->supplierRef?>&fromDate='+fromDate+'&toDate='+toDate; 
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
$content[0]=GridView::widget(
    [
        'dataProvider' => $docProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
          
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'options' =>
            ['id' => 'pjax0',],
        ],

        'columns' => [

			[
                'attribute' => 'docOrigNum',
				'label'     => 'Документ ERP',
                'filter'    => $model->getDocTypes (),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','docType');       
                $operationArray[0]='Не задан';
                $v=$operationArray[$model['docClassifyRef']];
                if (empty($model['docClassifyRef'])) $v =  $model['docTitle'];
                    
                    
                $val = $v." №".$model['docOrigNum']."<br>".$model['docOrigDate'];    
                $id = "docOrigNum".$model['id'];
                $action="setDoc(".$model['id'].",'ErpDoc');";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'      => $id
                   ]);                    
                    
                },
            ],		
     
			[
                'attribute' => 'supplierRef1C',
				'label'     => 'Счет в 1C',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                $id = "supplierRef1C".$model['id'];
                $action="setDoc(".$model['id'].",'ErpDoc');";
                $val = "№ ".$model['supplierRef1C']."<br>".$model['schetDate'];    
                if (empty($model['refSupplierSchet'])) $s="color:Crimson";
                                                 else  $s="color:Green";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'style'   => $s,
                     'id'      => $id
                   ]);                                        					
                },
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Контрагент',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => '-',
				'label'     => 'Связан',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) use ($roles) {
                    
                  if (!empty($model['purchaseRef'])){
                    return $model['purchaseRef']." ".$roles[$model['purchaseRole']];                        
                  }
                    
                  if (!empty($model['purchaseErpRef'])){                   
                   
                  return $model['purchaseErpRef'];
                  }  
                },                                
            ],		
                        
			[
                'attribute' => 'Товар',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $strSql = "SELECT goodTitle FROM {{%supplier_schet_content}} where schetRef = :schetRef LIMIT 4";                   
                   $wareList =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['refSupplierSchet'],])->queryAll();                    
                   $ret="";
                    for ($i=0; $i<count($wareList); $i++ )
                    {
                     $ret.=$wareList[$i]['goodTitle'];
                     if ($i>= 0) break;
                     $ret.="<br>";
                    }
                    if ($i<count($wareList)) $ret.="<br>...";                    
					return $ret;
                },
            ],		
            
        ],
    ]
	);

$content[1]= GridView::widget(
    [
        'dataProvider' => $schetProvider,
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
                'attribute' => 'schetNum',
				'label'     => 'Счет в 1С',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                       
					return "<a href='#' onclick='javascript:setDoc(\"".$model['id']."\",\"supplierSchet\");' >".$model['schetNum']."</a>";
                },
            ],		
			[
                'attribute' => 'schetDate',
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
                    
                  if (empty($model['purchaseRef'])) return "&nbsp;";  
                    return $model['purchaseRef']." ".$roles[$model['purchaseRole']];
                },
                
                
            ],		
            
			[
                'attribute' => 'Товар',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $strSql = "SELECT goodTitle FROM {{%supplier_schet_content}} where schetRef = :schetRef LIMIT 4";                   
                   $wareList =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['id'],])->queryAll();                    
                   $ret="";
                    for ($i=0; $i<count($wareList); $i++ )
                    {
                     $ret.=$wareList[$i]['goodTitle'];
                     if ($i>= 0) break;
                     $ret.="<br>";
                    }
                    if ($i<count($wareList)) $ret.="<br>...";                    
					return $ret;
                },
            ],		
            
        ],
    ]
	);
    
    
/*******************************/
$items = [
        
        [
            'label'=>'<i class="fas fa-home"></i> Документы в ERP',
            'content'=>$content[0],
            'active'=>true,
            
        ],
        
        [
            'label'=>'<i class="fas fa-home"></i> Счета поставщиков в 1C',
            'content'=>$content[1],
            'active'=>false
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
