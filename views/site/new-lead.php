<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Регистрация лида';

if (empty($model->nextContactDate )) $model->nextContactDate = date("d.m.Y", time());


$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/site/lead.js');

$this->registerCssFile('@web/phone.css');


$model->loadTextModule();
?>

<style>
.btn-local {
    padding:4px;    
    font-size:12px;
}
.search {
   position: relative;  
   display: inline; 
   floating:left; 
   overflow: hidden;
   margin-top:5px;
}
</style>

<!-- Obsoleted -->
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


<script type="text/javascript">
// Принудительно скроем
function closeOrgList(orgId, title, phone)
	{ 
	
	if (title == "") title = "Создать автоматически";
	
        $('#contactOrgTitle').val(title);	        
        $('#orgId').val(orgId);	        
    
        if($('#contactPhone').val() == "") {$('#contactPhone').val(phone);}
        
        $('#orgListForm').modal('hide');
        
}

function  chngState(status)
{
   var zakazId =<?=$model->zakazId?>;
   
   document.getElementById('status').value=status;  
   
}

function  addWareFromPrice(id,row)
{

var url = 'index.php?r=store/get-ware-price&id='+id;
 console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   console.log(res);
   console.log(row);
   var note = $('#note').val();      
   note = note + " "+res.wareTitle+" "+res.wareEd+" "+res[row];
   $('#note').val(note);      
   console.log(note);
 },
 error: function(){
   //console.log(res);   
  alert('Error while add document!');
 }
 });	



}

</script>
  
  <div class='row'>
	<div class="col-md-6">		
          <h3><?= Html::encode($this->title) ?></h3>
	</div>   
  
	<div class="col-md-5" style='text-align:right;'>			
	<br>
    <?php
                $action = "openSmallWin('store/ware-grp-sclad', 'wareGrpWin')";                   
                 echo \yii\helpers\Html::tag( 'div', 'Остатки на складе', 
                   [
                     'class'   => 'btn btn-primary',
                     'onclick' => $action,
                     'style'  => '',
                   ]);
    
    
    ?>
	  <?php
        $action = "openWin('/store/ware-price','priceWin');";
        echo \yii\helpers\Html::tag( 'div', "Прайс", 
        [
          'class'   => 'btn btn-primary',
          'id'      => 'status_21',
          'onclick' => $action,
          'title'   => 'Прайс',
          'style'   => 'font-size:12px;  width:100px;'
        ]); 
        echo "&nbsp;";
        ?>         	
     </div>   
    <div class="col-md-1" style='text-align:right;'>		        
    <br>
        <?php             
        echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-remove-sign'></span>", 
                   [
                     'class'   => 'clickable',                                       
                     'onclick' => "javascript:window.opener.location.reload(false); window.close();",
                     'style' => 'color:Crimson;font-size:20px;'
                   ]);  
       ?>               
	</div>   
 </div>






<?php $form = ActiveForm::begin(['id' => 'mainForm',]); 

/*'action' => 'index.php?r=/site/process-new-lead'
echo "<input type='submit'>";*/
?>  
<div class='row'>

  <div class="col-md-4">
		<?= $form->field($model, 'moduleText')->textarea([
		'id' => 'moduleText', 
		'rows' => 25, 
		'cols' => 25, 
		'onchange' => 'saveModuleText()',
		'style' => 'font-size:12px;'
		])->label('Вопросы-подсказки')?>		
  </div>   

  <div class="col-md-4">
		<?= $form->field($model, 'note')->textarea(['id' => 'note','rows' => 25, 'cols' => 25, 'style' => 'font-size:12px;'])->label('Комментарий')?>		
  </div>   
  <div class="col-md-1">   
   <?= \yii\helpers\Html::tag( 'div', ">>", 
    [
          'class'   => 'btn btn-primary',
          'id'      => 'scan',
          'onclick' => 'processForm();',
           'style' => 'position:relative; top:100px;width: 40px;',
   ]); ?>  
  </div>   
  <div class="col-md-3">	 
	 <?= $form->field($model, 'contactPhone')->textInput(['id' => 'contactPhone',])->label('Телефон ')?>            
     <?= $form->field($model, 'contactEmail')->textInput(['id' => 'contactEmail',])->label('E-Mail')?>	
     <?= $form->field($model, 'contactFIO')->textInput(['id' => 'contactFIO',])->label('Контактное лицо')?>	
     
     
     <table border='0' width='100%'><tr>
     <td><?= $form->field($model, 'contactOrgTitle')->textInput(['id' => 'contactOrgTitle', ])->label('Организация/Клиент')?> </td>
     <td width='25px'><input class="btn btn-primary btn-local"  style="width: 25px; margin-top:10px" type="button" value="..." onclick="javascript:showOrgList();"/></td>              
     </tr></table>
    <HR>
