<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
	$this->title = 'Отправка почтового сообщения';
?>
<style>
 
</style>

  <h2><?= Html::encode($this->title) ?></h2>

<?php $form = ActiveForm::begin(['options' => ['id'=> 'mail']]) ?>    
	 <?= $form->field($model, 'email')->label('Почтовый адрес')?>	
     <?= $form->field($model, 'subject')->label('Тема сообщения')?>	
	 <?= $form->field($model, 'body')->textarea(['rows' => 4, 'cols' => 25])->label('Текст письма')?>
	 <p>Присоединенный файл:</p>
	 <ul>
	 <?php
	 for ($i=0; $i< count($model->listAttached); $i++)
	 {
		 echo "<li>".$model->listAttached[$i];  
	 }
	 ?>
	 </ul> 
	 <table border='0' width='600px'>
	 <tr>
     <td align='left'><?= Html::submitButton('Отправить', ['class' => 'btn btn-primary', 'name' => 'send-button']) ?> </td>
	 <td align='left'><?= Html::submitButton('Присоединить', ['class' => 'btn btn-primary', 'name' => 'attach-button']) ?> </td>
	 <!--<td align='right'> <a class='btn btn-primary' href="index.php?r=site/mail-attach" target="_blank">Добавить файл в аттач</a></td>-->
	 </tr></table>
<?= $form->field($model, 'orgId')->hiddenInput()->label(false)?> 
<?php ActiveForm::end() ?>


  

</p>
