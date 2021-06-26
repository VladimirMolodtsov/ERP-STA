

function saveField(id, type)
{
    idx= type+id;
    
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;        
    saveData(chngFields);
}

function chngFields(res)
{
    /*изменили тип - меняем список операций*/
    if (res['dataType'] == 'contragentType' )   
    {
        getOperation(res['val'],res['id'] )        
    }
console.log(res);
}
function saveData(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-doc-param',
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


function loadDocFinalize()
{
    openWin("bank/operator/start-sync-reg-doc","progressWin");
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
     id=res['dataType']+res['id'];       
     document.getElementById(id).style.color='Green';    
     document.getElementById(id).innerHTML= res['orgTitle']+"<br>"+res['orgINN'];
     console.log(res); 
}
/**********/
function switchData(id, type)
{
    
    idx= type+id;
    
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-doc-param',
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
    
  if (res['isSwitch'] ==1 )    
  {  
   id=res['dataType']+res['id'];
   if (res['val'] == 1)     document.getElementById(id).style.background='Green';    
                   else     document.getElementById(id).style.background='White';      
  } 
  console.log(res); 
}

