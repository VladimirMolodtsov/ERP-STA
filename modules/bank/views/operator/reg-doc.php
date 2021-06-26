<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Pjax;

//$this->title = 'Регистрация документа';
//$this->params['breadcrumbs'][] = $this->title;


/*
`docTitle`  'Название',  
'docType`  'тип документа',
*/

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.form-small{
 font-size: 12px;
 padding:2px; 
 height:25px;
}    
.btn-small {
 width:20px;   
 height:20px; 
 padding:0px; 
 margin-top:-10px;
}

.lbl {
font-weight:bold;
padding-bottom:15px;    
}

.container {
width:500px;    
margin:0px    
}

.showval{
font-weight:normal;
font-size: 12px;
width: 100px;
}
</style>
<script>
function duplicate()
{
    document.location.href='index.php?r=/bank/operator/duplicate-doc&srcRef=<?=$model->id?>';
}

function openDoc()
{  
 var url = document.getElementById('docURI').value;   
 var docURIType= document.getElementById('docURIType').value;   
 if (docURIType == 0)  wid=window.open(url, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
 if (docURIType == 1) {
        
        var request = 'index.php?r=/yandex/api/get-uri&path='+url;
        console.log(request);
        $.ajax({
            url: request,
            type: 'GET',
            dataType: 'json',
           // data: data,
            success: function(res){     
                console.log(res);
                if(res.res==true){
                wid=window.open(res.href, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
                }else {
                alert(res.error);
                }
                
            },
            error: function(){
                alert('Error while saving data!');
            }
        });	    

       wid=window.open(url, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  }
 window.wid.focus();
}


function selectClassify()
{
    $('#selectClassifyDialog').modal('show');  
}

function startYandexUpload()
{
    $('#uploadYandexDialog').modal('show');  
}



function switchClass(classRef,grpRef)
{
$('#selectClassifyDialog').modal('hide');  

var Url = 'index.php?r=/bank/operator/get-classify-param&classRef='+classRef+'&grpRef='+grpRef
console.log(Url);
    var data = $('#saveDataForm').serialize();
        $.ajax({
            url: Url,
            type: 'GET',
            dataType: 'json',
            data: data,
            success: function(res){     
                showClassify(res);
            },
            error: function(){
                alert('Error while saving data!');
            }
        });	    

}

function showClassify(res)
{
    document.getElementById('docClassifyRef').value=res.classRef;
    document.getElementById('docTypeRef').value=res.grpRef;
    document.getElementById('docClassTitle').innerText=res.docClassTitle;
    document.getElementById('docTypeTitle').innerText=res.docTypeTitle;    
    console.log(res);  
    //document.location.reload(true);     
}


function selectDeal()
{
    var orgRef =  document.getElementById('refOrg').value;
    if (orgRef == 0) {alert("Организация не выбрана!"); return;}
    
    var docArticleRef=  document.getElementById('docArticleRef').value;
    
    var url =  "index.php?r=/site/org-deal-select&noframe=1&orgId="+orgRef+"&selectedDeal="+docArticleRef;  
   // console.log(url);
    document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDealFrame').src= url;
    $('#selectOrgDeal').modal('show');  
}

function closeOrgDeal(selectedDeal)
{       
    $('#selectOrgDeal').modal('hide');      
    document.getElementById('docArticleRef').value=selectedDeal;

    console.log('index.php?r=/bank/operator/get-deal-param&id='+selectedDeal);  
    var data = $('#saveDataForm').serialize();
        $.ajax({
            url: 'index.php?r=/bank/operator/get-deal-param&id='+selectedDeal,
            type: 'GET',
            dataType: 'json',
            data: data,
            success: function(res){     
                showDeal(res);
            },
            error: function(){
                alert('Error while saving data!');
            }
        });	    
}

function showDeal(res)
{

    document.getElementById('orgType').innerText=res.orgType;
    document.getElementById('orgDeal').innerText=res.orgDeal;

    console.log(res);  
    //document.location.reload(true);     
}


function selectOrg()
{
    search="";
    orgINN = document.getElementById('orgINN').value;
    if (orgINN != '')search="&searchINN="+orgINN;
    
    orgTitle = document.getElementById('orgTitle').value;
    if (orgTitle != '')search="&searchTitle="+orgTitle;
    
    //document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDialogFrame').src="index.php?r=/bank/operator/reg-org-list&noframe=1"+search;  
    $('#selectOrgDialog').modal('show');       
}


function closeOrgList(id, title)
{       

    console.log(id); 
    document.getElementById('dataType').value='findOrg';
    document.getElementById('dataVal').value=id;
    if (id> 0)  {  findOrg(setOrg);}
    else {
    document.getElementById('refOrg').value  = -2;
        if (title == '') title ='Создать автоматически';
        document.getElementById('orgTitle').value = title;     
    }
    
    $('#selectOrgDialog').modal('hide');          
}

function findOrg(showfunc)
{

    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/get-selected-org',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showfunc(res);           
        },
        error: function(){
            alert('Error while searching data!');
        }
    });	
}
function setOrg(res)
{
    console.log(res); 
    if (res['result'] == true)
    {
    document.getElementById('refOrg').value  = res['refOrg'];
    document.getElementById('orgINN').value  = res['orgINN'];
    document.getElementById('orgKPP').value  = res['orgKPP'];
    document.getElementById('orgTitle').value = res['orgTitle']; 
    
    document.getElementById('docNDS').value = res['defNDS']; 
   // document.getElementById('orgTitle').value = res['orgTitle']; 
    
    document.getElementById('dataId').value=document.getElementById('refOrg').value;
    document.getElementById('dataType').value='findByOrg';
    document.getElementById('docArticleRef').value=res['refDeal'];
    showDeal(res);
    checkRS();
    }
}
function checkRS()
{
  
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/get-account-info',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showAccountInfo(res);           
        },
        error: function(){
            //console.log(res); 
            alert('Error while searching data!');
        }
    });	
        
}

