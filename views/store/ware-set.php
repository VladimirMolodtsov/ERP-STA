<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;

$this->title = 'Выбор номенклатуры';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


$model->loadScladData(); //сначала пробуем открыть из поставщика
$model->loadNamesData(); //далее исходящий
$model->loadData(); //потом подгрузить номенклатуру
$model->loadWareSetPar(); // это заморочки с форматом

?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

.form-group {
margin-bottom:0px;

}
.help-block{
display:none;
}

.help-block-error{
display:none;
}
</style>
  
<script>


function showEditType(){
    $('#editTypeDialog').modal('show');
}
function closeTypeDialog(){
    loadType( );
    $('#editTypeDialog').modal('hide');
}

function showGroupType(){
    $('#editGroupDialog').modal('show');
}
function closeGroupDialog(){
    chngType( );
    $('#editGroupDialog').modal('hide');
}

function showProducerType(){
    $('#editProducerDialog').modal('show');
}

function closeProducerDialog(){
    loadProducer( );
    $('#editProducerDialog').modal('hide');
}

function showFormatDialog(){
    $('#editFormatDialog').modal('show');
}

function closeFormatDialog(){
    loadFormatList( );
    $('#editFormatDialog').modal('hide');
}


function showProduce(){
    $('#produceDataDialog').modal('show');  
}

function hideProduce(){
    $('#produceDataDialog').modal('hide');  
}


function selectWare(wareRef,edRef ) {    
	window.parent.addSelectedWare(wareRef,edRef);
}



function chngProd( ) {    
var saleType = $('input[id="saleType"]:checked').val();
//document.getElementById('wareLength').value ='';
document.getElementById('wareFormat').value ='';

  switch (saleType)
  {
   case '2':
   //document.getElementById('wareLengthCont').style.display = 'none';
   document.getElementById('produceBlock').style.display = 'block';   
   break;
   case '1':
   //document.getElementById('wareLengthCont').style.display = 'block';
   document.getElementById('produceBlock').style.display = 'none';   
   break;
  }

  loadFormatList ();

acceptFilter(0);
}

function loadFormatList( ) { 

  document.getElementById('wareFormatSel').value =0;
   var saleType = $('input[id="saleType"]:checked').val();
   $(document.body).css({'cursor' : 'wait'});   
   var url = 'index.php?r=store/get-format-list&saleType='+saleType;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
            console.log(res);
            
            var text = "<option value='0'>Формат</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";  
            }                        
            document.getElementById('wareFormatSel').innerHTML = text; // Устанавливаем options в select           
            $(document.body).css({'cursor' : 'default'});            
            //document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive ware groups!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });	
              
}


function cngFormat () {
   var wareFormatRef = document.getElementById('wareFormatSel').value;
   $(document.body).css({'cursor' : 'wait'});   
   var url = 'index.php?r=store/get-format-details&wareFormatSel='+wareFormatRef;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
            console.log(res);
            document.getElementById('wareFormat').value =res.wareFormat;
//            alert(res.wareFormat);
//            document.getElementById('wareLength').value =res.wareLength;
//            document.getElementById('wareWidth').value  =res.wareWidth;
//            document.getElementById('rolType').value    =res.rolType;

            $(document.body).css({'cursor' : 'default'});            
            //document.location.reload(true); 
            acceptFilter(0);   
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive format detail!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });	
 
}    

