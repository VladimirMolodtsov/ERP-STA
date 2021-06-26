

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
        url: 'index.php?r=/managment/fin/save-control-data',
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