<?php     
     if ($model->orgId != 0 )
     {
     echo "
     <table border='0' width='100%'><tr>
     <td> Сделка:";
     echo  \yii\helpers\Html::tag( 'div', $model->zakazInfo, 
     [
          'class'   => 'clickable',
          'id'      => 'zakzazInfo',
          'onclick' => 'openSdelka();',          
     ]);   
     echo "</td>     
      <td width='25px'><input class='btn btn-primary btn-local'  style='width: 25px; margin-top:10px' type='button' value='...' onclick='javascript:showZakazList();'/></td></tr></table>";        
     echo " <HR>";
     }     
?>    
     <?= $form->field($model, 'zakazId')->hiddenInput(['id' => 'zakazId', ])->label(false)?>
     <?= $form->field($model, 'atsRef')->hiddenInput(['id' => 'atsRef', ])->label(false)?>		     
     <?= $form->field($model, 'status')->hiddenInput(['id' => 'status', ])->label(false)?>	        
     <?= $form->field($model, 'contactId')->hiddenInput(['id' => 'contactId',])->label(false)?>	
     <?= $form->field($model, 'orgId')->hiddenInput(['id' => 'orgId',])->label(false)?>	
     <?= $form->field($model, 'orgTitle')->hiddenInput(['id' => 'orgTitle',])->label(false)?>	
	 
    
  </div> 
</div>
 <?php if ($model->orgId != 0) {  ?>
<div class='row'> 
  <div class="col-md-4">		    
    <?= $form->field($model, 'nextContactDate')->textInput(['class' => 'tcal',])->label('Дата следующего контакта ')?>
  </div>   

  <div class="col-md-4">    
  <b>Документы:</b>  
     <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus-sign'></span>", 
    [
          'class'   => 'btn btn-primary',
          'id'      => 'scan',
          'onclick' => 'openDocList();',
          'style'   => 'float:right; font-size:10px; height:22px; width:22px; padding:4px;'
   ]); ?>  
      
   <?= \yii\helpers\Html::tag( 'div', $model->docList, 
    [
          'id'      => 'docList',
          'style'   => 'width:275px;'
           
    ]); ?>  

   
  </div>     

  

</div>
<?php }?>
 

<div class='row'>
	<div class="col-md-12" style='text-align:right'>		
        <input class="btn btn-primary"  style="width: 150px;" type="button" value="Сохранить" onclick="javascript:saveMe();"/>
	</div>   
 </div>
  
<?php ActiveForm::end(); ?>
   
 
<!------------------------>

  
<?php
Modal::begin([
    'id' =>'orgListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='orgListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=site/lead-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

  
<?php
Modal::begin([
    'id' =>'docListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='docListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=site/lead-doc-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


  
<?php
Modal::begin([
    'id' =>'zakazListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='zakazFormFrame' width='570px' height='620px' frameborder='no' src='index.php?r=site/lead-zakaz-list&noframe=1&orgId=<?= $model->orgId?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

  
  
  
  <div id="overlay"></div>  
<!------------------------>
  

<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/site/process-new-lead']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->textarea(['id' => 'dataVal', 'style' => 'display:none' ])->label(false);

//echo "<input type='submit'>";
ActiveForm::end(); 
?>  
  

  
<?php
$js = <<<JS
$( '#contactOrgTitle' ).prop( "disabled", true ); 
JS;

 /*if ($openOrgCard < 0)
 $js .= "openWin('site/org-card&orgId=".$model['orgId']."&leadId=".$model['contactId']."', 'orgwin')";
*/
$this->registerJs($js);
?>  


<?php if(!empty($model->debug))
echo "<pre>";
print_r($model->debug);
echo "</pre>";
 ?>
