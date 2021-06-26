
function    openSchetList(refExtract, flt){
   document.getElementById('recordId').value=refExtract;
   document.getElementById('selectDocLnkDialogFrame').src="index.php?r=/bank/operator/schet-extract&noframe=1&refExtract="+refExtract+"&flt="+flt;   
   $('#selectDocLnkDialog').modal('show');       
}

function linkSchet(id)
{
  saveLnk(id, 'linkSchet');  
}
function unLinkSchet(id)
{
 saveLnk(id, 'unLinkSchet');     
}




function    openBankOperationList(refExtract, refOrg){
   document.getElementById('recordId').value=refExtract;
   document.getElementById('selectBankOperationFrame').src="index.php?r=/bank/operator/bank-operation-select&noframe=1&refExtract="+refExtract+"&refOrg="+refOrg;   
   $('#selectBankOperation').modal('show');       
}

function linkOperation(id)
{
  saveLnk(id, 'linkOperation');  
}
function unLinkOperation(id)
{
 saveLnk(id, 'unLinkOperation');     
}




function    openDocumentList(refExtract, flt){
   document.getElementById('recordId').value=refExtract;
   document.getElementById('selectDocLnkDialogFrame').src="index.php?r=/bank/operator/doc-extract&noframe=1&refExtract="+refExtract+"&flt="+flt;   
   $('#selectDocLnkDialog').modal('show');       
}
function linkDoc(id)
{
  saveLnk(id, 'linkDoc');  
}
function unLinkDoc(id)
{
 saveLnk(id, 'unLinkDoc');     
}
function saveLnk(id, type)
{

    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=id;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-extraction-lnk',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            chngLink(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function chngLink(res)
{
 console.log(res); 
 document.location.reload(true);           
}

function    switchPP(overdue,yesterday, today){

  url='index.php?r=/bank/operator/index';
  url+= '&overdue='+overdue;  
  url+= '&yesterday='+yesterday;
  url+= '&today='+today;
    
  document.location.href= url;  
}
/******/
function selectDeal(recordId, orgRef, selectedDeal)
{
    var url =  "index.php?r=/site/org-deal-select&noframe=1&orgId="+orgRef+"&selectedDeal="+selectedDeal;  
    console.log(url);
    document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDealFrame').src= url;
    $('#selectOrgDeal').modal('show');       
}

function closeOrgDeal(selectedDeal)
{       
    $('#selectOrgDeal').modal('hide');      
    document.getElementById('dataType').value='selectDeal';
    document.getElementById('dataVal').value=selectedDeal;

    var data = $('#saveDataForm').serialize();
        $.ajax({
            url: 'index.php?r=/bank/operator/save-extraction-param',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(res){     
                showDeal(res);
            },
            error: function(){
                alert('Error while saving data!');
            }
        });	    
}

function showDeal(res)
{
    console.log(res);  
    document.location.reload(true);     
}


/******/
function selectOrg(recordId,orgINN)
{
    document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDialogFrame').src="index.php?r=/bank/operator/doc-org-list&noframe=1&searchINN="+orgINN;  
    $('#selectOrgDialog').modal('show');       
}

function closeOrgList(selectOrg)
{       
    document.getElementById('dataType').value='refOrg';
    document.getElementById('dataVal').value=selectOrg;
 
    if(selectOrg == -2) createOrgByExtract(); 
    else                saveOrg(selectOrg);
    
    $('#selectOrgDialog').modal('hide');          
}


function createOrgByExtract()
{   
    document.getElementById('dataType').value='createOrg';
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-extraction-param',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showOrg(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


function saveOrg(selectOrg)
{    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-extraction-param',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showOrg(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

 
  function    showOrg(res){    
      if (res['res'] == false) return;        
      id="orgTitle_"+res['id'];       
      document.getElementById(id).style.color='Green';    
      document.getElementById(id).innerHTML= res['orgTitle'];
      console.log(res); 
      document.location.reload(true); 
  }
  /**********/
  

function saveData(id, type)
{
    
    idx= type+id;
    
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-extraction-param',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            chngFields(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function chngFields(res)
{
    /*изменили тип - меняем список операций*/
    if (res['dataType'] == 'contragentType' )   
    {
        getOperation(res['val'],res['id'] )        
    }
        if (res['dataType'] == 'orgType' )   
    {
        getDeals(res['val'],res['id'] )        
    }
    
    console.log(res);
}



function getOperation(typeId, recordId)
{
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/get-operation&id='+typeId,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            var text = "<option value='0'>не задан</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";  
            }
                        id= 'operationType'+recordId;
                        document.getElementById(id).innerHTML = text; // Устанавливаем options в select
                                    console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function getDeals(typeId, recordId)
{
    
    console.log ('index.php?r=/bank/operator/get-deals-list&typeId='+typeId+'&extractRef='+recordId);    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/get-deals-list&typeId='+typeId+'&extractRef='+recordId,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            var text = "<option value='0'>не задан</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";  
            }
                                    id= 'orgDeal'+recordId;
                                    document.getElementById(id).innerHTML = text; // Устанавливаем options в select
        console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}
