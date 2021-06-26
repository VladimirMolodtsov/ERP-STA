<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;

/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Ведение счета';
//$this->params['breadcrumbs'][] = $this->title;

$schetRecord = $model->loadSchetData();

$listStatus = $model-> getListStatus();

$record = $model->orgRecord;


         
         
?>
<link rel="stylesheet" type="text/css" href="tcal.css" />
<link rel="stylesheet" type="text/css" href="css/zvonki-common.css" />
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>
     
 /* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 28px;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}     


.circle {
    width: 25px; /* задаете свои размеры */
    height: 25px;
    overflow: hidden;
    display: inline;
    background: #4169E1;
    padding: 2px; /* создание отступов */
    text-align: center;
    border-radius: 50%;
    /* не забываем о кроссбраузерности */
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%
    border: #FFF 1px solid;
    /* тень */
    box-shadow: 0px 1px 1px 1px #bbb; 
    -moz-box-shadow: 0px 1px 1px 1px #bbb;
    -webkit-box-shadow: 0px 1px 1px 1px #bbb;
    /**/
    float:left;
    margin-left:4px;
    margin-top: 0px;
    color:white;
}
.circle:hover{
    background:#0000CD
}

.executed {
    background: #4169E1;
    color:white;
}

.planned {
    background: #C0C0C0;
    color:white;
}


.box_shadow {
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}

.itog {
    margin-top:4px;
    display: inline;    
    float:right;
    text-decoration: underline;  
}
.itog:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}

 .local_lbl
{    
    padding: 2px;
    font-size: 10pt;
    background: white;
    color: black;
    border:1px solid;    
    text-align : center;
    width: 250px;
    
}

</style>

<script type="text/javascript">

function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["Mainform"]["marketschetform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["Mainform"]["marketschetform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function doMail()
{      
  win=window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.forms["Mainform"]["marketschetform-contactemail"].value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
}


function openWin(url, wname)
{
  wid=window.open(url,  wname,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=800'); 
  window.wid.focus();
}


/*****************************************/
/***** Статусы ***************************/
/*****************************************/
var    schetStatusList = new Array();
schetStatusList[0]="&nbsp;";        
var curSchetStatus = <?=$model->docStatus?>;

<?PHP
$schetStatus=$listStatus['schet_status'];
for ($i=0;$i<count($schetStatus);$i++)
{
    echo "schetStatusList[".$schetStatus[$i]['razdelOrder']."]='".$schetStatus[$i]['Title']."';\n";
}
?>
var maxSchetStatus=<?=$schetStatus[count($schetStatus)-1]['razdelOrder']?>;

var    cashStatusList = new Array();
cashStatusList[0]="&nbsp;";    
var curCashStatus = <?=$model->cashState?>;
<?PHP
$cashStatus=$listStatus['cash_status'];
for ($i=0;$i<count($cashStatus);$i++)
{
    echo "cashStatusList[".$cashStatus[$i]['razdelOrder']."]='".$cashStatus[$i]['Title']."';\n";
}
?>
var maxCashStatus=<?=$cashStatus[count($cashStatus)-1]['razdelOrder']?>;

var    supplyStatusList = new Array();
supplyStatusList[0]="&nbsp;";    
var curSupplyStatus = <?=$model->supplyState?>;
<?PHP
$supplyStatus=$listStatus['supply_status'];
for ($i=0;$i<count($supplyStatus);$i++)
{
    echo "supplyStatusList[".$supplyStatus[$i]['razdelOrder']."]='".$supplyStatus[$i]['Title']."';\n";
}
?>
var maxSupplyStatus = <?=$supplyStatus[count($supplyStatus)-1]['razdelOrder']?>;

/**************************************/
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

function restoreSchetStatus()
{    
 setSchetStatus(curSchetStatus);
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

function chngSupplyStatus (n)
{
  var i=0;
  
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

</script>

<!---------------------------------------------------------------------->
<!--   <a class='btn-sm btn-primary box_shadow' href="index.php?r=market/market-zakaz&parent=schet&orgId=<?= Html::encode($record->id)?>&zakazId=<?= Html::encode($model->zakazId)?>"><?= Html::encode($model->schetNumber)?></a> -->
<!---------------------------------------------------------------------->
  <div class="page-title"><?= Html::encode($this->title) ?>  № 
  <input class="btn btn-primary box_shadow" style='padding-top:2px; padding-bottom:2px; padding-left:4px; padding-right:4px; height:25px;' type="button" value=" <?= Html::encode($model->schetNumber)?> " 
  onclick="javascript:openWin('index.php?r=market/market-zakaz&parent=schet&orgId=<?= Html::encode($record->id)?>&zakazId=<?= Html::encode($model->zakazId)?>', 'zakazWindow');"/>
  от: <strong><?= Html::encode($model->schetDate)?></strong>  на сумму:<strong>
  
<?php
if  (empty($schetRecord->schetSumm))  {echo "<font color='red'>N/A</font";}
else if  (empty($schetRecord->ref1C)) {echo "<font color='red'>".round($schetRecord->schetSumm,2)."</font";}
else                                     {echo "<font color='green'>".$schetRecord->schetSumm."</font";}
?> 
</strong></div>
 
  <div class="item-header">    Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u>   </div>
  <div style="font-size:10px"><?= Html::encode($record->shortComment)?></div>
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
<div style='width:950px; height:180px; '>  
  
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
        <div class='itog' onclick="javascript:openWin('index.php?r=fin/oplata-list&schetId=<?= $schetRecord->id ?>','finWin');" >
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
        echo "<div  id=".$id." class='circle ".$style."' style='margin-left:20px'  onclick='javascript:chngSupplyStatus(".$last.");' onmouseover='javascript:setSupplyStatus(".$last.");' onmouseout='javascript:restoreSupplyStatus();'>".$last."</div>";
        ?>    
        <div class='itog' onclick="javascript:openWin('index.php?r=fin/supply-list&schetId=<?= $schetRecord->id ?>','finWin');" >Поставок на сумму: <?php if (empty($schetRecord->summSupply)) echo 'N/A'; else echo $schetRecord->summSupply; ?> </div>
        </div>
        <div id='supply_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$supplyStatus[0]['Title']?> </div>
    </td>
   </tr> 

  </table>
  </div>
  <!-- Контактные данные -->
  <div style="position:relative; top:5px; display:inline-block; float:left; margin-right:10px; ">
  <br>
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 1, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;В работе </nobr><br>    
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 2, 'uncheck' => null]) ?> &nbsp;&nbsp;&nbsp;&nbsp;Отказ</nobr><br>
    <nobr><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 3, 'uncheck' => null, 'onclick' => 'setToFinishState();']) ?> &nbsp;&nbsp;&nbsp;&nbsp;Завершен</nobr><br>        
   </div>     
   <!-------------------------------------->
