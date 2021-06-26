<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;



$this->title = 'Задание на отгрузку';
//$this->params['breadcrumbs'][] = $this->title;


$schetRecord = $model->getSchetRecord();
$phoneList=$model->getCompanyPhones();
$detailList= $model->getZakazDetailBySchet();
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$this->registerJsFile('@web/tcal.js');
$this->registerCSSFile('@web/tcal.css');


 $adressList=$orgModel->getCompanyAdress();         
 if (count($adressList) >0)
 {
    $strAdress  =  "Индекс:".Html::encode($adressList[0]["index"])." ";
    $strAdress .=  "Область:".Html::encode($adressList[0]["area"])." ";
    $strAdress .=  "Город:".Html::encode($adressList[0]["city"])." ";    
    $strAdress .=  "Адрес:".Html::encode($adressList[0]["adress"]);
    $model->adress = $strAdress;
  }

?>
<style>
 .part-header{
    padding: 10px;	 
	color: white;
	text-align: left;
	background-color: DarkGreen ;
	font-size: 14pt;
 }
 .item-header{
    padding: 10px;	 
	color: black;
	text-align: left;	
	font-size: 14pt;
 } 
 
.phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
	font-size: 10pt;    
}
.phones {
    color:#f70;
    cursor: help
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;
}
 
</style>




<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function openPhoneDialogData()
{
   $('#selectPhoneDialog').modal('show');     
}
function setSelectedPhone(id)
{
    var url = 'index.php?r=site/get-phone-detail&id='+id;
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#selectPhoneDialog').modal('hide');       
            console.log(res);
            document.getElementById('contactPhone').value = res['phone'];    
          //  document.location.reload(true); 
        },
        error: function(){
            $('html, body').css("cursor", "auto");
            $('#selectPhoneDialog').modal('hide');       
 
            alert('Error while retrieve data!');
        }
    });	
}

function openAdressDialogData()
{
   $('#selectAdressDialog').modal('show');     
}

function setSelectedAdress(id)
{
    var url = 'index.php?r=site/get-adress-detail&id='+id;
    $('html, body').css("cursor", "wait");
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
            $('html, body').css("cursor", "auto");
            $('#selectAdressDialog').modal('hide');       
            console.log(res);
            document.getElementById('adress').value = res['adress'];    
          //  document.location.reload(true); 
        },
        error: function(){
            $('html, body').css("cursor", "auto");
            $('#selectAdressDialog').modal('hide');       
 
            alert('Error while retrieve data!');
        }
    });	

}
var dest;
function openOrgDialog(d)
{
    dest = d;
     $('#orgListDialog').modal('show');     

}

function setOrg(id, title, phone)
{
     $('#orgListDialog').modal('hide');     
     document.getElementById(dest).value = title;    
     
}



function doCall()
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.getElementById('contactPhone').value ,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}

function setAdress(adress)
{	    
  document.getElementById('adress').value=adress;	
}

function openSchet()
 {
    openWin('market/market-schet&id=<?= $model->id ?>','schetWin');
 }
 
 function  selectNote()
 {
    $('#dstNoteDialog').modal('show');  
 }
 
 function  saveDstNote()
 {  
     
    document.getElementById('dataVal').value  = document.getElementById('dstNote').value;    
    document.getElementById('recordId').value = document.getElementById('dstRef').value;    
    document.getElementById('dataType').value = 'dstNote';    
    document.getElementById('dataId').value=<?= $orgRecord->id ?>;    
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=market/save-dst',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            document.getElementById('dstRef').value = res['recordId'];    
          //  document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
 } 
 
 function  setDstNote(id)
 {
     
   var url = 'index.php?r=store/get-dst-note&id='+id;
   console.log(url);
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        //data: data,
        success: function(res){     
            console.log(res);
            document.getElementById('dstRef').value = res['dstRef'];    
            document.getElementById('dstNote').value = res['dstNote'];
            $('#dstNoteDialog').modal('hide');              
          //  document.location.reload(true); 
        },
        error: function(){
            alert('Error while retrieve data!');
        }
    });	
     

 
 }
 
 function  cleanDstNote()
 {
     
   document.getElementById('dstRef').value = -1;    
   document.getElementById('dstNote').value = "";
 
 }
 
 function  tkNotImportant()
 {
  document.getElementById('transportName').value = 'Не важно';       
     
 }
 
