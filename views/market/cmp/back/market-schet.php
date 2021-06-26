<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Collapse;

/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Ведение счета';
//$this->params['breadcrumbs'][] = $this->title;

$schetRecord = $model->loadSchetData();

$listStatus = $model-> getListStatus();

$record = $model->orgRecord;

//$this->registerCssFile('@web/tcal.css');
//$this->registerJsFile('@web/tcal.js');

$this->registerCssFile('@web/phone.css');
$this->registerCssFile('@web/css/zvonki-common.css');


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/market/market-schet.js');
$this->registerCssFile('@web/css/market/market-schet.css');         
?>

<style>

</style>

<script type="text/javascript">
/*****************************************/
/***** Статусы ***************************/
/*****************************************/
var    schetStatusList = new Array();
var    cashStatusList = new Array();
var    supplyStatusList = new Array();

schetStatusList[0]="&nbsp;";        
cashStatusList[0]="&nbsp;";    
supplyStatusList[0]="&nbsp;";    

<?PHP
    $schetStatus=$listStatus['schet_status'];
    for ($i=0;$i<count($schetStatus);$i++)  echo "schetStatusList[".$schetStatus[$i]['razdelOrder']."]='".$schetStatus[$i]['Title']."';\n";
    
    $cashStatus=$listStatus['cash_status'];    
    for ($i=0;$i<count($cashStatus);$i++)  echo "cashStatusList[".$cashStatus[$i]['razdelOrder']."]='".$cashStatus[$i]['Title']."';\n";

    $supplyStatus=$listStatus['supply_status'];
    for ($i=0;$i<count($supplyStatus);$i++) echo "supplyStatusList[".$supplyStatus[$i]['razdelOrder']."]='".$supplyStatus[$i]['Title']."';\n";    
?>
var curSchetStatus = <?=$model->docStatus?>;
var maxSchetStatus=<?=$schetStatus[count($schetStatus)-1]['razdelOrder']?>;
var curCashStatus = <?=$model->cashState?>;
var maxCashStatus=<?=$cashStatus[count($cashStatus)-1]['razdelOrder']?>;
var curSupplyStatus = <?=$model->supplyState?>;
var maxSupplyStatus = <?=$supplyStatus[count($supplyStatus)-1]['razdelOrder']?>;

function failSupplyStatus()
{
 alert('Поставка не завершена. Окончание работы со счетом не возможно!');
}
/**************************************/
function chngSupplyStatus (n)
{
  var i=0;
console.log(n);  
  var supplyRequestStatus = <?=$model->supplyRequestStatus?>;
  /*если не отказано или не выполнено*/
  if (!(supplyRequestStatus & (0x00004|0x00008)) && supplyRequestStatus >0 && n <2)
  {
      alert ('Поставка в процессе исполнения - отмена статуса невозможна!');
      return;
  }
  
  curSupplyStatus = n;
  
  for (i=1; i<=n; i++)  
  {
    id="supplyMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#4169E1';
  }
  
  for (i=n+1; i<=maxSupplyStatus ; i++)  
  {
    id="supplyMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#C0C0C0';
  }  
    document.forms["Mainform"]["marketschetform-supplystate"].value=n;  

  if (n == 1)  {document.getElementById('btn_request').style.display = 'inline';}
          else {document.getElementById('btn_request').style.display = 'none';}

          console.log(document.getElementById('btn_request').style.display);      
          
  if (n == 2)  {document.getElementById('btn_otpravka').style.display = 'inline';}
          else {document.getElementById('btn_otpravka').style.display = 'none';}
  
 /* if (n == 4)  {document.getElementById('btn_reply').style.display = 'inline';}
          else {document.getElementById('btn_reply').style.display = 'none';}*/
  
     var $st = document.forms["Mainform"]["marketschetform-status"]; 
   if (n == maxSupplyStatus)
   {  
     if ($st[2].checked!= true)$st[2].checked=true;      
     chngSchetStatus (maxSchetStatus); 
     chngCashStatus  (maxCashStatus);     
  }
  else
  {
    if ($st[2].checked) $st[0].checked=true;          
  }

  /*if (n != 5) {document.getElementById('btn_finish').style.display = 'none';}
         else {document.getElementById('btn_finish').style.display = 'inline';}*/
     
}

