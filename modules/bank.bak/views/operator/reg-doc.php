<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = 'Регистрация документа';
$this->params['breadcrumbs'][] = $this->title;

$model->loadData();

/*
`docTitle`  'Название',  
'docType`  'тип документа',
*/

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<?php $form = ActiveForm::begin(); ?>
  
<table class='table'>  
<tr>
<td>Номер регистрации</td><td><?= $form->field($model, 'docIntNum')->label(false)?></td>
<td>Контрагент </td><td><?= $form->field($model, 'orgTitle')->label(false)?></td>
<td>Наименование</td><td><?= $form->field($model, 'docTitle')->label(false)?></td>
</tr>
 
<tr>
<td>Входящий номер </td><td><?= $form->field($model, 'docOrigNum')->label(false)?></td>
<td>Дата</td><td><?= $form->field($model, 'docOrigDate')->label(false)?></td>
<td>Тип</td><td><?=  $form->field($model, 'docOrigStatus')->dropDownList([
    '0' => 'Оригинал',
    '1' => 'Копия',
    '2'=>'Скан'
   ])->label(false)?></td>
</tr>

<tr>
<td>Путь к документу</td><td><?= $form->field($model, 'docURI')->label(false)?></td>
<td>Сумма</td><td><?= $form->field($model, 'docSum')->label(false)?></td>
<td><?= $form->field($model, 'isFinance')->checkbox(['label' => 'Финансовый',]) ?></td>
<td><?= $form->field($model, 'isOplate')->checkbox(['label' => 'Оплачиваемый',])?></td>
</tr>


<tr>
<td colspan=5><?= $form->field($model, 'docNote')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?></td>
<td><?= $form->field($model, 'ref1C')->label('Ссылка в 1C')?></td>

</tr>

</table>
  

<?= $form->field($model, 'refDocHeader')->hiddenInput()->label(false)?>   
<?= $form->field($model, 'id')->hiddenInput()->label(false)?>
  
<div class='row'>
    <div class='col-md-5'></div>
    <div class='col-md-5'></div>
    <div class='col-md-2' style='text-align:right;'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?></div>
</div><?php ActiveForm::end(); ?>



