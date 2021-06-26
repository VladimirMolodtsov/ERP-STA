<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Создание новой задачи';
//$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;

?>

<script type="text/javascript">

/*function loadExtaract()
{
 $('#loadFileDialog').modal('show'); 
}*/

function showOrgList()
{
 document.getElementById('action').value = 'selectOrg';
 document.getElementById('taskEditForm').submit();
 }

function saveData()
{
 document.getElementById('action').value = 'save';
 document.getElementById('taskEditForm').submit();
 window.parent.readTaskChange();
}

</script> 

<style>
 .flbl {
     width: 175px;     
     padding: 5px;
     padding-left: 15px;
   }
 .btn-local {
    padding:4px;    
    font-size:12px;
}  
</style>
  
  <?php $form = ActiveForm::begin(['id' => 'taskEditForm']); //?>

  <?= $form->field($model, 'action')->hiddenInput(['id' => 'action'])->label(false)?>
  <?= $form->field($model, 'executorRef')->hiddenInput(['id' => 'executorRef'])->label(false)?>
  
  <table border='0' width='550px'>
  <tr>   
    <td class='flbl'>Контрагент</td>
    
    <td colspan=2><input id='orgTitle' name='orgTitle' readonly='true'  style="width: 200px;" value='<?= $model->orgTitle ?>'><input class="btn btn-primary btn-local"  style="width: 25px; margin-top:-5px" type="button" value="..." onclick="javascript:showOrgList();"/>    
    <?= $form->field($model, 'orgRef')->hiddenInput()->label(false)?></td>        
  </tr>
  <tr>    
    <td class='flbl'>Начало исполнения</td>
    <td><?= $form->field($model, 'startDate')->textInput(['id' => 'startDate', 'type' => 'date'])->label(false)?></td>  
    <td><?= $form->field($model, 'startTime')->textInput(['id' => 'startTime', 'type' => 'time'])->label(false)?></td>  
  </tr>
  <tr>
    <td class='flbl'>Плановое окончание</td>
    <td><?= $form->field($model, 'planDate')->textInput(['id' => 'planDate', 'type' => 'date'])->label(false)?></td>    
    <td><?= $form->field($model, 'planTime')->textInput(['id' => 'planTime', 'type' => 'time'])->label(false)?></td>    
  </tr>

  <tr>
    <td class='flbl'>Дедлайн</td>
    <td><?= $form->field($model, 'deadDate')->textInput(['id' => 'deadDate', 'type' => 'date'])->label(false)?></td>    
    <td><?= $form->field($model, 'deadTime')->textInput(['id' => 'deadTime', 'type' => 'time'])->label(false)?></td>    
  </tr>

  <tr>    
    <td colspan=3>
    <?= $form->field($model, 'note')->textarea(['id' => 'note','rows' => 5, 'cols' => 20])->label('Комментарий')?></td> 
    
  </tr>
  
  <tr>
    <td colspan='3' align='right'> 
     <?php // Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'onclick' => 'saveData();']) ?> 
     <a href="" onclick='saveData();' class='btn btn-primary'>Сохранить</a>      
    </td>  
  </tr>
  
  </table>
  
  
  <?php ActiveForm::end(); ?>
   
   
   
