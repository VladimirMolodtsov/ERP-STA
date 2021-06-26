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


$this->title = 'Выбор счета из 1с';
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/managment/fin-control-cfg.js');
$requestId=$model->id;

$model->loadShortData();

?>
<link rel="stylesheet" type="text/css" href="phone.css" />

<style>

</style>
  
<script>
function setSchet(id,type){

window.opener.setRef(id,type);
}

function changeShowDate(){

fromDate = document.getElementById('from_date').value;
toDate = document.getElementById('to_date').value;
//$.pjax.reload({container:'#pjax1'});
document.location.href='index.php?r=/bank/operator/select-schet&noframe=1&docid=<?= $model->id ?>&fromDate='+fromDate+'&toDate='+toDate; 

}

function syncPeriod(actionid){
    
var fromDate = document.getElementById('from_date').value;
var toDate = document.getElementById('to_date').value;

var url = 'index.php?r=/data/ajax-sync&actionid='+actionid+'&fromDate='+fromDate+'&toDate='+toDate; 
console.log(url);
    $('#showSyncProgress').modal('show');       
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: url, 
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#showSyncProgress').modal('hide');       
            var uid='#pjax'+res.actionid;            
            console.log(uid);
            $.pjax.reload({container:uid});
            console.log(res);
            //document.location.reload(true);   
            
        },
        error: function(){
             $('html, body').css("cursor", "auto");
             $('#showSyncProgress').modal('hide');       
            alert('Error while synchronize data!');
        }
    });	
}

function openOrg(id) {   
  var url  ='site/org-detail&orgId='+id;
  openWin(url,'orgWin');
}

function showClientSchet(id) {   
  var url  ='fin/client-schet-src&refSchet='+id;
  openWin(url,'schetWin');
}
</script>

<div class='row'>
    <div class ='col-sm-6'>      
      <b><?= $model->orgType ?></b>   <?= $model->docTitle ?> № <?= $model->docOrigNum ?>  от <?= $model->docOrigDate ?>, на сумму <?= $model->docSum ?> руб. 
    </div>

    <div class ='col-sm-6'>      
    <b><?= $model->orgShowTitle ?> </b> 
    </div>
</div>

<p><?= $model->docNote ?></p>  

<div class='spacer'></div>

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

