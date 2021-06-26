<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use kartik\grid\GridView;

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Взаимодействие с контрагентом';
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

function switchBox (orgRef, actionRef, grpCode)
{

    var data = new Array();
    $.ajax({
        url: 'index.php?r=site/switch-org-deal&orgRef='+orgRef+'&actionRef='+actionRef+'&grpCode='+grpCode,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSync(res);    
        },
        error: function(){
            alert('Error while retreving data!');
        }
    });	
}

function showSync(res)
{
   if (res['res'] == false) return;           
   idx=res['orgRef']+'_'+res['actionRef'];
   grpId='grp_'+res['orgRef']+'_'+res['grpCode'];
   
   switch (res['value'])
   {
     case 0:
      document.getElementById(idx).style.background='White';
     break;    
     case 1:
      document.getElementById(idx).style.background='LightGreen';
     break;
     case 2:     
      document.getElementById(idx).style.background='Blue';
      document.location.reload(true);  
     break;   
   }
   
   if ( res['grpValue'] == 0)
      document.getElementById(grpId).style.background='White';
   if ( res['grpValue'] == 1)
      document.getElementById(grpId).style.background='LightGreen';
   if ( res['grpValue'] == 2)
      document.getElementById(grpId).style.background='Blue';
      
   
   console.log(res);   
}


function switchMainBox (orgRef, grpCode)
{

    var data = new Array();
    $.ajax({
        url: 'index.php?r=site/switch-org-main-deal&orgRef='+orgRef+'&grpCode='+grpCode,
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
   idx='grp_'+res['orgRef']+'_'+res['grpCode']
   
   if (res['mainValue'] == res['grpCode'] )
       document.getElementById(idx).style.background='Blue';
   else{ if (res['typeValue'] & res['grpCode'] )    
           document.getElementById(idx).style.background='LightGreen';
        else     
          document.getElementById(idx).style.background='White';
       }   

       
       
   for (i=0; i<res['changedList'].length; i++)    
   {
      id=res['orgRef']+'_'+res['changedList'][i];
  //console.log(id);       
  // console.log(res['changedValue'][i]);       
      if(res['changedValue'][i] == 1)
          document.getElementById(id).style.background='LightGreen';
      if(res['changedValue'][i] == 2)
          document.getElementById(id).style.background='Blue';
      if(res['changedValue'][i] == 0)
          document.getElementById(id).style.background='White';
   }
       
          
    //document.location.reload(true);  
}

function openOrg(orgRef)
{

openWin('site/org-detail&orgId='+orgRef, 'childWin');
}


</script>

<div class='row'>
 <div class='col-md-9'></div>
 <div class='col-md-1'><a href='index.php?r=site/org-deals-cfg&noframe=1'><span class='glyphicon glyphicon-cog'></span></a></div>
 <div class='col-md-2'><a href='#' onclick="openEditWin('index.php?r=site/org-deals&format=csv&<?=Yii::$app->request->queryString?>')"> Скачать</a> 
</div>

<?php
$columns[]= [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',   
                'contentOptions' => ['style' => 'width:190px;', ],                
                'value' => function ($model, $key, $index, $column)  {	                                   
                    return "<div class='clickable' style='width:185px' onclick='openOrg(".$model['orgRef'].")'>".$model['orgTitle']."</a>";
                },                
            ];

$columns[]= [
                'attribute' => 'contactDate',
                'label'     => 'Контакт',
                'format' => 'raw',   
                'contentOptions' => ['style' => 'width:190px;', ],                
                'value' => function ($model, $key, $index, $column)  {	                                   
                
                $strSql="SELECT MAX(event_date) FROM {{%calendar}} where {{%calendar}}.ref_org= :refOrg";
                $eventTime= strtotime( Yii::$app->db->createCommand($strSql,[':refOrg' => $model['orgRef'],])->queryScalar());                
                $contactTime=strtotime($model['contactDate']);
                
                if ($eventTime >= 10 ) $eventDate="Последнее событие: ".date("Y.m.d", $eventTime);
                else                   $eventDate="";
                
                if ($contactTime >= 10 ) $contactDate=date("Y.m.d", $contactTime);
                else                     $contactDate="";
                
                
                return \yii\helpers\Html::tag( 'div', $contactDate, 
                   [                     
                     //'onclick' => $action,
                     //'style'  => 'padding:5px;'.$bg,
                     //'id'  => $id,
                     'title' => $eventDate,
                   ]);
                
                
                
                
                },                
            ];
            
      
/*$columns[]=  [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',                            
             ];*/
/************/

$model->loadGrpArticle();

for($i=0;$i<count($model->grpArticles);$i++)
{
  $columns=array_merge ($columns, $model->createColumns($i));      
}

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


<?php
echo "<pre>";
print_r($model->debug);
echo "</pre>";
?>


