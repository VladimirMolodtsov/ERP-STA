<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Лид. Квалификация товара';

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


<script type="text/javascript">

function  chngState(status)
{
   var zakazId =<?=$model->zakazId?>;
   
   document.getElementById('status').value=status;  
   

    document.getElementById('status_21').style.background='White';
    document.getElementById('status_21').style.color='Black';

    document.getElementById('status_10').style.background='White';
    document.getElementById('status_10').style.color='Black';
    
    document.getElementById('status_11').style.background='White';
    document.getElementById('status_11').style.color='Black';
    
    document.getElementById('status_12').style.background='White';
    document.getElementById('status_12').style.color='Black';

    
   switch (status){
   case 10: 
    document.getElementById('status_10').style.background='#286090';
    document.getElementById('status_10').style.color='White';
   break;
   
   case 11: 
    document.getElementById('status_11').style.background='#286090';
    document.getElementById('status_11').style.color='White';
   break;

   case 12: 
    showZakazList(<?= $model->contactId ?>);   
    document.getElementById('status_12').style.background='DarkGreen';
    document.getElementById('status_12').style.color='White';
   break;
   
   case 21: 
    document.getElementById('status_21').style.background='Brown';
    document.getElementById('status_21').style.color='White';
   break;
   
   
   }
   
}

function openProcessModal(refContact)
{
    $('#qualifyForm').modal('show');   
}

function openProcess(refContact)
{    
  var url = "index.php?r=site/lead-qualify&noframe=1&refContact="+refContact;
  wid=window.open(url, 'detailWin','toolbar=no,scrollbars=yes,resizable=yes,top=30,left=550,width=570,height=620'); 
  window.wid.focus();  
}

function doLeadCall(phone)
{       
  window.open("<?php echo $curUser->phoneLink; ?>"+phone,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');      
}


function regNewDoc(){ 
  url = 'index.php?r=bank/operator/reg-doc&noframe=1&id=0';
  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  window.wreg.focus();
}

function openScanWindow(){
Uri='https://drive.google.com/drive/folders/1vYt7wiJn_uO3wph0A27uoZ6HGBB0hRxE?usp=sharing';
  wid=window.open(Uri, 'scanWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();

}

// Принудительно скроем
function closeOrgList(orgId, title, phone)
	{ 
	
	if (title == "") title = "Создать автоматически";
	$('#orgId').val(orgId);	        
	
    $('#orgListForm').modal('hide');
     
    var url = 'index.php?r=site/get-lead-org-info&orgRef='+orgId;
    console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   showOrgInfo(res);        
 },
 error: function(){
   //console.log(res);
  alert('Error while retrive contragent info!');
 }
 });	
	
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
  alert('Error while add ware!');
 }
 });	



}


function showOrgInfo(res)
{
  console.log(res);    
  
  document.getElementById('orgTitle').innerText=res.orgTitle;  
  document.getElementById('orgINN').innerText=res.orgINN;
  document.getElementById('orgKPP').innerText=res.orgKPP;
  document.getElementById('orgOKPO').innerText=res.orgOKPO;
  document.getElementById('orgOGRN').innerText=res.orgOGRN;
  document.getElementById('orgAdress').innerText=res.orgAdress;
  document.getElementById('orgContact').innerText=res.orgContact;
  document.getElementById('orgOKTMO').innerText=res.orgOKTMO;
  
  var x =document.getElementById('orgPhones').innerHTML=res.orgPhones;
  var y = document.getElementById('orgEmails').innerHTML=res.orgEmails;
}



