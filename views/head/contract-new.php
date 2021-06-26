<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Collapse;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;

$curUser=Yii::$app->user->identity;
$this->title = 'Договор поставки Рутенберг';

$model->loadOrgData();

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

?>


<style> 

.child {
  padding:5px;
  text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 cursor:pointer;
}
td {
    padding:2px
}

</style>
<script>

function chkPredOplata()
{
    var predoplata = $('input[id="predoplata"]:checked').val();
    $("input").prop('disabled', false);
    if (predoplata == 50) $('#isPostPplate').prop('checked', true);
                     else $('#isPostPplate').prop('checked', false);
    $("#isPostPplate").prop('disabled', true);
}

function getNumber()
{
 $(document.body).css({'cursor' : 'wait'});   
 var url = 'index.php?r=head/get-contract-number';
 console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   $(document.body).css({'cursor' : 'default'});            
   console.log(res);
   $("#contractNumber").val(res.val);
   $("#internalNumber").val(res.val);
 },
 error: function(){
   //console.log(res);   
  $(document.body).css({'cursor' : 'default'});            
  alert('Error while add document!');
 }
 });	




}
</script>
        

 
 
<?php $form = ActiveForm::begin(); ?>

<div align='center'>
<h3><?= Html::encode($this->title) ?> </h3>

<table class='table table-striped' border='0' style='width:900px'>
<tr>
    <td width='250px'>Номер &nbsp;
    <div class='btn btn-default' onclick='getNumber();'>
       Получить     
    </div>
    </td><td>
    <?= $form->field($model, 'contractNumber')->textInput(['id' => 'contractNumber'])->label(false) ?>
    <?= $form->field($model, 'internalNumber')->hiddenInput(['id' => 'internalNumber'])->label(false) ?>
    </td>
</tr>
<tr>        
    <td>Менеджер</td><td><?= $form->field($model, 'userFormer')->label(false) ?></td>
</tr>

<tr>
    <td colspan='2'><h3>Реквезиты покупателя</h3></td>    
</tr>


<tr>
<td>Контрагент</td><td><?= $form->field($model, 'clientTitle')->label(false) ?></td>
</tr>

<tr>        
<td>Адрес</td><td><?= $form->field($model, 'clientAdress')->label(false) ?></td>
</tr>

<tr>        
<td>ИНН</td><td><?= $form->field($model, 'orgINN')->label(false) ?></td>
</tr>

<tr>        
<td>КПП</td><td><?= $form->field($model, 'orgKPP')->label(false) ?></td>
</tr>

<tr>
<td>Банковские реквезиты</td><td><?= $form->field($model, 'bankRekvesits')->label(false) ?></td>
</tr>

<tr>
<td><span title='Полное Фамилия Имя Отчество в родительном падеже. Например: Командира второго восточного фронта красной армии Чапаева Василия Ивановича'>В лице</span></td><td><?= $form->field($model, 'contactorFull')->label(false) ?></td>
</tr>


<tr>
<td><span title='Краткая форма Имени. Например: Чапаев В.И.'>Фамилия И.О.</span></td><td><?= $form->field($model, 'contractorShort')->label(false) ?></td>
</tr>

<tr>
<td><span title='Например: Директор ООО "Синергия"'>Должность</span></td><td><?= $form->field($model, 'contractorPost')->label(false) ?></td>
</tr>

<tr>
<td>Действует на основании</td><td><?= $form->field($model, 'contractorReason')->label(false) ?></td>
</tr>

<tr><td colspan='2'>
<table border='0'>
<tr>
    <td  width='250px'>Дата начала</td>
    <td>
          <?= $form->field($model, 'dateStart')->widget(DatePicker::classname(), [
            'options' => [],
            'removeButton' => false,
            'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'dd.mm.yyyy'
            ]
            ])->label(false);
            ?>    
      </td>
      <td width='25px'>&nbsp; </td>
      <td width='250px'>Дата окончания</td>
      <td >
          <?= $form->field($model, 'dateEnd')->widget(DatePicker::classname(), [
            'options' => [],
            'removeButton' => false,
            'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'dd.mm.yyyy'
            ]
            ])->label(false);
            ?>    
      </td>
