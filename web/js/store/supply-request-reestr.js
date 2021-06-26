function editMarketNote(id)
{
    
    var idx="marketNote"+id;
    var val = document.getElementById(idx).textContent;
    
    var orgId= 'orgTitle'+id;
    document.getElementById('orgTitle').textContent=document.getElementById(orgId).textContent;
    var goodId= 'goodTitle'+id;
    document.getElementById('goodTitle').textContent=document.getElementById(goodId).textContent;
    
    document.getElementById('noteText').textContent = document.getElementById(idx).title;
    document.getElementById('requestNote').value="";
    document.getElementById('requestId').value=id;
    document.getElementById('noteType').value=1;
    
    $('#noteEditDialog').modal('show');           
    
}

function editDiscusNote(id)
{
    
    var idx="discusNote"+id;
    var val = document.getElementById(idx).textContent;
    
    var orgId= 'orgTitle'+id;
    document.getElementById('orgTitle').textContent=document.getElementById(orgId).textContent;;
    var goodId= 'goodTitle'+id;
    document.getElementById('goodTitle').textContent=document.getElementById(goodId).textContent;
    
    document.getElementById('noteText').textContent = document.getElementById(idx).title;
    document.getElementById('requestNote').value="";
    document.getElementById('requestId').value=id;
    document.getElementById('noteType').value=2;
    
    $('#noteEditDialog').modal('show');           
    
}

function acceptNoteEdit()
{
    
    $('#noteEditDialog').modal('hide');           
    
    var data = $('#noteEditForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-supply-request-note',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            var idx="";
            switch (res['noteType'])
            {
                case '1': 
                    idx = "marketNote"+res['id'];
                    break;     
                case '2': 
                    idx = "discusNote"+res['id'];
                    break;  
                    
                default : 
                    idx = "discusNote"+res['id'];
                    break;  
                    
            }
               var str = res['requestNote'];      
               document.getElementById(idx).title = str;
               
               document.getElementById(idx).textContent = str.substr(0,150);
               //alert (str.replace("\n", "<br>"));
               //document.getElementById(idx).innerHTML = str.replace("\n", "<br>");        
               console.log(res);
               
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
    
    //$.pjax.reload({container:"#reestrGrid"});
}

function switchConfirmOP(id, type)
{
    $(document.body).css({'cursor' : 'wait'});
    ajaxSwitchInSupplyReestr(id, type);
    $(document.body).css({'cursor' : 'default'});   
    //$.pjax.reload({container:"#reestrGrid"});
}

function saveData(id, type)
{
    
    idx= type+id;
    document.getElementById('dataRequestId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-data-supply-request',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function    showSwitch(res){
    switch (res['switchType'])
    {
        case '1':
            id1="marketNeedAcpt"+res['id'];
            id2="marketIsAccept"+res['id'];
            if (res['val'] == 1)
            {
                document.getElementById(id1).style.background='DarkOrange';    
                document.getElementById(id2).style.display='block';    
            }else{
                document.getElementById(id1).style.background='Silver';    
                document.getElementById(id2).style.display='none';    
            }  
                        break;
        case '2':
            id1="marketNeedAcpt"+res['id'];
            id2="marketIsAccept"+res['id'];
            if (res['val'] == 1)
            {             
                document.getElementById(id1).style.background='DarkGreen';    
                document.getElementById(id2).style.background='DarkGreen';                 
                //console.log(document.getElementById(id2).style); 
            }else{            
                document.getElementById(id1).style.background='DarkOrange';    
                document.getElementById(id2).style.background='Crimson';    
                //console.log(document.getElementById(id2).style); 
            }  
                    break;
                    //3 10 11
                    //setAccepted               
                    //notAccepted
                    
        case '3':
            id="isAccepted"+res['id'];            
            idY="setAccepted"+res['id'];
            idN="notAccepted"+res['id'];                        
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }else if(res['val'] == -1){            
                document.getElementById(id).style.background='Crimson';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }
                         else{            
                             document.getElementById(id).style.background='LightGray';
                             document.getElementById(id).style.display='none';                                  
                             document.getElementById(idY).style.display='inline';                                  
                             document.getElementById(idN).style.display='inline';                                                                
                         }  
                                 break;
                                 
        case '4':
            id="supplyIsAccept"+res['id'];            
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';                 
            }else{            
                document.getElementById(id).style.background='LightGray';    
            }  
                    break;
                    
        case '5':
            id="productIsAccept"+res['id'];            
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';                 
            }else{            
                document.getElementById(id).style.background='LightGray';    
            }  
                    break;
                    
        case '6':
            id="discussIsFinish"+res['id'];            
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';                 
            }else{            
                document.getElementById(id).style.background='Crimson';    
            }  
                    break;
                    
                    
        case '7':
            id="isHaveOriginal"+res['id'];            
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';                 
            }else{            
                document.getElementById(id).style.background='LightGray';    
            }  
                    break;
                    
        case '8':
            id="isFinished"+res['id'];            
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';                 
            }else{            
                document.getElementById(id).style.background='LightGray';    
            }  
                    break;
                    
                    
        case '10':
            id="isAccepted"+res['id'];            
            idY="setAccepted"+res['id'];
            idN="notAccepted"+res['id'];                        
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }else if(res['val'] == -1){            
                document.getElementById(id).style.background='Crimson';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }
                         else{            
                             document.getElementById(id).style.background='LightGray';
                             document.getElementById(id).style.display='none';                                  
                             document.getElementById(idY).style.display='inline';                                  
                             document.getElementById(idN).style.display='inline';                                                                
                         }  
                                 break;
                                 
        case '11':
            id="isAccepted"+res['id'];            
            idY="setAccepted"+res['id'];
            idN="notAccepted"+res['id'];                        
            if (res['val'] == 1){             
                document.getElementById(id).style.background='DarkGreen';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }else if(res['val'] == -1){            
                document.getElementById(id).style.background='Crimson';
                document.getElementById(id).style.display='block';                                  
                document.getElementById(idY).style.display='none';                                  
                document.getElementById(idN).style.display='none';                                                                
            }
                         else{            
                             document.getElementById(id).style.background='LightGray';
                             document.getElementById(id).style.display='none';                                  
                             document.getElementById(idY).style.display='inline';                                  
                             document.getElementById(idN).style.display='inline';                                                                
                         }  
                                 break;
                                 
                                 
    }
        console.log(res); 
}


function switchInSupplyReestr(id, type)
{
    
    $(document.body).css({'cursor' : 'wait'});
    ajaxSwitchInSupplyReestr(id, type); 
    $.pjax.reload({container:"#reestrGrid"});
    $(document.body).css({'cursor' : 'default'});
}


function ajaxSwitchInSupplyReestr(id, type)
{
    
    
    document.getElementById('switchRequestId').value=id;
    document.getElementById('switchType').value=type;
    
    var data = $('#switchEditForm').serialize();
    $.ajax({
        url: 'index.php?r=store/switch-in-supply-request',
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
    //,async:false
    //  $.pjax.reload({container:"#reestrGrid"});
    
}


