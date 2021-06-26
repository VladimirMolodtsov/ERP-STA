
function chngDiv (requestId, dataRowId,  val)
{
    
    document.getElementById('dataRowId').value=dataRowId;
    document.getElementById('dataRequestId').value=requestId;
    document.getElementById('dataType').value='docCfgForm';
    document.getElementById('dataVal').value=val;        
    saveData(showDiv);   
}


function setDataUse (requestId, dataRowId,  val, isPrev)
{
    
    //openSwitchWin('/managment/fin/add-stat-row&rowRef='+requestId+'&statRow='+dataRowId+'&div='+val+'&isPrev='+isPrev );   

    document.getElementById('dataRowId').value=dataRowId;
    document.getElementById('dataRequestId').value=requestId;
    if (isPrev == 0) document.getElementById('dataType').value='utDivCurrent';
    if (isPrev == 1) document.getElementById('dataType').value='utDivPrev';
    document.getElementById('dataVal').value=val;        
    saveData(showDiv);   
    
    
}


function showDiv(res)
{   

    /*Индексы для элементов переключателей*/
    idx_m=res['dataType']+res['dataRowId']+'_-1';    
    idx_z=res['dataType']+res['dataRowId']+'_0';
    idx_p=res['dataType']+res['dataRowId']+'_+1';
    switch (res['val'] )  { 
       case '-1':   
        // -1 выделить, остальные снять выделение

       document.getElementById(idx_m).style.background='Crimson';    
       document.getElementById(idx_m).style.color='White';    
       
       document.getElementById(idx_z).style.background='White';    
       document.getElementById(idx_z).style.color='Blue';    
       
       document.getElementById(idx_p).style.background='White';    
       document.getElementById(idx_p).style.color='Blue';    
       break;
       
       case '0':   
           // 0 выделить, остальные снять выделение

           document.getElementById(idx_m).style.background='White';    
           document.getElementById(idx_m).style.color='Blue';    
           
           document.getElementById(idx_z).style.background='White';    
           document.getElementById(idx_z).style.color='Black';    
           
           document.getElementById(idx_p).style.background='White';    
           document.getElementById(idx_p).style.color='Blue';    
           break;
           
       case '1':   

           // +1 выделить, остальные снять выделение
           document.getElementById(idx_m).style.background='White';    
           document.getElementById(idx_m).style.color='Blue';    
           
           document.getElementById(idx_z).style.background='White';    
           document.getElementById(idx_z).style.color='Blue';    
           
           document.getElementById(idx_p).style.background='Green';    
           document.getElementById(idx_p).style.color='White';           
           break;
    }     
        
    console.log(res);
}



function saveDocCfgType(id)
{
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value='docType';
    document.getElementById('dataVal').value=$('input[name=docTypeControl]:checked').val();    
    saveData(console.log);    
    
}


function saveField(id, type)
{
    idx= type+id;
    
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;    
    saveData(console.log);
}

function saveData(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/managment/fin/save-cfg-data',
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

