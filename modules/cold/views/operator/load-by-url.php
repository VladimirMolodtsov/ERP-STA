<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


$this->title = 'Холодная база: Загрузка внешних данных ';
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
?>

     
<p> Введите ссылку на предварительно сформированную таблицу в формате csv. Как вариант ссылка может быть получена из таблицы Google.
(File->Publish to the web, в качестве параметров выберите "Entire Document" и "Comma-separated values (.csv)").
</p>     
<p>
Таблица должна включать следующие столбцы в указанном ниже порядке:
</p>

<table class='table table-stripped'>
<tr>    
    <td><b>ИНН</b></td> <td>Строка до 20 символов</td>   <td> </td>   
</tr>        
<tr>
    <td><b>Наименование</b></td> <td>Строка до 250 символов</td>   <td> Не может быть пустым</td>   
</tr>    
<tr>
    <td><b>Область</b></td> <td>Строка до 150 символов</td>   <td> </td>    
</tr>    
<tr>        
    <td><b>Город</b></td> <td>Строка до 250 символов</td>   <td>  Не может быть пустым</td>   
</tr>        
<tr>    
    <td><b>Адрес</b></td> <td>Строка до 250 символов</td>   <td> </td>   
</tr>        
<tr>    
    <td><b>X</b></td> <td>Десятичное число</td>   <td> Широта</td>   
</tr>        

<tr>    
    <td><b>Y</b></td> <td>Десятичное число</td>   <td> Долгота</td>   
</tr>        

<tr>    
    <td><b>Почтовый индекс</b></td> <td>Строка до 20 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td><b>E-mail</b></td> <td>Строка до 150 символов</td>   <td> Список, разделен ';' </td>   
</tr>        

<tr>    
    <td><b>Телефоны</b></td> <td>Строка до 250 символов</td>   <td> Не может быть пустым. Список телефонов разделенн ';'.</td>   
</tr>        

<tr>    
    <td><b>Ссылка на сайт</b></td> <td>Строка до 250 символов</td>   <td> URL сайта</td>   
</tr>        

</table>    

<p> Первая строка содержит заголовки и не обрабатывается. Важно! Не допускайте переноса строк внутри ячейки таблицы! </p>


 <?php $form = ActiveForm::begin([
    'layout'=>'horizontal',
     'fieldConfig' => [
             'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
            'horizontalCssClasses' => [
                'label' => 'col-sm-6',
                //'offset' => 'col-sm-offset-4',
                //'wrapper' => 'col-sm-6',
                'error' => '',
                'hint' => '',
            ],
        ],
   'options' => ['class' => 'edit-form form-inline'],

 ]); ?>  

 <div class='row'>
 <div class='col-md-7'>
     <?= $form->field($model, 'csvUrl')->textInput(['style' => 'width:250px;'])->label('Ссылка на CSV файл.')?>
 </div>    
 <div class='col-md-3'>
 <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?>
 </div>    
 <div class='col-md-2'>
 </div>    
 </div>
 <?php ActiveForm::end(); ?>