</script>
 
 <?php $form = ActiveForm::begin(); ?>   

<?php 
// и опять табличный дизайн - возится с div`ами нет времени
?> 
 <table width='100%'>
 <tr>
   <td valign='top' width='270px;'>
         <div style='position:relative; margin-top:-10px;'>
        <h3>Заявка на ОТГРУЗКУ</h3>
        <div style='text-align:right; padding-right:20px; font-size:11px;' > от <?= Html::encode($curUser->userFIO) ?></p>   
        </div>
   </td> 
   <td>
        <table width='100%' border=0>
        <tr> <td> Клиент </td><td colspan='3'>
        <?php  
        if(!(empty($orgRecord->orgFullTitle)))$orgTitle=$orgRecord->orgFullTitle;
        else $orgTitle=$orgRecord->title;
            $action = "openWin('site/org-detail&orgId=".$orgRecord->id."','orgWin')";
        echo \yii\helpers\Html::tag( 'div', $orgTitle, 
                   [
                     'id'      => 'orgTitle', 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => "font-size:15px; font-weight:bold",
                   ]);
        ?>

        
        
        
        </td></tr>
        <tr> <td> Юр. адрес </td><td colspan='3'><?php 
        for ($i=0;$i<count($adressList); $i++) {
            if($adressList[$i]['isOfficial'] == 1) {echo $adressList[$i]['adress']; break;}
        }
        ?></td></tr>
        <tr> 
            <td rowspan=2> Контактное лицо </td><td  rowspan=2 style='width:400px;'>
            <?= $form->field($model, 'contactFIO') ->textArea(
                            [
                                'id'=>'contactFIO',                                 
                                'style' => 'font-size:12px; width:350px;padding:2px; '
                            ])->label(false)?> </td>
           <td > Телефон </td><td width='160px'> 
           <?= $form->field($model, 'contactPhone')->textInput(
                            [ 
                             'id'=>'contactPhone', 
                              'style' => 'font-size:12px; width:150px; padding:2px;', 
                            ])->label(false)?>                            
        </td>
       <td width='20px'> 
       <?php
                $action = "openPhoneDialogData()";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:0px;',
                     'title' => 'Выбрать телефон',
                   ]);
    
    
      ?>       
       
       </td>
        </tr>
        <tr>                           
             <td > E-mail </td><td > <?= $form->field($model, 'contactEmail')   
                 ->textInput(['id'=>'contactEmail', 'style' => 'font-size:12px; width:150px;padding:2px; ' ])->label(false)?></td>
        </tr>
        </table>
   </td>
 </tr>
 </table>
 
<hr style='border:1px solid;'>

  <table border='0' width='80%'>
  <tr><td> Счёт № <span class='clickable' onclick='openSchet()'><?= Html::encode($schetRecord->schetNum) ?>  от     <?= date ("d.m.Y", strtotime($schetRecord->schetDate) )  ?></td> <td>Сумма счета:  <?= Html::encode($schetRecord->schetSumm) ?> Оплачено:     <?= Html::encode($schetRecord->summOplata) ?></td></tr>
  </table>
<br>
<table  width='90%' class='table table-striped table-bordered'  style="padding:3px" >
<tr>
<th style="padding:3px">Наименование</th>
<th style="padding:3px">К-во </th>
<th style="padding:3px">ед.изм </hd>
</tr>
<?php
  for ($i=0; $i<count($detailList);$i++ )
  {
	//if ($detailList[$i]['isActive'] == 0){continue;}  
    echo "<tr>\n";
	echo "<td style=эpadding:3px'> ".$detailList[$i]['wareTitle']."</td>\n"; 
	echo "<td style='padding:3px'>".$detailList[$i]['wareCount']."</td>\n";
	echo "<td style='padding:3px'>".$detailList[$i]['wareEd']."</td>\n";
	echo "</tr>\n";
  }