function copyCurrentWare(id){
   
   $(document.body).css({'cursor' : 'wait'});   
   var url = 'index.php?r=store/get-ware-data&id='+id
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
            console.log(res);

            if (res.res==true){
            document.getElementById('wareTitle').value = res.wareTitle;
            document.getElementById('wareType').value =  res.wareType;
            document.getElementById('wareGroup').value = res.wareGroup;
            document.getElementById('wareProducer').value = res.wareProducer;
            document.getElementById('wareDensity').value = res.wareDensity;
            document.getElementById('saleType').value = res.saleType;
            document.getElementById('wareFormatSel').value = res.wareFormat;           
            document.getElementById('wareMark').value = res.wareMark;
            document.getElementById('wareSort').value = res.wareSort;
            document.getElementById('wareNote').value = res.wareNote;
            document.getElementById('addNote').value = res.addNote;
            //document.getElementById('produceType').value = res.produceType;
            
            
            if(res.produceType ==1){
                $('input[name="produceType"][value="1"]').prop('checked', true);
                document.getElementById('wareWidth_l').value = res.wareWidth;
                document.getElementById('wareLength_l').value = res.wareLength;
            }
            if(res.produceType ==2){
                $('input[name="produceType"][value="2"]').prop('checked', true);
                document.getElementById('wareWidth_r').value = res.wareWidth;
                document.getElementById('wareLength_r').value = res.wareLength;
            }
            document.getElementById('warePackSize').value = res.warePackSize;
            //document.getElementById('outStatus').value = res.outStatus;
             $('input[name="outStatus"][value="'+res.outStatus+'"]').prop('checked', true);
              $('input[name="rolType"][value="'+res.rolType+'"]').prop('checked', true);
            //document.getElementById('rolType').value = res.rolType;
            document.getElementById('warePackWeight').value = res.warePackWeight;
            $(document.body).css({'cursor' : 'default'});
           } 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive ware groups!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });	
              
acceptFilter(0);                
}

function clearWare(){
   var refSclad=document.getElementById('refSclad').value;
   var id=document.getElementById('id').value;
   document.location.href='index.php?r=store/ware-set&noframe=1&refSclad='+refSclad+'&id='+id;   
}


function setCurrentWare(id){

   var refSclad=document.getElementById('refSclad').value;
   var refName=document.getElementById('refName').value;
   document.location.href='index.php?r=store/ware-set&noframe=1&id='+id+'&refSclad='+refSclad+'&refName='+refName;
}


function chngType( ) { 

   var wareType = document.getElementById('wareType').value;
   $(document.body).css({'cursor' : 'wait'});   
   var url = 'index.php?r=store/get-ware-groups&wareType='+wareType
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
            console.log(res);
            
            var text = "<option value='0'>Выбор вида товара</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";  
            }            
            document.getElementById('wareGroup').innerHTML = text; // Устанавливаем options в select           
            $(document.body).css({'cursor' : 'default'});            
            acceptFilter(0);            
            //document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive ware groups!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });	
              

}


function loadType( ) {

   $(document.body).css({'cursor' : 'wait'});
   var url = 'index.php?r=store/get-ware-types';
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){
            console.log(res);

            var text = "<option value='0'>Выбор типа товара</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";
            }
            document.getElementById('wareType').innerHTML = text; // Устанавливаем options в select
            $(document.body).css({'cursor' : 'default'});
            acceptFilter(0);
            //document.location.reload(true);
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive ware types!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });


}


function loadProducer( ) {

   $(document.body).css({'cursor' : 'wait'});
   var url = 'index.php?r=store/get-ware-producers';
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){
            console.log(res);

            var text = "<option value='0'>Выбор производителя</option>"; // Начинаем создавать элементы в select
            for(var i in res)
            {
                text += "<option value='" + i + "'>" + res[i] + "</option>";
            }
            document.getElementById('wareProducer').innerHTML = text; // Устанавливаем options в select
            $(document.body).css({'cursor' : 'default'});
            acceptFilter(0);
            //document.location.reload(true);
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive ware types!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });


}



function cngDensity () {
document.getElementById('wareDensity').value = document.getElementById('wareDensitySel').value;
acceptFilter(0);    
}    

function unFilter(){

document.getElementById('wareType').value = 0;
document.getElementById('wareGroup').value = 0;
document.getElementById('wareProducer').value = 0;
//var saleType = $('input[id="saleType"]:checked').val();
//var wareWidth = document.getElementById('wareWidth').value;
//var wareLength= document.getElementById('wareLength').value;

document.getElementById('wareFormatSel').value = 0;
document.getElementById('wareFormat').value ='';

document.getElementById('wareDensitySel').value = 0;
document.getElementById('wareDensity').value='';

document.getElementById('wareMark').value='';
document.getElementById('wareSort').value='';
document.getElementById('addNote').value='';

acceptFilter(0);    
}




