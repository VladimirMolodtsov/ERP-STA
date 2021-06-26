<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

use kartik\date\DatePicker;
use kartik\time\TimePicker;



$curUser=Yii::$app->user->identity;
$this->title = 'Контакт с контрагентом';
//$this->params['breadcrumbs'][] = $this->title;
$record=$orgModel->loadOrgRecord();
$phoneList=$orgModel->getCompanyPhones();
$adressList=$orgModel->getCompanyAdress();

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style> 

.child {
    width:100%
}
.child:hover {
   color:Blue;
   text-decoration: underline;
   background-color: LightGreen ;
   cursor:pointer;
}

.child-normal {
   color:Black;
}


.child-progress {
   color:Darkorange;
   font-weight: bold;
}

.child-finished {
   color:DarkGreen;
   font-weight: bold;
}

.child-selected {
   background-color: LightGreen;
}


.part-header
{
    padding: 2px;     
    color: Black;
    text-align: right;    
    background-color: LightBlue ;
    font-size: 11pt;
    font-weight: Bold;
}



</style>

<script type="text/javascript">

var refDeals    =  new Array();
var linkedDeals =  new Array();

function chngLinked(refDeal, stage) {

    id ="block_"+refDeal+"_"+stage;      
    style = document.getElementById(id).style;

     
     linkedDeals[refDeal][stage]=1;
     
     style.backgroundColor = 'LightGreen';   
        
    closeDialog('#showZakazDialog');
    closeDialog('#showSchetDialog');
    resetRefs();    
}

function resetRefs()
{
  var strRef="";
  var strStage="";
  var i;
  var j;
  for(i=0; i< refDeals.length; i++)
  {
  
    for(j=0;j<6;j++)strStage=strStage+linkedDeals[refDeals[i]][j]+",";
    strRef=strRef+refDeals[i]+"["+strStage+"],";
  }
  document.getElementById('refDeal').value=strRef;  
}
function closeZakazFrame()
{
    closeDialog('#showZakazDialog');

}

function closeSchetFrame()
{
    closeDialog('#showSchetDialog');

}

function openPrepare(zakazId) {
   //chngLinked(zakazId, 0);                          
   document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-zakaz-frame&noframe=1&orgId=<?=$model->orgId?>&zakazId='+zakazId;
   showDialog('#showZakazDialog');
   //$('#showZakazDialog').modal('show');     
}

function openZakaz(zakazId) {
    //chngLinked(zakazId, 1);
    document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-zakaz-frame&noframe=1&orgId=<?=$model->orgId?>&zakazId='+zakazId;
    showDialog('#showZakazDialog');
}


function openSchet(zakazId, schetId) {
      //  chngLinked(zakazId, 2);
    document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-schet-frame&noframe=1&orgId=<?=$model->orgId?>&id='+schetId;
    showDialog('#showZakazDialog');
}


function openOplata(zakazId, schetId) {

    document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-schet-frame&noframe=1&orgId=<?=$model->orgId?>&id='+schetId;
    showDialog('#showZakazDialog');
}

function openSupply(zakazId, schetId) {

    document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-schet-frame&noframe=1&orgId=<?=$model->orgId?>&id='+schetId;
    showDialog('#showZakazDialog');
}

function openFinish(zakazId, schetId) {

    document.getElementById('frameshowZakazDialog').src='index.php?r=market/market-schet-frame&noframe=1&orgId=<?=$model->orgId?>&id='+schetId;
    showDialog('#showZakazDialog');
}

// onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');                 
//openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['zakazId']."','zakazWin');                



