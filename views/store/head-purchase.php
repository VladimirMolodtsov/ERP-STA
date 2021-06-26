<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;
use yii\bootstrap\Collapse;


$curUser=Yii::$app->user->identity;

$this->title = 'Закупка товара';

$model->preparePurchase();

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>

.btn-small {	
	padding: 2px;	 
	font-size: 10pt;	
} 
 
.gridcell {
	width: 200px;		
	height: 100%;
    display: block;
    font-size: 12px;	
    text-align: left;
	/*background:DarkSlateGrey;*/
}	
.nonActiveCell {
	width: 200px;		
	height: 100%;
    display: block;
    font-size: 12px;	
    text-align: left;
}	

.gridcell:hover{
	background:Silver;
    cursor: pointer;
	color:#FFFFFF;
}
.editcell{
   width: 200px;		
   display:none;
   white-space: nowrap;
}

.label-local{
   width: 190px;		
}


.dval {
  float: right; /* блок занимает ширину содержимого, max-width её ограничивает */
  max-width: 8em;
}


.executed {
    background: #4169E1;
	color:white;
}

.planned {
    background: #C0C0C0;
	color:white;
}


</style>

<script type="text/javascript">


function unReject()
{
   openSwitchWin('store/purchase-unreject&id=<?= $model->id ?>');
}


function showEditBox(boxId)
{

 showId = 'dateBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
}

function setMarked(boxId)
{
    dateVal = '<?=date("Y-m-d")?>';
    openSwitchWin('store/purchase-set-val&id=<?= $model->id ?>&boxid='+boxId+'&dateVal='+dateVal);
}




function closeEditBox(boxId)
{

 showId = 'dateBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    
}

function rmWare(wareId)
{
    openSwitchWin('store/purchase-rmware&id=<?= $model->id ?>&wareref='+wareId);
}

function setDate(boxId)
{
 
 editId = 'edit_'+boxId;   
 dateVal = document.getElementById(editId).value;
 openSwitchWin('store/purchase-set-val&id=<?= $model->id ?>&boxid='+boxId+'&dateVal='+dateVal);
 window.opener.location.reload(false); 
}

function closeSchetList(schetId)
{
  openSwitchWin('store/purchase-set-schet&id=<?= $model->id ?>&schetId='+schetId);  
  window.opener.location.reload(false); 
}


function closeZaprosList(zaprosWareId)
{
  openSwitchWin('store/purchase-zapros-link&id=<?= $model->id ?>&zaprosWareId='+zaprosWareId);  
  window.opener.location.reload(false); 
}


</script>

<h3><?= Html::encode($this->title) ?></h3>
<div class='row'>

<div class="col-md-3" >
<nobr><a href=# onclick="javascript:openWin('site/org-detail&orgId=<?= $model->supplierRef ?>','orgwin')" > <font size='+2'> <?= Html::encode($model->supplierTitle) ?>  </font> </a>
</div>

<div class="col-md-1" >

</div>

<div class="col-md-2" >
<a href='#' class='btn btn-primary' onclick="javascript:openWin('site/reg-contact&id=<?= $model->supplierRef ?>','orgwin')" > Контакты <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> </a></nobr> 
</div>

<div class="col-md-2" >

</div>

<div class="col-md-4" ><nobr><font size='+1'><a href='#' onclick="javascript:showDialog('#schet_list_form');">  
<?php
 if ($model->schetRef == 0)     echo "Счет от поставщика не зарегестрирован";
 else {
     echo "Счет поставщика № ".$model->schetNum." от ".$model->schetDate."";
 }
?>
</font></a></nobr> 
</div>

</div>
<hr>
<?php  

if ($model->schetRef == 0)
{    
$providerWare = $model->getWareInPurcheProvider(Yii::$app->request->get());    
$content= \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $providerWare,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'wareTitle',
				'label'     => 'Номенклатура',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'refZakaz',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                $strSql = 'SELECT formDate, userFIO, title FROM {{%zakaz}},{{%user}},{{%orglist}} where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
               
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret = $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                return $ret;                
                }
            ],		

			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'wareEd',
				'label'     => 'Ед. изм',
                'format' => 'raw',
            ],		
            
        	[
                'attribute' => 'id',
				'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				return "<a href='#' onclick=\"javascript:rmWare('".$model['id']."');\"><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },
            ],		

            
        ],               
    ]
	);
    
    
