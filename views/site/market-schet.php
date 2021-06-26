<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Collapse;
use kartik\grid\GridView;

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
//$this->registerCssFile('@web/css/zvonki-common.css');


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/css/market/market-schet.css');         
?>


<style>
.page-title
{    
  font-size: 14pt;
  font-weight:bold;
}
.panel-heading
{    
 padding:2px;
}

.panel-body
{    
 padding:2px;
}
.summary
{
   display:none; 
}
.collapse-toggle
{
  font-size:12px;  
}
/***/
/*label
{
font-size:11px;
vertical-align:middle;
}*/
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
     
}

function closeSelectWare(){
        $('#addWareDialog').modal('hide');
}


function doMail()
{      
  win=window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.getElementById('contactEmail').value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
}
function doCallNew()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.getElementById('contactPhone').value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function openLead()
{
    openWin('site/lead-process&noframe=1&contactId=<?= $model->leadData['id']?>', 'leadWin');
}
function openZakaz()
{
    openWin('market/market-zakaz&parent=schet&zakazId=<?= $model->zakazId?>', 'zakazWin');
}

/* выставим*/
function parentReread(){
    //alert(window.opener.location); 
    window.opener.reloadSchet(<?= $schetRecord->id ?>);
}

function restoreSchetStatus(){    
 setSchetStatus(curSchetStatus);
}
function setSchetStatus(id){
document.getElementById('schet_status').innerHTML =schetStatusList[id];    
}

function setCashStatus(id){
document.getElementById('cash_status').innerHTML =cashStatusList[id];    
}
function restoreCashStatus(){
 setCashStatus(curCashStatus);    
}

function setSupplyStatus(id){
document.getElementById('supply_status').innerHTML =supplyStatusList[id];    
}
function restoreSupplyStatus(){
    setSupplyStatus(curSupplyStatus);        
}


/****************/

function showSchetList(){
    //$('#schetListForm').modal('show');   
    var url = 'market/client-schet-select&noframe=1&refOrg=<?= $record->id ?>&refSchet=<?= $schetRecord->id ?>';
    openWin(url, 'schet1CWin');    
}    

function closeClientSchetList(id){
    //$('#schetListForm').modal('hide');   
    saveParam(<?= $schetRecord->id ?>, 'ref1C', id)
}


/****************/


function copyWare()
{
  var URL = 'index.php?r=/market/copy-ware-in-schet&schetId=<?=$schetRecord->id?>';
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
           refreshWare(res); 
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	    
}

function rmWare(wareRef)
{
  var URL = 'index.php?r=/market/rm-ware-schet&schetId=<?=$schetRecord->id?>'+'&wareRef='+wareRef;
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
           refreshWare(res); 
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	    
}
function addSelectedWare(wareRef,edRef)
{

  var URL = 'index.php?r=/market/add-ware-schet&schetId=<?=$schetRecord->id?>'+'&wareRef='+wareRef+'&edRef='+edRef;
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
           refreshWare(res); 
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	
}

function syncTransport()
{


  var URL = 'index.php?r=/market/sync-schet-transport&schetId=<?=$schetRecord->id?>';
  console.log(URL);
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){
        console.log(res);
        document.location.reload(true);
        },
        error: function(){
            alert('Error while get transport data!');
        }
    });


}

function addWareFromPrice(id,value)
{
    document.getElementById('recordId').value=<?=$schetRecord->id?>;
    document.getElementById('dataId').value = id;
    document.getElementById('dataVal').value = value;
    document.getElementById('dataType').value='addWareFromPrice';    
    saveData();
}



function refreshWare(res)
{
    console.log(res); 
    document.location.reload(true); 
}
function addNewWare()
{
    addSelectedWare(0,0);
}

function saveNote()
{
    dataVal = document.getElementById('schetNote').value;
    saveParam(<?=$schetRecord->id?>, 'schetNote', dataVal);
}