function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone, phoneContactFIO)
{     

  if (phoneContactFIO != '')document.forms["Mainform"]["orgcontactform-contactfio"].value=phoneContactFIO;
  document.forms["Mainform"]["orgcontactform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone; 
  $('#showContactDialog').modal('hide');     
}

function setEmail(email, emailContactFIO)
{     

  if (emailContactFIO != '')document.forms["Mainform"]["orgcontactform-contactfio"].value=emailContactFIO;
  document.forms["Mainform"]["orgcontactform-contactemail"].value=email;
  //document.getElementById("cphone").innerHTML =phone;   
}


function doCall()
{       
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["Mainform"]["orgcontactform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');      
}

function doMail()
{       
  window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.forms["Mainform"]["orgcontactform-contactemail"].value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');      
}

function checkRejectNote()
{
    var str = document.forms["Mainform"]["orgcontactform-note"].value;
    str.trim();
   if (str=="") 
   {
       document.forms["Mainform"]["orgcontactform-note"].value="Контакт не состоялся";
   }
    
}
function checkOkNote()
{
    var str = document.forms["Mainform"]["orgcontactform-note"].value;
    str.trim();
   if (str=="Контакт не состоялся") 
   {
       document.forms["Mainform"]["orgcontactform-note"].value="";
   }
    
}

/**********/

function showContactPhoneList(contactFio) {
document.getElementById('frameShowContactDialog').src='index.php?r=site/show-phone-contact&noframe=1&refOrg=<?= $orgModel->orgId ?>&contactFIO='+contactFio;
$('#showContactDialog').modal('show');     
}

/**********/

/*Выносим в отдельный блок все что связано с назначением события*/
function showSelectEventTime() {

var d=document.getElementById('nextContactDate').value;
document.getElementById('frameEventTimeDialog').src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date='+d;
$('#selectEventTimeDialog').modal('show');     
}

function setSelectEventTime(eventTime) {
document.getElementById('nextContactTime').value = eventTime;
//document.getElementById('nextContactTimeShow').innerHTML = eventTime;
$('#selectEventTimeDialog').modal('hide');     
}

function submitMainForm ()
{
    if (document.getElementById('nextContactDate').value =='')
    {
        alert ("Дата следующего контакта должны быть заполнены");
        return;
    }
    
    if (document.getElementById('nextContactTime').value =='')
    {
        alert ("Дата и время следующего контакта должны быть заполнены");
        return;
    }

    if (document.getElementById('nextContactTime').value =='-')
    {
        alert ("Дата и время следующего контакта должны быть заполнены");
        return;
    }
  
    document.getElementById('Mainform').submit();        
}



</script>
<h3>
  <?= Html::encode($this->title) ?>:      
  <strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong>
</h3>

<div class="part-header"> Данные по контакту</div>
<div class="spacer"> </div>
 <?php $form = ActiveForm::begin(['id' => 'Mainform',]); ?>  

<div class ='row'>
   <div class ='col-md-5'>   

 <table border=0 style="border:0px" width=100%>
  <tr>        
     <td colspan='2'>   
     <?= $form->field($model, 'contactFIO')->label('Контактное лицо')?>     
     </td>
  </tr>
  <tr>        
     <td><?= $form->field($model, 'contactPhone')->label(false)?> </td>
     <td width="50px"> <div style="position:relative; top:-7px; left:10px;"> &nbsp;<button class="btn btn-primary" type="button" onclick="javascript:doCall();"><span class="glyphicon glyphicon-phone-alt"></span></button> </div></td>
  </tr>
  
  <tr>
     <td><?= $form->field($model, 'contactEmail')->label(false)?></td>
     <td  width="50px"><div style="position:relative; top:-7px; left:10px;">&nbsp;<button class="btn btn-primary"  type="button"  onclick="javascript:doMail();"><span class="glyphicon glyphicon-envelope"></button></div></td>
  </tr>
</table>      
  
 <table border=0 style="border:0px" width=100%> 
  <tr>        
    <td><?= $form->field($model, 'nextContactDate')->textInput([/*'class' => 'tcal',*/ 'style'=>'width:250px;', 'type' => 'date', 'id' =>'nextContactDate',
        'onchange' => 'showSelectEventTime()'  ])->label(false)?>
    </td>  
    <td><?= $form->field($model, 'nextContactTime')->textInput(['id' => 'nextContactTime', 'type' => 'time'])->label(false) ?></td>  
  </tr>
 </table>
    
        <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
<!------ 
 <table border=0 style="border:0px" width=100%> 
  <tr>        
    <td><?= $form->field($model, 'status')->radio(['label' => 'Информация получена', 'value' => 1, 'onclick' => 'checkOkNote();', 'uncheck' => null]) ?>
    </td>  
    <td><?= $form->field($model, 'status')->radio(['label' => 'Звонок не состоялся', 'value' => 2, 'onclick' => 'checkRejectNote();', 'uncheck' => null]) ?>          </td>  
  </tr>
 </table>
  --->          
</div>
   
<div class ='col-md-7'>   
<div style='height:350px;'>
<?php Pjax::begin(); ?>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $phoneProvider,
          'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],          
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
               'attribute' => 'phoneContactFIO',
                'label' => 'Контактное лицо',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                if (!empty ($model['phoneContactFIO'])){
                    return 
                    "<a href='#' onclick='javascript:showContactPhoneList(\"".Html::encode($model['phoneContactFIO'])."\")'>".
                    Html::encode($model['phoneContactFIO'])."</a>";                    
                    }
                return "&nbsp;";
                }                
            ],
          
             [
                'attribute' => 'phone',
                'label'     => 'Телефон',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                if (!empty ($model['phone'])){
                    return 
                    "<a href='#' onclick='javascript:setPhone(\"".Html::encode($model['phone'])."\",\"".Html::encode(trim($model["phoneContactFIO"]))."\");'>".Html::encode($model['phone'])."</a>";                    
                    }
                return "&nbsp;";
                    }
                    
            ],
            
            [
               'attribute' => 'lastD',
                'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.Y'],
            ],
          
           

        ],
    ]
);
?>
<?php Pjax::end(); ?>
</div>


