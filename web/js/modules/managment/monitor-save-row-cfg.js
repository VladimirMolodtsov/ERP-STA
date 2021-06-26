
//setDiv(rowRef, paramId, mult)
function setDiv (dataRequestId, dataRowId,  val, type)
{
    document.getElementById('dataRowId').value=dataRowId;
    document.getElementById('dataRequestId').value=dataRequestId;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=val;        
    saveData(showDiv);   
}

function showDiv(res)
{   

    /*Индексы для элементов переключателей*/
    idx_m=res['dataType']+'minus'+res['dataRowId'];    
    //console.log(idx_m);
    idx_z=res['dataType']+'zero'+res['dataRowId'];
    //console.log(idx_z);
    idx_p=res['dataType']+'plus'+res['dataRowId'];
    //console.log(idx_p);
    
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
           
           document.getElementById(idx_z).style.background='LightGray';    
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

function saveData(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/managment/head/save-row-cfg-data',
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