function doNewCall()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.getElementById("contactPhone").value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function doMail()
{      
  win=window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.getElementById("contactEmail").value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
}

function restoreSchetStatus()
{    
 setSchetStatus(curSchetStatus);
}

function setSchetStatus(id)
{
document.getElementById('schet_status').innerHTML =schetStatusList[id];    
}

function chngSchetStatus (n)
{
  var i=0;
  curSchetStatus = n;
  for (i=1; i<=n; i++)  
  {
    id="schetMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#4169E1';
  }
  for (i=n+1; i<=maxSchetStatus; i++)  
  {
    id="schetMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#C0C0C0';
  }  
  document.forms["Mainform"]["marketschetform-docstatus"].value=n;  
}

/**************************************/

function setCashStatus(id)
{
document.getElementById('cash_status').innerHTML =cashStatusList[id];    
}

function chngCashStatus (n)
{
  var i=0;
  curCashStatus = n;
  chngSchetStatus (maxSchetStatus);
  for (i=1; i<=n; i++)  
  {
    id="cashMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#4169E1';
  }  
  for (i=n+1; i<=maxCashStatus; i++)  
  {
    id="cashMarker_"+i;
    document.getElementById(id).style.backgroundColor ='#C0C0C0';
  }  
  document.forms["Mainform"]["marketschetform-cashstate"].value=n;  
}


function restoreCashStatus()
{
 setCashStatus(curCashStatus);    
}


/**************************************/
function setSupplyStatus(id)
{
document.getElementById('supply_status').innerHTML =supplyStatusList[id];    
}


function setToFinishState()
{    
     chngSchetStatus (maxSchetStatus); 
     chngCashStatus  (maxCashStatus);     
     chngSupplyStatus (maxSupplyStatus);
}

function restoreSupplyStatus()
{

    setSupplyStatus(curSupplyStatus);        
}

/* выставим*/
function parentReread()
{
    //alert(window.opener.location); 
    window.opener.reloadSchet(<?= $schetRecord->id ?>);
}

</script>


<!---------------------------------------------------------------------->
<!--   <a class='btn-sm btn-primary box_shadow' href="index.php?r=market/market-zakaz&parent=schet&orgId=<?= Html::encode($record->id)?>&zakazId=<?= Html::encode($model->zakazId)?>"><?= Html::encode($model->schetNumber)?></a> -->
<!---------------------------------------------------------------------->

<table border='0' width='1160px'>
<tr>
  <td width='350px' valign='top'> <?php/*левый блок*/?>  
    <div class="page-title"><?= Html::encode($this->title) ?>  № 
        <input class="btn btn-primary box_shadow" style='padding-top:2px; padding-bottom:2px; padding-left:4px; padding-right:4px; height:25px;' type="button" value=" <?= Html::encode($model->schetNumber)?> " 
        onclick="javascript:openWin('market/market-zakaz&noframe=1&parent=schet&orgId=<?= $record->id?>&zakazId=<?= $model->zakazId?>', 'zakazWin');"/>
        <br>от: <strong><?= Html::encode($model->schetDate)?></strong>  
        <br>на сумму:<strong>
        <?php
        if  (empty($schetRecord->schetSumm))  {echo "<font color='red'>N/A</font";}
        else if  (empty($schetRecord->ref1C)) {echo "<font color='red'>".round($schetRecord->schetSumm,2)."</font";}
        else                                  {echo "<font color='green'>".$schetRecord->schetSumm."</font";}
        ?> 
        </strong> руб.
    </div> 
    <div>    Контрагент: 
    <br><u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u>   </div>
    <div style="font-size:10px"><?= Html::encode($record->shortComment)?></div>
  </td>
  <td>
  <?php
  $providerWare = $model->getWareInSchetProvider(Yii::$app->request->get());    

  $contentWare= \yii\grid\GridView::widget(
    [
        'dataProvider' => $providerWare,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [

            [
                'attribute' => 'wareTitle',
                'label'     => 'Номенклатура',
                'format' => 'raw',
            ],        
            [
                'attribute' => 'wareCount',
                'label'     => 'К-во',
                'format' => 'raw',
            ],        
            [
                'attribute' => 'wareEd',
                'label'     => 'Ед.',
                'format' => 'raw',
            ],        
            [
                'attribute' => 'warePrice',
                'label'     => 'Цена',
                'format' => 'raw',
            ],        
            [
                'attribute' => 'dopRequest',
                'label'     => 'Доп. усл',
                'format' => 'raw',
            ],        
            
        ],               
    ]
    );
        
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Содержание счета: ▼",
            'content' => $contentWare,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

  ?>
  </td>
