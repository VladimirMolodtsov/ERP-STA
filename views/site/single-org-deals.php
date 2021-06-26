<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use kartik\grid\GridView;

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Операции контрагента';
//$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');

$model->initData();

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

function switchOrgDeal (actionRef, grpCode)
{

   console.log('index.php?r=site/switch-org-deal&orgRef=<?= $model->orgId ?>&actionRef='+actionRef+'&grpCode='+grpCode);   
    var data = new Array();
    $.ajax({        
        url: 'index.php?r=site/switch-org-deal&orgRef=<?= $model->orgId ?>&actionRef='+actionRef+'&grpCode='+grpCode,
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

 document.location.reload(true);  
      
   
   console.log(res);   
}


function switchOrgType (grpCode)
{

    var data = new Array();
    $.ajax({
        url: 'index.php?r=site/switch-org-main-deal&orgRef=<?= $model->orgId ?>&grpCode='+grpCode,
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
   //document.location.reload(true);  
   console.log(res);   
   idx='grp_'+res['grpCode']
   
   if (res['mainValue'] == res['grpCode'] )
       document.getElementById(idx).style.background='Blue';
   else{ if (res['typeValue'] & res['grpCode'] )    
           document.getElementById(idx).style.background='LightGreen';
        else     
          document.getElementById(idx).style.background='White';
       }   
       
   for (i=0; i<res['changedList'].length; i++)    
   {
      id='article_'+res['changedList'][i];
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
 <div class='col-md-9'><h3><?= $model->orgRecord->title ?></h3></div>
 <div class='col-md-1'><a href='index.php?r=site/org-deals-cfg'><span class='glyphicon glyphicon-cog'></span></a></div> 
</div>

<?php

for ($i=0;$i<count($model->orgTypeArray);$i++)
{
 
 $model->printHeadLine($i); 
 $model->printArticles($i); 


}

//echo "<pre>";
//print_r($model->varTypeArray);
//echo "</pre>";
?>







