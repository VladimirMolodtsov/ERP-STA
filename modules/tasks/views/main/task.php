<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Создание новой задачи';
$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;

?>
<style>
 .flbl {
     width: 175px;     
     padding: 5px;
     padding-left: 15px;
   }
</style>

  <h2><?= Html::encode($this->title) ?></h2>
  <?php $form = ActiveForm::begin(); ?>


  <table border='0' width='900px'>
  <tr>
    <td class='flbl'>Модуль</td><td><?= $form->field($model, 'moduleRef')->dropDownList($model->getModulesList())->label(false)?></td>  
    <td class='flbl'>Начало исполнения</td>
    <td><input type='datetime' class='form-control' name='startDateRaw' id='startDateRaw' value='<?= date("Y-m-d H:i:s") ?>' ></td>
  </tr>
  <tr>
    <td class='flbl'>Задача</td><td><?= $form->field($model, 'taskCode')->dropDownList($model->getTasksList())->label(false)?></td>  
    <td class='flbl'>Плановое окончание</td>
    <td><input type='datetime' class='form-control' name='startDateRaw' id='startDateRaw' value='<?= date("Y-m-d H:i:s") ?>' ></td>
  </tr>
  <tr>
    <td class='flbl'>Исполнитель</td><td><?= $form->field($model, 'executorRef')->dropDownList($model->getManagerList())->label(false)?></td>  
    <td class='flbl'>Дедлайн</td>
    <td><input type='datetime' class='form-control' name='startDateRaw' id='startDateRaw' value='<?= date("Y-m-d H:i:s") ?>' ></td>
  </tr>
  <tr>
    <td colspan='2' rowspan='2'> <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?> </td>  
    <td class='flbl'>Повторять</td><td><?= $form->field($model, 'repeater')->dropDownList($model->getRepeatList())->label(false)?></td>
  </tr>
  <tr>
    <td colspan='2' align='center'> 
     <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?> 
     <a href="" onclick='window.close();' class='btn btn-primary'>Отмена</a> 
    </td>  
  </tr>
  
  </table>
  
  <?php
  echo $form->field($model, 'startDate')->hiddenInput()->label(false);
  echo $form->field($model, 'planDate')->hiddenInput()->label(false);
  echo $form->field($model, 'deadline')->hiddenInput()->label(false);
  ?>
  <?php ActiveForm::end(); ?>
   
   
   