?>
 </table>      
  <br> 
  <table width=100% border='0'>
  <tr>
    <td style="padding:5px">Дата отгрузки</td>
    <td style="padding:5px"><?php echo $form->field($model, 'supplyDate')
        ->widget(DatePicker::classname(), [
        'options' => [
            'id' => 'supplyDate' ,
            'style' => 'width:150px;'
            ],
          
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'dd.mm.yyyy'
        ]
    ])->label(false);?>
    </td>
    <td></td>
    <td width='50%' rowspan='2'>
      <?= $form->field($model, 'note')->textarea(['rows' => 2, 'cols' => 25])->label('Комментарий к заявке ')?> 
    </td>
    </tr>
  
   <tr>
    <td style="padding:5px">Грузополучатель</td>
    <td style="padding:5px"><?= $form->field($model, 'consignee')->textInput(['id' => 'consignee'])->label(false)?> 
     <td width='20px'> 
       <?php
                $action = "openOrgDialog('consignee')";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:0px;',
                     'title' => 'Выбрать грузополучателя',
                   ]);
    
    
      ?>       
       
       </td>
   </td>
   </tr>    
   <table>   
  
   <hr style='border:2px solid Black;'> 

  <?php
  // Секция параметров доставки
  
  ?>
  <table width=100% border='0'>
  <tr><td style="padding:5px" width='40%' valign='top'>
    <table width=100% border='0'>
      <tr>  
        <td style="padding:5px">Отгрузка</td>    
        <td style="padding:5px" colspan=2>
         <?php  echo $form->field($model, 'dstType')->dropdownList(
            ['Самовывоз', 'Доставка клиенту', 'Передать транспортной компании'],
            [
                'prompt'=>'Выберите тип доставки',
                'id' =>'dstType'            
            ]
            )->label(false);
          ?>       
        </td>    
       </tr>  
       <tr>   
        <td style="padding:5px">Адрес отгрузки</td>    
        <td style="padding:5px" colspan=2>
         <?php  
         
         echo $form->field($model, 'scladRef')->dropdownList(
            $orgModel->getScladList(),
            [
                'id' =>'scladRef'            
            ]
            )->label(false);
          ?>       
        </td>    
      </tr>
   </table>   
    </td>
    <td style="padding:5px" align='right'  valign='top'>
        <table width=100% border='0'><tr>
        <td><?= $form->field($model, 'dstNote')->textarea(['id' => 'dstNote','rows' => 3, 'cols' => 10])->label('Комментарий к доставке')?>
        <?= $form->field($model, 'dstRef')->hiddenInput(['id' => 'dstRef'])->label(false)?></td>
        <td style='padding:5px;' valign='top'> 
        <?php
        $action= "selectNote();";
        echo "<br>";
        echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-search',
                     'onclick' => $action,
                     'style'  => 'font-size:14px;',
                     'title'  => 'Открыть список вариантов',
                     'id' => 'openNote'
                   ]);
        echo "<br>";
    
        $action= "cleanDstNote();";
        echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-remove',
                     'onclick' => $action,
                     'style'  => 'font-size:14px;color:Crimson;',
                     'title'  => 'Убрать комментарий',
                     'id' => 'openNote'
                   ]);
        echo "<br>&nbsp;<br>";

        $action= "saveDstNote('dstNote');";
        echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'clickable glyphicon glyphicon-floppy-disk',
                     'onclick' => $action,
                     'style'  => 'font-size:14px;',
                     'title'  => 'Сохранить в списке вариантов',
                     'id' => 'dstNote'
                   ]);

        
       ?>     
        </td></tr></table>  
<!--  -->    
   </td></tr><table>   

  <table width=100% border='0'>
  <tr> 
    <td style="padding:5px" >  
      Схема доставки 
     <hr>      
    <table width='100%' border=0>
    <tr>
        <td width='250px;'>Название транспортной компании</td>
        <td><?= $form->field($model, 'transportName')->textInput(['id' => 'transportName'])->label(false)?></td>
       <td width='20px'> 
       <?php
                $action = "openOrgDialog('transportName')";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'style'  => 'margin-left:5px; margin-top:-5px;',
                     'title' => 'Выбрать ТК',
                   ]);
    
    
      ?>       
       
       </td>
<td>
       <?php
                $action = "tkNotImportant()";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-ban-circle clickable',
                     'onclick' => $action,
                     'style'  => 'margin-left:5px; margin-top:-5px; color:Brown;',
                     'title' => 'Не важно',
                   ]);
    
    
      ?>       
</td>        

    </tr>
    <tr>
        <td>Плательщик</td>
        <td><?= $form->field($model, 'payer')->textInput(['id' => 'payer'])->label(false)?> </td>


       <td width='20px'> 
       <?php
                $action = "openOrgDialog('payer')";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'style'  => 'margin-left:5px; margin-top:-5px;',
                     'title' => 'Выбрать Плательщика за доставку',
                   ]);
    
    
      ?>       
       
       </td>
