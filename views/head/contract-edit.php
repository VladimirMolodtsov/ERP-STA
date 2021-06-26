<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Collapse;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Карточка договора';

 $model->loadData();

?>



<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


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

</script>
        <h3><?= Html::encode($this->title) ?> </h3>

 
 
<?php $form = ActiveForm::begin(); ?>

<table border='0'>

<tr>
<td>Дата регистрации</td><td><?= $form->field($model, 'creationTime')->label(false) ?></td>
<td>Номер</td>
<td>
    <?= $form->field($model, 'contractNumber')->textInput(['id' => 'contractNumber'])->label(false) ?>
    <?= $form->field($model, 'internalNumber')->hiddenInput(['id' => 'internalNumber'])->label(false) ?>
</td>
<td>Менеджер</td><td><?= $form->field($model, 'userFormer')->label(false) ?></td>
</tr>

<tr>
<td>Контрагент</td><td><?= $form->field($model, 'clientTitle')->label(false) ?></td>
<td>ИНН</td><td><?= $form->field($model, 'orgINN')->label(false) ?></td>
<td>КПП</td><td><?= $form->field($model, 'orgKPP')->label(false) ?></td>
</tr>

<tr>
<td>Адрес</td><td><?= $form->field($model, 'clientAdress')->label(false) ?></td>
<td>Реквезиты</td><td colspan='3'><?= $form->field($model, 'bankRekvesits')->label(false) ?></td>
</tr>

<tr>
<td>В лице</td><td><?= $form->field($model, 'contactorFull')->label(false) ?></td>
<td>Должность</td><td><?= $form->field($model, 'contractorPost')->label(false) ?></td>
<td>Кратко</td><td><?= $form->field($model, 'contractorShort')->label(false) ?></td>
</tr>

<tr>
<td>Действует на основании</td><td><?= $form->field($model, 'contractorReason')->label(false) ?></td>
<td>Дата начала</td><td><?= $form->field($model, 'dateStart')->label(false) ?></td>
<td>Дата окончания</td><td><?= $form->field($model, 'dateEnd')->label(false) ?></td>
</tr>

<tr>
<td>Оплата в течении</td><td><?= $form->field($model, 'oplatePeriod')->label(false) ?></td>
<td>С момента получения</td><td><?= $form->field($model, 'oplateStart')->label(false) ?></td>
<td>Предоплата</td><td><?= $form->field($model, 'predoplata')->label(false) ?></td>
</tr>


<tr>
<td>При условии</td><td colspan=3><?= $form->field($model, 'dopCondition')->label(false) ?></td>
<td>Постоплата</td><td><?= $form->field($model, 'postoplate')->label(false) ?></td>
</tr>

<tr>
<td>Телефоны</td><td><?= $form->field($model, 'phonesList')->label(false) ?></td>
<td>E-mail</td><td><?= $form->field($model, 'email')->label(false) ?></td>
<td>Сайт</td><td><?= $form->field($model, 'siteUrl')->label(false) ?></td>
</tr>

<tr>
<td>Ссылка на документ</td><td colspan=5><?= $form->field($model, 'docUrl')->label(false) ?></td>
</tr>

</table>
<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<?=  Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?> 


<?php ActiveForm::end(); ?>