</div>   
</div>
    
    

<!--- ******************************************* --->  

<div class="part-header"> Список активных сделок</div>   
<?php Pjax::begin(); ?>
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $activityProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
        
              [
                'attribute' => 'zakazId',
                'label'     => 'Подготовка',
                'format' => 'raw',            
                'contentOptions' =>['style'=>'padding:0px;width:75px; '],
                
                'value' => function ($model, $key, $index, $column) {                    

                $id= "block_".$model['zakazId']."_"."0";
                $ret="<script>refDeals.push(".$model['zakazId'].");linkedDeals[".$model['zakazId']."]= new Array(0,0,0,0,0,0);</script>\n";                
                $action="openPrepare(".$model['zakazId'].");";
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT isActive, isFormed, isGoodReserved from {{%zakaz}} where id=:refDeal', 
                [':refDeal' => $model['zakazId'],])->queryAll();                

                 $listWare= Yii::$app->db->createCommand(
                'SELECT good from {{%zakazContent}} where refZakaz=:refDeal and good is not null and isActive=1', 
                [':refDeal' => $model['zakazId'],])->queryAll();                
                
                                
                $class='child-progress';
                if ($listData[0]['isFormed'] == 1) $class='child-finished';
                else  if (count ($listWare) > 0)   $class='child-finished';
                
                $ret .="<div id='".$id."' class='child $class' onclick='".$action."' >". date("d.m.y", strtotime($model['formDate']))."</div>";                                           
                
                return $ret;                                           
                },
            ],            

        
            [
                'attribute' => 'zakazId',
                'label'     => 'Заказ',
                'format' => 'raw',            
                'contentOptions' =>['style'=>'padding:0px;width:350px; '],
                
                'value' => function ($model, $key, $index, $column) {                    

                $id= "block_".$model['zakazId']."_"."1";  
                $ret="";              
                $action="openZakaz(".$model['zakazId'].");";

                 $listData= Yii::$app->db->createCommand(
                'SELECT isActive, isFormed, isGoodReserved from {{%zakaz}} where id=:refDeal', 
                [':refDeal' => $model['zakazId'],])->queryAll();                
                
                $class='child-progress';
                if (count($listData)==0)$class='child-normal';
                if ($listData[0]['isFormed'] == 1) $class='child-finished';

                 $listWare= Yii::$app->db->createCommand(
                'SELECT good from {{%zakazContent}} where refZakaz=:refDeal and good is not null and isActive=1 order by value*count DESC', 
                [':refDeal' => $model['zakazId'],])->queryAll();                
                
                $title="";
                for ($i=0; $i<count($listWare); $i++ )
                {
                    $title.= mb_substr($listWare[$i]['good'],0,75,'utf-8')."...\n";                
                }
                $title.="\n";
                
                if (count($listWare) == 0)$class='child-normal';
                $ret.="<div id='".$id."' class='child $class' title='$title' onclick='".$action."' >".mb_substr($listWare[0]['good'],0,40,'utf-8')."...</div>";                                                          
                return $ret; 
                },
            ],            

            [
                'attribute' => 'schetId',
                'label'     => 'Счет',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;width:175px; '],
                'value' => function ($model, $key, $index, $column) {                                    
                
                $id= "block_".$model['zakazId']."_"."2";
                $ret="";
                $action="openSchet(".$model['zakazId'].",".$model['schetId'].");";

                $status= Yii::$app->db->createCommand(
                'SELECT max(docStatus) from {{%schet}} where id=:refSchet', 
                [':refSchet' => $model['schetId'],])->queryScalar();                
   
   
                $class='child-progress';
                $progress = "";
                if ($status == 1) {$class='child-progress'; $progress = "Счет зарегистрирован"; }
                if ($status == 2) {$class='child-finished';  $progress = "Счет получен клиентом"; }
                else  if (empty ($status) )   $class='child-normal';
                
                
                
                $title = "Счет № ". $model['schetNum']." на сумму ".number_format($model['schetSumm'],2,'.',' ')." от ". date("d.m.Y", strtotime($model['schetDate']));                                           
                 
                 $title .= "\n".$progress;
                 
                 $ret.="<div id='".$id."' class='child $class' title='$title' onclick='".$action."' >&nbsp;";
                 if (!empty($model['schetId']) ){
                 $ret.= number_format($model['schetSumm'],2,'.','&nbsp;')."&nbsp;от&nbsp;". date("d.m.y", strtotime($model['schetDate']));                                                            
                 if (empty($model['ref1C'])) $ret.= "<span class='glyphicon glyphicon-warning-sign' style='color:Darkorange'></span>";
                 }
                 $ret.="</div>";
                 return $ret;
                },
            ],            
    
            [
                'attribute' => 'Оплата',
                'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;width:150px; '],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                
                $id= "block_".$model['zakazId']."_"."3";
                $ret="";
                $action="openOplata(".$model['zakazId'].",".$model['schetId'].");";
                
                                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();
                 
                 
                $class='child-progress';
                $progress = "";
                $title ="";

                if ($listData[0]['sumOplata']+10 > $model['schetSumm']) {$class='child-finished';  }
                if (count($listData)==0)   $class='child-normal';
                if($listData[0]['sumOplata'] == 0) $class='child-normal';
                if ($listData[0]['sumOplata'] > 0)  $title ="Оплата на сумму ". number_format($listData[0]['sumOplata'],2,'.',' ')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));       
                                                                        
                 $ret.="<div id='".$id."' class='child $class' title='$title' onclick='".$action."' >&nbsp;";
                 $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;'); 
                 $ret.="</div>";
                 return $ret;                  
                },
            ],            
            
            [
                'attribute' => 'lastSupply',
                'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;width:150px; '],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                $id= "block_".$model['zakazId']."_"."4";
                $ret="";
                $action="openSupply(".$model['zakazId'].",".$model['schetId'].");";
                
                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();

                $class='child-progress';
                $progress = "";
                $title ="";
                
                                                                 
                if ($listData[0]['sumSupply']+10 > $model['schetSumm']) {$class='child-finished';  }
                if (count($listData)==0)   $class='child-normal';
                if($listData[0]['sumSupply'] == 0) $class='child-normal';
                if ($listData[0]['sumSupply'] > 0)  $title ="Оплата на сумму ". number_format($listData[0]['sumSupply'],2,'.',' ')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));       
                                                                        
                 $ret.="<div id='".$id."' class='child $class' title='$title' onclick='".$action."' >&nbsp;";
                 $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;'); 
                 $ret.="</div>";
                 return $ret;                  
             
                },
            ],            


            [
                'attribute' => '-',
                'label'     => 'Завершение',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;'],
                'value' => function ($model, $key, $index, $column) {                                    
                 
                $id= "block_".$model['zakazId']."_"."5";
                $ret="";
                $action="openFinish(".$model['zakazId'].",".$model['schetId'].");";

                $status= Yii::$app->db->createCommand(
                'SELECT max(supplyState) from {{%schet}} where id=:refSchet', 
                [':refSchet' => $model['schetId'],])->queryScalar();                
   
   
                $class='child-progress';
                $progress = "";                
                if ($status == 5) {$class='child-finished';  $progress = "Работа со счетом завершена /Документы сданы"; }
                else  if (empty ($status) )   $class='child-normal';
               
                $title = "Счет № ". $model['schetNum']." на сумму ".number_format($model['schetSumm'],2,'.',' ')." от ". date("d.m.Y", strtotime($model['schetDate']));                                                            
                 $title .= "\n".$progress;                 
                 $ret.="<div id='".$id."' class='child $class' title='$title' onclick='".$action."'> &nbsp;";
                 if ($status == 5) $ret.= "<span class='glyphicon glyphicon-ok' style='color:Green'></span>";
                 $ret.="</div>";
                 return $ret;
                 
                },
            ],            
            
                        
            /****/
        ],
    ]
); 
?>