</tr>
</table>  
  
  
  
 <hr noshade size='5'>
 <!--- Регистрация контакта старт--->    
  <?php $form = ActiveForm::begin(['id' => 'Mainform',
        'layout'=>'horizontal',
        'options' => ['class' => 'form-inline'],
        /*'fieldConfig' => [
             'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            'horizontalCssClasses' => [
            //   'label' =>   'col-sm-4',
            //    'offset' =>  'col-sm-offset-4',
            //    'wrapper' => 'col-sm-8',
                'error' => '',
                'hint' => '',
            ],
        ],*/
  ]); ?>



  <!-- Верхний блок background-color:gray-->  
<div style='width:1000px; height:180px; '>  
  
  <div style='width:520px; padding:3px; height:180px; position: relative;  left: 0px; display:inline-block; float:left; background-color:AliceBlue; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%;'>            
  <table style='width:510px;'>   
   <tr>
    <td width='80px'>Счет </td>
    <td>
        <div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
        <?php     
        $id = "schetMarker_0";       
        echo "<div  id=".$id." class='circle planned' style='margin-right:20px' onclick='javascript:chngSchetStatus(0);'  onmouseover='javascript:setSchetStatus(0);' onmouseout='javascript:restoreSchetStatus();'>X</div>";
        
        for ($i=1;$i<=count($schetStatus);$i++)
        {        
         if ($i <= $model->docStatus ){$style="executed";}
               else {$style="planned";}              
        $id = "schetMarker_".$i;       
        echo "<div  id=".$id." class='circle ".$style."' onclick='javascript:chngSchetStatus(".$i.");'  onmouseover='javascript:setSchetStatus(".$i.");' onmouseout='javascript:restoreSchetStatus();'>".$i."</div>";
        
        }
        ?>    
        </div>
        <div id='schet_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$schetStatus[0]['Title']?> </div>
    </td>
   </tr> 
   <tr>
    <td>Деньги</td>
    <td>
        <div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
        <?php 
        $id = "cashMarker_0";       
        echo "<div  id=".$id." class='circle planned' style='margin-right:20px' onclick='javascript:chngCashStatus(0);'  onmouseover='javascript:setCashStatus(0);' onmouseout='javascript:restoreCashStatus();'>X</div>";
        
        
        for ($i=1;$i<=count($cashStatus);$i++)
        {        
         if ($i <= $model->cashState ){$style="executed";}
               else {$style="planned";}              
        $id = "cashMarker_".$i;              
        echo "<div id=".$id." class='circle ".$style."' onclick='javascript:chngCashStatus(".$i.");' onmouseover='javascript:setCashStatus(".$i.");' onmouseout='javascript:restoreCashStatus();'>".$i."</div>";
        }
        ?>    
        <div class='itog' onclick="javascript:openWin('fin/oplata-list&schetId=<?= $schetRecord->id ?>','finWin');" >
        Платежек на сумму:  <?php if (empty($schetRecord->summOplata)) echo 'N/A'; else echo $schetRecord->summOplata; ?> </div>
        
        </div>
        <div id='cash_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$cashStatus[0]['Title']?> </div>
    </td>
   </tr> 
   <tr>
    <td>Товар</td>
    <td>
        <div style='width:440px; padding:5px; height:35px; background-color:GhostWhite'>
        <?php 
        $id = "supplyMarker_0";       
        echo "<div  id=".$id." class='circle planned' style='margin-right:20px' onclick='javascript:chngSupplyStatus(0);'  onmouseover='javascript:setSupplyStatus(0);' onmouseout='javascript:restoreSupplyStatus();'>X</div>";

        $last = count($supplyStatus);
        for ($i=1;$i < $last;$i++)
        {        
         if ($i <= $model->supplyState ){$style="executed";}
               else {$style="planned";}
        $id = "supplyMarker_".$i;                             
        echo "<div  id=".$id." class='circle ".$style."'  onclick='javascript:chngSupplyStatus(".$i.");' onmouseover='javascript:setSupplyStatus(".$i.");' onmouseout='javascript:restoreSupplyStatus();'>".$i."</div>";        
        }
        $id = "supplyMarker_".$last;               
