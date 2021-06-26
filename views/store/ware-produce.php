<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Выбор номенклатуры';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>
  
<script>

function selectWare(wareRef,edRef ) {    
	window.parent.addSelectedWare(wareRef,edRef);
}


function chngProd( ) {    
var saleType = $('input[id="saleType"]:checked').val();
document.getElementById('wareFormat').value =0;
document.getElementById('wareLength').value ='';
document.getElementById('wareWidth').value ='';

  switch (saleType)
  {
   case '1':
   document.getElementById('wareLengthCont').style.display = 'none';
   break;
   case '2':
   document.getElementById('wareLengthCont').style.display = 'block';   
   break;
  }

acceptFilter( );
}

function cngFormat () {
document.getElementById('wareLength').value ='';
document.getElementById('wareWidth').value ='';
acceptFilter( );    
}    


function acceptFilter( ) {    
var wareType = document.getElementById('wareTypeShow').value;
var wareGrp = document.getElementById('wareGrpShow').value;
var wareProd = document.getElementById('wareProdShow').value;
var format = document.getElementById('wareFormat').value;
var density = document.getElementById('wareDensityShow').value;
var warePack= document.getElementById('warePackShow').value;

var wareWidth = document.getElementById('wareWidth').value;
var wareLength= document.getElementById('wareLength').value;

var saleType = $('input[id="saleType"]:checked').val();

var wareMark= document.getElementById('wareMark').value;
var wareSort= document.getElementById('wareSort').value;

var url = 'index.php?r=store/ware-select&noframe=1&wareType='+wareType+'&wareGrp='+wareGrp+'&wareProd='+wareProd;
    url = url+'&format='+format+'&density='+density+'&warePack='+warePack+'&wareSort='+wareSort+'&wareMark='+wareMark;
    url = url+'&saleType='+saleType+'&wareWidth='+wareWidth+'&wareLength='+wareLength;
document.location.href=url;
}

function createWare(){
    document.getElementById('dataVal').value=document.getElementById('wareTitle').value;    
    document.getElementById('dataType').value='createWare';
    
    document.getElementById('wareTitle').value=document.getElementById('wareTitleShow').value;    
    document.getElementById('wareTypeRef').value=document.getElementById('wareTypeShow').value;    
    document.getElementById('grpRef').value=document.getElementById('wareGrpShow').value;    
    document.getElementById('producerRef').value=document.getElementById('wareProdShow').value;    
    document.getElementById('density').value=document.getElementById('wareDensityShow').value;    
    document.getElementById('format').value=document.getElementById('wareFormatShow').value;    
    document.getElementById('warePack').value=document.getElementById('warePackShow').value;    

    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-ware-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}

function switchSaleType()
{
  
  var saleType = $('input[id="saleType"]:checked').val();
  
  switch (saleType)
  {
   case '1':   
   document.getElementById('produceBlock').style.display = 'none';
   break;
   case '2':
   document.getElementById('produceBlock').style.display = 'block';   
   break;
  }
}
</script>


  
<div id='produceData' align='center'>      
<table width='600px' border='1' ><tr>
<td width='50%' align='right' valign='top'>
<table border='0' class='table ' style='width:300px;font-size:12px;'>    
<tr>
    <td colspan='2' align='center'  style='font-size:120%;'> 
    <?php
        echo $form->field($model, 'produceType')->radio(['label' => 'Порезка на листы', 'value' => 1, 'uncheck' => null]);
     ?>    
    </td>
</tr>    
<tr>
    <td align='right'>Формат ширина (мм)</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  
  
<tr>
    <td align='right'>Длинна (м)</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  
  
<tr>
    <td align='right'>Листов в пачке</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  

<tr>
    <td align='right' ><b>Отгрузка</b> </td>
    <td> 
    <?php
        echo $form->field($model, 'outStatus')->radio(['label' => 'в пачках', 'value' => 1, 'uncheck' => null]);
        echo $form->field($model, 'outStatus')->radio(['label' => 'в листах', 'value' => 2, 'uncheck' => null]);
        echo $form->field($model, 'outStatus')->radio(['label' => 'в кг', 'value' => 3, 'uncheck' => null]);
     ?>    
    </td>
</tr>    
  
    
</table> 

</td>

<td width='50%' align='left'>
<table border='0' class='table ' style='width:400px;font-size:12px;'>    
<tr>
    <td colspan='2' align='center'  style='font-size:120%;'> 
    <?php
        echo $form->field($model, 'produceType')->radio(['label' => 'Перемотка в ролики', 'value' => 2 , 'uncheck' => null]);
     ?>    
    </td>
</tr>    
<tr>
    <td align='right'> Формат ширина (мм)</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  
  
<tr>
    <td align='right'>Намотка длинна (мм)</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  

 
 
   
<tr>
    <td align='right'>Ролик</td>
    <td>
    <?php

        echo $form->field($model, 'rolType')->radio(['label' => 'вн.диаметр 20 мм без втулки', 'value' => 1, 'uncheck' => null]);
        echo $form->field($model, 'rolType')->radio(['label' => 'вн.диаметр 50 мм со втулкой', 'value' => 2, 'uncheck' => null]);
        echo $form->field($model, 'rolType')->radio(['label' => 'вн.диаметр 76 мм со втулкой', 'value' => 3, 'uncheck' => null]);
           
    ?>
    </td>
 </tr>  

<tr>
    <td align='right' ><b>Отгрузка</b> </td>
    <td> 
    <?php        
        echo $form->field($model, 'outStatus')->radio(['label' => 'в роликах', 'value' => 4, 'uncheck' => null]);
        echo $form->field($model, 'outStatus')->radio(['label' => 'в кг', 'value' => 3, 'uncheck' => null]);
     ?>    
    </td>
        
</tr>    
  

<tr>
    <td align='right'>Вес рулона</td>
    <td>
    <?php
     echo  Html::textInput( 
       'format', 
       $model->warePack,                                
      [
         'class' => 'form-control',
         'style' => 'padding:1px;', 
         'id' => 'warePackShow', 
         //'placeholder' => 'упаковка',
      ]);       
    ?>
    </td>
 </tr>  
    
</table> 

</td>
</tr>
</table> 
<div class='spacer'></div>

<table width='600px' border='0' >
<tr>
    <td >Вид сырья</td>
    <td>
   <?php
    echo  $form->field($model, 'wareGrp')
    ->dropDownList($wareGroups,
    [
        'id' => 'wareGrp', 
    ])->label(false);
    ?>
    </td>
 </tr>  
 
<tr>
    <td >Название сырья</td>
    <td>
   <?php
    echo  $form->field($model, 'wareGrp')
    ->dropDownList($wareGroups,
    [
        'id' => 'wareGrp', 
    ])->label(false);
    ?>
    </td>
 </tr>  

 
<tr>
    <td >Дополнение/комментарии</td>
    <td>
   <?php
    echo  $form->field($model, 'wareProductionNote')
    ->label(false);
    ?>
    </td>
 </tr>    
</table>

</div>    







    
<?php    
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
echo $form->field($model, 'warePack' )->hiddenInput(['id' => 'warePack' ])->label(false);
echo $form->field($model, 'wareTypeRef' )->hiddenInput(['id' => 'wareTypeRef' ])->label(false);



echo "<input type='submit'>";
ActiveForm::end(); 
?>


<?php
if(!empty($model->debug)){
echo "<pre>";    
print_r($model->debug);
echo "</pre>";
}
?>