function showAccountInfo(res)
{
    console.log(res); 
    if (res['result'] == true)
    {
    document.getElementById('refAccount').value  = res['refAccount'];
    document.getElementById('orgBIK').value  = res['orgBIK'];
    document.getElementById('orgRS').value  = res['orgRS'];
    document.getElementById('orgBank').value = res['orgBank']; 
    document.getElementById('orgKS').value = res['orgKS']; 
    }
}


function selectAccount()
{
    search="";
    refOrg = document.getElementById('refOrg').value;
    
    search="&refOrg="+refOrg;
    document.getElementById('selectAccountDialogFrame').src="index.php?r=/bank/operator/reg-org-acc&noframe=1"+search;  
    $('#selectAccountDialog').modal('show');       
}

function closeAccList(id)
{       
    document.getElementById('dataType').value='findById';
    document.getElementById('recordId').value=id;    
    checkRS();    
    $('#selectAccountDialog').modal('hide');          
}

function setOrigDate(){    
 document.getElementById('docOrigDate').value=document.getElementById('origDate').value;
}

function submitForm(){
if (document.getElementById('isOplate').checked)
{
  if (document.getElementById('docSum').value == ''){
    alert('Не заполнено поле Сумма для документа на оплату!'); return;}  
  if (document.getElementById('docNDS').value == ''){
    alert('Не заполнено поле НДС для документа на оплату!'); return;}
}
document.getElementById('mainForm').submit();
}
</script>