function openOtvesList(wareListRef,  wareNameRef) {    
    url = 'store/ware-otves-list&wareRef='+wareListRef+'&wareNameRef='+ wareNameRef+'&refSchet=<?= $schetRecord->id ?>&refZakaz=0';    
    openWin(url,'otvesWin');
}

function showWareDialog(showProdutcion)
{

  $url="index.php?r=store/ware-select&noframe=1&mode=1&orgRef=<?= $model->orgId ?>&refSchet=<?= $schetRecord->id ?>";

    document.getElementById('frameAddWareDialog').src = $url+"&showProdutcion="+showProdutcion;
    $(".modal-dialog").width(900);
    $('#addWareDialog').modal('show');     
}

function openCalcWindow(){
//Uri='https://drive.google.com/drive/folders/1vYt7wiJn_uO3wph0A27uoZ6HGBB0hRxE?usp=sharing'; //корень
Uri='https://docs.google.com/spreadsheets/d/1yeupWLhUeLA9Z513L5KVsQtXcLrOM-4DBm_Cnzk9Ux8/edit#gid=0';   //upload
  wid=window.open(Uri, 'calcWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();
}

function openTarifWindow(){
//Uri='https://drive.google.com/drive/folders/1vYt7wiJn_uO3wph0A27uoZ6HGBB0hRxE?usp=sharing'; //корень
//Uri='https://docs.google.com/spreadsheets/d/1-_Adc-2cMJYEtcWMpGRX_1DmIJj5lmon_0_1hCCZFYM/edit?usp=sharing';   //upload
//  wid=window.open(Uri, 'tarifWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
//  window.wid.focus();
    openWin('store/transport-tarif','tarifWin');
}

var showTransportVal=<?= $model->showTransport ?>;
function printSchet(stamp){
  //showTransport = $('input[name="showTransport"]:checked').val();  
  var Url = 'market/print-schet&schetId=<?=$schetRecord->id?>&stamp='+stamp+'&showTransport='+showTransportVal;                   
  openWin(Url,'printWin');                     
}

function saveShowTransport(showTransport){
  //showTransport = $('input[name="showTransport"]:checked').val();  
  showTransportVal=showTransport;
    document.getElementById('recordId').value=<?=$schetRecord->id?>;
    document.getElementById('dataVal').value = showTransport;
    document.getElementById('dataType').value='showTransport';    
    if (showTransport==0) {document.getElementById('showTransportInfo').style.display ='none';}
                     else {document.getElementById('showTransportInfo').style.display ='block';}
    
   // saveData();
}  
</script>



<?php $this->registerJsFile('@web/js/market/market-schet.js'); ?>
<?php $form = ActiveForm::begin(['id' => 'Mainform',]); ?>
<table border='0' width='1160px'>
<tr>
  <td width='350px' valign='top'> <?php/*левый блок*/?>  
    <div class="page-title"><?= Html::encode($this->title) ?></div>
    
    <?php if (!empty($model->leadData['contactDate'])) $lDate= date("d.m.Y", strtotime($model->leadData['contactDate']));
    else $lDate="";?>
    <div id='lead'> 
    Лид № <span class='clickable' onClick='openLead()')><?= $model->leadData['id']?></span>  
    от  <?= $lDate ?>    
    </div>

    
    <div id='zakaz'> 
    Заказ № <span class='clickable' onClick='openZakaz()')><?= $model->zakazId?></span>  
    от  <?= date("d.m.Y", strtotime($model->zakazDate)) ?>
    на сумму <?= number_format($model->zakazSum,2,'.','&nbsp;') ?>
    </div>

    <div id='erp'> 
    ERP&nbsp;&nbsp; № <span><?= $schetRecord->schetNum ?></span>  
    от  <?= date("d.m.Y", strtotime($schetRecord->schetDate)) ?>
    на сумму <?= number_format($schetRecord->schetSumm,2,'.','&nbsp;') ?>
    </div>

    <div id='erp'> 
    <?php
    if (empty($schetRecord->refClientSchet)) {
        $style='color:Crimson;';
        $schetLabel = $schetRecord->ref1C;
    }
    else { 
        $style='';
        $schet1C = $model->load1CSchetData($schetRecord->refClientSchet);
        $schetLabel ="";
        
        $schetNumArray=preg_split("//u",$schet1C['schetRef1C']);
        $i=0;
        //доберемся до не 0 с начала
        while ($i<count($schetNumArray))
        {
          if(!is_numeric($schetNumArray[$i])){$i++; continue;}//Буквы спереди  
          if ($schetNumArray[$i] != 0) break; 
       //     print_r($schetNumArray);          
       //   echo $i.":".$schetNumArray[$i]." ";
          $i++;
        }
        if ($i==0)$i++;
        $schetNum = mb_substr($schet1C['schetRef1C'], $i-1, NULL, 'utf-8');

        $schetLabel= $schetNum;
        $schetLabel.= " от ".date ("d.m.Y", strtotime($schet1C['schetDate']));        
        $schetLabel.= " на сумму  ".number_format($schet1C['schetSumm'],2,'.','&nbsp;') ;
    }
    ?>
    <span class='clickable' style='<?=$style?>' onclick="showSchetList();">1С &nbsp;&nbsp;&nbsp; № 
    <?= $schetLabel ?></span>  
    <!--от  <?= date("d.m.Y", strtotime($schetRecord->schetDate)) ?>-->
    <!--на сумму <?= number_format($schetRecord->schetSumm,2,'.','&nbsp;') ?>-->
    </div>
    
   <!--     <br>от: <strong><?= Html::encode($model->schetDate)?></strong>  
        <br>на сумму:<strong>
        <?php
        if  (empty($schetRecord->schetSumm))  {echo "<font color='red'>N/A</font";}
        else if  (empty($schetRecord->ref1C)) {echo "<font color='red'>".round($schetRecord->schetSumm,2)."</font";}
        else                                  {echo "<font color='green'>".$schetRecord->schetSumm."</font";}
        ?> 
        </strong> руб.
   -->  
    <div>  <b>  Контрагент: </b>
    <br><u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong></u>   </div>
    <div style="font-size:10px"><?= Html::encode($record->shortComment)?></div>
  </td>
  <td valign='top'>
  <?php
  $providerWare = $model->getWareInSchetProvider(Yii::$app->request->get());    

  $contentWare= GridView::widget(
    [
        'dataProvider' => $providerWare,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'showFooter' => false,
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'attribute' => 'wareTitle',
                'label'     => 'Номенклатура',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:460px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = "wareTitle".$model['id'];
                 $action =  "saveField(".$model['id'].", 'wareTitle');"; 
                 return Html::textInput( 
                          $id, 
                          $model['wareTitle'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:470px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => 'wareCount',
                'label'     => 'К-во',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "wareCount".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareCount');"; 
                     return Html::textInput( 
                          $id, 
                          $model['wareCount'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
            [
                'attribute' => '',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:5px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "otves".$model['id'];
                    
                    $strSql="SELECT wareListRef FROM {{%ware_names}} where id =:wareNameRef";
                    $wareListRef=Yii::$app->db->createCommand($strSql,[':wareNameRef' => $model['wareNameRef'],])->queryScalar();
                    
                    
                    $strSql="SELECT count(id) FROM {{%otves_list}} where refWareList =:wareRef";
                    $otvesCnt=Yii::$app->db->createCommand($strSql,[':wareRef' => $wareListRef,])->queryScalar();
                    
                    $style ="";
                    $action =  "openOtvesList(".$wareListRef.",  ".$model['wareNameRef'].");"; 
                    if(empty($otvesCnt)){
                        $style ='color:LightGray';
                        $action ="";    
                    } 
                    
                   return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-shopping-cart'></span>", 
                   [
                     'class' => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style' => $style, 
                   ]);
                   
                },
            ],        
            
            [
                'attribute' => 'wareEd',
                'label'     => 'Ед.изм',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "wareEd".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareEd');"; 
                     return Html::textInput( 
                          $id, 
                          $model['wareEd'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
            
            [
                'attribute' => 'warePrice',
                'label'     => 'Цена',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:65px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "warePrice".$model['id'];
                    $action =  "saveField(".$model['id'].", 'warePrice');"; 
                     return Html::textInput( 
                          $id, 
                          $model['warePrice'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => '-',
                'label'     => 'Сумма',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id= 'wareSum'.$model['id'];
                    $sum = $model['warePrice']*$model['wareCount'];
                    return \yii\helpers\Html::tag( 'div', $sum , 
                   [
                     'id'      => $id,
                     'style'   => 'padding:6px;'
                   ]);
                },
            ],        

            /*                
            [
                'attribute' => 'dopRequest',
                'label'     => 'Доп. условия',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:100px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "dopRequest".$model['id'];
                    $action =  "saveField(".$model['id'].", 'dopRequest');"; 
                     return Html::textInput( 
                          $id, 
                          $model['dopRequest'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:200px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],*/        
            
            [
                'attribute' => 'id',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;', 'align' => 'center'],
                'value' => function ($model, $key, $index, $column) {
                    
                    $id= 'rmWare'.$model['id'];
                    $action="rmWare(".$model['id'].")"; 
                    return \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-remove-circle',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'margin-top:5px; color: Crimson;',
                     'title'   => 'Удалить',                
                   ]);
                },                
            ],        
            
            
           
        ],
     ]
     );



    $contentWare.="<div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>
    ";
    
    
    $id = 'btnAddNewWare';
    $action = 'addNewWare()';
    $contentWare.=\yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:12px;',
                     'title'   => 'Добавить произвольный',                
                   ]);                   
    $contentWare.="&nbsp;&nbsp;";                       
    
    $id = 'btnAddWareList';
    $action = 'showWareDialog(1)';
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Товар/сырье', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 90px;font-size:11px;',
                     'title'   => 'Добавить сырье из списка товаров реализации',                
                   ]);
     $contentWare.="&nbsp;&nbsp;";                      
                    
    $id = 'btnAddProdList';
    $action = 'showWareDialog(2)';
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Продукция', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 90px;font-size:11px;',
                     'title'   => 'Добавить продукцию из списка товаров реализации',                
                   ]);
     $contentWare.="&nbsp;&nbsp;";                                     

    $id = 'btnCalc';
    $action = 'openCalcWindow()';
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Калькулятор', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:11px;width: 90px;',
                     'title'   => 'Калькулятор производства',                
                   ]);
    $contentWare.="&nbsp;&nbsp;";                                      
    
   /* $id = 'btnTarif';
    $action = 'openTarifWindow()';
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Тарифы', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
                     'title'   => 'Тарифы на доставку',                
                   ]);*/
                   
    $id = 'btnRemain';
    $action = "openWin('store/ware-grp-sclad','remainWin');";
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Остатки',
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 90px;',
                     'title'   => 'Остатки на складах',
                   ]);
    $contentWare.="&nbsp;&nbsp;";

    $id = 'btnPrice';
    $action = "openWin('store/ware-price','priceWin');";
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Прайс',
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 90px;',
                     'title'   => 'Остатки на складах',
                   ]);
    $contentWare.="&nbsp;&nbsp;";
    
    $contentWare.="<div style='display:inline; float:right'>";
    $id = 'lblWareSumm';
    $contentWare.= \yii\helpers\Html::tag( 'span', "Итого по счету <b>".number_format($schetRecord->schetSumm,2,'.'," ")."</b> руб.", 
                   [
                     'id'      => $id,
                     'title'   => 'Сумма по счету',                
                     'style'   => 'margin-left: 30px; margin-right: 30px;',
                   ]);
    
    $id = 'btnCopyWareList';
    $action = 'copyWare()';
    $contentWare.= \yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'clickable glyphicon glyphicon-copy',
                     'id'      => $id,
                     'onclick' => $action,                     
                     'title'   => 'Копировать из счета 1С',                
                   ]);
    
    $contentWare.="</div>";
                   

                   

                   
                   
    $contentWare.="    
     </div>   
  </div>
   ";

         
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Содержание счета: ▼",
            'content' => $contentWare,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 

  ?>
 
 <?php
 /*чтобы сумму посчитать заранее*/
 $contentTransport= GridView::widget(
    [
        'dataProvider' => $model->getSchetTransportProvider(Yii::$app->request->get()),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [
            'class' => 'table table-bordered table-small'
        ],
        'columns' => [
            [
                'attribute' => 'typeText',
                'label'     => 'Доставка',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:165;'],
            ],

            [
                'attribute' => 'route',
                'label'     => 'Куда/Откуда',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:205px;'],
            ],

            [
                'attribute' => 'weight',
                'label'     => 'вес',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
            ],

            [
                'attribute' => 'price',
                'label'     => 'цена',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
            ],

            [
                'attribute' => 'val',
                'label'     => 'Сумма',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
            ],

            [
                'attribute' => 'note',
                'label'     => 'Доп. условия',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:250px;'],
            ],

        ],
     ]
     );

    $contentTransport.="<div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>";
 ?>
 
 <div class='row'>     
     <div class='col-md-2'>
     <?php /*echo Html::radio('showTransport', ($model->showTransport ==1 ? true : false),*/
     /*echo $form->field($model, 'showTransport')->radio(
     [
         'id' => 'showTransport',
         'label' => 'Отображать',   
         'value' => 1,
         'class' => 'radio-n',
         'onclick' => 'saveShowTransport();',
     ]);*/
     echo  $form->field($model, 'showTransport')->radio([
         'label' => 'Компактно',    
         'value' => 1, 
         'id' => 'showTransport_1', 
         'uncheck' => null,
         'onclick' => 'saveShowTransport(1);',
         ]);
     ?>
     </div>  
     
     <div class='col-md-2'>
     <?php /*echo Html::radio('showTransport', ($model->showTransport ==2 ? true : false), */
     /*echo $form->field($model, 'showTransport')->radio(
     [
         'id' => 'showTransport',
         'label' => 'Детально',
         'value' => 2,
         'class' => 'radio-n',
         'onclick' => 'saveShowTransport();',
     ]);*/
     echo  $form->field($model, 'showTransport')->radio([
         'label' => 'Детально',
         'value' => 2, 
         'id' => 'showTransport_2', 
         'uncheck' => null,
         'onclick' => 'saveShowTransport(2);',
         ]);
         
     ?>
     </div>  

     
     <div class='col-md-2'>
     <?php /*echo Html::radio('showTransport', ($model->showTransport ==0 ? true : false), */
     /*echo $form->field($model, 'showTransport')->radio(
     [
         'id' => 'showTransport',
         'label' => 'Скрыть',
         'value' => 0,
         'class' => 'radio-n',
         'onclick' => 'saveShowTransport();',
     ]);*/
     
     echo  $form->field($model, 'showTransport')->radio([
         'label' => 'Скрыть',
         'value' => 0, 
         'id' => 'showTransport_0', 
         'uncheck' => null,
         'onclick' => 'saveShowTransport(0);',
         ]);
     ?>
     </div>  
          
     <div class='col-md-6'>     
     <div id='showTransportInfo' style=" font-weight:bold; font-size:15px; text-align:right; <?= ($model->showTransport ==0 ? 'display:none' : 'display:block')   ?>"> Всего с доставкой: 
     <?= number_format(($schetRecord->schetSumm+$model->sumTransport),2,'.'," ") ?> руб.  </div>  
     </div>       
 </div>  
 
 <?php


    $id = 'btnSyncTransport';
    $action = 'syncTransport();';
    $contentTransport.=\yii\helpers\Html::tag( 'span', '',
                   [
                     'class'   => 'glyphicon glyphicon-refresh clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:12px;',
                     'title'   => 'Взять из заказа',
                   ]);
    $contentTransport .="</div>";
    
    echo Collapse::widget([
    'items' => [
        [
            'label' => "Cтоимость доставки: ".$model->sumTransport." руб. ▼",
            'content' => $contentTransport,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]);

 ?>



<?php
 $action = "saveNote();";
 $contentWare =  Html::textArea( 
                          $id, 
                          $model->schetNote,                                
                              [
                              'class' => 'form-control',
                              'style' => ' font-size:11px;padding:1px;', 
                              'id' => 'schetNote', 
                               'rows' => 5, 
                               'cols' => 35,
                               'onchange' => $action,
                              ]);

 
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Комментарий к счету для заказчика/ Счет-договор: ▼",
            'content' => $contentWare,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 
?>
   
  
  </td>
  
  
  
</tr>
</table>  
  
  
<!-------------------------------------->
<!--------- Середина  ------------------>
<!-------------------------------------->  
 <hr noshade size='5'>
 <!--- Регистрация контакта старт--->    
  


<table border='0' width='1160px'><tr>
<td>
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
        <div class='itog'  style='font-size:11px;' onclick="showSchetList();" >
        Счет 1С: <?= $schetLabel ?> </div>
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
          
        <div class='itog' style='font-size:11px;' onclick="javascript:openWin('fin/oplata-list&schetId=<?= $schetRecord->id ?>','finWin');" >
        1С:  <?php if (empty($schetRecord->summOplata)) echo 'N/A'; else echo $schetRecord->summOplata; ?> </div>

        <div class='itog' style='margin-right:10px;font-size:11px;' onclick="javascript:openWin('fin/extract-list&schetId=<?= $schetRecord->id ?>','finWin');" >
        ПП банк:  <?php if (empty($model->sumExtract)) echo 'N/A'; else echo $model->sumExtract; ?> </div>
        
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
        <div class='itog'  style='font-size:11px;' onclick="javascript:openWin('fin/supply-list&schetId=<?= $schetRecord->id ?>','finWin');" >Поставок на сумму: <?php if (empty($schetRecord->summSupply)) echo 'N/A'; else echo $schetRecord->summSupply; ?> </div>
        </div>
        <div id='supply_status' style='width:440px;  font-size:12px height:25px; background-color:Gainsboro'> <?=$supplyStatus[0]['Title']?> </div>
    </td>
   </tr> 

  </table>
  </div>
</td> 

<td>
 <!-- Контактные данные -->
    <table border=0 style="border:0px; width:400px; padding:5px" >
    <tr>                
        <td width='110px'><?= $form->field($model, 'contactPhone')->textInput(['id'=>'contactPhone',
        'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Номер телефона'])->label(false)?></td>    
        <td valign='top' align='left'> <a href='#' class="btn btn-primary" title="Позвонить" onclick="javascript:doCallNew();"/><span class="glyphicon glyphicon-phone-alt"></span></a></td>
        <td  width='240px' align='left'><?= $form->field($model, 'contactFIO')->textInput(
          ['id'=>'contactFIO', 'style'=>'width:220px; margin:0px;font-size:12px;padding:2px;', 
          'placeHolder' => 'Контактное лицо'])
          ->label(false)?></div></td>
    </tr>
    <tr>    
     <td colspan=3>
     <div style='height:90px; width:390px; overflow:auto;'>
     <table  border='0'>
     <?php
     $phoneList=$model->getCompanyPhones();
     $out=0;
     for ($i=0;$i<count($phoneList);$i++)
     { 
        if ($phoneList[$i]["status"] == 2){ continue; echo " <font color='red'>*</font>";}
        //$out++; if($out>3) break;        
      $phoneList[$i]["phone"] = preg_replace("/\D+/u","",$phoneList[$i]["phone"]);
      if(mb_strlen($phoneList[$i]["phone"],'utf-8')<10) continue;
      if(mb_strlen($phoneList[$i]["phone"],'utf-8')>12) continue;  
        
       echo "<tr>"; 
        echo "<td width='120px'>"; 
        echo \yii\helpers\Html::tag( 'div', Html::encode($phoneList[$i]["phone"]), 
        [                  
            'class' => 'clickable',
            'onclick' => "setPhone('".Html::encode($phoneList[$i]["phone"])."','".Html::encode($phoneList[$i]["phoneContactFIO"])."');",            
        ]);                
        echo "</td>"; 
        echo "<td width='250px;'>"; 
        echo  $phoneList[$i]["phoneContactFIO"]; 
        echo "</td>"; 
       echo "</tr>";
     }
     ?>  
     </table>
     </div>
     <div class='spacer'></div>
    </td>
    </tr>
    </table>
    
    <table border=0 style="border:0px; width:100%; padding:5px" >
    <tr>                    
        <td width='340px'><?= $form->field($model, 'contactEmail')->textInput(['id'=>'contactEmail', 'style'=>'width:320px; margin:0px;'])->label(false)?></td>
        <td valign='top'><a href="#" class="btn btn-primary"  title="Написать письмо" onclick="javascript:doMail();"/><span class="glyphicon glyphicon-envelope"></span></a></td>        
    </tr>
    </table>
    
    
</td>  
</tr></table>
   
 
 <!-------------------------------------->
 <!--------- НИЗ ------------------------>
 <!-------------------------------------->
<hr>
<table border='0' width='1160px'><tr>

 <!------ Лево низ-------------------------------->
<td width='400px'>
    <!-- Выводим последний контакт -->
   <div style='width:350px; position: relative;  left: 0px; display:inline-block; float:left;'>            
    <div class="small_part-header"> Предыдущий контакт   
    <span style="position:relative;left:40px;font-size:10px"><a href ="index.php?r=site/contacts-detail&id=<?= $record->id ?>"> (Просмотреть историю контактов) </a></span>
    </div>
    
     <div style='background-color: Cornsilk; width:370px; height:160px; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%; padding:5px;'>
        <?php
        $contactsDetail=$model->getContactDetail(0);
        $action="openWin('site/zakaz-contacts-detail&refZakaz=".$model->zakazId."','contactWin')";        
        $N = count ($contactsDetail);
        $val="";
        for ($i=0;$i<$N;$i++)
        {    
         if(trim($contactsDetail[$i]['note']) == '') continue;
         $val.="<div class='contact_title'> <b>";
         $val.=date("d.m",strtotime( $contactsDetail[$i]['contactDate']))." </b> ";
         $val.=$contactsDetail[$i]['contactFIO']."  ".$contactsDetail[$i]['phone']."</div>\n";
         if (mb_strlen($contactsDetail[$i]['note'])> 260)
              $val.="<div class='contact_body'>".mb_substr($contactsDetail[$i]['note'],0,260, 'utf-8')."...</div>\n";
         else $val.="<div class='contact_body'>".$contactsDetail[$i]['note']."</div>\n";               
        }
        echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'class'   => 'contactShow',
          'id'      => 'contactList',                    
          'style'   => 'height:150px; padding:2px; overflow:auto',
          'onclick' => $action,
        ]); 

        ?>  
       </div>

  </td>  

