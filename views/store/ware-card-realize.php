<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;

$this->title = 'Карточка товара реализации';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$model -> loadData();
?>

<style>
.btn-small{
margin:2px;
font-size: 10pt;
padding:2px;
height:20px;
width:20px;
}


</style>
  
<script>


/*************/
function saveData(val)
{
    document.getElementById('dataVal').value=val;
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-warename-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){
            console.log(res);
            document.location.reload(true);
        },
        error: function(){
            alert('Error while saving data!');
        }
    });
}


</script>



<?php $form = ActiveForm::begin([
   'options' => ['class' => 'edit-form'],
   'id'=> 'mainForm'
]);

?>




 <table border=0 width=100% class='table table-small'>
 
  <tr>
  <td class='lbl'>  Название товара реализации </td>
  <td colspan=2><?= $form->field($model, 'wareTitle')->textarea(['rows' => 2,
         'style' => 'width:400px', 'class' => 'form-control form-small', 'cols' => 25])->label(false)?></td>

  <td class='lbl' align='right'>  Ед. изм. </td>
  <td ><?= $form->field($model, 'wareEd')->textInput([
         'style' => 'width:200px', 'placeHolder' => ' Ед. изм. в реализации'])->label(false)?></td>
 </td>
 </tr>


   <tr>
  <td class='lbl'>  Описание товара  </td>
  <td colspan=2>
    Связан с внутренней номенклатурой:
    <div> <b>
        <?= $model->nomTitle ?>
    </b></div>

    <div> <i>
        <?= $model->nomNote ?>
    </i></div>

  </td>

 </tr>



 <tr>
  <td class='lbl'>  Тип  </td> 
  <td class='data'> <?= $form->field($model, 'wareType')->dropDownList($model->getWareTypes(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px', 'readonly' =>'true'])->label(false)?>
   </td>
            
  <td class='lbl'> Группа   </td> 
  <td class='data'> <?= $form->field($model, 'wareGroup')->dropDownList($model->getWareGroups(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px', 'readonly' =>'true'])->label(false)?>
   </td>  

  <td class='lbl'> Производитель   </td>
  <td class='data'> <?= $form->field($model, 'wareProducer')->dropDownList($model->getWareProducer(), ['class' => 'form-control form-small', 'style' => 'margin-top:0px;width:200px', 'readonly' =>'true', ])->label(false)?>
   </td>

</tr>
 


 <tr>
  <td class='lbl'>  Цена  </td>
  <td class='data'> <?= $form->field($model, 'v1')->textInput(['id' => 'v1', 'placeHolder' => 'до 100 кг' ])->label(false)?>
   </td>
  <td class='data'> <?= $form->field($model, 'v2')->textInput(['id' => 'v2', 'placeHolder' => 'до 400 кг' ])->label(false)?>
   </td>
  <td class='data'> <?= $form->field($model, 'v3')->textInput(['id' => 'v3', 'placeHolder' => 'до 3000 кг' ])->label(false)?>
   </td>
  <td class='data'> <?= $form->field($model, 'v4')->textInput(['id' => 'v4', 'placeHolder' => 'более 3000 кг' ])->label(false)?>
   </td>


</tr>






<tr>
<td colspan=6 align='right'>

<?php
echo $form->field($model, 'id' )->hiddenInput(['id' => 'id' ])->label(false);
?>

<?= Html::submitButton('Сохранить',
    [
        'class' => 'btn btn-primary',
        'style' => 'width:150px;background-color: DarkGreen ;',
        'onclick' => 'saveWare()',
        ]) ?>
</td>
</tr>

<?php ActiveForm::end(); ?>







<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-nomenklatura-detail']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end();
?>

