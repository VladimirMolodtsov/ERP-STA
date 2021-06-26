<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Потребности';
//$this->params['breadcrumbs'][] = $this->title;

$moduleText=$model->getModuleText();
$curUser=Yii::$app->user->identity;
$moduleText=$model->getModuleText();

$model->loadData();

?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
  .phone_view {
    display:none;
    margin:5px 0px;
    padding:10px;
    width:98%;
    border:1px solid #ffbc80;
    background:#ffffdf;
    font-size: 10pt;    
}

 
 /* кликабельный текст */
.phones {
    color:#f70;
    cursor: help
}
.phones:hover{
    border-bottom:1px dashed green;
    color:green;
}
 
 
</style>

<script>
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone)
{
  document.forms["w0"]["coldneedform-contactphone"].value=phone;
  document.getElementById("cphone").innerHTML =phone;   
}

function doCall()
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w0"]["coldneedform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

</script>


  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>
  
  <p><b><i>Цель: выяснить потребности и регулярность поставок.</i></b></p>
  
  Наименование компании: <u><strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($model->id)?>"><?= Html::encode($model->orgTitle)?></a></strong></u> <br>
<div class='spacer'></div>
  <table border=0 style="border:0px" width=100%>
      <tr>       
    <td width ='300px'>Телефон по которому состоялся контакт </td>
    <td ><?= $form->field($model, 'contactPhone')->label(false)?> </td>
    <td width="150px"  > <div style="position:relative; top:-7px; left:10px;"> &nbsp; <input class="btn btn-primary" style="width: 100px;" type="button" value="Позвонить" 
        onclick="javascript:doCall();"/> </div></td>
    </tr>
  </table>
  
    <p class="phone">
    <span class="phones" onclick="view('phone_list'); return false">Показать остальные телефоны...</span>
    </p>
    <p>
    <span id="phone_list" class="phone_view">
     <?php
     $phoneList=$model->getCompanyPhones();
     for ($i=0;$i<count($phoneList);$i++)
     {                
        echo "<a href='#' onclick='javascript:setPhone(".Html::encode($phoneList[$i]["phone"]).");'>".Html::encode($phoneList[$i]["phone"])."</a>";                
        if ($phoneList[$i]["status"] == 1){echo " <font color='green'>*</font>";}
        if ($phoneList[$i]["status"] == 2){echo " <font color='red'>*</font>";}
        echo ";\n";
     }
     ?>  
   </span> <p>
    
  <table border=0 style="border:0px" width=100%>
   <tr>
   <td width='450px' valign='top' style='padding:5px;'>    
   
    <div class="part-header"> Речевой модуль второго личного контакта </div>
    <p>
      <?php echo $moduleText; ?>;
    </p>
     <div class='spacer'></div>
     <hr>
    <?php
     echo "<i>".$model->prevContactDate."</i> &nbsp;";
     echo $model->prevContactFIO;
     echo "<br>\n";
     echo $model->prevContactText;
    ?>
     <p><a href ="index.php?r=site/contacts-detail&id=<?= $model->id ?>"> Просмотреть историю контактов </a></p>
   </td> 
   
   <td valign='top' style='padding:5px;'>
   <div class="part-header"> Результаты разговора</div>
   <p> Заполняется только если состоялся разговор</p>        
     <table border =0  style="border:0px" width='100%'>
     <tr>
       <td>E-mail </td><td><?= $form->field($model, 'contactEmail')->label(false)?></td>
       <td><div style="position:relative; top:-7px; left:10px;">&nbsp;<a  class="btn btn-primary" target="_blank" href='index.php?r=site/mail&email=<?php echo $model->contactEmail ?>'>Написать письмо</a></div>
       </td>              
     </tr>
     <tr>   
         <td>Контактное лицо </td><td colspan='2'><?= $form->field($model, 'contactFIO')->label(false)?>  </td>
     </tr>    
     <tr>   
         <td>Интерес к (товар)</td><td colspan='2'><?= $form->field($model, 'interes')->label(false)?>  </td>
     </tr>    
     <tr>   
         <td>Регулярность (дней)</td><td colspan='2'><?= $form->field($model, 'regular')->label(false)?>  </td>
     </tr>    
     </table>                     
   
   </td>
   </tr>
   </table>
    
   <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
 
   	<div class='row'>
    <div class='col-md-5'><p> Заполнил: <b>  <?= Html::encode($curUser->userFIO)?></b></p></div>
    <div class='col-md-5'></div>
    <div class='col-md-2' style='text-align:right;'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?></div>
    </div>
  
   
   <div style='visibility:hidden'> <?= $form->field($model, 'id')->hiddenInput()->label(false)?></div>
   <?php ActiveForm::end(); ?>
   
   