<?php Pjax::end(); ?>

<table class='table table-bordered'>
<tr>
 <td style='padding:0px;width:150px;'><div class='child' >Новая сделка</div></td>
 <td style='padding:0px;width:150px;'></td>
 <td style='padding:0px;width:250px;'></td>
 <td style='padding:0px;'></td>
</tr>
</table>
<div style='text-align:right; verical-align:bottom;'>
    <a class='btn btn-primary' href="#" onclick="javascript: submitMainForm();" style ='background-color: ForestGreen;'> Сохранить </a>
</div>    
<?= $form->field($model, 'orgId')->hiddenInput()->label(false)?>
<?= $form->field($model, 'refDeal')->hiddenInput( ['id' =>'refDeal'])->label(false)?>
   <?php ActiveForm::end(); ?>
  
  
<div class="part-header"> Предыдущие Контакты </div>   
  
<?php Pjax::begin(); ?>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $contactProvider,
          'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],          
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            [
                'attribute' => 'grd_contactDate',
                    'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.y H:i'],
            ],
          
            'grd_contactFIO:raw:Контактное лицо',
            
              [
                'attribute' => 'grd_phone',
                    'label'     => 'Телефон/почта',                
                'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                    
                                        
                                        
                    if (!empty ($model['grd_phone'])){
                    return 
                    "<a href='#' onclick='javascript:setPhone(\"".Html::encode($model['grd_phone'])."\",\"".Html::encode(trim($model["grd_contactFIO"]))."\");'>".Html::encode($model['grd_phone'])."</a>";
                    
                    }
                    if (!empty ($model['contactEmail'])){return 
                    "<a href='#' onclick='javascript:setEmail(\"".Html::encode($model['contactEmail'])."\",\"".Html::encode(trim($model["grd_contactFIO"]))."\");'>".Html::encode($model['contactEmail'])."</a>";
                    }
                    
                return "&nbsp;";
                    }
                    
            ],

               [
                'attribute' => 'note',
                    'label'     => 'Комментарий',                
                'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                         return mb_substr($model['grd_note'],0,260);
                    }
                    
            ],

               'grd_userFIO:raw:Менеджер',
        ],
    ]
);
?>
<?php Pjax::end(); ?>
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:history.back();"/>





