<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
	$this->title = 'Отправка коммерческого предложения';
?>
<style>
 
</style>

  <h2><?= Html::encode($this->title) ?></h2>

  
  
<?php $form = ActiveForm::begin(['options' => ['id'=> 'mail']]) ?>    
	 <?= $form->field($model, 'email')->label('Почтовый адрес')?>	
	 
     <?= $form->field($model, 'subject')->label('Тема сообщения')?>	
	 
	 <div style='background-color: GhostWhite;'>
	 <?= $page ?>
	 </div>
	 
	 <?= $form->field($model, 'body')->textarea(['rows' => 4, 'cols' => 25])->label('Дополнительный текст письма')?>
	 </ul> 
	 <table border='0' width='600px'>
	 <tr>
     <td align='left'><?= Html::submitButton('Отправить', ['class' => 'btn btn-primary', 'name' => 'send-button']) ?> </td>
	 </tr></table>
<?php ActiveForm::end() ?>


  

</p>