if ($model->isFinishSupplyRequest == 1) $action= "javascript:chngSupplyStatus(".$last.");";       
                                else    $action= "javascript:failSupplyStatus();";       
        echo "<div  id=".$id." class='circle ".$style."' style='margin-left:20px'  onclick='".$action."' onmouseover='javascript:setSupplyStatus(".$last.");' onmouseout='javascript:restoreSupplyStatus();'>".$last."</div>";
        ?>    
        <div class='itog' onclick="javascript:openWin('fin/supply-list&schetId=<?= $schetRecord->id ?>','finWin');" >Поставок на сумму: <?php if (empty($schetRecord->summSupply)) echo 'N/A'; else echo $schetRecord->summSupply; ?> </div>
        </div>
        <div id='supply_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$supplyStatus[0]['Title']?> </div>
    </td>
   </tr> 

  </table>
  </div>
  <!-- Контактные данные -->
  <div style="position:relative; width:450px; top:5px; display:inline-block; float:right; margin-right:10px; ">
  <div>   
    <table border=0 style="border:0px; width:100%; padding:5px" ><tr>
    <td>Телефон:</td>
    <td><div style="position:relative;left:-80px"><?= $form->field($model, 'contactPhone')->textInput(['id'=> 'contactPhone','style'=>'width:300px; margin:0px; padding:10px; left:0px'])->label(false)?></div></td>
    <td valign='top'> <a href='#' class="btn btn-primary" title="Позвонить" onclick="javascript:doNewCall();"/><span class="glyphicon glyphicon-phone-alt"></span></a></td>
    </tr><tr>    
    <td>E-Mail:</td>
    <td><div style="position:relative;left:-80px"><?= $form->field($model, 'contactEmail')->textInput(['style'=>'width:300px; margin:0px; padding:10px; left:0px'])->label(false)?> </div></td>
    <td valign='top'><a href="#" class="btn btn-primary"  title="Написать письмо" onclick="javascript:doMail();"/><span class="glyphicon glyphicon-envelope"></span></a></td></tr>
    <tr>    
    <td>Контактное лицо:</td><td colspan='2' ><div style="position:relative;left:-90px">
    <?= $form->field($model, 'contactFIO')->textInput(['style'=>'width:350px; margin:0px; padding:10px;'])->label(false)?></div></td>
    
    </tr></table>
  
     <p class="phone"><span class="phones" onclick="view('phone_list'); return false">Показать остальные телефоны...</span></p>
    <p>
    <span id="phone_list" class="phone_view">
     <?php
     $phoneList=$model->getCompanyPhones();
     for ($i=0;$i<count($phoneList);$i++)
     {                
        echo "<a href='#' onclick='javascript:setPhone(".Html::encode($phoneList[$i]["phone"]).");'>".Html::encode($phoneList[$i]["phone"])."</a>";                
        if ($phoneList[$i]["status"] == 1){echo " <font color='green'>*</font>";}
        if ($phoneList[$i]["status"] == 2){echo " <font color='red'>*</font>";}
        echo ";\n";
     }
     ?>  
   </span>    
  </div> 
   
 </div> 
 
 <!-------------------------------------->
