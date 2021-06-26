
var selectedTask=0;
function showOrg(orgRef)
{
    openWin("site/contacts-detail&id=" +orgRef, "orgwin");
}


/*function rejectTask(id)
{
    openSwitchWin("tasks/main/reject-task&id="+id);  
}*/

function addFreeTask()
{    
    $('#newTask').modal('show');     
    //openWin("/tasks/main/market-task&noframe=1&refManager=<?=$userId ?>", "childwin");      
}

function readTaskChange()
{
    $('#newTask').modal('hide'); 
    openSwitchWin("site/success");  
}

var oldBg= "";
var oldColor= "";
function selectTask (id)
{
    
    if(selectedTask > 0){
        var prevDivId="taskbox_"+selectedTask; 
        document.getElementById(prevDivId).style.backgroundColor =oldBg; 
        document.getElementById(prevDivId).style.color =oldColor; 
    }
       selectedTask = id;
       var divId="taskbox_"+id; 
       oldBg=  document.getElementById(divId).style.backgroundColor ;
       oldColor=  document.getElementById(divId).style.color ;
       document.getElementById(divId).style="color:White; background-color:DarkBlue;"; 
       
}

function unSelectTask ()
{
    if(selectedTask > 0){
        var prevDivId="taskbox_"+selectedTask;    
        document.getElementById(prevDivId).style.backgroundColor =oldStyle; 
    }
}

function acceptTask(dt, tm)
{
    if(selectedTask == 0) {
        addFreeTask();
        return;}
            var strSrc= 'index.php?r=/tasks/main/market-task-accept&noframe=1';
            strSrc= strSrc +"&id="+selectedTask;
            strSrc= strSrc +"&dt="+dt;
            strSrc= strSrc +"&tm="+tm;    
            document.getElementById('acceptTaskFrame').src=strSrc;
            
            $('#acceptTaskDialog').modal('show');    
}

function removeTask (id)
{
    openSwitchWin("tasks/main/remove-task&id="+id);  
}

function markTaskDone(id)
{
    $('#markTaskDialog').modal('show');     
    var strSrc= 'index.php?r=/tasks/main/mark-task-done&noframe=1';
    strSrc= strSrc +"&eventRef="+id;
    document.getElementById('markTaskFrame').src=strSrc;
    
}



function showOrgList()
{
    $(orgSelectList).css('display', 'block'); // убираем у модального окна display: none;
    //.animate({opacity: 1}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз   
    //document.getElementById('orgSelectList').css('display', 'block');
    //document.getElementById('action').value = 'selectOrg';
    //document.getElementById('taskEditForm').submit();
}

function setOrg(id, title)
{
    //$('.collapse-toggle').text("Контрагент: "+title);
    document.getElementById('orgRef').value = id;
    document.getElementById('orgTitle').value = title;
    $(orgSelectList).css('display', 'none');
}


function saveData()
{
    document.getElementById('taskEditForm').submit(); 
    //window.parent.readTaskChange();   
}



/**/

/**********/
function switchData(id, type)
{
    
    idx= type+id;
    
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value=type;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/tasks/market/save-event-exec-detail',
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
        id=res['dataType']+res['dataRequestId'];
        if (res['val'] == 1)     document.getElementById(id).style.background='DarkBlue';    
        else     document.getElementById(id).style.background='White';      
    } 
      console.log(res); 
}
/********************/

function setExec(id)
{
    document.getElementById('setExecRequestId').value=id;
    idNote='execNote'+id ;
    
    document.getElementById('setExecDataVal').value = document.getElementById(idNote).innerHTML;
    $('#setExecDialog').modal('show');    
}