<!--- Контакт старт--->   

<?php
/********** Диалог с выбором времени *****************/
Modal::begin([
    'id' =>'selectEventTimeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameEventTimeDialog'  src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date=<?= $model->nextContactDate ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>       
</div><?php
Modal::end();
/***************************/

/********** Диалог с показом *****************/
Modal::begin([
    'id' =>'showContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='520px' frameborder='no' id='frameShowContactDialog'  src='index.php?r=site/show-phone-contact&noframe=1&contactFIO=&refOrg=' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>       
</div><?php
Modal::end();
/***************************/
?>


<!--/********** Диалог с показом *****************/-->
  
<!--- Форма заказы ----->	
  <div id="showZakazDialog" class='popup_form' style='height: 690px; width: 1100px; margin-left: -500px; margin-top: -300px;'>
	<span id="showZakazDialog_close"  class='popup_close' onclick='closeDialog("#showZakazDialog")' ><span class='glyphicon glyphicon-remove'></span></span>	
	<iframe width='1050px' height='650px' frameborder='no' id='frameshowZakazDialog'  src='index.php?r=market/market-zakaz-frame&orgId=<?=$model->orgId?>&zakazId=' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>  

<!--- Форма счета ----->	
  <div id="showSchetDialog" class='popup_form' style='height: 690px; width: 1100px; margin-left: -500px; margin-top: -300px;'>
	<span id="showSchetDialog_close"  class='popup_close' onclick='closeDialog("#showZakazDialog")' ><span class='glyphicon glyphicon-remove'></span></span>	
	<iframe width='1050px' height='650px' frameborder='no' id='frameshowZakazDialog'  src='index.php?r=market/market-schet-frame&orgId=<?=$model->orgId?>&schetId=' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>  
  
  
<!--/***************************/-->



     
<!--- Контакт финиш--->  
 <div id="overlay" class='overlay'></div>
