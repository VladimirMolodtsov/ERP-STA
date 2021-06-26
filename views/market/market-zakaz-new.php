<?php

/* @var $this yii\web\View */

//use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\jui\AutoComplete;

/*use yii\jui\DatePicker;*/

$curUser=Yii::$app->user->identity;

$this->title = 'Работа с заявкой';
//$this->params['breadcrumbs'][] = $this->title;

$zakazRecord=$model->getZakazRecord();  
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
    
?>

<style>

.contactShow {
   background-color: #e8ffc8; 
   width:370px; 
   height:100px; 
   box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); 
   border-radius: 1%; 
   padding:5px;'
   font-size:11px;
   overflow:auto;
   padding:3px;
}    
.contactShow::hover {  
 cursor:pointer;
} 


.button {
    width: 150px;
    font-size: 10pt;    
} 
 .btn-block{
    padding: 2px;     
 }
 
    
table.menu    { border-left:0px solid; border-spacing: 15px;     border-collapse: separate; }
tr.menu-row   { height:30px; }
td.menu-point { background:DarkSlateGrey; color:#FFFFFF; font-weight:bold; text-align:center; padding:10px; border:0px}
a.menu-point  {  color:#FFFFFF; font-weight:bold;  font-style: normal; }
a.menu-point:hover {  color:#FFFFFF; font-weight:bold; }    

</style>

<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function closeSelectWare(){
        $('#addWareDialog').modal('hide');
}

function setPhone(contactPhone, contactFIO)
{      
  document.getElementById("contactPhone").value =contactPhone;   
  document.getElementById("contactFIO").value =contactFIO;   
  doCallNew();
}

function doMail()
{      
  win=window.open("mailto:"+document.getElementById("contactEmail").value);
  //win=window.open("index.php?r=site/mail&noframe=1&orgId=<?= Html::encode($model->orgId)?>&email="+document.getElementById("contactEmail").value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
}

function doCallNew()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.getElementById("contactPhone").value,'doCall','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function sendKP()
{      
  w=window.open("index.php?r=market/send-zakaz&zakazId=<?= Html::encode($zakazRecord['id']) ?>&email="+document.forms["Mainform"]["marketzakazform-contactemail"].value,'send','toolbar=no,scrollbars=yes,resizable=yes,top=95,left=550,width=1150,height=700');     
  window.w.focus();
  //window.location.href="index.php?r=market/send-zakaz&zakazId=<?= Html::encode($zakazRecord['id']) ?>&email="+document.forms["Mainform"]["marketzakazform-contactemail"].value;
}


function showAdd()
{
    if (document.getElementById("addRequest").style.visible == "hidden")
    { document.getElementById("addRequest").style.visible = "visible"; }
    else document.getElementById("addRequest").style.visible = "hidden";
}

</script>



<script type="text/javascript">
/*********************/
/* Новые скрипты */

function regNewSchet()
{
  var URL = 'index.php?r=market/create-schet&zakazId=<?=$zakazRecord['id']?>';
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
           refreshSchet(res); 
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	
}

function refreshSchet(res)
{
    console.log(res); 
    document.location.reload(true); 
}


function addSelectedWare(wareRef,wareEd)
{

  var URL = 'index.php?r=/market/add-ware-zakaz&zakazId=<?=$zakazRecord['id']?>'+'&wareRef='+wareRef+'&wareEd='+wareEd;
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

function refreshWare(res)
{
    console.log(res); 
    document.location.reload(true); 
}

function addNewWare()
{
    addSelectedWare(0,0);
}



function rmTransport(id)
{
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value='delTransport';    
    saveData();
}

function addTransportTarif(id,value)
{
    document.getElementById('recordId').value=<?=$zakazRecord['id']?>;
    document.getElementById('dataId').value = id;
    document.getElementById('dataVal').value = value;
    document.getElementById('dataType').value='addTransportTarif';    
    saveData();
}

function addSelfTransport(id,value)
{
    document.getElementById('recordId').value=<?=$zakazRecord['id']?>;
    document.getElementById('dataId').value = id;
    document.getElementById('dataVal').value = 1;
    document.getElementById('dataType').value='addSelfTransport';    
    saveData();
}

function showSelfTransport()
{
    $('#cityScladListDialog').modal('show'); 
}

function addCityTransport(id,value)
{
    $('#cityScladListDialog').modal('hide'); 
    document.getElementById('recordId').value=<?=$zakazRecord['id']?>;
    document.getElementById('dataId').value = id;
    document.getElementById('dataVal').value = 2;
    document.getElementById('dataType').value='addCityTransport';    
    saveData();
}

function addNewTransport()
{
    document.getElementById('recordId').value=<?=$zakazRecord['id']?>;
    document.getElementById('dataVal').value = 0;
    document.getElementById('dataType').value='addTransport';    
    saveData();
}

function saveData()
{
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-zakaz-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload==true)document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


function saveField(id, type)
{   
    idx= type+id;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-zakaz-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function openOrg()
{
    var orgId = $('#orgId').val();
    var url = 'site/org-detail&orgId='+ orgId;
    openWin(url,'orgWin')
}
function showOrgList()
{
    $(".modal-dialog").width(600);
    $('#orgListForm').modal('show');   
}

function closeOrgList(orgId, title, phone)
	{ 
	
	if (title == "") title = "Создать автоматически";
	
        $('#zakazOrgTitle').html(title);	        
        $('#orgTitle').val(title);	                
        $('#orgId').val(orgId);	                
        $('#orgListForm').modal('hide');
   getOrgInfo(orgId);
   document.getElementById('Mainform').submit();
}

function getOrgInfo(orgId)
{
 var url = 'index.php?r=market/get-org-info&orgId='+orgId;
 console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
        $('#contactPhone').val(res.contactPhone);	                
        $('#contactEmail').val(res.contactEmail);	                
        $('#contactFIO').val(res.contactFIO);	                
 },
 error: function(){
   //console.log(res);
  alert('Error while add document!');
 }
 });	
}

function setStatus(status)
{    
   document.getElementById('status').value=status;
   var idx = 'status'+status;   
   if (status == 2) {
    document.getElementById('status2').style.background='Crimson';
    document.getElementById('status2').style.color='White';
    document.getElementById('status3').style.background='White';
    document.getElementById('status3').style.color='Black';    
    
   } else {
    document.getElementById('status2').style.background='White';
    document.getElementById('status2').style.color='Black';
    document.getElementById('status3').style.background='Blue';
    document.getElementById('status3').style.color='White';    
       
       
   }
}

function regNewDoc(){ 
  url = 'index.php?r=bank/operator/reg-doc&noframe=1&id=0';
  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  window.wreg.focus();
}

function openScanWindow(){
Uri='https://drive.google.com/drive/folders/1vYt7wiJn_uO3wph0A27uoZ6HGBB0hRxE?usp=sharing';
  wid=window.open(Uri, 'scanWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();

}

/**************/

function openDocList()
{   
//Показ диалога
    $(".modal-dialog").width(600);
    $('#docListForm').modal('show');   
}

function removeDoc(docId)
{
 var zakazid = $('#zakazId').val();
 var url = 'index.php?r=market/rm-doc-to-zakaz&zakazid='+zakazid+'&docid='+docId;
 console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   showDocList(res);        
 },
 error: function(){
   //console.log(res);
  alert('Error while add document!');
 }
 });	
}

