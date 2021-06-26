<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Транспортные тарифы';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


?>

<style>

.btn-small{
margin:2px;
font-size: 10pt;
padding:2px;
height:20px;
width:20px;
}

.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-txt {    
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}



</style>
  
<script>


/*************/
function syncTarif()
{
    //document.getElementById('dataVal').value=val;    
    //var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});   
    $.ajax({
        url: 'index.php?r=data/sync-transport-tarif',
        type: 'GET',
        dataType: 'json',
        //data: data,
        success: function(res){     
            console.log(res);
            $(document.body).css({'cursor' : 'default'});            
            document.location.reload(true); 
            
        },
        error: function(){
            alert('Error while sync data!');
            $(document.body).css({'cursor' : 'default'});                        
        }
    });	
}
 

function saveData ()
{
    var data = $('#saveDataForm').serialize();
    $(document.body).css({'cursor' : 'wait'});
    $.ajax({
        url: 'index.php?r=store/save-tarif-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
            if(res.reload==true)document.location.reload(true);
        },
        error: function(){
            alert('Error while save data!');
            $(document.body).css({'cursor' : 'default'});
        }
    });

}

function selectTransport(id,row)
{
 window.opener.addTransportTarif(id,row);
 window.close();

}
</script>

 <div align='right'><span onclick='syncTarif()' class='clickable glyphicon glyphicon-refresh'></span></div>
<div class='spacer'></div>
     
<?php

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],        
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'attribute' => 'city',
                'label' => 'Город',
                'format' => 'raw',
            ],
            [
                'attribute' => 'company',
                'label' => 'ТК',
                'format' => 'raw',
            ],
            [
                'attribute' => 'v1',
                'label' => 'менее 50',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v1';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                

            ],
            [
                'attribute' => 'v2',
                'label' => '50',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v2';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'v3',
                'label' => '100',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v3';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'v4',
                'label' => '200',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v4';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'v5',
                'label' => '500',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v5';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'v6',
                'label' => '1000',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v6';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'v7',
                'label' => '3000',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $v='v7';
                 if (empty($model[$v])) return "&nbsp;";
                 $action="selectTransport(".$model['id'].",'".$v."')";
                 $id=$v.$model['id'];  
                  return \yii\helpers\Html::tag( 'div', $model[$v], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
                
            ],
            [
                'attribute' => 'timeNote',
                'label' => 'Доставка',
                'format' => 'raw',                
            ],
            
            
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-tarif-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
