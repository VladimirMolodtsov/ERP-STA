<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use kartik\grid\GridView;

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Настройка статьи';
//$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');



?>

<style> 

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

.btn-smaller{
margin:1px;
padding:1px;
height:15px;
width:15px;
}
.localLabel {
width:65px;
padding:2px;
font-size:10px;
color:black;
word-wrap: normal;
top:0px;
}

th{
word-wrap: normal;
width:65px;
top:0;

}


</style>

<script>

function switchType (id, grpCode)
{
var url = 'index.php?r=site/switch-deal-cfg&id='+id+'&grpCode='+grpCode;
console.log(url);   
    var data = new Array();
    $.ajax({
        url:  url,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showMainFlg(res);    
        },
        error: function(){
            alert('Error while retreving data!');
        }
    });	
}

function showMainFlg(res)
{

   console.log(res);   
    document.location.reload(true);  
}

function switchSign(id, val)
{

    var data = new Array();
    $.ajax({
        url: 'index.php?r=site/switch-deal-sign&id='+id+'&val='+val,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showVal(res);    
        },
        error: function(){
            alert('Error while retreving data!');
        }
    });	
}
function showVal(res)
{

   console.log(res);   
   document.location.reload(true);  
}



</script>


<?php
$columns[]= [
                'attribute' => 'fltArticle',
                'label'     => 'Статья',
                'format' => 'raw',   
                'value' => function ($model, $key, $index, $column){              
                        return $model['article'];  
                }
            ];
            
            
$columns[]= [
                'attribute' => 'signValue',
                'label'     => 'Сумма',
                 'contentOptions' => [ 'align' =>'center'], 
                'format' => 'raw',   
               'value' => function ($model, $key, $index, $column){                                                
                $id='sign_'.$model['id'].'_';
                
                if ($model['signValue'] < 0) $bg="background-color:Blue; color:White";
                                        else $bg="background-color:White";
                $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-minus'></span>", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'onclick' => "switchSign(".$model['id'].",-1)",
                     'style'  => 'font-size:11px;padding:1px;'.$bg,
                     'id'  => $id."-1",
                     'title' => 'Расход',
                   ]);                   
                $val.="&nbsp;";   

                if ($model['signValue'] == 0) $bg="background-color:Blue; color:White";
                                        else $bg="background-color:White";                   
                $val .= \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'onclick' => "switchSign(".$model['id'].",0)",
                     'style'  => 'font-size:11px;padding:1px;'.$bg,
                     'id'  => $id."0",
                     'title' => 'Все',
                   ]);                   
                $val.="&nbsp;";   
                   
                if ($model['signValue'] > 0) $bg="background-color:Blue; color:White";
                                        else $bg="background-color:White";                   
                $val .= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'onclick' => "switchSign(".$model['id'].",1)",
                     'style'  => 'font-size:11px;padding:1px;'.$bg,
                     'id'  => $id."1",
                     'title' => 'Доход',
                   ]);                   
                   
                 return $val;  
                },                                
            ];
                
            
            
$model->loadGrpArticle();      

for($i=0;$i<count($model->grpArticles);$i++)
{
  $columns[]= $model->createCfgColumn($i);      
}
/*
Отключим метку требования связи с 1С
$columns[]=[
                'attribute' => 'isRef1C',
                'label'     => '1С',
                 'contentOptions' => [ 'align' =>'center'], 
                'format' => 'raw',   
               'value' => function ($model, $key, $index, $column){                                                
                $id='ref1C_'.$model['id'];
                
                if ($model['isRef1C'] > 0) $bg="background-color:Blue; color:White";
                                       else $bg="background-color:White";
                $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'onclick' => "switchType(".$model['id'].",'ref1C')",
                     'style'  => 'font-size:11px;padding:1px;'.$bg,
                     'id'  => $id,
                     'title' => 'Есть связь с 1С',
                   ]);                   
                $val.="&nbsp;";   
                  
                 return $val;  
                },                                
            ];
*/



 echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
      //  'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
      
        'responsive'=>true,
        'hover'=>false,
        
        /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
         ], */       
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
         ],


        'columns' => $columns
    ]
); 
?>