// Принудительно скроем
function closeDocList(docId, title)
{
 $('#docListForm').modal('hide');
 var zakazid = $('#zakazId').val();
 //var data = $('form').serialize();
 
 var url = 'index.php?r=market/add-doc-to-zakaz&zakazid='+zakazid+'&docid='+docId;
 console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   showDocList(res);        
 },
 error: function(){
   //console.log(res);
  alert('Error while add document!');
 }
 });	
	
}

function showDocList(res)
{
  console.log(res);    
  if (res['res'] == true){ $('#docList').html(res['val']);	}
}




function showWareDialog(showProdutcion)
{

  $url='index.php?r=store/ware-select&noframe=1&mode=1&orgRef=<?= $model->orgId ?>&refZakaz=<?= $zakazRecord['id'] ?>';

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
/*Uri='https://docs.google.com/spreadsheets/d/1-_Adc-2cMJYEtcWMpGRX_1DmIJj5lmon_0_1hCCZFYM/edit?usp=sharing';   //upload
  wid=window.open(Uri, 'tarifWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();*/
openWin('store/transport-tarif','tarifWin');  
}




</script>

<script type="text/javascript">
/*Выносим в отдельный блок все что связано с назначением события*/
function showSelectEventTime() {

var d=document.getElementById('nextContactDate').value;
document.getElementById('frameEventTimeDialog').src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date='+d;
$(".modal-dialog").width(650);
$('#selectEventTimeDialog').modal('show');     
}