</div>    



  
  <div style='width:1000px; height:180px;'>  
  
    <!-- Выводим последний контакт -->
   <div style='width:350px; position: relative;  left: 0px; display:inline-block; float:left;'>            
    <div class="small_part-header"> Предыдущий контакт   
    <span style="position:relative;left:40px;font-size:10px"><a href ="index.php?r=site/contacts-detail&id=<?= $record->id ?>"> (Просмотреть историю контактов) </a></span>
    </div>
    <div style='background-color: BlanchedAlmond; height:150px; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%; padding:5px;'>
    <?php

     $contactsDetail=$model->getContactDetail();
     $cnt = count ($contactsDetail);
     if ($cnt> 1) $cnt = 1;
     for ($i=0;$i<$cnt;$i++)
     {    
        echo "<div class='contact_title'> <b>";
        echo date("d-m-Y",strtotime( $contactsDetail[$i]['contactDate']))." </b> ";
        echo $contactsDetail[$i]['contactFIO']."  ".$contactsDetail[$i]['phone']."</div>\n";
        if (mb_strlen($contactsDetail[$i]['note'])> 260){echo "<div>".Html::encode(mb_substr($contactsDetail[$i]['note'],0,260, 'utf-8'))."...</div>\n";}               
        else {echo "<div>".$contactsDetail[$i]['note']."</div>\n";}               
     }
     ?>  
     </div>
     
<script type="text/javascript">
/*Выносим в отдельный блок все что связано с назначением события*/
function showSelectEventTime() {

var d=document.getElementById('nextContactDate').value;
document.getElementById('frameEventTimeDialog').src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date='+d;
$('#selectEventTimeDialog').modal('show');     
}

function setSelectEventTime(eventTime) {
document.getElementById('nextContactTime').value = eventTime;
document.getElementById('nextContactTimeShow').innerHTML = eventTime;
$('#selectEventTimeDialog').modal('hide');     
}

function submitMainForm ()
{
   /* if (document.getElementById('nextContactDate').value =='')
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
    }*/
  
    document.getElementById('Mainform').submit();        
}


</script>     
     
    <div style="margin-left:15px; padding: 5px;">Дата/время следущего чекаута</div> 
    <div class='row'>
        <div class='col-md-8' style='position:relative;left:-30px'>
        
        <?= $form->field($model, 'nextContactDate')->textInput([/*'class' => 'tcal',*/ 'style'=>'width:150px;', 'type' => 'date', 'id' =>'nextContactDate',
        'onchange' => 'showSelectEventTime()'  ])->label(false)?></div>
        <a href="#" onclick='showSelectEventTime()'> <div class='col-md-2' id='nextContactTimeShow'><?=$model->nextContactTime ?></div></a>         
        <?= $form->field($model, 'nextContactTime')->hiddenInput(['id' => 'nextContactTime',])->label(false)?>   
        
    </div> 
  </div>     

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
?>
  
  
    <!-- Статусы -->
  <div style='width:200px; position: relative;  left: 0px; display:inline-block; float:left;'>
    <br>
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 1, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;В работе </nobr><br>    
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 2, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;Отказ</nobr><br>
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 3, 'uncheck' => null, 'onclick' => 'setToFinishState();']) ?> &nbsp;&nbsp;&nbsp;&nbsp;Завершен</nobr><br>        
  </div>
  
  <div style="position:relative; width:450px; top:20px; display:inline-block; float:left; margin-left:-60px; ">
    <div style = "margin-left:120px;">Комментарий: </div>
    <?= $form->field($model, 'note')->textarea(['rows' => 5, 'cols' => 45])->label(false)?>
  </div>

