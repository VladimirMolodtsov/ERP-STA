
function addPurch(refDoc,refDocOplata)
{    
    document.getElementById('recordId').value=refDoc;
    document.getElementById('dataType').value='addPurch';
    document.getElementById('dataId').value=refDocOplata;        
    $('#selectPurchDialog').modal('show');           
}

function setPurch (purchId)
{    
   console.log(purchId) ; 
   document.getElementById('dataVal').value=purchId;         
   $('#selectPurchDialog').modal('hide');   
    saveData(showPurch);
}

 function    showPurch(res){    
     if (res['res'] == false) console.log(res) ;        
     else document.location.reload(true);  
     /*id=res['dataType']+res['recordId'];       
     document.getElementById(id).style.color='Green';    
     document.getElementById(id).innerHTML= res['orgTitle'];//+"<br>"+res['orgINN'];
     console.log(res); */
}

function saveField(recordId, dataType, dataId)
{
    idx= recordId+dataType+dataId;
 //   alert (idx);
    document.getElementById('recordId').value=recordId;
    document.getElementById('dataType').value=dataType;
    document.getElementById('dataId').value=dataId;
    document.getElementById('dataVal').value=document.getElementById(idx).value;        
    saveData(chngFields);
}

function    removeDocFromOplata(recordId)
{
    document.getElementById('recordId').value=recordId;
    document.getElementById('dataType').value="remove";
    saveData(chngFields);
}

function saveData(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/buh/save-store-oplata',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showfunc(res);           
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}
function    chngFields(res){    
    console.log(res); 
    if (res['isSwitch'] == 1) document.location.reload(true);  
}

var curRecord =0;
function selectOrg(recordId,orgINN)
{
    document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDialogFrame').src="index.php?r=/bank/operator/doc-org-list&noframe=1&searchINN="+orgINN;  
    $('#selectOrgDialog').modal('show');       
}

function closeOrgList(id)
{       
    document.getElementById('dataType').value='refOrg';
    document.getElementById('dataVal').value=id;
    
    saveData(showOrg);
    
    $('#selectOrgDialog').modal('hide');          
}
 
 function    showOrg(res){    
     if (res['res'] == false) return;        
     document.location.reload(true);  
     /*id=res['dataType']+res['recordId'];       
     document.getElementById(id).style.color='Green';    
     document.getElementById(id).innerHTML= res['orgTitle'];//+"<br>"+res['orgINN'];
     console.log(res); */
}
/**********/
function switchData(recordId, dataType, dataId)
{
    
    idx= recordId+dataType+dataId;
    
    document.getElementById('recordId').value=recordId;
    document.getElementById('dataType').value=dataType;
    document.getElementById('dataId').value=dataId;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/buh/save-store-oplata',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
           showSwitch(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}



function    showSwitch(res){
   
 idx= res['recordId']+res['dataType']+res['dataId'];
 switch (res['dataType']){
 case 'extractStatus':
    switch (res['val']){
      case 1:    
          document.getElementById(idx).style.background='LightGreen';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Принято';
      break;
      case 11:    
          document.getElementById(idx).style.background='Yellow';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Организация не распознана';
      break;
      case 3:    
          document.getElementById(idx).style.background='Green';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Сформировано';
      break;
      case 4:    
          document.getElementById(idx).style.background='DarkGreen';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Оплачено';
      break;                 
      case 5:    
          document.getElementById(idx).style.background='Crimson';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Отказано';
      break;                 
      default: 
          document.getElementById(idx).style.background='White';
          document.getElementById(idx).style.color='White';          
          document.getElementById(idx).title = 'Принять';
    }
 break;
 default:
  document.location.reload(true);  
 break; 
 }
  
  console.log(res);   
}

function    preparePaymentOrder(res){

 var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/buh/prepare-payment-order',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
           showOrder(res); 
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	

}

function    showOrder(res){
 
 console.log(res); 
 openWin("/bank/buh/pay-orders",'downloadWin')
 //document.location.reload(true); 
}

/*****/
function    openOplataList(refDocOplata, flt){
  // openWin("/bank/buh/supplier-oplata&refSuppSchet="+refSuppSchet,'childWin') 
   document.getElementById('recordId').value=refDocOplata;
   document.getElementById('selectPayOrderDialogFrame').src="index.php?r=/bank/buh/supplier-oplata&noframe=1&refDocOplata="+refDocOplata+"&flt="+flt;   
   $('#selectPayOrderDialog').modal('show');       
}

function    linkOplata(id){

    //recordId == id doc_oplata
    document.getElementById('dataType').value='link'; //action
    document.getElementById('dataId').value=id; // id supplier_oplata

    saveLnkOplata();

    $('#selectPayOrderDialog').modal('hide');          
}

function    unLinkOplata(id){
    //recordId == id doc_oplata
    document.getElementById('dataType').value='unlink'; //action
    document.getElementById('dataId').value=id; // id supplier_oplata
    saveLnkOplata();
    $('#selectPayOrderDialog').modal('hide');          
}

function saveLnkOplata()
{      
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/buh/save-lnk-oplata',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
           showLnkOplata(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function showLnkOplata(res)
{
 //console.log(res); 
 document.location.reload(true);         
}

/*****/
function    openExtractList(refDocOplata, flt){  
   document.getElementById('recordId').value=refDocOplata;
   document.getElementById('selectExtractDialogFrame').src="index.php?r=/bank/buh/extract-oplata&noframe=1&refDocOplata="+refDocOplata+"&flt="+flt;   
   $('#selectExtractDialog').modal('show');       
}

function    linkExtract(id){

    //recordId == id doc_oplata
    document.getElementById('dataType').value='linkExtract'; //action
    document.getElementById('dataId').value=id; // id supplier_oplata

    saveLnkOplata();

    $('#selectExtractDialog').modal('hide');          
}

function    unLinkExtract(id){
    //recordId == id doc_oplata
    document.getElementById('dataType').value='unlinkExtract'; //action
    document.getElementById('dataId').value=id; // id supplier_oplata
    saveLnkOplata();
    $('#selectExtractDialog').modal('hide');          
}

function    switchPP(type){
    id = type+'Val';
    document.getElementById('dataType').value = type;
    document.getElementById('dataVal').value = document.getElementById(id).value;

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/buh/switch-pp',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
           showSwitchPP(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	

}
function    showSwitchPP(res){
 idx    = res['dataType'] 
 id     = res['dataType']+'Val';
  document.getElementById(id).value=res['value'];
 if (res['value'] == 1)  document.getElementById(idx).style.background='DarkBlue';
                   else  document.getElementById(idx).style.background='White';
                   
 
 document.getElementById('ppTxt').innerHTML = res['ppTxt'];
  console.log(res);   
}

