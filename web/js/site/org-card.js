var curId  = 0;


function switchReject(id)
{    

    $('#showSyncProgress').modal('show');       
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: 'index.php?r=/site/switch-org&id='+id,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#showSyncProgress').modal('hide');       
            document.location.reload(true);   
            
        },
        error: function(){
             $('html, body').css("cursor", "auto");
             $('#showSyncProgress').modal('hide');       
            alert('Error while saving data!');
        }
    });	
    
}

function syncCard(id)
{    

    $('#showSyncProgress').modal('show');       
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: 'index.php?r=/data/sync-single-org&id='+id,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#showSyncProgress').modal('hide');       
            document.location.reload(true);   
            
        },
        error: function(){
             $('html, body').css("cursor", "auto");
             $('#showSyncProgress').modal('hide');       
            alert('Error while saving data!');
        }
    });	
    
}

function acceptEdit(type, id)
{    
    boxId = type+id;
    
    editId = 'edit_'+boxId;   
    val= document.getElementById(editId).value;

    savePhoneData(type, id, val);
    //openSwitchWin('bank/buh/set-statistics&dtstart=<?=$model->dtstart?>&col='+col+'&order='+order+'&val='+val); 
    closeEditBox(boxId);
}
function showEditBox(boxId)
{

 closeEditBox(curId);
 curId = boxId;
 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
 
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
    document.getElementById(editId).focus();  
    $(editId).focus();    
}

function closeEditBox(boxId)
{
if (boxId == "0") {return;}

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    

}
function addNewDblGis(orgId)
{
    openSwitchWin("site/add-new-dbl-gis&orgRef="+orgId);
}
function addNewOkved(orgId)
{
    openSwitchWin("site/add-new-okved&orgRef="+orgId);
}

function addNewPhone(orgId)
{
    openSwitchWin("site/add-new-phone&orgRef="+orgId);
    
}
function addNewAccount(orgId)
{
    openSwitchWin("site/add-new-acc&orgRef="+orgId);    
}

function addNewAdress(orgId)
{
    openSwitchWin("site/add-new-adress&orgRef="+orgId);    
}

function addNewEmail(orgId)
{
    openSwitchWin("site/add-new-email&orgRef="+orgId);    
}

function addNewUrl(orgId)
{
    openSwitchWin("site/add-new-url&orgRef="+orgId);    
}


function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["w0"]["orgdetail-contactphone"].value=phone;
}

function doCall()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w0"]["orgdetail-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

function setEmail(email)
{
  document.forms["w0"]["orgdetail-contactemail"].value=email;
}

function setUrl(url)
{
  document.forms["w0"]["orgdetail-contacturl"].value=url;
}


function setAdressStatusWin (id, stat)
{      
  wid=window.open("index.php?r=site/chng-adress-stat&id="+id+"&stat="+stat,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
}

function setPhoneStatusWin (id, stat)
{      
  wid=window.open("index.php?r=site/chng-phone-stat&id="+id+"&stat="+stat,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
}

function setEmailStatusWin (id, stat)
{      
  wid=window.open("index.php?r=site/chng-email-stat&id="+id+"&stat="+stat,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
}

function setUrlStatusWin (id, stat)
{      
  wid=window.open("index.php?r=site/chng-url-stat&id="+id+"&stat="+stat,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
}

function setAdress(id, area, city, district, adress, index)
{        
  document.forms["w0"]["orgdetail-adressid"].value=id;    
  document.forms["w0"]["orgdetail-adressarea"].value=area;    
  document.forms["w0"]["orgdetail-adresscity"].value=city;    
  document.forms["w0"]["orgdetail-adressdistrict"].value=district;    
  document.forms["w0"]["orgdetail-adress"].value=adress;    
  document.forms["w0"]["orgdetail-index"].value=index;    
}



/**********/
function saveField(id, type)
{
    idx= id+type;
    
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/site/save-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSaved(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}



/**********/
function savePhoneData(type, id, val)
{
    
    idx= id+type;
    
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=val;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/site/save-phone-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSaved(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


function    showSaved(res){    
    console.log(res); 

   if (res['dataType'] == 'dostavkaAdd')document.location.reload(true);      
   if (res['dataType'] == 'dostavkaDel')document.location.reload(true);      
   
   if (res['dataType'] == 'phoneDel')document.location.reload(true);      
   if (res['dataType'] == 'isDefaultPhone')document.location.reload(true);      
   
   if (res['dataType'] == 'accountDel')document.location.reload(true);         
   
   if (res['dataType'] == 'adressDel')document.location.reload(true);    
   if (res['dataType'] == 'isOfficialAdress')document.location.reload(true);    

   
   if (res['dataType'] == 'emailDel')document.location.reload(true);      
   if (res['dataType'] == 'isDefaultEmail')document.location.reload(true);  
   
   if (res['dataType'] == 'urlDel')document.location.reload(true);  
   
   if (res['dataType'] == 'isDefault')document.location.reload(true);      


   if (res['dataType'] == 'okvedDel')document.location.reload(true);      
   if (res['dataType'] == 'dblGisDel')document.location.reload(true);  

    
    
    
   idx= res['dataRequestId']+res['dataType'];     
    if (res['isSwitch'] == 1){
    if (res['val']  == 1 )   document.getElementById(idx).style.background='Green';
                    else     document.getElementById(idx).style.background='White';    
   }

   if (res['dataType'] == 'phoneStatus'){
    if (res['val']  == 2 )   document.getElementById(idx).style.background='Crimson';
                    else     document.getElementById(idx).style.background='Green';    
   }
   if (res['dataType'] == 'isBadEmail'){
    if (res['val']  == 2 )   document.getElementById(idx).style.background='Crimson';
                    else     document.getElementById(idx).style.background='Green';    
   }
   
   if (res['dataType'] == 'isBadAdress'){
    if (res['val']  == 1 )   document.getElementById(idx).style.background='Crimson';
                    else     document.getElementById(idx).style.background='Green';    
   }

   if (res['dataType'] == 'isBadUrl'){
    if (res['val']  == 1 )   document.getElementById(idx).style.background='Crimson';
                    else     document.getElementById(idx).style.background='Green';    
   }


    
//       
/*    idx='viewBox_'+res['dataType']+res['dataRequestId'];
    console.log(idx);     
    document.getElementById(idx).innerHTML  = res['val'] ;
    */
  //document.location.reload(true);      
}
/********************/