<?php $form = ActiveForm::begin(
[ 
  'options' =>[      
    'style' => 'font-size: 12px;',
    'id'    => 'mainForm'
  ],
   
]); ?>
<br> 
<table width='450px' border='0' style='padding:0px;' >

    <tr>
        <td  class='lbl'>Номер</td>   
        <td><?= $form->field($model, 'docIntNum')->textInput([
        'id' => 'docIntNum',  'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>
        <td  class='lbl'>Источник</td>   
        <td><?= $form->field($model, 'docSRC')->dropDownList([        
            '1' => 'Бумаги',
            '2' => 'E-mail',
            '3' => 'FAX',
            ], ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:100px'])->label(false)?></td>
    </tr>   
    

    <tr>
        <td class='lbl'>Ссылка</td>   
        <td colspan='3'><table border=0><tr><td><?= $form->field($model, 'docURI')->textInput([
        'id' => 'docURI', 'class' => 'form-control form-small', 'style' => 'width:300px'  ])->label(false)?></td>
        <!--<td>&nbsp;<div onclick='startYandexUpload()' class='btn btn-default' style='margin-top:-17px;padding:2px;color:Brown'>Я</div></td>-->
        </tr></table></td>        
        <td><div onclick='openDoc()' class='glyphicon glyphicon-eye-open clickable' style='top:-5px;'></div></td>   
    </tr>
    
    <tr>
        <td class='lbl'>Поток</td>   
        <td class='lbl'><?= 
        \yii\helpers\Html::tag( 'div', $model->docTypeTitle, 
                   [
                     'id'      => 'docTypeTitle', 
                     'onclick' => 'selectClassify();',
                     'class'   => 'clickable showval',
                   ]);        
        ?></td>
        <td class='lbl'>Документ</td>     
        <td class='lbl'><?= 
        \yii\helpers\Html::tag( 'div', $model->docClassTitle, 
                   [
                     'id'      => 'docClassTitle', 
                     'onclick' => 'selectClassify();',
                     'class'   => 'clickable showval',
                   ]);        
        ?></td>
     <td><div onclick='selectClassify()' class='glyphicon glyphicon-search clickable' style='top:-5px;'></div></td>           
    </tr>   


    <tr>
        <td class='lbl'>Контрагент</td>   
        <td  colspan='3'><?= $form->field($model, 'orgTitle')->textInput([
        'id' => 'orgTitle', 'class' => 'form-control form-small', 'style' => 'width:300px'  ])->label(false)?></td>
        <td><div onclick='selectOrg()' class='glyphicon glyphicon-search clickable' style='top:-5px;'></div></td>   
   </tr>
   
   
   
    <tr>
        <td class='lbl'>ИНН</td>      
        <td><?= $form->field($model, 'orgINN')->textInput([
        'id' => 'orgINN', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>
        <td class='lbl'>КПП</td>   
        <td><?= $form->field($model, 'orgKPP')->textInput([
        'id' => 'orgKPP', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>
    </tr>   

    <?php
      /*  if ($model->docArticleRef == 0) $c='color:Crimson;';
                                   else */$c='';
    ?>
    <tr>
        <td class='lbl'>Тип</td>   
        <td class='lbl'><?= 
        \yii\helpers\Html::tag( 'div', $model->orgType, 
                   [
                     'id'      => 'orgType', 
                     'onclick' => 'selectDeal();',
                     'class'   => 'clickable showval',
                     'style'  =>  $c,
                   ]);        
        ?></td>
        <td class='lbl'>Статья</td>     
        <td class='lbl'><?= 
        \yii\helpers\Html::tag( 'div', $model->orgDeal, 
                   [
                     'id'      => 'orgDeal', 
                     'onclick' => 'selectDeal();',
                     'class'   => 'clickable showval',
                     'style'  =>  $c,
                   ]);        
        ?></td>
     <td><div onclick='selectDeal()' class='glyphicon glyphicon-search clickable' style='top:-5px;'></div></td>           
    </tr>   
           
    <tr>
    <td class='lbl'>Версия</td>   
        <td><?= $form->field($model, 'docOrigStatus')->dropDownList([        
    '0' => 'Оригинал',
    '1' => 'Копия',
    '2'=>'Скан',
   ], ['class' => 'form-control form-small', 'style' => 'width:100px'])->label(false)?></td>
    
 
    <td  class='lbl'>Ответст.</td>   
        <td><?= $form->field($model, 'docGoal')->dropDownList([        
                'бухгалтерия' => 'бухгалтерия',
                'офис' => 'офис',
                'производство'=>'производство'
                ], ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:100px'])->label(false)?></td>

    </td>
</tr>    
    
    <tr>
        <td class='lbl'>Вх. №</td>   
        <td><?= $form->field($model, 'docOrigNum')->textInput([
        'id' => 'docOrigNum', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>
        <td class='lbl'>Дата </td>   
        <td>
        <?php
        $action ="setOrigDate();";
        echo DatePicker::widget([
                        'name' => 'origDate',
                        'id'   => 'origDate',
                        'value' => $model->docOrigDate,    
                        'type' => DatePicker::TYPE_INPUT,
                        'options' => [
                        'onchange' => $action,
                        'style' => 'width:100px; font-size:12px;',
                        ],
                        'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd.mm.yyyy'        
                        ]
                    ]);
        ?>
        <?= $form->field($model, 'docOrigDate')->hiddenInput([
        'id' => 'docOrigDate', 'class' => 'form-control form-small', 'style' => 'padding-top:0px;width:100px'  ])->label(false)?></td>
    </tr>   

    <tr>
        <td colspan='5'>
        <table width='100%' border='0'>
        <tr>
        <td class='lbl' width='75px'>Сумма</td>   
        <td width='100px'>
        <?= $form->field($model, 'docSum')->textInput([
        'id' => 'docSum', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?>
        </td>   
        <td  width='85px'>
         <div style='text-align:left;margin-top:-15px;margin-left:5px;' title='В оплату'><?= $form->field($model, 'isOplate')->checkbox(['label' => 'В оплату',  'id' =>'isOplate'])?></div> 
        </td>   
        <td class='lbl'><div style='margin-left:20px'>НДС</div></td>  
        <td style='padding-left:0px;'><?= $form->field($model, 'docNDS')->textInput([
        'id' => 'docNDS', 'class' => 'form-control form-small', 'style' => 'width:50px'  ])->label(false)?></td>   
        <td align='left' style="padding:0px"><div style='margin-left:-30px;margin-top:-20px;'>%</div></td>
        </tr></table>
    </tr>    
    

<!--    <tr>
        <td class='lbl'>Передать</td>   
        <td><?= $form->field($model, 'docOwner')->dropDownList([        
                'бухгалтерия' => 'бухгалтерия',
                'офис' => 'офис',
                'производство'=>'производство'
                ], ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:100px'])->label(false)?></td>   
    </tr>    -->


     <tr>
        <td class='lbl'> Номер 1С</td>   
        <td><?= $form->field($model, 'ref1C_input')->textInput([
                'id' => 'ref1C_input', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>   

        <td class='lbl'>Счет в № 1С</td>
        <td><?= $form->field($model, 'ref1C_schet')->textInput([
                'id' => 'ref1C_input', 'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>   
    </tr>    



     <tr>
        <td class='lbl'>Р/с</td>   
        <td colspan='3' ><?= $form->field($model, 'orgRS')->textInput([
                'id' => 'orgRS',  'class' => 'form-control form-small', 'style' => 'width:300px'  ])->label(false)?></td>   
        <td><div onclick='selectAccount()' class='glyphicon glyphicon-search clickable' style='top:-5px;'></div></td>   
    </tr>    

    <tr>
        <td class='lbl'>Банк</td>   
        <td colspan='3' ><?= $form->field($model, 'orgBank')->textInput([
                'id' => 'orgBank',  'class' => 'form-control form-small', 'style' => 'width:300px'  ])->label(false)?></td>   
    </tr>    

     <tr>
         <td colspan='5'><table><tr>
             <td class='lbl' style='width:75px;'>БИК</td>   
             <td><?= $form->field($model, 'orgBIK')->textInput([
                'id' => 'orgBIK',  'class' => 'form-control form-small', 'style' => 'width:100px'  ])->label(false)?></td>   
             <td class='lbl'  style='padding-left:20px;width:50px;' >К/с</td>   
             <td ><?= $form->field($model, 'orgKS')->textInput([
                'id' => 'orgKS',  'class' => 'form-control form-small', 'style' => 'width:180px'  ])->label(false)?>
                </div></td>   
          </tr></table> </td>
    </tr>    

     <tr>
         <td colspan=5><?= $form->field($model, 'docNote')->textarea(['rows' => 2, 
         'style' => 'width:400px', 'class' => 'form-control form-small', 'cols' => 25])->label('Комментарий')?></td>           
     </tr>    

     <tr>
        <td class='lbl'>Статус</td>   
                <td><?= $form->field($model, 'contragentType')->dropDownList([        
    $model->getTypeArray()
                ], ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:100px'])->label(false)?></td>   

        <td class='lbl'>Операция</td>   
                <td><?= $form->field($model, 'operationType')->dropDownList([        
    $model->getAllOperationArray()
                ], ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:100px'])->label(false)?></td>   
    </tr>    
     
     <tr>
     <td></td>     
     <td>
     <?php if(!empty($model->id)) {?>
         <div class='btn btn-primary' onclick= 'duplicate();'>Дублировать</div>
     <?php } ?>
     </td>
     
     <td></td>
     <td colspan='2' align='right'>
     <div class='btn btn-primary' onclick= 'submitForm();'>Сохранить</div>
     
     <?php // Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'onclick' => 'submitForm()']) ?></td>     
     </tr>    
     
</table>

<?= $form->field($model, 'docURIType')->hiddenInput(['id' =>'docURIType'])->label(false)?>   
<?= $form->field($model, 'docTypeRef')->hiddenInput(['id' =>'docTypeRef'])->label(false)?>   
<?= $form->field($model, 'docClassifyRef')->hiddenInput(['id' =>'docClassifyRef'])->label(false)?>   
<?= $form->field($model, 'docArticleRef')->hiddenInput(['id' =>'docArticleRef'])->label(false)?>   
<?= $form->field($model, 'refAccount')->hiddenInput(['id' =>'refAccount'])->label(false)?>   
<?= $form->field($model, 'refOrg')->hiddenInput(['id' =>'refOrg'])->label(false)?>   
<?= $form->field($model, 'refDocHeader')->hiddenInput()->label(false)?>   
<?= $form->field($model, 'id')->hiddenInput()->label(false)?>
  
    
<?php ActiveForm::end(); ?>
</td></tr>
</table>


<?php
Modal::begin([
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:450px'>
    <iframe id='selectOrgDialogFrame' width='450px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/reg-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
Modal::begin([
    'id' =>'selectAccountDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:350px'>
    <iframe id='selectAccountDialogFrame' width='350px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/reg-org-acc&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
Modal::begin([
    'id' =>'selectOrgDeal',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectOrgDealFrame' width='420px' height='420px' frameborder='no'   src='index.php?r=/site/org-deal-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectClassifyDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:450px'>
    <iframe id='selectClassifyFrame' width='420px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/reg-doc-classify&noframe=1&docId=<?=$model->id?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'uploadYandexDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
]);?><div style='width:450px'>
  <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => 'uploadYandexForm', 'action'=>'index.php?r=/bank/operator/upload-yandex']);    
    echo  $form->field($model, 'loadFile')->fileInput() ;
    echo  $form->field($model, 'id')->hiddenInput()->label(false);
    echo Html::submitButton('Загрузить', ['class' => 'btn btn-primary']) ;
    ActiveForm::end(); 
  ?>
</div>
<?php Modal::end();?>


<?php
//echo $model->refOrg;
 $form = ActiveForm::begin(['id' => 'saveDataForm', 'action'=>'index.php?r=/bank/operator/get-selected-org']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";

ActiveForm::end(); 
?>

