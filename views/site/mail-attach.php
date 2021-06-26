<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
	$this->title = 'Отправка почтового сообщения';
?>
<style>
 
</style>

  <h2><?= Html::encode($this->title) ?></h2> 

  <p>Добавить файл.</p>
  <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'attachFile')->fileInput() ?>

    <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>

 
</p>