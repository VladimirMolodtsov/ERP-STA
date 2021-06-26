
//setDiv(rowRef, paramId, mult)
function saveField (dataRequestId, type)
{
    var id = type+dataRequestId;    
    document.getElementById('dataRequestId').value=dataRequestId;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(id).value;        
    saveData(showRes);   
}

function showRes(res)
{   

    /*Индексы для элементов переключателей*/
    idx=res['dataType']+res['dataRowId'];    
    //console.log(idx_m);    
    //document.getElementById(idx).value = res['val'];       
    console.log(res);
}

function saveData(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/managment/head/save-purch-classify-data',
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

