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
  window.parent.closeOrgDeal(actionRef);
}

</script>

<?php
for ($i=0;$i<count($model->orgTypeArray);$i++)
{
  if(empty($model->orgTypeArray[$i]['isSet'])) continue; 
 
  $model->printHeadLineSelect($i);  
  $model->printArticlesSel($i); 


}

?>







