<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */


/*
 0x0001     Маркетинг
 0x0002     Холодные звонки
 0x0004     Активные продажи
 0x0008     Кадры
 
 0x0010     Снабжение
 0x0020     Управление
 0x0040     Финансы
 0x0080     Менеджер 2ур.
 
 0x0100     Коммерческий директор
 0x0200     Начальник производства
 0x0400     Оператор банка
 0x0800     Глав Бух?

*/

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Пользователи и роли';
//$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<style>
.lbl {
    font-size:11px;
}
     
</style>
    
<script>
function saveRole()
{
   
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=site/role-edit',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){                       
                window.parent.successSave(res);
        },
        error: function(){
            window.parent.errorSave();
        }
    });	
}

</script>

 <div>
    <?php 
    if ($model->id ==0){echo "<h4>Новый пользователь:</h4>";}
    else               {echo "<h4>Редактировать пользователя:</h4>";} 
    $form = ActiveForm::begin(['id' => 'saveDataForm']); ?>
  
  
   <?= $form->field($model, 'userFio')->label('ФИО пользователя')?>
   <?= $form->field($model, 'userName')->label('Логин')?>   
   <?= $form->field($model, 'password')->label('Пароль')?>
   <?= $form->field($model, 'phoneLink')->label('Ссылка на виртуальную АТС')?>
   <?= $form->field($model, 'phoneInternаl')->label('Номер на виртуальной АТС')?>

   
   <p><b>Назначить роли:</b></p>
   <table border="0" width="100%">
   <tr>
   <td>
   <p> Отдел продаж</p>   
   <?= $form->field($model, 'isHeadMarket')->checkbox(['label' => 'Коммерческий директор'])?>
   <?= $form->field($model, 'isColdOp')->checkbox(['label' => 'Оператор холодных звонков'])?>
   <?= $form->field($model, 'isSchetOp')->checkbox(['label' => 'Менеджер активных продаж'])?>
   <?= $form->field($model, 'isSchet2Op')->checkbox(['label' => 'Менеджер 2 уровня'])?>
   <br>
   <?= $form->field($model, 'isDataOp')->checkbox(['label' => 'Маркетинг'])?>
   </td>
   <td>
   <p> Доставка и снабжение</p>   
   <?= $form->field($model, 'isHeadSclad')->checkbox(['label' => 'Начальник производства'])?>   
   <?= $form->field($model, 'isScladOp')->checkbox(['label' => 'Склад/снабжение'])?>   
   <p> Прочее</p>   
   <?= $form->field($model, 'isFinOp')->checkbox(['label' => 'Финансы'])?>   
   <?= $form->field($model, 'isBankOp')->checkbox(['label' => 'Оператор банка'])?>
   <?= $form->field($model, 'isPersonalOp')->checkbox(['label' => 'Менеджер по кадрам'])?>
   <?= $form->field($model, 'isHead')->checkbox(['label' => 'Управление'])?>
   </td>
   </tr>
   </table>

   <?= $form->field($model, 'userNote')->textArea(['id'=>'userNote'])->label('Комментарий')?>

      
   <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
             <div class='btn btn-primary' onclick='saveRole()'>Сохранить</div>              
            </div>
    </div>
    <div style='visibility:hidden'>   <?= $form->field($model, 'id')->label('Id')?>   </div>
    <?php ActiveForm::end(); ?>
 
</div>
