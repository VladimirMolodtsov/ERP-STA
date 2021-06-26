<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Collapse;


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
    <td width='75px'>Номер столбца</td> <td><b>Наименование</b></td> <td>Тип данных</td>   <td>Комментарий </td>   
</tr>        

<tr>    
    <td colspan='4' align='center'>Данные о предприятии.</td>   
</tr>        

<tr>    
    <td>1</td> <td><b>ИНН</b></td> <td>Строка до 20 символов</td>   <td> </td>   
</tr>        

<tr>
    <td>2</td> <td><b>Название организации</b></td> <td>Строка до 250 символов</td>   <td> Не может быть пустым</td>   
</tr>    

<tr>
    <td>3</td> <td><b>Разделы</b></td> <td>Строка до 150 символов</td>   <td> </td>    
</tr>    

<tr>
    <td>4</td> <td><b>Подразделы</b></td> <td>Строка до 150 символов</td>   <td> </td>    
</tr>    

<tr>
    <td>5</td> <td><b>Рубрики</b></td> <td>Строка до 150 символов</td>   <td> </td>    
</tr>    

<tr>
    <td>6</td> <td><b>Область</b></td> <td>Строка до 150 символов</td>   <td> </td>    
</tr>    

<tr>        
    <td>7</td> <td><b>Населенный пункт</b></td> <td>Строка до 250 символов</td>   <td>  Не может быть пустым</td>   
</tr>        

<tr>        
    <td>8</td> <td><b>Район</b></td> <td>Строка до 250 символов</td>   <td>  Не может быть пустым</td>   
</tr>        

<tr>    
    <td>9</td> <td><b>Адрес</b></td> <td>Строка до 250 символов</td>   <td> </td>   
</tr>        
<tr>    
    <td>10</td> <td><b>X</b></td> <td>Десятичное число</td>   <td> Широта</td>   
</tr>        

<tr>    
    <td>11</td> <td><b>Y</b></td> <td>Десятичное число</td>   <td> Долгота</td>   
</tr>        

<tr>    
    <td>12</td><td><b>Почтовый индекс</b></td> <td>Строка до 20 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>13</td><td><b>E-mail</b></td> <td>Строка до 150 символов</td>   <td> Список, разделен ',' </td>   
</tr>        

<tr>    
    <td>14</td><td><b>Телефоны</b></td> <td>Строка до 250 символов</td>   <td> Не может быть пустым. Список телефонов разделенн ','.</td>   
</tr>        

<tr>    
    <td>15</td><td><b>Факсы</b></td> <td>Строка до 250 символов</td>   <td>  Список телефонов разделенн ','.</td>   
</tr>        


<tr>    
    <td>16</td><td><b>Ссылка на сайт</b></td> <td>Строка до 250 символов</td>   <td> URL сайта</td>   
</tr>        

</table>    


<?php 
$label = "Данные о выполнении этапов. Данные столбцы могут отсутствовать. ";     


 

$content ="
<table class='table table-stripped'>
 <tr>    
    <td width='75px'>Номер столбца</td> <td><b>Наименование</b></td> <td>Тип данных</td>   <td>Комментарий </td>   
</tr>        

<tr>    
    <td>17</td><td><b>Дата первого контакта</b></td> <td>дата в текстовом формате дд.мм.гггг</td>   <td> </td>   
</tr>        

<tr>    
    <td>18</td><td><b> Телефон первого контакта</b></td> <td>Строка до 30 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>19</td><td><b>Разговор 1</b></td> <td>Строка до 250 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>20</td><td><b>ФИО первого контакта</b></td> <td>Строка до 75 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>21</td><td><b>Должность первого контакта</b></td> <td>Строка до 75 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>22</td><td><b>E-mail для первого КП</b></td> <td>Строка до 50 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>23</td><td><b>ФИО снабженца</b></td> <td>Строка до 75 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>24</td><td><b>Телефон снабженца</b></td> <td>Строка до 75 символов</td>   <td> </td>   
</tr>        


<tr>    
    <td>25</td><td><b>Дата второго контакта</b></td> <td>дата в текстовом формате дд.мм.гггг</td>   <td> </td>   
</tr>        

<tr>    
    <td>26</td><td><b>Телефон второго контакта</b></td> <td>Строка до 30 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>27</td><td><b>Разговор 2</b></td> <td>Строка до 250 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>28</td><td><b>ФИО второго контакта</b></td> <td>Строка до 75 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>29</td><td><b>E-mail для второго КП</b></td> <td>Строка до 50 символов</td>   <td> </td>   
</tr>        

<tr>    
    <td>30</td><td><b>Интерес</b></td> <td>Строка до 250 символов</td>   <td> </td>   
</tr>        
</table>    
";


 echo Collapse::widget([
    'items' => [
        [
            'label' => $label ,
            'content' => $content,
            'contentOptions' => [],
            'options' => []
        ]
    ]
]); 

?>



<p> Первая строка содержит заголовки и не обрабатывается. 
Этапы обработки импортируются на основании заполненных столбцов с датами контактов, не заполненная яцейка с датой этапа рассматривается как конец данных, дальнейшие этапы не импортируются.Важно! Не допускайте переноса строк внутри ячейки таблицы! </p>

<p> <a href='../modules/cold/template.csv'>Скачать шаблон</a>

<p> 
    Загрузка возможна через ссылку на csv файл (например сформированный в GoogleDoc - Ссылка на CSV файл) либо через загрузку файла с локального диска (Загрузить CSV файл)
</p>
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
  <div class='col-md-7' style='text-align:left;'>
     <?= $form->field($model, 'description')->textInput(['style' => 'width:250px;'])->label('Тематическая группа')?> <br>
 </div>    
 <div class='col-md-3'>
 <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?>
 </div>    
 <div class='col-md-2'>
 </div>    
 </div>
 
 <div class='row'>
  <div class='col-md-7' style='text-align:left;'>
     <?= $form->field($model, 'csvUrl')->textInput(['style' => 'width:250px;'])->label('Ссылка на CSV файл.')?> <br>
 </div>    
 <div class='col-md-3'>
 </div>    
 <div class='col-md-2'>
 </div>    
 </div>

 <div class='row'>
  <div class='col-md-7' style='text-align:left;'>
     <?= $form->field($model, 'csvFile')->fileInput()->label('Загрузить CSV файл.')  ?>          
 </div>    
 <div class='col-md-3'>
 </div>    
 <div class='col-md-2'>
 </div>    
 </div>

 
 <?php ActiveForm::end(); ?>