<!------ Центр низ-------------------------------->
<td width='170px' style='padding:10px' >
    <!-- Статусы -->
   <table border='0' width='100%'>
   <tr>
        <td style='padding:0px'><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 1, 'id' => 'status_1', 'uncheck' => null]) ?></td>
        <td style='padding:0px'>В работе</td>
   </tr>
   <tr>
        <td style='padding:0px'><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 2, 'id' => 'status_2', 'uncheck' => null]) ?></td>
        <td style='padding:0px'>Отказ</td>
   </tr>
   <tr>
        <td style='padding:0px'><?= $form->field($model, 'status')->radio(['label' => false, 'value' => 3, 'id' => 'status_3', 'uncheck' => null, 'onclick' => 'setToFinishState();']) ?></td>
        <td style='padding:0px'>Завершен</td>
   </tr>   
   </table>   
</td>  

<!------ Право низ-------------------------------->
<td style='padding-left:50px' >
  <div >Комментарий: </div>
    <?= $form->field($model, 'note')->textarea(['style'=>'margin-left:0px;', 'rows' => 5, 'cols' => 35])->label(false)?>
</td>  
</tr>
<tr>
<td>
    <div style="margin-left:15px; padding: 5px;">Дата/время следущего чекаута</div> 
    <div class='row'>
        <div class='col-md-5'>        
        <?= $form->field($model, 'nextContactDate')->textInput([/*'class' => 'tcal',*/ 'style'=>'width:150px;', 'type' => 'date', 'id' =>'nextContactDate',
         'onchange' => 'showSelectEventTime()'  ])->label(false)?></div><a href="#" onclick='showSelectEventTime()'> 
        <div class='col-md-2' align='left'  id='nextContactTimeShow'><?php
        if($model->nextContactTime == '-') echo "<span class='glyphicon glyphicon-search'></span>";
        else echo $model->nextContactTime;
        ?></a>         
        </div>
        <?= $form->field($model, 'nextContactTime')->hiddenInput(['id' => 'nextContactTime',])->label(false)?>           
    </div>     
