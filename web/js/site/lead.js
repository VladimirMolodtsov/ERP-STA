function removeDoc(docId)
{
 var leadid = $('#contactId').val();
 var url = 'index.php?r=site/rm-doc-to-lead&leadid='+leadid+'&docid='+docId
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


function openDocList()
{   
//Показ диалога

    $('#docListForm').modal('show');   
}


// Принудительно скроем
function closeDocList(docId, title)
{
 $('#docListForm').modal('hide');
 var leadid = $('#contactId').val();
 //var data = $('form').serialize();
 
 var url = 'index.php?r=site/add-doc-to-lead&leadid='+leadid+'&docid='+docId
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



function showOrgList()
{   
//Показ диалога

    $('#orgListForm').modal('show');   
}

        
function showZakazList(leadId)
{   
//Показ диалога
    $('#recordId').val(leadId);	        
    $('#zakazListForm').modal('show');   
}

function closeZakazList(zakazId, formDate, schetNum, schetDate )    
	{ 
    
    var zakzazInfo = "Заказ "+zakazId+" от "+formDate;
    
        chngState(12);
        $('#zakzazInfo').html(zakzazInfo);	        
        $('#zakazId').val(zakazId);	        
        $('#zakazListForm').modal('hide');   
        saveMe();
        window.opener.location.reload(false);
        
}
    
    
function clearZakazList()
{
        chngState(10);
        $('#zakzazInfo').html('');	        
        $('#zakazId').val(0);	
        window.opener.location.reload(false);        
}    
    
function openSdelka ()
{
    var zakazId = $('#zakazId').val();
    var orgId = $('#orgId').val();
    openWin('market/market-zakaz&orgId='+orgId+'&zakazId='+zakazId, 'sdelkaWin');
}   
    
function saveMe()
{ 
    
  $('#orgTitle').val($('#contactOrgTitle').val());	          
  document.forms['mainForm'].submit();  
  window.opener.location.reload(false);

  //window.close();
}    


function getNewZakaz(orgId)
{  
  $('#dataVal').val(orgId);	        
  $('#dataType').val('newZakaz');
    
 var data = $('form').serialize();
 $.ajax({
 url: 'index.php?r=site/get-new-zakaz',
 type: 'POST',
 dataType: 'json',
 data: data,
 success: function(res){
   closeZakazList(res.zakazId, res.formDate, '', '' );    
   openWin ('market/market-zakaz&orgId='+orgId+'&zakazId='+res.zakazId, 'childwin');
 console.log(res);
 },
 error: function(){
 alert('Error!');
 }
 });	
	
}
function saveModuleText()
{
    document.getElementById('dataVal').value=document.getElementById('moduleText').value;
    document.getElementById('dataType').value="moduleText";
    saveData();
}

function saveData()
{
   var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/site/save-lead-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showfunc(res);           
        },
        error: function(){
        console.log("warning");            
        }
    });		
    
}

function showfunc(res)
{
    console.log(res);
    window.opener.location.reload(false);
}

function processForm()
{

 var data = $('#mainForm').serialize();
 $.ajax({
 url: 'index.php?r=site/process-new-lead',
 type: 'POST',
 dataType: 'json',
 data: data,
 success: function(res){
	
$('#contactPhone').val(res.contactPhone);	
$('#contactOrgTitle').val(res.contactOrgTitle);	
$('#contactFIO').val(res.contactFIO);	
$('#contactId').val(res.contactId);	
$('#contactEmail').val(res.contactEmail);	
$('#orgId').val(res.orgId);	

console.log(res);
window.opener.location.reload(false);
 },
 error: function(){
 alert('Error!');
 }
 });	
	
}