</script>

  
  <div class='row'>
	<div class="col-md-4">		
          <h3><?= Html::encode($this->title) ?></h3>
	</div>   
  
	<div class="col-md-8" style='text-align:right;'>		
     <div class='spacer'></div>
         <?php
                $action = "openSmallWin('store/ware-grp-sclad', 'wareGrpWin')";                   
                 echo \yii\helpers\Html::tag( 'div', 'Остатки', 
                   [
                     'class'   => 'btn btn-primary',
                     'onclick' => $action,
                     'style'  => '',
                     'title'  => 'Остатки на складе'
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
        <?php
        
        if ($model->status == 21) $style=" background-color:Brown; color:White";
                           else  $style="";                                           
        echo \yii\helpers\Html::tag( 'div', "Игнор.", 
        [
          'class'   => 'btn btn-default',
          'id'      => 'status_21',
          'onclick' => 'chngState(21);',
          'title'   => 'Игнорировать',
          'style'   => 'font-size:12px;  width:100px;'.$style
        ]); 
        echo "&nbsp;";
        
        if ($model->status == 11) $style=" background-color:#286090; color:White";
                           else  $style="";
        echo \yii\helpers\Html::tag( 'div', "Отложить", 
        [
          'class'   => 'btn btn-default',
          'id'      => 'status_11',
          'onclick' => 'chngState(11);',
          'title'   => 'Отложить',
          'style'   => 'font-size:12px;  width:100px;'.$style
        ]); 
        echo "&nbsp;";

        if ($model->status == 12) $style=" background-color:DarkGreen; color:White";
                           else  $style="";
        
        echo \yii\helpers\Html::tag( 'div', "В сделке", 
        [
          'class'   => 'btn btn-default',
          'id'      => 'status_12',
          'onclick' => 'chngState(12);',
          'title'   => 'Отложить',
          'style'   => 'font-size:12px;  width:100px;'.$style
        ]); 
        echo "&nbsp;";
                
        if ($model->status >= 10 && $model->status < 20 && $model->status != 11 &&  $model->status != 12) $style=" background-color:#286090; color:White";
                           else  $style="";
        
        echo \yii\helpers\Html::tag( 'div', "В работе", 
        [
          'class'   => 'btn btn-default',
          'id'      => 'status_10',
          'onclick' => 'chngState(10);',
          'title'   => 'В работе',
          'style'   => 'font-size:12px;  width:100px;'.$style
        ]);         
        echo "&nbsp;";
        
        ?>       
        
        
        
	</div>   
 </div>



<?php $form = ActiveForm::begin(['id' => 'mainForm',]); ?>  
<div class='row'>

  <div class="col-md-4">
		<?= $form->field($model, 'note')->textarea(['id' => 'note', 'style' => 'height:450px;font-size:12px;'])->label('Комментарий')?>		
		

      <div>    
  <b>Документы:</b>  
  
    <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-folder-open'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'openScanWindow();',
          'style'   => 'font-size:12px; height:22px; width:22px; padding:4px; display:inline'
   ]); ?>  

  
    <?= \yii\helpers\Html::tag( 'div', "<span class='glyphicon  glyphicon-list-alt'></span>", 
    [
          'class'   => 'clickable',
          'id'      => 'scan',
          'onclick' => 'regNewDoc();',
          'style'   => 'font-size:12px; height:22px; width:22px; padding:4px; display:inline'
   ]); ?>  
   
   
  
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
  <div class="col-md-3">	 
<?php    $orgInfo = $model->getOrgInfoArray(); ?>
<div style = 'height:450px;'>
     <table border='0' width='100%'>
     <tr> 
     <td colspan='2'>
     <?php
      $val = Html::encode($orgInfo['orgTitle']);
      $action = "openWin('site/org-detail&orgId=".$orgInfo['orgRef']."','orgWin');";
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'class'   => 'clickable',
          'id'      => 'orgTitle',
          'onclick' => $action,          
       ]); 
     
     ?>
     
     </td> 
          <td width='25px'>
          <div class='clickable' style='padding:2px; font-size:14px;' onclick='javascript:showOrgList();'><span class='glyphicon glyphicon-search'></span></div>
