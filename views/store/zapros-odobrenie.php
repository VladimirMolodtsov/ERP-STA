<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;

$this->title = 'Запрос - одобрение';

$zaprosRecord = $model-> prepareZaprosOdobrenie();


?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script>  

<style> 

</style>

<script type="text/javascript">

function showAddCategory()
{   
    $('#category_form').modal('show'); 
}
	
function closeAddCategory()
	{ 
	    $('#category_form').modal('hide');  
	}	
    
function createCategory()
    {
        categoryName = document.getElementById('categoryName').value;              
        openSwitchWin('store/add-zapros-category&zaprosId=<?=$zaprosRecord->id ?>&categoryName='+categoryName);       
    }    
    
function setCategory()
    {
    
        n = document.getElementById("zaprosCategory").options.selectedIndex;
        zaprosCategory = document.getElementById("zaprosCategory").options[n].value;    

        n1 = document.getElementById("zaprosType").options.selectedIndex;
        zaprosType  = document.getElementById("zaprosType").options[n1].value;   

        wareTitle = document.getElementById("wareTitle").value;          
        //var radios = getElementsByName('PurchesForm[zaprosType]');
        //document.getElementsByName('zaprosType');
        //document.f1.r1[0].checked=true;
        //if (radios[0].checked) zaprosType = 1;
        //if (radios[1].checked) zaprosType = 2;                
        openSwitchWin('store/chng-zapros-category&zaprosId=<?=$zaprosRecord->id ?>&zaprosType='+zaprosType+'&zaprosCategory='+zaprosCategory+'&wareTitle='+wareTitle);       
    }    
</script>

<h4><?= Html::encode($this->title) ?> </h4>

<?php $form = ActiveForm::begin(['id' => 'Mainform' ]); ?>  
<table border='0'>
<tr>
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Тип:</b> </td>
	<td colspan='3'>
    <?= $form->field($model, 'wareTitle')
    ->textInput(['id' => 'wareTitle', ])->label(false); ?>
    </td>

</tr>
<tr>
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Тип:</b> </td>
	<td>
    <?= $form->field($model, 'zaprosType')
    ->dropDownList([
        '1' => 'Сырье',
        '2' => 'Товар',        
    ],
    [
        'id' => 'zaprosType',        
    ])->label(false); ?>
    </td>
    
<?php Pjax::begin(); ?>    
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Категория:</b> </td>
	<td>
    <?= $form->field($model, 'zaprosCategory')
    ->dropDownList($model->getCategoryType(),
    [
        'prompt' => 'Выберите вариант',
        'id' => 'zaprosCategory',
        'onchange' => 'setCategory();'
    ])->label(false);
    ?>    
    </td>    
<?php Pjax::end(); 
    /*<td style='vertical-align:top; padding:10px;' ><a href="#" onclick="showAddCategory();">
    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
    </td>*/
?>        
</tr>	
<tr>
	<td colspan='4'><b>Наценка на продукцию:</b> </td>	
</tr>	
<tr>
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Крупный опт:</b> </td>
	<td><?= $form->field($model, 'pcntVal1')->textInput(['style'=>'width:100px;'])->label(false)?> </td>    
  	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Партнерам:</b> </td>
	<td><?= $form->field($model, 'pcntVal2')->textInput(['style'=>'width:100px;'])->label(false)?> </td>    

</tr>	
    <tr>
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Мелкий опт:</b> </td>
	<td><?= $form->field($model, 'pcntVal3')->textInput(['style'=>'width:100px;'])->label(false)?> </td>    
	<td width="200px"  style='vertical-align:top; padding:10px;' ><b>Розница:</b> </td>
	<td><?= $form->field($model, 'pcntVal4')->textInput(['style'=>'width:100px;'])->label(false)?> </td>    
    </tr>	
<tr>
</tr>	

</table>


<div class='row'>

    <div class='col-md-12' style='text-align:right' >
   <?php //<input type="button" id="btn-submit" style='background-color: ForestGreen;' class="btn btn-primary"  value="Сохранить" onclick='saveForm();'>	?>
   <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen;', 'name' => 'actMainform']) ?> 
    </div>
</div>
 

<br>

<hr>
<?= $form->field($model, 'id')->hiddenInput()->label(false)?> 

<?php ActiveForm::end(); ?>


   
<!--- ******************************************************  --->    
<!--- ******************************************************  --->  
<?php
Modal::begin([
    'id' =>'category_form',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4>Создать категорию</h4>',

]);?>

    <div>
        <input class='form-control' name='categoryName' id='categoryName' value=''>
    </div>
    <br>
    <input class="btn btn-primary"  style="width: 175px;" type="button" onclick='createCategory();' value="Создать"  />

    
<?php
Modal::end();
?>
  