<td></td>        
        
    </tr>
  
    <tr>
        <td>Передача товара</td>
        <td><?php  echo $form->field($model, 'isToTerminal')->dropdownList(
                ['0' => 'По адресу', '1' => 'До терминала',],
                ['prompt'=>'Выберите пункт передачи']
        )->label(false);
  ?></td>
    </tr>
    
    <tr>
        <td>Адрес доставки</td>
        <td >
        <?= $form->field($model, 'adress')->textInput(['id' =>'adress'])->label(false)?>
        </td>


       <td width='20px'> 
       <?php
                $action = "openAdressDialogData()";                   
                 echo \yii\helpers\Html::tag( 'div', '', 
                   [
                     'class'   => 'glyphicon glyphicon-search clickable',
                     'onclick' => $action,
                     'style'  => 'margin-left:5px; margin-top:-5px;',
                     'title' => 'Выбрать адрес доставки',
                   ]);
    
    
      ?>       
       
       </td>
<td></td>        
        
    </tr>
    </table>  
    </td>

    <td style="padding:5px" >      
      Информация для ТК
      <div style='border: 1px solid Black; width:450px; height: 300px;'>
      <table width='100%' border=0>  
       <tr> 
            <td  style='padding:5px' valign='top' width='150px'><b>Название ТК</b></td>
            <td style='padding:5px'><?= $model->transportName ?></td>
       </tr>       
       <tr> 
            <td  style='padding:5px' valign='top'><b>Доставка по адресу</b></td>
            <td  style='padding:5px'><?= $model->adress ?></td>
       </tr>       
       <tr> 
            <td  style='padding:5px' valign='top'><b>Контактное лицо </b></td>
            <td  style='padding:5px'><?= $model->contactFIO   ?></td>
       </tr>       

       <tr> 
            <td   style='padding:5px' valign='top'><b>Телефон </b></td>
            <td  style='padding:5px'><?= $model->contactPhone   ?></td>
       </tr>       

       <tr> 
            <td   style='padding:5px' valign='top'><b>Грузополучатель </b></td>
            <td  style='padding:5px'><?= $model->consignee   ?></td>
       </tr>       

       <tr> 
            <td  style='padding:5px' valign='top'><b>Плательщик </b></td>
            <td  style='padding:5px' ><?= $model->payer   ?></td>
       </tr>  
      
      </table>
     </div>  
    </td>

    
  </tr>
</table>
  
  
  
  <tr>
  <td style="padding:5px" colspan=2></td>
  </tr>
  
  <tr>
  </tr>
  </table>

<table><tr>
<td><?= Html::submitButton('Сформировать', ['class' => 'btn btn-primary']) ?></td><td>&nbsp;
<a class='btn btn-primary' href="#" onclick="javascript: window.close(); "> Закрыть</a>
<!--<input class="btn btn-primary"  style="width: 150px;" type="button" value="Вернутся" onclick="javascript:history.back();"/>-->
</td>
</tr></table>
<?php ActiveForm::end(); ?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/market/save-dst']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo "<div style='display:none'>";
echo $form->field($model, 'dataVal' )->textInput(['id' => 'dataVal' ])->label(false);
echo "</div>";
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
//echo "<div align='center'><input type='submit' ></div>";
ActiveForm::end(); 

/*echo "<pre>";
print_r($model->debug);
echo "</pre>";*/
?>




<?php
/********** Диалог с выбором комментария к доставке *****************/
Modal::begin([
    'id' =>'dstNoteDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameDstNoteDialog'  src='index.php?r=store/dst-note-select&noframe=1&refOrg=<?= $schetRecord->refOrg ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div><?php
Modal::end();
/***************************/
?>

<?php
Modal::begin([
    'id' =>'orgListDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],   
]);?><div style='width:600px'>
    <iframe id='orgListDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/site/select-org&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectPhoneDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],   
]);?><div style='width:600px'>
    <iframe id='selectPhoneDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/site/phone-select&noframe=1&refOrg=<?= $orgRecord->id ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
Modal::begin([
    'id' =>'selectAdressDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:600px'>
    <iframe id='selectAdressDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/site/adress-select&noframe=1&refOrg=<?= $orgRecord->id ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>