$content.= "<div class='row'>
    <div class='col-md-9'>
    </div>
    <div class='col-md-3'>
        <input  class='btn btn-primary'  style='width: 200px;' type='button' value='Добавить запрос' onclick='showDialog(\"#zapros_list_form\");' />
    </div>
</div>
";
    
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Состав закупки:  ▼ ",
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 
    
}

if ($model->schetRef > 0)
{
    
$providerWare = $model->getWareInPurcheProvider(Yii::$app->request->get());    
$contentWare= \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $providerWare,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'wareTitle',
				'label'     => 'Номенклатура',
                'format' => 'raw',
            ],		

            [
                'attribute' => 'refZakaz',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                
                if (empty ($model['refZakaz'])) return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -1 )  return "<i>Снабж.</i>";
                if ($model['refZakaz'] == -2 )  return "<b>Управ.</b>";
                $strSql = 'SELECT formDate, userFIO, title FROM {{%zakaz}},{{%user}},{{%orglist}} where
                {{%zakaz}}.ref_user = {{%user}}.id AND {{%zakaz}}.refOrg = {{%orglist}}.id
                AND {{%zakaz}}.id =:refZakaz ';
                $dataList = Yii::$app->db->createCommand($strSql, [':refZakaz' => $model['refZakaz'],])->queryAll();                                        
               
                $ret = $model['refZakaz']." от ".date("d.m",strtotime($dataList[0]['formDate']))."<br>";
                $ret = $dataList[0]['title']."<br><i>".$dataList[0]['userFIO']."</i>";
                return $ret;                
                }
            ],		
            
			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'wareEd',
				'label'     => 'Ед. изм',
                'format' => 'raw',
            ],		
            
        ],               
    ]
	);
    
$contentWare.= "<div class='row'>
    <div class='col-md-9'>
    </div>
    <div class='col-md-3'>
        <input  class='btn btn-primary'  style='width: 200px;' type='button' value='Добавить запрос' onclick='showDialog(\"#zapros_list_form\");' />
    </div>
</div>
";    
    
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Исходный состав закупки:  ▼ ",
            'content' => $contentWare,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
    
    
$provider = $model->getWareInSchetProvider(Yii::$app->request->get());        
$content =  \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
//		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'goodTitle',
				'label'     => 'Номенклатура',
                'format' => 'raw',
            ],		
                        

			[
                'attribute' => 'goodCount',
				'label'     => 'К-во',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'goodEd',
				'label'     => 'Ед. изм',
                'format' => 'raw',
            ],		            

			[
                'attribute' => 'goodSumm',
				'label'     => 'На сумму',
                'format' => 'raw',
            ],		            
            
        ],               
    ]
	);
    
    

 echo Collapse::widget([
    'items' => [
        [
            'label' => "Товары по счету:  ▼ ",
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 

}

?>   

<hr>

<?php $form = ActiveForm::begin(['id' => 'Mainform' ]); ?>

<?= $form->field($model, 'zakazNote')->textarea(['id' => 'zakazNote','row' =>5, 'style'=>'margin:0px; padding:0px; left:0px'])->label('Комментарий к закупке')?>
<div style='text-align: right;'>  
 <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen; margin-top:0px', 'name' => 'actMainform']) ?>
</div> 

<?php ActiveForm::end(); ?>


<h4>Состояние закупки</h4>  
<table class='table table-striped table-bordered' >
<thead>
<tr>
    <th>#</th>
    <th>Согласование закупки </th>
    <th>Отслеживание счета </th>
    <th>Отслеживание товара </th>
    <th>Документы </th>
</tr>
</thead>
<tbody>
<tr>
    <td>0</td>
    <td><?=$model->printEditBox('s1', 0)?></td>
    <td><?=$model->printEditBox('s2', 0)?></td>
    <td><?=$model->printEditBox('s3', 0)?></td>
    <td><?=$model->printEditBox('s4', 0)?></td>
</tr>

<tr>
    <td>1</td>
    <td><?=$model->printEditBox('s1', 1)?></td>
    <td><?=$model->printEditBox('s2', 1)?></td>
    <td><?=$model->printEditBox('s3', 1)?></td>
    <td><?=$model->printEditBox('s4', 1)?></td>
</tr>

<tr>
    <td>2</td>
    <td><?=$model->printEditBox('s1', 2)?></td>
    <td><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 2);    
    ?></td>
    <td><?=$model->printEditBox('s3', 2)?></td>
    <td><?=$model->printEditBox('s4', 2)?></td>
</tr>

<tr>
    <td>3</td>
    <td><?=$model->printEditBox('s1', 3)?></td>
        <td><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 3)?></td>
    <td><?=$model->printEditBox('s3', 3)?></td>
    <td><?=$model->printEditBox('s4', 3)?></td>
</tr>

<tr>
    <td>4</td>
    <td><?=$model->printEditBox('s1', 4)?></td>
        <td><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 4)?></td>
    <td><?=$model->printEditBox('s3', 4)?></td>
    <td></td>