</td>  
     </tr>
  
     <tr> 
     <td><b>ИНН</b></td>
     <td>
     <?php
      $val = Html::encode($orgInfo['orgINN']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgINN',
       ]); 
     
     ?>
    </td>
     </tr>

     <tr> 
     <td><b>КПП</b></td>
     <td>
      <?php
      $val = Html::encode($orgInfo['orgKPP']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgKPP',
       ]); 
     
     ?>
     </td>
     </tr>

     <tr> 
     <td><b>ОКПО</b></td>
     <td>
     <?php
      $val = Html::encode($orgInfo['orgOKPO']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgOKPO',
       ]); 
     
     ?>
     </td>
     </tr>
     
     <tr> 
     <td><b>ОКТМО</b></td>
     <td><?php
      $val = Html::encode($orgInfo['orgOKTMO']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgOKTMO',
       ]); 
     
     ?>
     </td>
     </tr>

     <tr> 
     <td><b>ОГРН</b></td>
     <td><?php
      $val = Html::encode($orgInfo['orgOGRN']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgOGRN',
       ]); 
     
     ?>
    </td>
     </tr>

     <tr> 
     <td colspan='2'><?php
      $val = Html::encode($orgInfo['orgAdress']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgAdress',
       ]); 
     
     ?>
    </td>     
     </tr>

     
     <tr> 
     
     <td colspan='2'>
     <?php
      $val = $orgInfo['orgPhones'];      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgPhones',
       ]); 
     
        ?>
     </td>               
     </tr>

     <tr> 
         <td colspan='2'>
         <?php
        $val =$orgInfo['orgEmails'];      
        echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgEmails',
        ]); 
     
        ?>
     </td>               
     </tr>

      <tr> 
     <td colspan='2'>
     <?php
      $val = Html::encode($orgInfo['orgContact']);      
      echo \yii\helpers\Html::tag( 'div', $val, 
        [
          'id'      => 'orgContact',
       ]); 
     
     ?>
     </td>     
     </tr>
    </table> 
</div >    
    <HR>
    

  <div >
  <?php     
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
      <td width='25px'><div class='clickable' style='padding:2px; font-size:14px;' onclick='javascript:showZakazList(".$model->contactId.");'><span class='glyphicon glyphicon-search'></span></div></td>
      <td width='25px'><div class='clickable' style='color:Crimson; padding:2px; font-size:14px;' onclick='javascript:clearZakazList();'><span class='glyphicon glyphicon-remove'></span></div></td>
      </tr></table>";        
     echo " <HR>";
  ?>    
  </div>     
    
  </div> 

  <div class="col-md-5">
  <div style='width:100%;font-size:12px; overflow:auto' onclick="openProcess(<?=$model->contactId?>)">
  <?=$detailText?>
  </div>
<div class='spacer'></div>
  
 	<div  style='text-align:right'>		
        <input class="btn btn-primary"  style="width: 150px;" type="button" value="Сохранить" onclick="javascript:saveMe();"/>
	</div>   

 </div>   

 </div>

 
     <?= $form->field($model, 'status')->hiddenInput(['id' => 'status', ])->label(false)?>	      
     <?= $form->field($model, 'zakazId')->hiddenInput(['id' => 'zakazId', ])->label(false)?>	      
     <?= $form->field($model, 'contactId')->hiddenInput(['id' => 'contactId',])->label(false)?>	
     <?= $form->field($model, 'orgId')->hiddenInput(['id' => 'orgId',])->label(false)?>	
     <?= $form->field($model, 'orgTitle')->hiddenInput(['id' => 'orgTitle',])->label(false)?>	
     <?= $form->field($model, 'atsRef')->hiddenInput(['id' => 'atsRef', ])->label(false)?>		     
 
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



<?php
Modal::begin([
    'id' =>'qualifyForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='qualifyFrame' width='570px' height='900px' frameborder='no' src='index.php?r=site/lead-qualify&noframe=1&refContact=<?= $model->contactId ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

  
  
  <div id="overlay"></div>  
<!------------------------>
  

<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/site/save-lead-data']);
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

$this->registerJs($js);
?>  


<?php if(!empty($model->debug))
echo "<pre>";
print_r($model->debug);
echo "</pre>";
 ?>




 
 