<hr>
<br>&nbsp;<br>
 <div style="position:relative; top:50px; display:inline-block; float:right; margin-right:0px; text-align: right;">
    <?php    
    
    if ($model->supplyState < 1){ $style="style='display:none;'";}
    else 
    {
    $style="";
    
    if ($model->supplyRequestId > 0 ){

        if ($model->supplyRequestStatus == 0 )
        {
                echo "<div class='local_lbl'> Заявка на поставку зарегестрирована.</div><br>";
                $style="style='background-color: DarkGray;'";    }
        }
        if ($model->supplyRequestStatus > 0 )
        {                
                $style="style='display:none;'";
        }                
        
        if ($model->supplyRequestStatus & 0x00004 )
        {            
                echo  "<input class='btn btn-danger local_lbl' style='border-color:Crimson;'  type=button value='Отказ по доставке' onclick='javascript:openWin(\"store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";    
                //echo "<div class='local_lbl' style='border-color:Crimson;' > Отказ по доставке </div><br>";
        }                
        elseif ($model->supplyRequestStatus & 0x00008 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:ForestGreen;'  type=button value='Доставлен' onclick='javascript:openWin(\"store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";                    
        }                        
        elseif ($model->supplyRequestStatus & 0x00002 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:#5bc0de;'  type=button value='Доставляется' onclick='javascript:openWin(\"store/supply-request-new&noframe=0&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";                    
        }                
        elseif ($model->supplyRequestStatus & 0x00001 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:Green;'  type=button value='Принято' onclick='javascript:openWin(\"store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";    
        }                
        
        
    }        
    
    
    echo "<a id='btn_request' class='btn btn-primary' ".$style." href=\"#\" onclick=\"javascript:openWin('market/market-request-supply&schetId=".$model->id."','childwin');\"> Заявка на отгрузку </a>&nbsp; ";          
    
    
    if ($model->supplyState != 2){ $style="style='display:none;'";}
                            else { $style="";}
                            
    echo "<a id='btn_otpravka' ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:openExtWin('".$model->getCfgValue(4)."','childwin');\"> Отчёт об отправке </a>";
    
    if ($model->supplyState != 4){ $style="style='display:none;'";}
                            else { $style="";}
    echo "<a id='btn_reply'  ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:openWin('market/market-reply&id=".$model->id."','childwin');\"> Отзыв </a>";
 
     /*if ($model->supplyState != 5){ $style="style='display:none;'";}
                            else { $style="";}
    echo "<a id='btn_finish'  ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:if (confirm('Работа со счетом будет завершена! Продолжить?')){document.location.href ='index.php?r=market/market-schet-finish&id=".$model->id."'}\"> Завершить работу со счетом </a>";*/
    
    ?>  
    &nbsp;&nbsp;&nbsp;   
   <a class='btn btn-primary' style='background-color: ForestGreen;' href="#" onclick="javascript:openWin('data/sync-schet-by-id&schetId=<?= $schetRecord->id ?>&schetTime=<?= strtotime($schetRecord->schetDate) ?>&refOrg=<?=$record->id?>')"> Синхрон. 1С </a>    
   
   <?php if ( $model->status == 1 )
        //echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform', 'onClick' => 'submitMainForm();']) ?>         
        <a class='btn btn-primary' href="#" onclick="javascript: submitMainForm();" style ='background-color: ForestGreen;'> Сохранить </a>
        <a class='btn btn-primary' href="#" onclick="javascript: if (confirm('Не сохраненные изменения будут потеряны! Выйти?')){document.location.href = 'index.php?r=market/market-schet-close';} "> Выйти </a>
   </div>
   
 </div>
 
 </div>

      


<!--- Контакт финиш--->  
   <?= $form->field($model, 'docStatus')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'cashState')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'supplyState')->hiddenInput()->label(false)?> 

   <?= $form->field($model, 'id')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'src')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>      
   <?php ActiveForm::end(); ?>
   
   

<!--- ******************************************************  --->  
<script type="text/javascript">
restoreSchetStatus();
restoreCashStatus();
restoreSupplyStatus();

<?php

 if ($model->changed == true)
 { switch ($model->src)
   {
    case 'task':
      echo 'parentReread();';
    break;  
   }
 }   
?>

</script>    

  
<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r ($model->debug);
    echo "</pre>";
}
//Регестрим аякс скрипты
/*$js = <<<JS

$('#nextContactDate').on('input', function() {
alert('nextContactDate changed!');
});

 
JS;

$this->registerJs($js);*/



?>  


   