</td>
<td></td>

<td>
    <?php    
//position:relative; top:50px; display:inline-block; float:right; margin-right:0px;     
    if ($model->supplyState < 1){ $style="style='display:none;'";}
    else 
    {
    $style="";
    
    if ($model->supplyRequestId > 0 ){

        if ($model->supplyRequestStatus == 0 )
        {
                echo "<div class='local_lbl'> Заявка на поставку зарегестрирована.</div><br></div>";
                $style="style='background-color: DarkGray;'";    
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
        
    }        
    
    ?>  
</td>
</tr>
<tr>
<td></td>
<td>        
             <?php 
                 echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-print'></span>", 
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'print',
                     'onclick' => 'printSchet(1);',
                     'title'   => 'c печатью'     
                   ]);
               ?>

             <?php 
                 echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-print'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'print',
                     'onclick' => 'printSchet(0);',
                     //'onclick' => "openWin('market/print-schet&stamp=0&schetId=".$schetRecord->id."','printWin');",    
                     'title'   => 'без печати'                 
                   ]);
               ?>
               
</td>
<td  align='right'>
   <?php
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
       <a class='btn btn-primary' style='background-color: ForestGreen;' href="#" onclick="javascript:openWin('data/sync-schet-by-id&schetId=<?= $schetRecord->id ?>&schetTime=<?= strtotime($schetRecord->schetDate) ?>&refOrg=<?=$record->id?>')"> Синхрон. 1С </a>    
       <a class='btn btn-primary' href="#" onclick="javascript: submitMainForm();" style ='background-color: ForestGreen;'> Сохранить </a>
       <a class='btn btn-primary' href="#" onclick="javascript: if (confirm('Не сохраненные изменения будут потеряны! Выйти?')){document.location.href = 'index.php?r=market/market-schet-close';} "> Выйти </a>       
</td>

</tr></table>
 
<hr>

<!--- Контакт финиш--->  
   <?= $form->field($model, 'docStatus')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'cashState')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'supplyState')->hiddenInput()->label(false)?> 

   <?= $form->field($model, 'id')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'src')->hiddenInput()->label(false)?> 
   <?= $form->field($model, 'zakazId')->hiddenInput()->label(false)?>      
   <?php ActiveForm::end(); ?>
   
   
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
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=market/save-schet-param']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'addWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'class' => 'modal-big'
]);?>
    <iframe width='860px' height='620px' frameborder='no' id='frameAddWareDialog'  src='index.php?r=store/ware-select&noframe=1&mode=1&orgRef=<?= $model->orgId ?>&refSchet=<?= $schetRecord->id ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
<?php
Modal::end();
/***************************/
?>