<?php
$content[0]  ="<div style='width:100%; text-align:right'>"; 
$content[0] .="<span class='glyphicon glyphicon-refresh clickable' onclick='syncPeriod(0);' ></span></div>";
$content[0] .= GridView::widget(
    [
        'dataProvider' => $clientProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
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
                'attribute' => 'ref1C',
                'label' => 'Счет в 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; font-size:11px;'],
     
                'value' => function ($model, $key, $index, $column)  {     
                $val = $model['schetRef1C'];
                $strSql = "SELECT SUM(wareSum) from {{%client_schet_content}} 
                WHERE refHeader=:schetRef"; 
                $sum =  Yii::$app->db->createCommand($strSql,[':schetRef' => $model['id']])->queryScalar(); 
                $val .= "<br>от ".date('d.m.Y', strtotime($model['schetDate'])); 
                $val .= "<br>".number_format($sum,2,'.','&nbsp;'); 
                
                $id = "schetRef1C".$model['id'];
                $action="showClientSchet(".$model['id'].");";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'      => $id
                   ]);                    

                },
            ],

 			[
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $action="setSchet(".$model['id'].",'clientSchet');";
                 return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);
                },
            ],		
 

            [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;  font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                  $title = "ИНН ".$model['orgINN']."\n"."КПП ".$model['orgKPP'];
                  $val = $model['orgTitle']."<br>";                  
                  $val .= "ИНН ".$model['orgINN']."<br>";
                  $val .= "КПП ".$model['orgKPP'];
                  return  \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px; width:450px;  font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                $strSql = "SELECT wareTitle from {{%client_schet_content}}  WHERE refHeader=:schetRef
                ORDER BY id"; 
                $list =  Yii::$app->db->createCommand($strSql,[':schetRef' => $model['id']])->queryAll(); 
                $val="";
                $title = "";
                for ($i=0;$i<count($list);$i++)
                {
                    if ($i<=1)$val.=$list[$i]['wareTitle']."<br>";
                    $title.=$list[$i]['wareTitle']."\n";;
                }
                             
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
 

            
        ],
    ]
);
$content[1] ="<div style='width:100%; text-align:right'>"; 
$content[1] .="<span class='glyphicon glyphicon-refresh clickable' onclick='syncPeriod(1);' ></span></div>";
$content[1] .= GridView::widget(
    [
        'dataProvider' => $supplierProvider,
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

        'columns' => [
        
            [
                'attribute' => 'ref1C',
                'label' => 'Счет в 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;  font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['supplierRef1C'];
                $strSql = "SELECT SUM(goodSumm) from {{%supplier_schet_content}} 
                WHERE schetRef=:schetRef"; 
                $sum =  Yii::$app->db->createCommand($strSql,[':schetRef' => $model['id']])->queryScalar(); 
                $val .= "<br>от ".date('d.m.Y', strtotime($model['schetDate'])); 
                $val .= "<br>".number_format($sum,2,'.','&nbsp;'); 
                
                $id = "supplierRef1C".$model['id'];
                $action="setSchet(".$model['id'].",'supplierSchet');";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'      => $id
                   ]);                    

                },
            ],
            
 			[
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $action="setSchet(".$model['id'].",'supplierSchet');";
                 return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);
                },
            ],		
            
                                       

            [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                  $title = "ИНН ".$model['orgINN']."\n"."КПП ".$model['orgKPP'];
                  $val = $model['orgTitle']."<br>";                  
                  $val .= "ИНН ".$model['orgINN']."<br>";
                  $val .= "КПП ".$model['orgKPP'];
                  return  \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                $strSql = "SELECT goodTitle from {{%supplier_schet_content}}  WHERE schetRef=:schetRef
                ORDER BY id"; 
                $list =  Yii::$app->db->createCommand($strSql,[':schetRef' => $model['id']])->queryAll(); 
                $val="";
                $title = "";
                for ($i=0;$i<count($list);$i++)
                {
                    if ($i<=1)$val.=$list[$i]['goodTitle']."<br>";
                    $title.=$list[$i]['goodTitle']."\n";;
                }
                             
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            
         ]       
                
    ]
);


$content[2]  ="<div style='width:100%; text-align:right'>"; 
$content[2] .="<span class='glyphicon glyphicon-refresh clickable' onclick='syncPeriod(2);' ></span></div>";
$content[2] .= GridView::widget(
    [
        'dataProvider' => $supplyProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,         
        'responsive'=>true,
        'hover'=>false,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'options' =>
            ['id' => 'pjax2',],
        ],

        'columns' => [
        
            [
                'attribute' => 'ref1C',
                'label' => 'Отгрузка в 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;  font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
     
                $val = $model['ref1C'];
                $strSql = "SELECT SUM(supplySumm) from {{%supply}} 
                WHERE ref1C=:ref1C AND supplyDate= :supplyDate"; 
                $sum =  Yii::$app->db->createCommand($strSql,
                [':ref1C' => $model['ref1C'],
                ':supplyDate' => $model['supplyDate'],
                ])->queryScalar(); 
                $val .= "<br>от ".date('d.m.Y', strtotime($model['supplyDate'])); 
                $val .= "<br>".number_format($sum,2,'.','&nbsp;'); 
                
                $id = "ref1C".$model['id'];
                $action="setSchet(".$model['id'].",'supplyRef');";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'      => $id
                   ]);                    

                },
            ],
                                       
 			[
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $action="setSchet(".$model['id'].",'supplyRef');";
                 return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);
                },
            ],		

            [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                  $title = "ИНН ".$model['orgINN']."\n"."КПП ".$model['orgKPP'];
                  $val = $model['orgTitle']."<br>";                  
                  $val .= "ИНН ".$model['orgINN']."<br>";
                  $val .= "КПП ".$model['orgKPP'];
                  return  \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                $strSql = "SELECT supplyGood from {{%supply}}  WHERE ref1C=:ref1C AND supplyDate= :supplyDate"; 
                $list =  Yii::$app->db->createCommand($strSql,
                [':ref1C' => $model['ref1C'],
                ':supplyDate' => $model['supplyDate'],
                ])->queryAll(); 
                $val="";
                $title = "";
                for ($i=0;$i<count($list);$i++)
                {
                    if ($i<=1)$val.=$list[$i]['supplyGood']."<br>";
                    $title.=$list[$i]['supplyGood']."\n";;
                }
                             
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            
         ]       
                
    ]
);

