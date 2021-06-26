<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
//use yii\bootstrap\Modal;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\widgets\Pjax;
use yii\bootstrap\Collapse;


$this->title = 'Создание новой задачи';
//$this->params['breadcrumbs'][] = $this->title;

$curUser=Yii::$app->user->identity;
$model->loadData();
?>

<script type="text/javascript">



function showOrgList()
{
 $(orgSelectList).css('display', 'block'); // убираем у модального окна display: none;
					//.animate({opacity: 1}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз   
 //document.getElementById('orgSelectList').css('display', 'block');
 //document.getElementById('action').value = 'selectOrg';
 //document.getElementById('taskEditForm').submit();
 }

function setOrg(id, title)
{
  //$('.collapse-toggle').text("Контрагент: "+title);
  document.getElementById('orgRef').value = id;
  document.getElementById('orgTitle').value = title;
   $(orgSelectList).css('display', 'none');
 }


function saveData()
{
  document.getElementById('taskEditForm').submit(); 
  //window.parent.readTaskChange();   
}

</script> 

<style>
 .flbl {
     width: 175px;     
     padding: 5px;
     padding-left: 15px;
   }
 .btn-local {
    padding:4px;    
    font-size:12px;
}  
</style>
    
<div id='orgSelectList' style='display: none;'> 
<?php    
    Pjax::begin();
    $userArray = $orgModel->getManagerList();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $orgModel,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed small'],
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'orgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    $ret= "<a href='#' onclick='setOrg(".$model['id'].",\"".$model['orgTitle']."\" );' >".$model['orgTitle']."</a>";
                    return $ret;
                },
            ],		
				
            [
	
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
				'filter'    => $userArray,
                
            ],
			
        ],
    ]
);
/*echo Collapse::widget([
    'items' => [
        [            
            'label' => "Контрагент ...",
            'labelOptions' => ['id' => 'orgTitleCollapseLabel'],
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => ['id' => 'orgTitleCollapse']
        ]
    ]
]); 
 */
/*
 
*/
Pjax::end(); 
?>
</div>   
   
  <?php $form = ActiveForm::begin(['id' => 'taskEditForm']); ?>
  <?= $form->field($model, 'action')->hiddenInput(['id' => 'action'])->label(false)?>
  <?= $form->field($model, 'executorRef')->hiddenInput(['id' => 'executorRef'])->label(false)?>
  <?= $form->field($model, 'orgRef')->textInput(['id'=>'orgRef'])->label(false)?></td>        

  <table border='0' width='530px'>
  <tr>   
    <td class='flbl'>Приоритет</td>    
    <td colspan=2>
    <?= $form->field($model, 'taskPriority')->dropDownList($model->getPriorityList())->label(false)?></td>        
    </tr>
    <tr>   
    <td class='flbl'>Контрагент</td>

    <td colspan=2><input id='orgTitle'  name='orgTitle' readonly='true'  style="height: 35px; width: 330px;" value='<?= $model->orgTitle ?>'><input class="btn btn-primary btn-local"  style="width: 25px; height:35px; margin-top:-5px" type="button" value="..." onclick="javascript:showOrgList();"/>        
  </tr>
    
  <tr>    
    <td class='flbl'>Начало исполнения</td>
    <td><?= $form->field($model, 'startDate')->textInput(['id' => 'startDate', 'type' => 'date'])
        ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
    ?></td>  
    <td><?= $form->field($model, 'startTime')->textInput(['id' => 'startTime', 'type' => 'time'])
     ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 1,
        'showSeconds' => true,
        'showMeridian' => false
    ]
    ])->label(false)
    ?></td>  
  </tr>
  <tr>
    <td class='flbl'>Плановое окончание</td>
    <td><?= $form->field($model, 'planDate')->textInput(['id' => 'planDate', 'type' => 'date'])        
    ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
?></td>    
    <td><?= $form->field($model, 'planTime')->textInput(['id' => 'planTime', 'type' => 'time'])
         ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 1,
        'showSeconds' => true,
        'showMeridian' => false
    ]
    ])->label(false)
?></td>    
  </tr>

  <tr>
    <td class='flbl'>Дедлайн</td>
    <td><?= $form->field($model, 'deadDate')->textInput(['id' => 'deadDate', 'type' => 'date'])
            ->widget(DatePicker::class, [
    'language' => 'ru',
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.M.yyyy'
    ]
    ])->label(false)
?></td>    
    <td><?= $form->field($model, 'deadTime')->textInput(['id' => 'deadTime', 'type' => 'time'])
         ->widget(TimePicker::class, [
    'language' => 'ru',
    'pluginOptions' => [
        'minuteStep' => 1,
        'showSeconds' => true,
        'showMeridian' => false
    ]
    ])->label(false)
?></td>    
  </tr>

  <tr>    
    <td colspan=3>
    <?= $form->field($model, 'note')->textarea(['id' => 'note','rows' => 5, 'cols' => 20])->label('Комментарий')?></td> 
    
  </tr>
  
  <tr>
    <td colspan='3' align='right'> 
     <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', ])  ?>
    <?php // <a href="" onclick='saveData();' class='btn btn-primary'>Сохранить</a>     ?>
    </td>  
  </tr>
  
  </table>
  
  
  <?php ActiveForm::end(); ?>
   
   
<?php
/*echo "<pre>";
print_r($model);
echo "</pre>";*/    
?>


   
   