function setExecSave()
{
    $('#setExecDialog').modal('hide');   
    var data = $('#setExecForm').serialize();
    $.ajax({
        url: 'index.php?r=/tasks/market/save-set-exec',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showExec(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	    
    
    
}

function    showExec(res){
    if (res['res'] == false) return;
        id='isExec'+res['dataRequestId'];
        document.getElementById(id).style.background='Green';    
        id='execNote'+res['dataRequestId'];
        document.getElementById(id).innerHTML = res['val'];
        console.log(res); 
}
/********************/
function getSchetData(id)
{
    
    //var data = $('#saveDataForm').serialize();
    data = [];
    $.ajax({
        url: 'index.php?r=/tasks/market/get-schet&id='+id,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            resetSchet(res)
        },
        error: function(){
            alert('Error while retriving data!');
        }
    });	
}

function resetSchet(res)
{
    console.log(res); 
    var isCashGarant = 'isCashGarant'+res['id'];
    var isSupply = 'isSupply'+res['id'];
    var isCashGet = 'isCashGet'+res['id'];        
    var isDocGet = 'isDocGet'+res['id'];        
    var isFinished= 'isFinished'+res['id'];  
    
    if (res['cashState'] >= 1)
    {
        console.log('here'); 
        document.getElementById(isCashGarant).classList.remove("hidden", "glyphicon", "glyphicon-check", "clickable");      
        document.getElementById(isCashGarant).classList.add( "btn", "btn-primary", "circle");
        document.getElementById(isCashGarant).style.background='Green';
        document.getElementById(isCashGarant).style.color='Black';
        document.getElementById(isSupply).classList.remove("hidden");      
    }
    else
    {   
        document.getElementById(isCashGarant).classList.remove( "btn", "btn-primary", "circle");        
        document.getElementById(isCashGarant).classList.add( "glyphicon", "glyphicon-check", "clickable");                      
        document.getElementById(isCashGarant).style.background='';
        document.getElementById(isCashGarant).style.color='Blue';
    }
    
    if (res['supplyState'] >= 1)
    {
        document.getElementById(isSupply).classList.remove("glyphicon", "glyphicon-shopping-cart", "clickable");      
        document.getElementById(isSupply).classList.add( "btn", "btn-primary", "circle");
        document.getElementById(isSupply).style.background='Green';
        document.getElementById(isSupply).style.color='Black';
        document.getElementById(isCashGet).classList.remove("hidden");            
    }
    else
    {   
        document.getElementById(isSupply).classList.remove( "btn", "btn-primary", "circle");        
        document.getElementById(isSupply).classList.add( "glyphicon", "glyphicon-shopping-cart", "clickable");                      
        document.getElementById(isSupply).style.background='';
        document.getElementById(isSupply).style.color='Blue';
    }

    if (res['cashState'] >= 4)
    {
        document.getElementById(isCashGet).classList.remove("glyphicon", "glyphicon-usd", "clickable");      
        document.getElementById(isCashGet).classList.add( "btn", "btn-primary", "circle");
        document.getElementById(isCashGet).style.background='Green';
        document.getElementById(isCashGet).style.color='Black';
        document.getElementById(isDocGet).classList.remove("hidden");            
    }
    else
    {   
        document.getElementById(isCashGet).classList.remove( "btn", "btn-primary", "circle");        
        document.getElementById(isCashGet).classList.add( "glyphicon", "glyphicon-usd", "clickable");                      
        document.getElementById(isCashGet).style.background='';
        document.getElementById(isCashGet).style.color='Blue';
    }
                    
    
    if (res['supplyState'] >= 4)
    {
        document.getElementById(isDocGet).classList.remove("glyphicon", "glyphicon-folder-open", "clickable");      
        document.getElementById(isDocGet).classList.add( "btn", "btn-primary", "circle");
        document.getElementById(isDocGet).style.background='Green';
        document.getElementById(isDocGet).style.color='Black';
        document.getElementById(isFinished).classList.remove("hidden");            
    }
    else
    {   
        document.getElementById(isDocGet).classList.remove( "btn", "btn-primary", "circle");        
        document.getElementById(isDocGet).classList.add( "glyphicon", "glyphicon-folder-open", "clickable");                      
        document.getElementById(isDocGet).style.background='';
        document.getElementById(isDocGet).style.color='Blue';
    }
                    
    if (res['supplyState'] >= 5)
    {
        document.getElementById(isFinished).classList.remove("glyphicon", "glyphicon-ok-sign", "clickable");      
        document.getElementById(isFinished).classList.add( "btn", "btn-primary", "circle");
        document.getElementById(isFinished).style.background='Green';
        document.getElementById(isFinished).style.color='Black';         
    }
    else
    {   
        document.getElementById(isFinished).classList.remove( "btn", "btn-primary", "circle");        
        document.getElementById(isFinished).classList.add( "glyphicon", "glyphicon-ok-sign", "clickable");                      
        document.getElementById(isFinished).style.background='';
        document.getElementById(isFinished).style.color='Blue';
    }
    
}