</tr>

<tr>
    <td>5</td>
    <td></td>
        <td><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 5)?></td>
    <td><?=$model->printEditBox('s3', 5)?></td>
    <td></td>
</tr>

<tr>
    <td>6</td>
    <td></td>
        <td ><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 6)?></td>
    <td><?=$model->printEditBox('s3', 6)?></td>
    <td></td>
</tr>

<tr>
    <td>7</td>
    <td></td>
    <td><?php
            if ($model->schetRef == 0)echo "<div class='nonActiveCell'>Нет счета</div>";
            else echo $model->printEditBox('s2', 7)?></td>
    <td><?=$model->printEditBox('s3', 7)?></td>
    <td></td>
</tr>

<tr>
    <td>8</td>
    <td></td>
    <td><div style='color:DarkGreen;text-align:right;font-size:13px;' ><?= $model->printOplateSum() ?></div></td>
    <td><?=$model->printEditBox('s3', 8)?></td>
    <td><?php echo $model->printEditBox('s4', 8); 
     if ($record->isRejectPurchase == 1) {    
     echo "<div style='color:Crimson;font-weight:bold;text-align:left;font-size:13px;' ><br><a href='#' onclick='unReject()'>Восстановить</a>";
     }
     ?></td>
</tr>

</tbody>
</table>
 <a name='status' id='status' ></a> 
<hr>


<h4>Порядок работы:</h4>
<ol> 
 <li> После создания закупки, добавьте в нее необходимые запросы. (кнопка "Добавить запросы").   </li>
 <li> Отправьте сформированную закупку на согласование.   </li>
 <li> Запросите окончательный вариант счета у поствщика.   </li>
 <li> Щелкнув по ссылке "Счет от поставщика не зарегестрирован" свяжите закупку с актуальным счетом. 
      <b>Внимание! после регистрации счета Вы не сможете связать новые запросы с закупкой</b> </li>
 <li> Отправьте закупку с привязанным счетом на согласование.  </li>
 <li> После согласования счета подтвердите размещение заказа у поставщика  </li>
 <li> Зарегестрируйте счет в бухгалтерии. (Счет должен попасть в реестр платежей.)</li>
 <li> Отследите производство и доставку товара</li>
 <li> Отследите оформление документов</li>
 <li> <b>После получения статуса "Поставка закрыта" работа с ней прекращается и редактировать форму закупки нельзя!  </b></li>
</ol>

  
  <!-------------->

<!--- Форма список счетов ----->	
  <div id="schet_list_form" class='popup_form' style='height: 650px; width: 620px; margin-left: -300px; margin-top: -400px;'>
	<span id="schet_list_close"  class='popup_close' onclick='closeDialog("#schet_list_form")' >X</span>	
	<iframe width='600px' height='620px' frameborder='no'   src='index.php?r=store/purchase-schet-list&noframe=1&supplierRef=<?=$model->supplierRef?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>


<!--- Форма список запрочов ----->	
  <div id="zapros_list_form" class='popup_form' style='height: 650px; width: 620px; margin-left: -300px; margin-top: -400px;'>
	<span id="zapros_list_close"  class='popup_close' onclick='closeDialog("#zapros_list_form")' >X</span>	
	<iframe width='600px' height='620px' frameborder='no'   src='index.php?r=store/purchase-zapros-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
   <br>   
  </div>


  
<!--- ******************************************************  --->  
<div id="overlay" class='overlay'></div>
  
  
<?php
/* Закрытие диалогов по щелчку на подложке*/
$js = <<<JS

// по крестику или подложке    
$(document).ready(
function() 
{ 
    
    
 /*Настройка Collapse*/
 $( '.panel-heading').each(function(){
   $( this ).css({'color':'Blue'});
  });

  
	/* Закрытие модального окна*/
	$('#overlay').click( 
	function()
	{ // ловим клик по крестику или подложке
		$('#schet_list_form', '#zapros_list_form')
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	}
	);
}
);

JS;

$this->registerJs($js);
?>  

  