function acceptFilter(mode) {    
var wareType = document.getElementById('wareType').value;
var wareGrp = document.getElementById('wareGroup').value;

var wareProd = document.getElementById('wareProducer').value;

var saleType = $('input[id="saleType"]:checked').val();
//var wareWidth = document.getElementById('wareWidth').value;
//var wareLength= document.getElementById('wareLength').value;

var wareFormatSel = document.getElementById('wareFormatSel').value;
var wareFormat = document.getElementById('wareFormat').value;
var wareDensity= document.getElementById('wareDensity').value;

var wareMark= document.getElementById('wareMark').value;
var wareSort= document.getElementById('wareSort').value;

var addNote= document.getElementById('addNote').value;
var id= document.getElementById('id').value;

var url = 'index.php?r=store/ware-set-frame&noframe=1&wareType='+wareType+'&wareGrp='+wareGrp+'&wareProd='+wareProd;
    url = url+'&wareFormatSel='+wareFormatSel+'&wareFormat='+wareFormat+'&wareSort='+wareSort+'&wareMark='+wareMark;
    url = url+'&saleType='+saleType;
    url = url+'&id='+id+'&wareDensity='+wareDensity+'&addNote='+addNote;
//document.location.href=url;

console.log(url);
document.getElementById('frameSelectWareDialog').src=url;
//    document.getElementById('mainForm').submit();

}




function createWare(){

document.getElementById('wareTitle').readOnly = false;

    $(document.body).css({'cursor' : 'wait'});
    var data = $('#mainForm').serialize();
    $.ajax({
        url: 'index.php?r=store/create-ware-title',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.getElementById('wareTitle').value=res.wareTitle;
            $(document.body).css({'cursor' : 'default'});            
            //document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while retrive ware title!');
        }
    });	
    
 document.getElementById('wareTitle').readOnly = true;   
}


function saveWare (){

    $(document.body).css({'cursor' : 'wait'});
    var data = $('#mainForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-ware',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);            
            $(document.body).css({'cursor' : 'default'});            
            //document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while retrive ware title!');
        }
    });	
    
  acceptFilter(0);      
}


function linkNameWare(act){

//var saleType = $('input[id="saleType"]:checked').val();
  $(document.body).css({'cursor' : 'wait'});
    var data = $('#mainForm').serialize();
    var url='index.php?r=store/lnk-ware&src=name&act='+act;
    console.log(url);
    $.ajax({
        url: 'index.php?r=store/lnk-ware&src=name&act='+act,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            //console.log(res);
            $(document.body).css({'cursor' : 'default'});
            document.location.reload(true);
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while retrive ware title!');
        }
    });
  acceptFilter(1);
}


function linkScladWare(act){

var saleType = $('input[id="saleType"]:checked').val();
  $(document.body).css({'cursor' : 'wait'});
    var data = $('#mainForm').serialize();
    $.ajax({
        url: 'index.php?r=store/lnk-ware&src=sclad&act='+act,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);            
            $(document.body).css({'cursor' : 'default'});            
            document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while retrive ware title!');
        }
    });	
  acceptFilter(1);    
}
</script>

<h3>Конструктор номенклатуры</h3>

<?php $form = ActiveForm::begin(
[ 
  'options' =>[      
    'style' => 'font-size: 12px;',
    'id'    => 'mainForm',    
  ],
  //'action' => 'index.php?r=store/create-ware-title'
  //'action' => 'index.php?r=store/lnk-ware'
  //'action' => 'index.php?r=store/save-ware'
  'action' => 'index.php?r=store/lnk-ware&src=sclad&act=1'

]); ?>



<div>  
<table width='100%' border='0' class='table table-striped' style='font-size:12px;'>    