$content[3]  ="<div style='width:100%; text-align:right'>"; 
$content[3] .="<span class='glyphicon glyphicon-refresh clickable' onclick='syncPeriod(3);' ></span></div>";
$content[3] .= GridView::widget(
    [
        'dataProvider' => $purchProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,         
        'responsive'=>true,
        'hover'=>false,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'options' =>
            ['id' => 'pjax3',],
        
        ],

        'columns' => [
        
            [
                'attribute' => 'ref1C',
                'label' => 'Отгрузка в 1С',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;  font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
     
                $strSql = "SELECT headerRef from {{%control_purch_content}}  WHERE id=:ref"; 
                $headerId = Yii::$app->db->createCommand($strSql,[':ref' => $model['id']])->queryScalar();

                $val = $model['ref1C'];
                $strSql = "SELECT SUM(purchSum) from {{%control_purch_content}} 
                WHERE headerRef=:headerRef
                AND ref1C=:ref1C AND purchDate= :purchDate
                ";  
                $sum =  Yii::$app->db->createCommand($strSql,
                [':ref1C' => $model['ref1C'],
                ':purchDate' => $model['purchDate'],
                ':headerRef' => $headerId,
                ])->queryScalar(); 
                $val .= "<br>от ".date('d.m.Y', strtotime($model['purchDate'])); 
                $val .= "<br>".number_format($sum,2,'.','&nbsp;'); 
                
                $id = "ref1C".$model['id'];
                $action="setSchet(".$model['id'].",'purchRef');";
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'      => $id
                   ]);                    

                },
            ],
            
            
 			[
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $action="setSchet(".$model['id'].",'purchRef');";
                 return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-plus',
                     'onclick' => $action,
                     'title'   => 'Выбрать',
                   ]);
                },
            ],		
            
                                       

            [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                  $title = "ИНН ".$model['orgINN']."\n"."КПП ".$model['orgKPP'];
                  $val = $model['orgTitle']."<br>";                  
                  $val .= "ИНН ".$model['orgINN']."<br>";
                  $val .= "КПП ".$model['orgKPP'];
                  return  \yii\helpers\Html::tag( 'div', $val , 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions'   =>   ['style' => 'padding:2px;   font-size:11px;'],
                'value' => function ($model, $key, $index, $column)  {
           
                $strSql = "SELECT headerRef from {{%control_purch_content}}  WHERE id=:ref"; 
                $headerId = Yii::$app->db->createCommand($strSql,[':ref' => $model['id']])->queryScalar();
                $strSql = "SELECT purchTitle from {{%control_purch_content}}  WHERE headerRef=:headerRef
                AND ref1C=:ref1C AND purchDate= :purchDate
                "; 
                $list =  Yii::$app->db->createCommand($strSql,
                [
                ':ref1C' => $model['ref1C'],
                ':purchDate' => $model['purchDate'],                
                ':headerRef' => $headerId,
                ])->queryAll(); 
                $val=" ";
                $title = "";
                for ($i=0;$i<count($list);$i++)
                {
                    if ($i<=1)$val.=$list[$i]['purchTitle']."<br>";
                    $title.=$list[$i]['purchTitle']."\n";;
                }
                             
                  return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'title'      => $title
                   ]);                    

                },
            ],
            
            
         ]       
                
    ]
);


/*******************************/
$items = [
        
        [
            'label'=>'<i class="fas fa-home"></i> Счета клиентам',
            'content'=>$content[0],
            'active'=>true,
            
        ],
        
        [
            'label'=>'<i class="fas fa-home"></i> Счета поставщиков',
            'content'=>$content[1],
            'active'=>false
        ],
        [
            'label'=>'<i class="fas fa-home"></i> Отгрузка товара',
            'content'=>$content[2],
            'active'=>false
        ],
        [
            'label'=>'<i class="fas fa-home"></i> Поступление товара',
            'content'=>$content[3],
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
<br>

<?php
Modal::begin([
    'id' =>'showSyncProgress',
    //'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Поиск в 1С </h4>',
]);?>
<div style='width:100%; text-align:center;'><img src='img/ajax-loader.gif'></div>
<?php
Modal::end();
?>
