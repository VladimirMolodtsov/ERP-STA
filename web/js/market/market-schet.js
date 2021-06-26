function saveField(id, type)
{   
    idx= type+id;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-schet-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
          var sum = document.getElementById('wareCount'+res.recordId).value * document.getElementById('warePrice'+res.recordId).value;  
           console.log(sum);
           document.getElementById('wareSum'+res.recordId).innerText = sum;
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function saveData()
{   
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-schet-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload == true) document.location.reload(true);    
        },
        error: function(){
            document.location.reload(true);    
            //alert('Error while saving data!');
        }
    });	
}

/**************************************/
function saveParam(recordId, dataType, dataVal)
{   
    document.getElementById('recordId').value=recordId;
    document.getElementById('dataType').value=dataType;
    document.getElementById('dataVal').value=dataVal;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-schet-param',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            if(res.needReload == 1) document.location.reload(true);             
            console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}
/**************************************/



function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(contactPhone, contactFIO)
{      
  document.getElementById("contactPhone").value =contactPhone;   
  document.getElementById("contactFIO").value =contactFIO;   
  doCallNew();
}
function failSupplyStatus()
{
 alert('Поставка не завершена. Окончание работы со счетом не возможно!');
}
/**************************************/

function doMail()
{      
  win=window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.forms["Mainform"]["marketschetform-contactemail"].value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');     
  window.win.focus();
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


/**************************************/

function setToFinishState()
{    
     chngSchetStatus (maxSchetStatus); 
     chngCashStatus  (maxCashStatus);     
     chngSupplyStatus (maxSupplyStatus);
}



/*****************************************************************/
/*****************************************************************/
/*Выносим в отдельный блок все что связано с назначением события*/
function showSelectEventTime() {

$(".modal-dialog").width(650);

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
  
    document.getElementById('Mainform').submit();        
}