<?php if(!empty($model->refName)) { ?>
<tr>
    <td width='200px'>Исходящее наименование</td>
    <td>
    <?php
    $style='color:Black';
    $disabled=false;
    $unlink=false;
    $class='alert-secondary';
    if ($model->lnkName ==0) { $style='color:Black'; $unlink=true;}
    else {
        if ($model->lnkName == $model->id) {$style='color:DarkGreen;font-weight:bold;';
            $comment='Товар привязан к текущей номенклатуре!';
            $class='alert-success';
            }
        if ($model->lnkName != $model->id) {$style='color:Crimson;font-weight:bold;';
            $comment='Товар привязан к другой номенклатуре!';
            $class='alert-danger';
            }

        }

      echo \yii\helpers\Html::tag( 'div', $model->nameWareTitle,
      [
        'id'      => 'nameWareTitle',
        'style'   => $style,
      ]);

    if(!empty($comment)){
	echo Alert::widget([
    'options' => [
        'class' => $class
    ],
    'body' => $comment
    ]);
    }
    ?>
    </td>
     <td align='right' width='150px'>
    <?php
    $lncAction='linkNameWare(1);';
    if ( $model->id == 0 ) {$disabled=true;$style='color:DarkGray'; $lncAction='';}
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon  glyphicon-link'></span>",
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => $lncAction,
                     'title'   => 'Привязать складское название к номенклатуре',
                     'style'   => $style,
                     'disabled' => $disabled
                   ]);
    ?>
    <?php
    $lncAction='linkNameWare(0);';
    if ( $model->lnkName  == 0 ) {$disabled=true;$style='color:DarkGray'; $lncAction='';}
    echo \yii\helpers\Html::tag( 'div', "<span class=' 	glyphicon glyphicon-remove-sign'></span>",
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => $lncAction,
                     'title'   => 'Отвязать складское название от номенклатуре',
                     'style'   => 'text-decoration:line-through',
                     'disabled' => $disabled
                   ]);
    ?>
    <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-filter'></span>",
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => 'acceptFilter(0);',
                     'title'   => 'Отфильтровать',
                   ]);
    ?>

    </td>

 </tr>
<?php } ?>



<?php if(!empty($model->refSclad)) { ?>
<tr>
    <td width='200px'>Товар от поставщика</td>
    <td> 
    <?php    
    $style='color:Black';
    $disabled=false;
    $unlink=false;
    $class='alert-secondary';
    if ($model->lnkSclad ==0) { $style='color:Black'; $unlink=true;}
    else {
        if ($model->lnkSclad == $model->id) {$style='color:DarkGreen;font-weight:bold;';
            $comment='Товар привязан к текущей номенклатуре!';
            $class='alert-success';
            }   
        if ($model->lnkSclad != $model->id) {$style='color:Crimson;font-weight:bold;';   
            $comment='Товар привязан к другой номенклатуре!';
            $class='alert-danger';
            }                           
            
        }
        if (!empty($model->lnkProd) && $model->saleType == 2 ) {
            $comment='Товар используется для производства номенклатуры!';
            $class='alert-primary';
        }
    
    
      echo \yii\helpers\Html::tag( 'div', $model->scladWareTitle,        
      [
        'id'      => 'scladWareTitle',
        'style'   => $style,
      ]);    
      
    if(!empty($comment)){
	echo Alert::widget([
    'options' => [
        'class' => $class
    ],
    'body' => $comment
    ]);      
    }  
    ?>
    </td>   
     <td align='right' width='150px'> 
    <?php
    $lncAction='linkScladWare(1);';
    if ( $model->id == 0 ) {$disabled=true;$style='color:DarkGray'; $lncAction='';}
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon  glyphicon-link'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => $lncAction,
                     'title'   => 'Привязать складское название к номенклатуре',                     
                     'style'   => $style,
                     'disabled' => $disabled
                   ]);    
    ?>               
    <?php
    $lncAction='linkScladWare(0);';    
    if ( $model->lnkSclad  == 0 ) {$disabled=true;$style='color:DarkGray'; $lncAction='';}    
    echo \yii\helpers\Html::tag( 'div', "<span class=' 	glyphicon glyphicon-remove-sign'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => $lncAction,
                     'title'   => 'Отвязать складское название от номенклатуре',                     
                     'style'   => 'text-decoration:line-through',
                     'disabled' => $disabled
                   ]);    
    ?>
    <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-filter'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => 'acceptFilter(0);',
                     'title'   => 'Отфильтровать',                     
                   ]);    
    ?>               
    
    </td>
    
 </tr>    
<?php } ?>



<?php if(!empty($model->id) || !empty($model->refSclad) ) { ?>
<tr>
    <td width='200px'>Выбранное номенклатурное название</td>
    <td > 

    <?= $model->requestTitle ?>
    
    </td>
    
    <td align='right' width='150px'>    
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-share-alt'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => 'unFilter();',
                     'title'   => 'сбросить фильтры',                     
                   ]);    
    ?>               
    
    </td>

    
          
 </tr>    
<?php } ?>


