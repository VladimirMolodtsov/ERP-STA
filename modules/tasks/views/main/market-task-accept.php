<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = 'Принятие задачи';
//$this->params['breadcrumbs'][] = $this->title;
$curUser=Yii::$app->user->identity;

$model->loadMarketTask();
?>

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
  
<script>

function saveData()
{
 document.getElementById('taskAcceptForm').submit();
 window.parent.readTaskChange();
}


function refreshTimeList()
{
    $.pjax.reload({container:"#timeList"}); 
}
</script>  
  
  
  <?php $form = ActiveForm::begin(['id' => 'taskAcceptForm']); //?>
    <?= $form->field($model, 'id')->hiddenInput(['id' => $model->id])->label(false)?>
  
  <table border='0' width='550px'>
  <tr>   
    <td class='flbl'>Контрагент</td>    
    <td colspan='2'><?= Html::encode($model->orgTitle) ?></td>        
  </tr>
  <tr>
    <td class='flbl'>Выбраное время</td>
    <td><?= $form->field($model, 'acceptDate')->textInput(['id' => 'deadDate', 'readonly'=>'true'])->label(false)?></td>
    <td><?= $form->field($model, 'acceptTime')->textInput(['id' => 'deadDate', 'readonly'=>'true'])->label(false)?></td>        
    
    </td>    
  </tr>

  <tr>    
    <td class='flbl'>Начало исполнения</td>
    <td><b><?= Html::encode($model->startDate)?></b></td>  
    <td><b><?= Html::encode($model->startTime)?></b></td>  
  </tr>
  <tr>
    <td class='flbl'>Плановое окончание</td>
    <td><b><?= Html::encode($model->planDate)?></b></td>    
    <td><b><?= Html::encode($model->planTime)?></b></td>    
  </tr>

  <tr>
    <td class='flbl'>Дедлайн</td>
    <td><b><?= Html::encode($model->deadDate)?></b></td>    
    <td><b><?= Html::encode($model->deadTime)?></b></td>    
  </tr>

  <tr>    
    <td colspan=3 style='background-color:WhiteSmoke;'>    
        <b><?= Html::encode($model->note)?></b>
        <div>&nbsp;</div></td>
  </tr>
  
  <tr>
    <td colspan='3' align='right'> 
     <a href="" onclick='saveData();' class='btn btn-primary'>Сохранить</a>      
     <?php // Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'onclick' => 'saveData();']) ?> 
    </td>  
  </tr>
  
  </table>
  
  
  <?php ActiveForm::end(); ?>
   
   
   
