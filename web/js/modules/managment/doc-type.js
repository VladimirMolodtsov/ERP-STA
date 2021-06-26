function addNewTypeRow ()
{
  document.location.href = 'index.php?r=/managment/doc/add-doc-type';  
}
function addNewOperationRow()
{
  document.location.href = 'index.php?r=/managment/doc/add-doc-operation';  
}

function removeOperation(id)
{
  document.location.href = 'index.php?r=/managment/doc/rm-doc-operation&id='+id;  
}

function removeType(id)
{
  document.location.href = 'index.php?r=/managment/doc/rm-doc-type&id='+id;  
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
        url: 'index.php?r=/managment/doc/save-doc-data',
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

