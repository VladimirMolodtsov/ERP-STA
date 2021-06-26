<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper; 


$curUser=Yii::$app->user->identity;
$this->title = 'Дубликаты контрагента';
//$this->params['breadcrumbs'][] = $this->title;
$record=$model->loadOrgRecord();

$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');
    
$orgGrpRef =$record->orgGrpRef;

$phones =  array_unique(ArrayHelper::map($model->getPhoneList(), 'id', 'phone')); 
sort($phones);
$emails =  array_unique(ArrayHelper::map($model->getEmailList(), 'id', 'email')); 
sort($emails);

?>

<style> 

.clickable{
 color:Blue;
 font-size: 16px;
}

.clickable:hover {
 color:Blue;
 cursor:pointer;
}


</style>
<script>

function mergeWithCurrent(slaveRef){

    openSwitchWin("site/merge-org&masterRef=<?= $model->orgId?>&slaveRef="+slaveRef);
}

function addToHolding(slaveRef){
      
   openSwitchWin("site/add-org-in-grp&grpId=<?= $orgGrpRef ?>&orgId="+slaveRef); 
}

function createHolding(slaveRef){
 
  alert('Головная организация не входит в групповой контакт!') 
    
}


</script>
<?php $s="font-weight:bold;"; if ($record->isOrgActive == 0) $s="font-weight:bold;text-decoration: line-through;" ?>
 <font size="+1"> 
 <div class='row'>
    <div class='col-md-5'>    
    <?php
       $action="openWin('site/org-detail&orgId=".$model->orgId."','orgDetail');"; 
       echo \yii\helpers\Html::tag( 'div',  Html::encode($this->title) .":".  Html::encode($record->title) , 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,                     
                     'style'   => $s,
                   ]);
    ?>    
    </div>    

    <div class='col-md-5' style='text-align:right;'>
    <?php if ($record->orgGrpRef == 0) echo "Не входит в групповой контакт.";  
                                 else  echo "Группа компаний <b>".$model->orgGroupTitle."</b>";        
     ?>        
    </div>    
    
    <div class='col-md-1'>
    
    
    </div>    
 
    <div class='col-md-1'>

    </div>    
 </div>
</font>    
<br>


 <div class='row'>
     <div class='col-md-2'>    
        ID: <b><?= Html::encode($record->id) ?></b></span>    
    </div>    

    <div class='col-md-2'>    
        ИНН: <b><?= Html::encode($record->orgINN) ?></b></span>    
    </div>    
    <div class='col-md-2'>    
        КПП: <b><?= Html::encode($record->orgKPP) ?></b></span>    
    </div>    

    <div class='col-md-2'>
    <ul>
<?php
    for ($i=0;$i<count($phones); $i++ )
    {      
      echo "<li>".$phones[$i]."</li>";
    }
?> 
    </ul>
    </div>    
 
    <div class='col-md-2'>
    <ul>
<?php
    for ($i=0;$i<count($emails); $i++ )
    {      
      echo "<li>".$emails[$i]."</li>";
    }
?> 
    </ul>

    </div>    
 </div>

 <div class='spacer'></div>


