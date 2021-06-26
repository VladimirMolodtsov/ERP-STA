<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Речевые модули и коммерческие предложения';
$this->params['breadcrumbs'][] = $this->title;

$provider = $model->getNeedTitleProvider();
?>
    <h1><?= Html::encode($this->title) ?></h1>
		
    <?php $form = ActiveForm::begin(); ?>
    
	<p>В тексте речевых модулей можно использовать следующие подстановки:</p>
	<ul>
	  <li> [ORGITLE] - будет заменено на название организации </li>
	  <li> [FIO]     - будет заменено на ФИО контакта </li>
	  <li> [PHONE]   - будет заменено на телефон контакта</li>
	  <li> [EMAIL]   - будет заменено на электронную почту контакта </li>
	  <li> [CONTACTDATE]   - будет заменено на дату последнего контакта </li>
	  
	</ul>
    
    <?= $form->field($model, 'module1')->textarea(['rows' => 4, 'cols' => 25])->label('Речевой модуль первого контакта')?>
    <?= $form->field($model, 'module2')->textarea(['rows' => 4, 'cols' => 25])->label('Речевой модуль Выяснения потребностей')?>
  
<script>

function setNeedTitle(id, title, row)
{
  document.forms["w0"]["modulelistform-needtitle"].value=title;
  document.forms["w0"]["modulelistform-needrow"].value=row;
  document.forms["w0"]["modulelistform-needid"].value=id;
  
}

</script>

<br>
     <p>Название потребностей. Всего обрабатывается до 10 потребностей.</p>
    
<?php echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider ,
        //'filterModel' => $model,
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],		    
            'Title:raw:Заголовок',
            'row:raw:Порядковый номер',			
			[
                'attribute' => 'Редактировть',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='#E' onclick=\"javascript:setNeedTitle('".$model['id']."','".$model['Title']."','".$model['row']."');\">Редактировать</a>";
                },
            ],		
			[
                'attribute' => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a href='index.php?r=site/needtitle-rm&id=".$model['id']."'>Удалить</a>";
                },
            ],		
        ],
		
    ]
);
?>
  <br>
  
    <table width='100%' border='0' style="padding: 15px;" id="E">
	<tr>
	<td width="50%"><?= $form->field($model, 'needTitle')->label('Заголовок')?></td>
	<td><?= $form->field($model, 'needRow')->label('Порядковый номер')?></td>
	<td align="left"><div style="position:relative; top:5px; left:10px;"><input class='btn btn-primary' type="button" value="Новый" onclick="javascript:window.location='index.php?r=site/modules'"/></div></td>	
	<tr>
	</table>
	<span style='visibility:hidden'> <?= $form->field($model, 'needId')->label('needId')?></span>
	
    <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'save-button']) ?>                								    
			</div>
    </div>    
    <?php ActiveForm::end(); ?>	
    <br>
	<div class="col-lg-offset-1 col-lg-11">
	<br>
	<a href="../uploads/proposal.pdf" target="_blank">Коммерческое предложение</a> <br>
    <input class='btn btn-primary' type="button" value="Загрузить коммерческое предложение" onclick="javascript:window.location='index.php?r=site/upload-proposal'"/>								
    </div>
 
</div>