</div>    


<div class='spacer'>  </div>
  
  <div class='row'>  
  <div class='col-sm-7'>  
    <!-- Выводим последний контакт -->
        <div style='width:520px;  height:125px; background-color: BlanchedAlmond; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%; padding:5px;'>

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
  </div>   
 <div class='col-sm-5'>  
 <div style="position:relative; top:50px; display:inline-block; float:left; margin-right:0px; text-align: right;">
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
                echo  "<input class='btn btn-danger local_lbl' style='border-color:Crimson;'  type=button value='Отказ по доставке' onclick='javascript:openWin(\"index.php?r=store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";    
                //echo "<div class='local_lbl' style='border-color:Crimson;' > Отказ по доставке </div><br>";
        }                
        elseif ($model->supplyRequestStatus & 0x00008 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:ForestGreen;'  type=button value='Доставлен' onclick='javascript:openWin(\"index.php?r=store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";                    
        }                        
        elseif ($model->supplyRequestStatus & 0x00002 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:#5bc0de;'  type=button value='Доставляется' onclick='javascript:openWin(\"index.php?r=store/supply-request-new&noframe=0&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";                    
        }                
        elseif ($model->supplyRequestStatus & 0x00001 )
        {                                
                echo  "<input class='btn btn-success local_lbl' style='border-color:Green;'  type=button value='Принято' onclick='javascript:openWin(\"index.php?r=store/supply-request-new&noframe=0&&viewMode=market&id=".$model->supplyRequestId."\", \"supplyWin\");'><br><br>";    
        }                
        
        
    }        
    
    
    echo "<a id='btn_request' class='btn btn-primary' ".$style." href=\"#\" onclick=\"javascript:openWin('index.php?r=market/market-request-supply&schetId=".$model->id."','childwin');\"> Заявка на отгрузку </a>";          
    
    
    if ($model->supplyState != 2){ $style="style='display:none;'";}
                            else { $style="";}
                            
    echo "<a id='btn_otpravka' ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:openWin('".$model->getCfgValue(4)."','childwin');\"> Отчёт об отправке </a>";
    
    if ($model->supplyState != 4){ $style="style='display:none;'";}
                            else { $style="";}
    echo "<a id='btn_reply'  ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:openWin('index.php?r=market/market-reply&id=".$model->id."','childwin');\"> Отзыв </a>";
 
     /*if ($model->supplyState != 5){ $style="style='display:none;'";}
                            else { $style="";}
    echo "<a id='btn_finish'  ".$style." class='btn btn-primary' href=\"#\" onclick=\"javascript:if (confirm('Работа со счетом будет завершена! Продолжить?')){document.location.href ='index.php?r=market/market-schet-finish&id=".$model->id."'}\"> Завершить работу со счетом </a>";*/
    
    ?>  
    &nbsp;&nbsp;&nbsp;   
   <a class='btn btn-primary' style='background-color: ForestGreen;' href="#" onclick="javascript:openWin('index.php?r=data/sync-schet-by-id&schetId=<?= $schetRecord->id ?>&schetTime=<?= strtotime($schetRecord->schetDate) ?>&refOrg=<?=$record->id?>')"> Синхрон. 1С </a>    
   
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
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>      
   <?php ActiveForm::end(); ?>
<!--- ******************************************************  --->  
<script type="text/javascript">
restoreSchetStatus();
restoreCashStatus();
restoreSupplyStatus();
</script>    

  
<?php

//Регестрим аякс скрипты
/*$js = <<<JS

$('#nextContactDate').on('input', function() {
alert('nextContactDate changed!');
});

 
JS;

$this->registerJs($js);*/
?>  


   