<?php
$orgINN = $record->orgINN;
$orgKPP = $record->orgKPP;
$orgTitle = $record->title;
$phones =  $model->getPhoneList();
$mails =  $model->getEmailList();


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
            
            [
                'attribute' => 'orgRef',
                'label'     => 'Id',
                'format' => 'raw',     
            ],            

            
            [
                'attribute' => 'orgTitle',
                'label'     => 'Дубликат',
                'format' => 'raw',   
                'value' => function ($model, $key, $index, $column) use ($orgTitle, $orgGrpRef) {	                    
                if ($model['orgTitle'] == $orgTitle ) $cl ='Crimson';
                if (!empty($model['orgGrpRef']) && $orgGrpRef == $model['orgGrpRef'] ) $cl ='Green';       
                $s=""; 
                if ($model['isOrgActive'] == 0) 
                {
                 $s="font-weight:bold;text-decoration: line-through;color:Crimson";                 
                }
                 $action="openWin('site/org-detail&orgId=".$model['orgRef']."','orgDetail');"; 
                 $val = \yii\helpers\Html::tag( 'div', $model['orgTitle'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,                     
                     'style'   => $s,
                   ]);
                 return $val;
                },                
            ],            

            [
                'attribute' => 'orgINN',
                'label'     => 'ИНН',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use ($orgINN, $orgGrpRef) {	                    
                if ($model['orgINN'] == $orgINN ) $cl ='Crimson';
                else $cl ='Black';                    
                if (!empty($model['orgGrpRef']) && $orgGrpRef == $model['orgGrpRef'] ) $cl ='Green';       
                    return "<font color=$cl>".$model['orgINN']."</font>";
                },                
                
            ],            
            [
                'attribute' => 'orgKPP',
                'label'     => 'КПП',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use ($orgKPP, $orgGrpRef) {	                    
                if ($model['orgKPP'] == $orgKPP ) $cl ='Crimson';
                else $cl ='Black';                    
                if (!empty($model['orgGrpRef']) && $orgGrpRef == $model['orgGrpRef'] ) $cl ='Green';       
                    return "<font color=$cl>".$model['orgKPP']."</font>";
                },                                
            ],            

            [
                'attribute' => '-',
                'label'     => 'Телефоны',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use ($phones, $orgGrpRef) {	                                    
                 $phoneList =  Yii::$app->db->createCommand('SELECT DISTINCT phone from {{%phones}} 
                 where ref_org=:ref_org',[':ref_org'=>$model['orgRef']])->queryAll();
                 
                 $N0 = count($phones);
                 $N1 = count($phoneList);                 
                 $ret="";
                 for ($i=0; $i< $N1; $i++)
                 {
                    $cl = 'Black'; 
                    if (!empty($model['orgGrpRef']) && $orgGrpRef == $model['orgGrpRef'] ) $cl ='Green';       
                    else {
                    for ($j=0; $j< $N0; $j++){if ($phones[$j]['phone'] == $phoneList[$i]['phone']){$cl='Crimson'; break;} } 
                    }
                    $ret.="<font color=$cl>".$phoneList[$i]['phone']."</font></br>";                    
                 }                     

                  return   $ret;
                },                                
            ],            

            [
                'attribute' => '-',
                'label'     => 'Почта',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use ($mails, $orgGrpRef) {	                                    
                 $emaillist =  Yii::$app->db->createCommand('SELECT DISTINCT email from {{%emaillist}} 
                 where ref_org=:ref_org',[':ref_org'=>$model['orgRef']])->queryAll();
                 
                 $N0 = count($mails);
                 $N1 = count($emaillist);                 
                 $ret="";
                 for ($i=0; $i< $N1; $i++)
                 {
                    $cl = 'Black'; 
                    if (!empty($model['orgGrpRef']) && $orgGrpRef == $model['orgGrpRef'] ) $cl ='Green';       
                    else {                    
                    for ($j=0; $j< $N0; $j++){ if ($mails[$j]['email'] == $emaillist[$i]['email']){$cl='Crimson'; break;} }
                    }
                    $ret.="<font color=$cl>".$emaillist[$i]['email']."</font></br>";                    
                 }                     

                  return   $ret;
                },                                
            ],            


            [
                'attribute' => '-',
                'label'     => 'Действия',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) use ($orgGrpRef){	                                    
                $val ="";
                   $action = "mergeWithCurrent(".$model['orgRef'].")"; 
                $val .= \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-transfer  clickable',
                     'onclick' => $action,
                     'title'   => 'Слить с текущим',
                   ]);

/*                $val .="&nbsp;";
                   $action = "markAsGood(".$model['orgRef'].")"; 
                $val .= \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-ok-sign  clickable',
                     'onclick' => $action,
                     'title'   => 'Подтвердить различия',
                   ]);*/
                   
                    
                $val .="&nbsp;";
                if (!empty($orgGrpRef) ) $action = "addToHolding (".$model['orgRef'].")"; 
                                    else $action = "createHolding(".$model['orgRef'].")"; 
                $val .= \yii\helpers\Html::tag( 'span', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-plus-sign clickable',
                     'onclick' => $action,
                     'title'   => 'Добавить в холдинг',
                   ]);
                $val .="&nbsp;";
                
                return $val;
                    
                
                },                                
            ],            

                        
            /****/
        ],
    ]
); 