function setSelectEventTime(eventTime) {
document.getElementById('nextContactTime').value = eventTime;
document.getElementById('nextContactTimeShow').innerHTML = eventTime;
$('#selectEventTimeDialog').modal('hide');     
}

function submitMainForm ()
{     
    document.getElementById('Mainform').submit();        
}

function openSchet(shetId)
{
  var url = 'market/market-schet&id='+shetId;
  openWin(url,'schetWin'); 
}


function openOtvesList(wareListRef,  wareNameRef) {    
    url = 'store/ware-otves-list&wareRef='+wareListRef+'&wareNameRef='+ wareNameRef+'&refSchet=0&refZakaz=<?= $zakazRecord['id'] ?>';    
    openWin(url,'otvesWin');
}

</script>     



<?php/*Табличный дизайн - нужна  жесткая фиксация по строкам*/?>

<table border='0' width='1160px'>
<tr>
  <td width='260px' valign='top'> <?php/*левый блок*/?>
    <div style='width:220px;'>   <?php/*Шапка*/?>  
        Наименование компании:<br>
        <u><strong>
        <span class='clickable' onclick="openOrg();" id='zakazOrgTitle'><?= Html::encode($zakazRecord['title'])?></span></strong></u>&nbsp;&nbsp;&nbsp;<span class='glyphicon glyphicon-search clickable' onclick='javascript:showOrgList();'></span>
        
        <div ><?= Html::encode($this->title)?> <br>
        <span style='text-decoration:underline'> <?= Html::encode($zakazRecord['id'])?>  от <?= Html::encode(date('d.m.Y',strtotime($zakazRecord['formDate'])) )?> </span>    </div>        
  
        <div style="font-size:10px"><?= Html::encode($zakazRecord['shortComment'])?></div>  
   </div>    
   
  <div class='spacer'> </div> 
  <div>    
  <b>Документы:</b>  
  
    <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-folder-open'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'openScanWindow();',
          'style'   => 'font-size:12px; height:22px; width:22px; padding:4px; display:inline'
   ]); ?>  

  
    <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon  glyphicon-list-alt'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'regNewDoc();',
          'style'   => 'font-size:12px; height:22px; width:22px; padding:4px; display:inline'
   ]); ?>  
   
   
  
     <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus-sign'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'openDocList();',
          'style'   => 'font-size:10px; height:22px; width:22px; padding:4px; display:inline'
   ]); ?>  
      
   <?= \yii\helpers\Html::tag( 'div', $model->docList, 
    [
          'id'      => 'docList',
          'style'   => 'font-size:12px;width:220px;'
           
    ]); ?>  
      </div>     
   
   
 </td>

  <td width='900px' valign='top'> <?php/*Правый блок*/?>
  <div class='spacer'> </div>
  <div style='width:900px;'> 
    <?php    
    echo GridView::widget(
    [
        'dataProvider' => $model->getZakazDetailProvider(),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
    //    'showFooter' => true,
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'columns' => [
            [
                'attribute' => 'good',
                'label'     => 'Предложенный товар',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:300px;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = "wareTitle".$model['id'];
                 $action =  "saveField(".$model['id'].", 'wareTitle');"; 
                 return Html::textInput( 
                          $id, 
                          $model['good'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:300px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => 'count',
                'label'     => 'К-во',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "count".$model['id'];
                    $action =  "saveField(".$model['id'].", 'count');"; 
                     return Html::textInput( 
                          $id, 
                          $model['count'],                                
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
                'attribute' => 'ed',
                'label'     => 'Ед.',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "ed".$model['id'];
                    $action =  "saveField(".$model['id'].", 'ed');"; 
                     return Html::textInput( 
                          $id, 
                          $model['ed'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:45px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
            
            [
                'attribute' => 'value',
                'label'     => 'Цена',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "value".$model['id'];
                    $action =  "saveField(".$model['id'].", 'value');"; 
                     return Html::textInput( 
                          $id, 
                          $model['value'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
                            
            [
                'attribute' => 'dopRequest',
                'label'     => 'Доп. условия',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:200px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "dopRequest".$model['id'];
                    $action =  "saveField(".$model['id'].", 'dopRequest');"; 
                     return Html::textInput( 
                          $id, 
                          $model['dopRequest'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:190px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
              [
                'attribute' => 'id',
                'label'     => 'Запр.',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;', 'align' => 'center'],
                
                'value' => function ($model, $key, $index, $column) {
                  
                $strSql= "select id, zaprosType,relizeValue FROM {{%purchase_zakaz}} WHERE refZakazContent =:refZakazContent ORDER BY zaprosType ASC";
                $inPurch = Yii::$app->db->createCommand($strSql, [':refZakazContent' => $model['id'],])->queryAll();
                 
                if (count($inPurch) > 0)  
                { 
                   if ($inPurch[0]['zaprosType'] == 0) return "В закупке";
                   
                    if ($inPurch[0]['zaprosType'] == 1)
                    {
                       if ($inPurch[0]['relizeValue'] >0 ) return number_format($inPurch[0]['relizeValue'],2,'.','');
                       return "Запрос цены";
                    }
                }   
                    
                $action = "openWin('store/purchase-create-from-client-zakaz&contentid=".$model['id']."','purchWin');";                        
                return "<a href='#' onclick=\"".$action."\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></a>";
                    
                },                
            ],        

    
            [
                'attribute' => 'id',
                'label'     => 'Актуален',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;', 'align' => 'center'],
                'value' => function ($model, $key, $index, $column) {

                if ($model['isActive'] == 1){
                        return "<a href='index.php?r=market/market-zakaz-delete&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'><span class='label label-success'>Yes</span></a>";
                    }
                        return "<a href='index.php?r=market/market-zakaz-reverse&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."'><span class='label label-danger'>No</span></a>";
                    
                },                
            ],        

            [
                'attribute' => 'id',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;', 'align' => 'center'],
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=market/market-zakaz-remove&id=".$model['id']."&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."' style='color:Crimson'><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },                
            ],        
            
        ],
     ]
     );
     ?>
     <div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>
        
   
        <?php 
        $id = 'btnAddNewWare';
        $action = 'addNewWare()';
        echo \yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:12px;',
                     'title'   => 'Добавить произвольный',                
                   ]);                   
        ?>         
        &nbsp;
    <?php          
    $id = 'btnAddWareList';
    $action = 'showWareDialog(1)';
    $contentWare= \yii\helpers\Html::tag( 'div', 'Товар/сырье', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
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
                     'style'   => 'width: 105px;',
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
                     'style'   => 'width: 105px;',
                     'title'   => 'Калькулятор производства',                
                   ]);
    $contentWare.="&nbsp;&nbsp;";                                      
    
/*    $id = 'btnTarif';
    $action = 'openTarifWindow()';
    $contentWare.= \yii\helpers\Html::tag( 'div', 'Тарифы', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
                     'title'   => 'Тарифы на доставку',                
                   ]);*/
    echo $contentWare;               
        ?>
        <div style='display:inline; float:right'>
          Итого: <b><?= number_format($model->sumZakaz,2,'.','&nbsp;') ?> руб. &nbsp;&nbsp;</b>
        </div>
     </div>   
  </div>
<!---->

  <div style='width:900px;'> 
    <?php    
    echo GridView::widget(
    [
        'dataProvider' => $model->getZakazTransportProvider(Yii::$app->request->get()),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
    //    'showFooter' => true,
        'tableOptions' => [
            'class' => 'table table-bordered table-small'
        ],
        'columns' => [
/*            [
                'attribute' => 'type',
                'label'     => 'Доставка',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:200;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = "transportType".$model['id'];
                 $action =  "saveField(".$model['id'].", 'transportType');";                  
                 return Html::dropDownList('type', $model['type'],
                     [0 => 'Самовывоз', 1 => 'Доставка ТК'],                      
                     [           
                       'class' => 'form-control',
                       'style' => 'width:200px; font-size:11px;padding:1px;', 
                       'id' => $id, 
                       'onchange' => $action,                       
                     ]);
                },
            ],        
*/
            [
                'attribute' => 'type',
                'label'     => 'Доставка',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:165;'],
                'value' => function ($model, $key, $index, $column) {
                 $id = "typeText".$model['id'];
                 $action =  "saveField(".$model['id'].", 'transportTypeText');"; 
                 if (empty($model['type']))
                     return Html::textInput( 
                          $id, 
                          $model['typeText'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:165px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                                                   
                 return $model['typeText'];
                },
            ],        
            
            [
                'attribute' => 'route',
                'label'     => 'Куда',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:205px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "route".$model['id'];
                    $action =  "saveField(".$model['id'].", 'route');"; 
                     return Html::textInput( 
                          $id, 
                          $model['route'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:205px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => 'weight',
                'label'     => 'вес',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "transportWeight".$model['id'];
                    $action =  "saveField(".$model['id'].", 'transportWeight');"; 
                     return Html::textInput( 
                          $id, 
                          $model['weight'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
                       
            [
                'attribute' => 'price',
                'label'     => 'цена',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "transportPrice".$model['id'];
                    $action =  "saveField(".$model['id'].", 'transportPrice');"; 
                     return Html::textInput( 
                          $id, 
                          $model['price'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        
                      
            [
                'attribute' => 'val',
                'label'     => 'Сумма',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:75px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "transportVal".$model['id'];
                    $action =  "saveField(".$model['id'].", 'transportVal');"; 
                     return Html::textInput( 
                          $id, 
                          $model['val'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:65px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],                              
                            
            [
                'attribute' => 'note',
                'label'     => 'Доп. условия',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:250px;'],
                'value' => function ($model, $key, $index, $column) {
                    $id = "transportNote".$model['id'];
                    $action =  "saveField(".$model['id'].", 'transportNote');"; 
                     return Html::textInput( 
                          $id, 
                          $model['note'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:250px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],        

            [
                'attribute' => 'id',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px;', 'align' => 'center'],
                'value' => function ($model, $key, $index, $column) {
                 $action =  "rmTransport(".$model['id'].");";   
                 $id = "rmTransport".$model['id'];
                  return \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-remove-circle clickable',
                     'id'      => $id,
                     'style' => 'color:Crimson', 
                     'onclick' => $action,
                     'title'   => 'Удалить',                
                   ]); 
                   
                },                
            ],        
            
        ],
     ]
     );
     ?>
     <div style='width:100%; text-align:left; margin-top:-20px; padding:5px;'>
        
   
        <?php 
        $id = 'btnAddNewTransport';
        $action = 'addNewTransport()';
        echo \yii\helpers\Html::tag( 'span', '', 
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'font-size:12px;',
                     'title'   => 'Добавить произвольный',                
                   ]);                   
        ?>         

        <?php 
        $id = 'btnAddSelfTransport';
        $action = 'showSelfTransport()';
        echo \yii\helpers\Html::tag( 'div', 'Самовывоз', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
                     'title'   => 'Добавить самовывоз',                
                   ]);                   
        ?>         
        
    <?php 
    $id = 'btnTarif';
    $action = 'addCityTransport(0,0)';
    echo \yii\helpers\Html::tag( 'div', 'По городу', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
                     'title'   => 'Доставка по городу',                
                   ]);
    
        ?>
        
            
    <?php 
    $id = 'btnTarif';
    $action = 'openTarifWindow()';
    echo \yii\helpers\Html::tag( 'div', 'Межгород', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 105px;',
                     'title'   => 'Тарифы на междугороднюю доставку',                
                   ]);
    
        ?>
        
        <div style='display:inline; float:right'>
          Итого: <b><?= number_format($model->sumTransport,2,'.','&nbsp;') ?> руб. &nbsp;&nbsp;</b>
        </div>
     </div>   
  </div>

  
</td>


</tr>
</table>

<hr noshade size='5' style="margin-top:-3px;">

<?php/*Нижний блок*/?>    
<?php $form = ActiveForm::begin(['id' => 'Mainform']); ?>
<table border='0' width='1160px'>
<tr>
  <td  width='400px'>
    <p>Исходная заявка</p>
       <?php
       if (empty($model->initLeadRef)) $action="openWin('site/lead-process&zakazId=".$model->zakazId."','contactWin')";        
                                  else $action="openWin('site/lead-process&contactId=".$model->initLeadRef."','contactWin')";        
        echo \yii\helpers\Html::tag( 'div', mb_substr($model->initLead,0, 560, 'utf-8'), 
        [
          'class'   => 'contactShow',
          'id'      => 'initLead',          
          'title'   =>  $model->initLead,
          'onclick' => $action,
        ]); 
       ?>
        
    <div style='margin-top:5px;padding:5px;'> 
    <span class='clickable' style='font-weight:bold; color:Black;' onclick="openWin('site/contacts-detail&id=<?= $model->orgId ?>','contactWin')">История</span>               
        </div>
        <div style='background-color: #e8ffc8; width:370px; height:150px; box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5); border-radius: 1%; padding:5px;'>
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
          'style'   => 'height:150px; overflow:auto',
          'onclick' => $action,
        ]); 

        ?>  
       </div>
   </td>      
   
  <td width='400px' valign='top'>
  <div style='padding:5px'>    
  <b>Счет:</b>  
  
  
     <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus-sign'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'regNewSchet();',
          'style'   => 'font-size:15px; height:22px; width:22px; padding:4px; display:inline',
          'title'   => 'Создать',
   ]); ?>  
      
   <?php 
   $N=count($model->schetList);
   $val ="";
   for ($i=0;$i<$N;$i++ ){
    $style="";
    $action = "openSchet(".$model->schetList[$i]['id'].");";
    $schetText = $model->schetList[$i]['schetNum'];
    $schetText .= " от ". date("d.m.Y", strtotime($model->schetList[$i]['schetDate']));
    $schetText .= " на ". number_format($model->schetList[$i]['schetSumm'],2,'.','&nbsp;')."р.";
    $id = 'schetList'.$model->schetList[$i]['id'];    
    if($model->schetList[$i]['isSchetActive'] == 0) $style .= "color:Green;";
    if($model->schetList[$i]['isReject'] == 1) $style .= "color:Crimson;";
    
    $val.=\yii\helpers\Html::tag( 'div', $schetText, 
    [          
      'id'    => $id,          
      'class' => 'clickable',
      'onclick' => $action,
      'style'   => $style,      
    ]);  
       
   }
     echo \yii\helpers\Html::tag( 'div', $val, 
    [
      
          'id'      => 'schetList',
          'style'   => 'font-size:12px;width:300; height:30px;overflow:auto;margin:10px; '
           
    ]); 
    ?>  
    </div>     
   
   
    <table border=0 style="border:0px; width:100%; padding:5px" >
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
     <div style='height:100px; width:390px; overflow:auto;'>
     <table  border='0'>
     <?php
     $phoneList=$model->getCompanyPhones();
     $out=0;
     for ($i=0;$i<count($phoneList);$i++)
     { 
        if ($phoneList[$i]["status"] == 2){ continue; echo " <font color='red'>*</font>";}
//        $out++; if($out>3) break;        
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
  <td valign='top'>  
    <div style='height:55px'></div>
    <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 40])->label("Комментарий:")?>    
    
    <?php 
        if($model->nextContactTime == '-' ) $nextContactTime = "<span class='glyphicon glyphicon-search'></span>";
                                        else  $nextContactTime = $model->nextContactTime;                                        
    ?> 
    <table width='100%'><tr>
        <td>Дата/время чекаута</td> 
        <td> <?= $form->field($model, 'nextContactDate')->textInput([/*'class' => 'tcal',*/ 'style'=>'width:130px;', 'type' => 'date', 'id' =>'nextContactDate',
        'onchange' => 'showSelectEventTime()'  ])->label(false)?></td>
        <td><div id='nextContactTimeShow' onclick='showSelectEventTime()' class='clickable' ><?= $nextContactTime ?></div></td>   
    </tr></table>    
     
    
  </td>      
</tr>
</table>

    
<hr>
<div class='row'>
    <div class='col-md-3'>
         <?php 
        $id = 'status2';
        $style="";
        $action = 'setStatus(2)';
        if ($model->status==2) $style='background-color:Crimson;color:White;';        
        echo \yii\helpers\Html::tag( 'div', 'Отказ', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 100px;'.$style,
                   ]);
        ?>&nbsp;
         <?php 
        $id = 'status3';
        $style="";
        $action = 'setStatus(3)';
        if ($model->status==3) $style='background-color:Blue;color:White;';
        echo \yii\helpers\Html::tag( 'div', 'В работе', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => 'width: 100px;'.$style,
                   ]);
        ?>
    </div>      
    <div class='col-md-1'>
        
    </div>

    <div class='col-md-2'>
        
    </div>

    <div class='col-md-2'>
         <?php 
                 echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-print'></span>", 
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'submit',
                     'onclick' => "openWin('market/print-zakaz&zakazId=".$model->zakazId."','printWin');",                     
                   ]);
        ?>
    </div>    
    <div class='col-md-2' align='right'>
         <?php 
                 echo \yii\helpers\Html::tag( 'div', 'Сохранить', 
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'submit',
                     'onclick' => 'submitMainForm();',
                     'style'   => 'background-color: ForestGreen;',
                   ]);
        ?>
    </div>    
    <div class='col-md-2'>
        <a class='btn btn-primary' href="#" onclick="javascript: if (confirm('Не сохраненные изменения будут потеряны! Выйти?'))
        {document.location.href='index.php?r=site/success';} "> Выйти </a>
    </div>

</div>
 
 

  
    

    
    
      

<!--- Контакт финиш--->     
   <?= $form->field($model, 'nextContactTime')->hiddenInput(['id' => 'nextContactTime',])->label(false)?>
   <?= $form->field($model, 'status')->hiddenInput(['id' => 'status'])->label(false)?> 
   <?= $form->field($model, 'orgId')->hiddenInput(['id' => 'orgId'])->label(false)?> 
   <?= $form->field($model, 'orgTitle')->hiddenInput(['id' => 'orgTitle'])->label(false)?> 
   <?= $form->field($model, 'zakazId')->hiddenInput(['id' => 'zakazId'])->label(false)?>    
   <?php ActiveForm::end(); ?>
<!--- ******************************************************  --->  
<!--- ******************************************************  --->     


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=market/save-zakaz-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

<!--- ******************************************************  --->  
<!--- ******************************************************  --->     

<?php
Modal::begin([
    'id' =>'orgListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?>
<div style='width:600px'>
    <iframe id='orgListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=site/lead-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

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
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'addWareDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?>
    <iframe width='860px' height='620px' frameborder='no' id='frameAddWareDialog'  src='index.php?r=store/ware-select&noframe=1&mode=1&orgRef=<?= $model->orgId ?>&refZakaz=<?= $zakazRecord['id'] ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
<?php
Modal::end();
/***************************/
?>

<?php
Modal::begin([
    'id' =>'docListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='docListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=site/lead-doc-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>



<?php
Modal::begin([
    'id' =>'cityScladListDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:550px'>

  <?php
  $providerSclad = $model->getScladListProvider(Yii::$app->request->get());    
  echo GridView::widget(
    [
        'dataProvider' => $providerSclad,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'showFooter' => false,
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-small'
        ],
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],
        'columns' => [
            [
                'attribute' => 'sladTitle',
                'label'     => 'Склад',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                 $id= 'sladTitle'.$model['id'];
                 $action="addSelfTransport(".$model['id'].",0)"; 
                    return \yii\helpers\Html::tag( 'div', $model['sladTitle'], 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Добавить доставку',                
                   ]);

                },
            ],        
            [
                'attribute' => 'scladAdress',
                'label'     => 'Адрес',
                'format' => 'raw',
            ],        
           
        ],
     ]
     );
?>

</div>
<?php Modal::end();?>

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>



<script>
 window.onload = function() {
    //alert('Страница загружена');
    // к этому моменту страница загружена

var orgId=document.getElementById('orgId').value;
if (orgId == 0 || orgId =='')
{
   showOrgList(); 
}
    
  };
</script>