<tr>
    <td width='200px'>Сконструированное название</td>
    <td > 
    <?php
      if ($model->isConfirmed) $style="color:DarkGreen;font-weight:bold;";
      else $style="color:Black;font-weight:normal;";
      echo  $form->field($model, 'wareTitle')
            ->textInput([        
                'id' => 'wareTitle',
                'readonly' => true,   
                'style'   => $style,
                //'placeholder' => 'Номенклатурное название'     
            ])->label(false);    
    ?>
    </td>   

    <td align='right' width='150px'>     
    <?php
    echo \yii\helpers\Html::tag( 'div', "<span class=' 	glyphicon glyphicon-wrench'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                     'onclick' => 'createWare();',
                     'title'   => 'Создать номенклатуру',                     
                   ]);    
    ?>               
    
    <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                      'onclick' => 'saveWare();',
                     'title'   => 'Сохранить',
                   ]);    
    ?>               
    
   
       <?php
       
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-repeat'></span>", 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'btn-search',
                      'onclick' => 'clearWare();',
                     'title'   => 'Вернуть к исходному',
                   ]);    
    ?>               
    </td>   
 
 </tr>    
  
</table> 

</div>

<table width='100%' border='1'><tr>
<td width='340px' valign='top' style='padding:10px;'>
<br>
<!-------------------------------->
<div id='mainData' style='_display:none' align='center'>      
<table width='320px' border='0' class='table table-striped table-small' style='font-size:12px;padding:2px'>
<tr>
    <td width='25px'>
    <div class='clickable' onclick='showEditType();'>Тип товара</div></td>
    <td colspan=2> 
    <?php
    $wareTypes =$model->getWareTypes();
    
    echo  $form->field($model, 'wareType')
    ->dropDownList($wareTypes,
    [      
        'id' => 'wareType', 
        'style' => 'background:gray;color:#fff;',
        'onChange' => 'chngType()',        
        'prompt' => 'Выбор типа',
    ])->label(false);
    ?>
    </td>
</tr>
<tr>    
    
    <td  width='75px'>
    <div class='clickable' onclick='showGroupType();'>Вид товара</div></td>
    </td>
    <td colspan=2 > 
    <?php
    $wareGroups =$model->getWareGroups();
    echo  $form->field($model, 'wareGroup')
    ->dropDownList($wareGroups,
    [
        'id' => 'wareGroup', 
        'style' => 'background:gray;color:#fff;',   
        'onchange' => 'acceptFilter(0);',
        'prompt' => 'Выбор вида товара',     
    ])->label(false);
    ?>
    </td>
 </tr>

 
 <tr>    
    <td colspan =3>
    <table> <tr>
    <td  width='140px' align='left'>     
    <?php
    
    $action ="chngProd();";
    echo $form->field($model, 'saleType')->radio(['label' => 'В сырье', 'value' => 1, 'uncheck' => null, 'id' => 'saleType', 'onchange' => $action]);    
        ?>
    </td>
    <td  width='150px' align='left'>     
    <?php    
    echo $form->field($model, 'saleType')->radio(['label' => 'Нужна переработка', 'value' => 2, 'uncheck' => null, 'id' => 'saleType', 'onchange' => $action]);
        ?>
    </td>
    
    <td align='right' width='20px;' >&nbsp;     
    <div id='produceBlock' style='<?php if($model->saleType == 1) echo "display:none;" ?>'>
    <?php    
       echo \yii\helpers\Html::tag( 'div', '', 
          [
          'class'   => 'clickable glyphicon glyphicon-cog',
          'id'      => 'produceConfig',
          'onclick' => "showProduce()",          
           ]);
    ?>
    </div>
    </td>
    </tr></table>
    </td>
</tr>

 
 <tr>
    <td>Плотность</td>
    <td>
    <?php
    $wareDensity =$model->getWareDensity();
    
    echo  $form->field($model, 'wareDensitySel')
    ->dropDownList($wareDensity,
    [
        'id' => 'wareDensitySel', 
        'style' => 'background:gray;color:#fff;',        
        'onchange' => 'cngDensity();',
        'prompt' => 'Плотность',      
        
    ])->label(false);
    ?>
    </td>
    <td>
    <?php
    echo  $form->field($model, 'wareDensity')
    ->textInput([        
        'id' => 'wareDensity',       
        'onchange' => 'acceptFilter(0);',
    ])->label(false);

    ?>
    </td>
   </tr>

   