</tr>
</table>
</td></tr>

<tr>
<td>Предоплата</td><td>
<?php
   echo  $form->field($model, 'predoplata')->radio([
         'label' => 'Без предоплаты',    
         'value' => 0, 
         'id' => 'predoplata', 
         'uncheck' => null,  
         'onClick' => 'chkPredOplata();'
         ]);


   echo  $form->field($model, 'predoplata')->radio([
         'label' => '100%',    
         'value' => 100, 
         'id' => 'predoplata', 
         'uncheck' => null,  
         'onClick' => 'chkPredOplata();'
         ]);
    
   echo  $form->field($model, 'predoplata')->radio([
         'label' => '50%',    
         'value' => 50, 
         'id' => 'predoplata', 
         'uncheck' => null,  
         'onClick' => 'chkPredOplata();'
         ]);
    
?>         
</td>
</tr>

<tr>
<td>Оплата остатка</td>
<td>
<?php
echo $form->field($model, 'isPostPplate')->checkbox(['label' => ', а оставшиеся 50%', 'id' => 'isPostPplate', 'disabled' => true]);
//echo $form->field($model, 'postoplate')->label(false) ; 
?>
</tr>


<tr>
<td>Оплата в течении</td><td>

<?php
   echo  $form->field($model, 'oplatePeriod')->radio([
         'label' => '5 (Пяти)',    
         'value' => 5, 
         'id' => 'oplatePeriod_5', 
         'uncheck' => null,  
         ]);
    
   echo  $form->field($model, 'oplatePeriod')->radio([
         'label' => '10 (Десяти)',    
         'value' => 10, 
         'id' => 'oplatePeriod_10', 
         'uncheck' => null,  
         ]);

   echo  $form->field($model, 'oplatePeriod')->radio([
         'label' => '15 (Пятнадцати)',    
         'value' => 15, 
         'id' => 'oplatePeriod_15', 
         'uncheck' => null,  
         ]);
   echo  $form->field($model, 'oplatePeriod')->radio([
         'label' => '20 (Двадцати)',    
         'value' => 20, 
         'id' => 'oplatePeriod_20', 
         'uncheck' => null,  
         ]);
   echo  $form->field($model, 'oplatePeriod')->radio([
         'label' => '30 (Тридцати)',    
         'value' => 30, 
         'id' => 'oplatePeriod_30', 
         'uncheck' => null,  
         ]);
             
?>         
</td>
</tr>

<tr>
<td>С момента получения</td><td>

<?php
   echo  $form->field($model, 'oplateStart')->radio([
         'label' => 'счёта',    
         'value' => 'счёта', 
         'id' => 'oplateStart_schet', 
         'uncheck' => null,  
         ]);
    
   echo  $form->field($model, 'oplateStart')->radio([
         'label' => 'товара',    
         'value' => 'товара', 
         'id' => 'oplateStart_ware', 
         'uncheck' => null,  
         ]);
    
?>         
</tr>

<tr>
<td>При условии</td><td colspan=3>
<?php
echo $form->field($model, 'isDopCondition')->checkbox(['label' => 'и при условии предоставления Поставщиком документов, указанных в п. 2.1
настоящего договора.']);
//echo $form->field($model, 'dopCondition')->hiddenInput()->label(false);
?>
</td>
</tr>


<tr>
<td>Телефоны</td><td><?= $form->field($model, 'phonesList')->label(false) ?></td>
</tr>

<tr>
<td>E-mail</td><td><?= $form->field($model, 'email')->label(false) ?></td>
</tr>

<tr>
<td>Сайт</td><td><?= $form->field($model, 'siteUrl')->label(false) ?></td>
</tr>

<tr>
<td>Ссылка на документ</td><td colspan=5><?= $form->field($model, 'docUrl')->label(false) ?></td>
</tr>

</table>

</div>
<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'refOrg')->hiddenInput()->label(false) ?>
<?=  Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?> 


<?php ActiveForm::end(); ?>