<tr>    
   <td><div class='clickable' onclick='showFormatDialog();'>Формат</div></td>      
    <td width='120px'>
    <?php
    $formatTypes =$model->getWareFormat();
    
    echo  $form->field($model, 'wareFormatSel')
    ->dropDownList($formatTypes,
    [
        'id' => 'wareFormatSel', 
        'style' => 'background:gray;color:#fff;',        
        'onchange' => 'cngFormat();',
        'prompt' => 'Формат',    
        ])->label(false);    
    ?></td>
    <td>
        <?php
            echo  $form->field($model, 'wareFormat')
            ->textInput([
                'id' => 'wareFormat',
                'placeholder' => 'Формат'
            ])->label(false);
        ?>
    </td>
  </tr>

<tr>   
   <td width='100px'>Марка</td>
   <td colspan=2> 
    <?php
    
    echo  $form->field($model, 'wareMark')
    ->textInput([        
        'id' => 'wareMark',         
        'style' => 'width:150px; font-size:11px;padding:1px;', 
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>
</tr>    

<tr>    
    <td>Сорт</td>
    <td colspan=2> 
    <?php
    echo  $form->field($model, 'wareSort')
    ->textInput([        
        'id' => 'wareSort',         
        'style' => 'width:150px; font-size:11px;padding:1px;', 
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>
 </tr>
     
 <tr>   
    <td>
    <div class='clickable' onclick='showProducerType();'>Производитель</div></td>
    </td>
    <td colspan=2> 
    <?php
  $wareProducer =$model->getWareProducer();
   // $wareGroups[0]='Все'; 
    echo  $form->field($model, 'wareProducer')
    ->dropDownList($wareProducer,
    [
        'id' => 'wareProducer', 
        'style' => 'background:gray;color:#fff;',   
        'prompt' => 'Ппроизводитель',         
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>   
 </tr>

  <tr>   
    <td>Дополнение</td>
    <td colspan=2> 
    <?php
    echo  $form->field($model, 'addNote')
    ->textInput([        
        'id' => 'addNote',         
        'style' => 'width:150px; font-size:11px;padding:1px;', 
        'onchange' => 'acceptFilter(0);',
    ])->label(false);
    ?>
    </td>   
 </tr>

  <tr>   
    <td colspan=3> 
    <?php
    echo  $form->field($model, 'wareNote')
    ->textArea([        
        'id' => 'wareNote',         
        'style' => 'height:80px;font-size:11px;padding:1px;', 
    ])->label('Описание товара');
    ?>
    </td>
 </tr>
 
</table> 
 
 
         
</div>
    
<!-------------------------------->
</td>
<td valign=top style='padding:10px;'>
<!-------------------------------->

<?php
$wareType = $model->wareType;
$wareGrp = $model->wareGroup;
$wareProd = $model->wareProducer;
$saleType = $model->saleType;
$wareWidth = $model->wareWidth;
$wareLength= $model->wareLength;
$wareFormat = $model->wareFormat;
$wareFormatSel = $model->wareFormatSel;
$wareDensity= $model->wareDensity;
$wareMark= $model->wareMark;
$wareSort= $model->wareSort;
$addNote= $model->addNote;
$id= $model->id;

$url = 'index.php?r=store/ware-set-frame&noframe=1&wareType='.$wareType.'&wareGrp='.$wareGrp.'&wareProd='.$wareProd;
$url .= '&wareFormatSel='.$wareFormatSel.'&wareFormat='.$wareFormat.'&wareSort='.$wareSort.'&wareMark='.$wareMark;
$url .= '&saleType='.$saleType;
$url .= '&id='.$id.'&wareDensity='.$wareDensity.'&addNote='.$addNote;
?>

    <iframe width='100%' height='720px' frameborder='no' id='frameSelectWareDialog'  src='<?= $url ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  


<!-------------------------------->
</td>
</tr></table>
    
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'produceDataDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);
?><div id='produceData'  align='center'>      

<table width='100%' border='0' class='table table-striped' style='font-size:12px;'>    
<?php if(!empty($model->refName)) { ?>
<tr>
    <td width='200px'>Исходящее наименование</td>
    <td>
    <?php
      echo \yii\helpers\Html::tag( 'div', $model->nameWareTitle,
      [
      ]);?>
    </td>
 </tr>
<?php } ?>

<?php if(!empty($model->refSclad)) { ?>
<tr>
    <td width='200px'>Товар от поставщика</td>
    <td> 
    <?php    
      echo \yii\helpers\Html::tag( 'div', $model->scladWareTitle,        
      [
      ]);
    ?>      
    </td>
 </tr>    
<?php } ?>
<?php if(!empty($model->id) || !empty($model->refSclad) ) { ?>
<tr>
    <td width='200px'>Выбранное номенклатурное название</td>
    <td> 
        <?= $model->requestTitle ?>
    </td>
 </tr>    
<?php } ?>
</table> 

<table width='500px' border='1' ><tr>
    <td align='right' valign='top'>
        <table border='0' class='table ' style='font-size:12px;'>
        <tr>
            <td colspan='2' align='center'  style='font-size:120%;'> 
                <?php echo $form->field($model, 'produceType')->radio(['name'=> 'produceType', 'label' => 'Порезка на листы', 'value' => 1, 'uncheck' => null]); ?>    
            </td>
        </tr>    
        <tr>
        <td align='right'>Формат ширина (мм)</td>
        <td>
            <?php            
            echo  $form->field($model, 'wareWidth_l')
                ->textInput([        
                    'id' => 'wareWidth_l',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);
            
              ?>
            </td>
         </tr>    
         <tr>
         <td align='right'>Длинна (мм)</td>
            <td>
            <?php
            echo  $form->field($model, 'wareLength_l')
                ->textInput([        
                    'id' => 'wareLength_l',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);            
            ?>
            </td>
         </tr>    
        <tr>
        <td align='right'>Листов в пачке</td>
            <td>
            <?php
            
            echo  $form->field($model, 'warePackSize')
                ->textInput([        
                    'id' => 'warePackSize',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);
            ?>
            </td>
         </tr>  
        <tr>
        <td align='left' colspan='2'><b>Отгрузка</b> 
        <div style='margin-left:20px;' >
            <?php
                echo $form->field($model, 'outStatus')->radio(['name'=> 'outStatus', 'id'=>'outStatus','label' => 'в пачках', 'value' => 1, 'uncheck' => null]);
                echo $form->field($model, 'outStatus')->radio(['name'=> 'outStatus','id'=>'outStatus','label' => 'в листах', 'value' => 2, 'uncheck' => null]);
                echo $form->field($model, 'outStatus')->radio(['name'=> 'outStatus','id'=>'outStatus','label' => 'в кг', 'value' => 3, 'uncheck' => null]);
             ?>    
          </div>   
            </td>
        </tr>    
    </table></td>
    <td align='left'>
    <table border='0' class='table ' style='font-size:12px;'>    
    <tr>
        <td colspan='2' align='center'  style='font-size:120%;'> 
        <?php  echo $form->field($model, 'produceType')->radio(['name'=> 'produceType', 'label' => 'Перемотка в ролики', 'value' => 2 , 'uncheck' => null]);  ?>    
        </td>
    </tr>    
<tr>
    <td align='right'> Формат ширина (мм)</td>
    <td>
    <?php
        echo  $form->field($model, 'wareWidth_r')
               ->textInput([        
                    'id' => 'wareWidth_r',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);            
    ?>
    </td>
 </tr>  
<tr>
    <td align='right'>Намотка длинна (м)</td>
    <td>
    <?php
            echo  $form->field($model, 'wareLength_r')
                ->textInput([        
                    'id' => 'wareLength_r',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);            
    ?>
    </td>
 </tr>  
    <tr>
    <td align='left' colspan='2'><b>Ролик</b>
    <div style='margin-left:20px;'>
    <?php

        echo $form->field($model, 'rolType')->radio(['name'=> 'rolType', 'label' => 'вн.диаметр 20 мм без втулки', 'value' => 20, 'uncheck' => null]);
        echo $form->field($model, 'rolType')->radio(['name'=> 'rolType', 'label' => 'вн.диаметр 50 мм со втулкой', 'value' => 50, 'uncheck' => null]);
        echo $form->field($model, 'rolType')->radio(['name'=> 'rolType', 'label' => 'вн.диаметр 76 мм со втулкой', 'value' => 76, 'uncheck' => null]);
           
    ?>
    </div>
    </td>
 </tr>  

<tr>
    <td align='left' colspan='2'><b>Отгрузка</b> 
    <div style='margin-left:20px;'>
    <?php        
        echo $form->field($model, 'outStatus')->radio(['name'=> 'outStatus','id'=>'outStatus', 'label' => 'в роликах', 'value' => 4, 'uncheck' => null]);
        echo $form->field($model, 'outStatus')->radio(['name'=> 'outStatus','id'=>'outStatus','label' => 'в кг', 'value' => 5, 'uncheck' => null]);
     ?>    
     </div>
    </td>
        
</tr>    
<tr>
    <td align='right'>Вес рулона</td>
    <td>
    <?php
            echo  $form->field($model, 'warePackWeight')
                ->textInput([        
                    'id' => 'warePackWeight',         
                    'style' => 'width:75px; font-size:11px;padding:1px;', 
                ])->label(false);            
    ?>
    </td>
     </tr>  
    </table></td>
</tr>
</table> 
<div class='spacer'></div>

<tr>
    <td></td>
    <td align='right'><br>
            <div class='btn btn-primary' onclick='hideProduce();'>Принять/Закрыть</div>
    </td>
 </tr>    
</table>

</div><?php
Modal::end();
/***************************/
?>
        
    
    
<?php    
echo $form->field($model, 'refName' )->hiddenInput(['id' => 'refName' ])->label(false);
echo $form->field($model, 'refSclad' )->hiddenInput(['id' => 'refSclad' ])->label(false);
echo $form->field($model, 'id' )->hiddenInput(['id' => 'id' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end();     
/*************************/
?>    
 
 
    
    
<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-ware-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);


echo $form->field($model, 'wareTitle' )->hiddenInput(['id' => 'wareTitle' ])->label(false);
echo $form->field($model, 'grpRef' )->hiddenInput(['id' => 'grpRef' ])->label(false);
echo $form->field($model, 'producerRef' )->hiddenInput(['id' => 'producerRef' ])->label(false);
echo $form->field($model, 'density' )->hiddenInput(['id' => 'density' ])->label(false);
echo $form->field($model, 'format' )->hiddenInput(['id' => 'format' ])->label(false);
echo $form->field($model, 'wareTypeRef' )->hiddenInput(['id' => 'wareTypeRef' ])->label(false);



//echo "<input type='submit'>";
ActiveForm::end(); 
?>





<?php
if(!empty($model->debug)){
echo "<pre>";    
print_r($model->debug);
echo "</pre>";
}
?>



<script>
//window.onload =acceptFilter(); 
/*$(function() {
   
});*/
</script>



<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'editTypeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>
    <iframe width='550px' height='620px' frameborder='no' id='frameEditTypeDialog'  src='index.php?r=store/ware-config-type&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>

    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'closeTypeDialog();',
                     'title'   => 'Обновить список типов',
                   ]);
    ?>
    </div>
<?php
Modal::end();
/***************************/
?>

<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'editGroupDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>
    <iframe width='550px' height='620px' frameborder='no' id='frameEditGroupDialog'  src='index.php?r=store/ware-config-group&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>
    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'closeGroupDialog();',
                     'title'   => 'Обновить список групп',
                   ]);
    ?>
    </div>
<?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением товара *****************/
Modal::begin([
    'id' =>'editProducerDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>
    <iframe width='550px' height='620px' frameborder='no' id='frameEditProducerDialog'  src='index.php?r=store/ware-config-producer&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>
    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'closeProducerDialog();',
                     'title'   => 'Обновить список производителей',
                   ]);
    ?>
    </div>

<?php
Modal::end();
/***************************/
?>
<?php
/********** Диалог с добавлением формата *****************/
Modal::begin([
    'id' =>'editFormatDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?>
    <iframe width='550px' height='620px' frameborder='no' id='frameEditFormatDialog'  src='index.php?r=store/ware-config-format&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>
    <div align='right'>
        <?php
    echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ok'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'btn-search',
                     'onclick' => 'closeFormatDialog();',
                     'title'   => 'Обновить список производителей',
                   ]);
    ?>
    </div>

<?php
Modal::end();
/***************************/
?>

