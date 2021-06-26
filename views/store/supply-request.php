<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Collapse;

$this->title = 'Задание на отгрузку';

 $disable = "";

 $curUser=Yii::$app->user->identity;
 if (!($curUser->roleFlg & 0x0010)) { 
 $model->viewMode = 'market';
 $disable = "disabled"; 
 }
 else
 {
  $model->setViewed();         
 }

 $supplyRecord = $model->loadSupplyData();

 if (empty($supplyRecord)) 
 {   
    echo "Данные не найдены. Возможно не коректно задан идентификатор задания.";
    return;
 }

?>	


<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<script type="text/javascript" src="phone.js"></script>  
<link rel="stylesheet" type="text/css" href="phone.css" />

<style>
.local-buttons
{
	width:200px;
}

.local_btn {
	font-size: 12px;
	margin:4px;
	padding:4px;
	width:200px;
    padding: 2px;
	font-size: 10pt;
} 

.local_lbl
{
	margin:4px;
	padding: 2px;
	font-size: 10pt;
	border:1px solid;
	width: 200px;
	border-radius: 4px;
	display:inline-block;
	position:relative;
	top:2px;
    text-align:center;
	
}

</style>

<script type="text/javascript">
<?php

$deliverList = $model->getDeliversList();
?>
var btnAcceptRequest = <?php if($supplyRecord->supplyState & 0x00001) echo 1; else echo 0;?>;
var btnRejectRequest = <?php if($supplyRecord->supplyState & 0x00004) echo 1; else echo 0;?>;
var btnAcceptInDeliver = <?php if($supplyRecord->supplyState & 0x00002) echo 1; else echo 0;?>;
var btnIsFinish = <?php if($supplyRecord->supplyState & 0x00008) echo 1; else echo 0;?>;


function addJobRequest()
{
     openWin('store/purchase-zakaz&zakazref=<?=  $model->extSupplyData['refZakaz'] ?>','purchWin');
}


function createPurchase(id)
{
     openWin('store/create-good-request&zakazref='+id,'purchWin');
}

function openZapros(id)
{    
    openWin('store/purchase-zakaz&id='+id,'purchWin');
}

function openPurchase(id)
{
    openWin('store/purchase&id='+id,'purchWin');
}


function acceptInWork()
{
	if (btnAcceptRequest == 0 )
	{
		document.getElementById('btnAcceptRequest').value = 'Принято';
		document.getElementById('btnAcceptRequest').style ='background-color: Green;color:white;';
		btnAcceptRequest =1;
		document.forms["w1"]["supplyform-isacceptinwork"].value=1;

		document.getElementById('btnRejectRequest').value = 'Отказать';
		document.getElementById('btnRejectRequest').style ='background-color: White; color:Crimson';
		btnRejectRequest =0;
		document.forms["w1"]["supplyform-isreject"].value=0;
	}
    /*else	
	{
		document.getElementById('btnAcceptRequest').value = 'Принять в работу';
		document.getElementById('btnAcceptRequest').style ='background-color: white;color:Green;';
		btnAcceptRequest =0;
		document.forms["w1"]["supplyform-isacceptinwork"].value=0;
	}*/	
}

function rejectInWork()
{
	if (btnRejectRequest == 0 )
	{
		document.getElementById('btnRejectRequest').value = 'Отказано';
		document.getElementById('btnRejectRequest').style ='background-color: Crimson;';
		btnRejectRequest =1;
		document.forms["w1"]["supplyform-isreject"].value=1;
    	
        document.getElementById('btnAcceptRequest').value = 'Принять в работу';
		document.getElementById('btnAcceptRequest').style ='background-color: white;color:Green;';
        document.forms["w1"]["supplyform-isacceptinwork"].value=0;
        btnAcceptRequest =0;

	}/*else	
	{
		document.getElementById('btnRejectRequest').value = 'Отказать';
		document.getElementById('btnRejectRequest').style ='background-color: #286090';
		btnRejectRequest =0;
		document.forms["w1"]["supplyform-isreject"].value=0;
	}*/	
}

function acceptInDeliver()
{
	if (btnAcceptInDeliver == 0 )
	{
		document.getElementById('btnAcceptInDeliver').value = 'Доставляется';
		document.getElementById('btnAcceptInDeliver').style ='background-color: Green;color:white;';
		btnAcceptInDeliver =1;
		document.forms["w1"]["supplyform-isacceptindeliver"].value=1;
	}else	
	{
		document.getElementById('btnAcceptInDeliver').value = 'В доставку';
		document.getElementById('btnAcceptInDeliver').style ='background-color: white;color:Green;';
		btnAcceptInDeliver =0;
		document.forms["w1"]["supplyform-isacceptindeliver"].value=0;
	}	
}

function setIsFinish()
{
	if (btnIsFinish == 0 )
	{
		document.getElementById('btnIsFinish').value = 'Доставка произведена';
		document.getElementById('btnIsFinish').style ='background-color: Green;color:white;';
		btnIsFinish =1;
		document.forms["w1"]["supplyform-isfinished"].value=1;
	}else	
	{
		document.getElementById('btnIsFinish').value = 'Доставлен';
		document.getElementById('btnIsFinish').style ='background-color: white;color:Green;';
		btnIsFinish =0;
		document.forms["w1"]["supplyform-isfinished"].value=0;
	}	
}

function reSyncRemain()
{
    openSwitchWin("store/resync-remain&zakazid=<?= $model->extSupplyData['refZakaz'] ?>");
}
</script>
 
 
  <h3>Заявка на отгрузку № <?= $model->extSupplyData['id'] ?>. </h3>
    
  <p align='right'> от <?= $model->extSupplyData['userFIO'] ?></p>  
  <p> Клиент <?= $model->extSupplyData['title'] ?></p>
  <table border='0' width='80%'>
  <tr><td> Счёт № <?= $model->extSupplyData['schetNum'] ?>  от 
  <?= date ("d.m.Y", strtotime($model->extSupplyData['schetDate']))?></td> 
  <td>Сумма счета:  <?= Html::encode($model->extSupplyData['schetSumm']) ?> 
  Оплачено:     <?= Html::encode($model->extSupplyData['summOplata']) ?></td></tr>
  </table>
<br>

<?php
/*
          $this->detailList[$i]['zaprosRef'] = 0;
          $this->detailList[$i]['zaprosDate'] = '';          
          $this->detailList[$i]['zaprosActive'] = 0;
          $this->detailList[$i]['status']    = 0; 
          $this->detailList[$i]['purchaseRef'] = 0;
          $this->detailList[$i]['purchaseCreation'] = '';          
          $this->detailList[$i]['purchaseisFinished'] = 0;
          $this->detailList[$i]['purchaseisReject'] = 0;          
*/

$content = "<table class='table table-strip' width='800px'   style='padding:3px' > ";
$content .= "<thead><tr><th style='padding:3px'>Наименование</th><th style='padding:3px'>К-во </th><th style='padding:3px'>Ед.изм </th>
<th style='padding:3px'>Доставить</th><th style='padding:3px'>В доставках</th>
<th style='padding:3px'>Склад</th><th style='padding:3px'>Закупка</th></tr></thead>";
  for ($i=0; $i<count($model->detailList);$i++ )
  {
	if ($model->detailList[$i]['isActive'] == 0){continue;}  
    $content .="<tr>\n";
		if (empty($model->detailList[$i]['good'])){		$content .="<td style=padding:3px'> ".$model->detailList[$i]['initialZakaz']."</td>\n"; }
									  else {		$content .="<td style=padding:3px'> ".$model->detailList[$i]['good']."</td>\n"; }
		
	$content .="<td 'style=padding:3px'>".$model->detailList[$i]['count']."</td>\n";
	$content .="<td 'style=padding:3px'>".$model->detailList[$i]['ed']."</td>\n";
    if (empty($model->detailList[$i]['sumCnt'])) $sumCnt = 0;
                                            else $sumCnt = floatval($model->detailList[$i]['sumCnt']);
    $remain = floatval($model->detailList[$i]['count']) - $sumCnt;
    $content .="<td 'style=padding:3px'>".$remain."</td>\n";
    $content .="<td 'style=padding:3px'>".$sumCnt."</td>\n";
    $content .="<td 'style=padding:3px'>".$model->detailList[$i]['wareRemain']."</td>\n";
    
    $addNew="<a href='#' onclick='javascript:createPurchase(".$model->detailList[$i]['id'].");'> 
              <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a>";
    
    $openPurchase ="<a href='#' onclick='javascript:openPurchase(".$model->detailList[$i]['purchaseRef'].");'> 
              Зак.№ ".$model->detailList[$i]['purchaseRef']." от ".date("d.m",strtotime($model->detailList[$i]['purchaseCreation']))."</a>\n";    

    $openZapros ="<a href='#' onclick='javascript:openZapros(".$model->detailList[$i]['zaprosRef'].");'> 
              Зап.№ ".$model->detailList[$i]['zaprosRef']." от ".date("d.m",strtotime($model->detailList[$i]['zaprosDate']))."</a>\n";
    
    $purch =$addNew;
    if (!empty($model->detailList[$i]['purchaseRef']))
    {
        if ($model->detailList[$i]['purchaseisReject'] != 1) $purch =$openPurchase;        
    }
    elseif (!empty($model->detailList[$i]['zaprosRef']))
    {
        if ($model->detailList[$i]['zaprosActive'] == 1) $purch =$openZapros;        
    }        
    
    $content .="<td 'style=padding:3px'>".$purch."</td>\n";             
	$content .="</tr>\n";
  }

  
  
  
$content .=" </table>  
";
  
    if (!empty($model->detailList[0]['wareSyncDate']))
        $content .="<p align='right'>Остатки на складе приведены на дату:".date("d.m.Y h:i", strtotime($model->detailList[0]['wareSyncDate']))."\n";
    else 
        $content .="<p align='right'>Остатки на складе не определены\n";

    $content .=" <a href='#' onclick='reSyncRemain();'><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></a></p><br> ";

$content .=" <div class='row'>
  	<div class='col-md-9' >

	</div>
   	<div class='col-md-3' style='text-align:right;'>
        <input type='button' class='btn btn-default' value='Закупка услуг' onclick='addJobRequest();'>
	</div>

</div>";
    
echo Collapse::widget([
    'items' => [
        [
            'label' => 'Товары в заявке',
            'content' => $content,
            'contentOptions' => ['class' => 'in'],
            'options' => ['class' => 'in']
        ],
    ]
]);  

?>



<?php //print_r($model->detailList) ?>



  <table width=100% border='0'>
  <tr>
    <td style="padding:5px">Дата отгрузки: <b><?= $supplyRecord->supplyDate ?></b>	</td>     
  </tr>
  <tr> 
    <td style="padding:5px"> Тип доставки: <b> <?= $model->listSupplyType[$supplyRecord->supplyType] ?> </b> &nbsp;  </td>
    <td rowspan='2' colspan='2' style="padding:5px">
    <div style='border: 1px LightGray solid; width:100%'><?= $supplyRecord->dstNote?>&nbsp;</div></td>    
  </tr>
  
  <tr>
    <td style="padding:5px">Контактный телефон: <b><?= $supplyRecord->contactPhone ?></b></td> 
    <td style="padding:5px">Контактное лицо: <b><?= $supplyRecord->contactFIO ?> </b></td>
    <td style="padding:5px">E-mail: <b><?= $supplyRecord->contactEmail?> </b></td>
  </tr>  
  </table>
    
 <p style="padding:5px">Адрес: <?= $supplyRecord->adress ?></p>
 <p style="padding:5px">Дополнительная информация:  <pre style='background: White;'> <?= $supplyRecord->requestNote?></pre>

<?php 
 if ($model->viewMode == 'market') { ?>	  
 <p> Комментарий к исполнению: <pre style='background: White;'>	<?= $supplyRecord->supplyNote ?></pre>
 <?php } 
 else {?>
 <?php $form = ActiveForm::begin(); ?>   
 <?= $form->field($model, 'id')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'viewMode')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isAcceptInWork')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isReject')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isAcceptInDeliver')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isFinished')->hiddenInput()->label(false)?>	

 <hr> 

 <div class='row'  >   
  	<div class="col-md-9" >
	<?= $form->field($model, 'supplyNote')->textarea(['rows' => 2, 'cols' => 55])->label('Комментарий к исполнению')?>		
	</div>
   	<div class="col-md-3" style='margin-top:25px'>
    
	</div>
</div>
 <?php } // заполнение комментария ?>
 
 <br>
   
<div class='row'  >   
    <div class="col-md-8">
 <table class='table '>
 <!--- Просмотрено --->
 <tr> 
    <td valign='top'>
    <?php  if ($supplyRecord->viewManagerRef > 0) 
           echo "<div class='local_lbl' style='background: Green; border-color:Green; color: White;' >Просмотренно </div> ";
      else echo "<div class='local_lbl' style='background: White; border-color:Black; color: Black;' >Не просмотренно </div> " ;
    ?>
    </td>
    <td width='75px' align='right'>
		 Время просмотра:  
    </td> 
    <td  style='padding:10px;'>
		 <?php if ($supplyRecord->viewManagerRef > 0) echo date('d.m.Y', strtotime($supplyRecord->execView) )."<br>".$model->extSupplyData['viewManager']; ?>
    </td>
 </tr> 
 
 <!--- Принято в работу --->
 <tr>
 <td valign='top' >
    <?php 
        if ($supplyRecord->supplyState & 0x00001) {  //Уже Принято ?> 
        
        <input <?= $disable ?>  id='btnAcceptRequest' class="btn btn-primary local-buttons local_btn"  
                style='background: Green; border-color:Green; color: White;'
                type="button" value="Принять в работу" 	onclick="javascript:acceptInWork();"/> 	
        <?php 
        }  //echo "<div class='local_lbl' style='background: Green; border-color:Green; color: White;' >Принято </div>";
        else{  //Принять 
       ?>
        <input <?= $disable ?>  id='btnAcceptRequest' class="btn btn-primary local-buttons local_btn"  
                style='background: White; border-color:Black; color: Green;'
                type="button" value="Принять в работу" 	onclick="javascript:acceptInWork();"/> 	
     <?php } ?>
     
 </td>
 <td width='75px' align='right'>
 
 </td> 
 <td  style='padding:10px;'>    

    <?php 
          if($supplyRecord->supplyState & 0x00004) {$style="background-color: Crimson;"; $val="Отказано";}
                                              else {$style="background-color: White; color:Crimson;"; $val="Отказать";}
    ?> <input <?= $disable ?> id='btnRejectRequest' class="btn btn-primary local_btn"  style="<?= $style ?>" 
              type="button" value="<?= $val ?>" 	onclick="javascript:rejectInWork();"/>

 </td>
 
 
 </tr>
 
 <!--- Принято в работу ---> 
 <tr> 
 <td valign='top'rowspan='2' >
     <?php //Уже Доставляется
        if ($supplyRecord->supplyState & 0x00002)  { ?>

        <input  <?= $disable ?> id='btnAcceptInDeliver' class="btn btn-primary local-buttons local_btn"  
               style='background: Green; border-color:Black; color: White;'
               type="button" value="Доставляется" 	onclick="javascript:acceptInDeliver();"/>
        
        
        <?php }//  echo "<div class='local_lbl' style='background: Green; border-color:Green; color: White;' >Доставляется</div>";
        else{  //В доставку
       ?>
       <input  <?= $disable ?> id='btnAcceptInDeliver' class="btn btn-primary local-buttons local_btn"  
               style='background: White; border-color:Black; color: Green;'
               type="button" value="В доставку" 	onclick="javascript:acceptInDeliver();"/>
        
     <?php } ?>
 </td>
  
 <td width='75px' align='right'> Ожидаемая дата <br> доставки:</td> 
 <td  style='padding:10px;'>
 <?php if ($model->viewMode == 'market') echo $model->supplyDate; else {?>
    <?= $form->field($model, 'supplyDate')->textInput(['class' => 'tcal',])->label(false)?> 
 <?php } ?>                           
 </td>
</tr>

<tr>

 <td width='75px' align='right'>
         Информация по доставке:  
 </td> 
 <td  style='padding:10px;'>    
   <?php if ($model->viewMode == 'market') echo $model->execNum; else {?> 
		 <?= $form->field($model, 'execNum')->label(false)?> </div>
   <?php } ?>                                 
 </td>

</tr>

<!--- Доставлен ---> 
<tr> 
 <td valign='top' rowspan=3>
    <?php // ?>


     <?php //доставлено
        if ($supplyRecord->supplyState & 0x00008){  ?>

        <input <?= $disable ?> id='btnIsFinish' class="btn btn-primary local-buttons local_btn"  
            style='background-color: Green;color:white;'
            type="button" value="Доставка произведена"  onclick="javascript:setIsFinish();"/>               

        <?php } //  echo "<div class='local_lbl' style='background: Green; border-color:Green; color: White;' >Доставка произведена</div>";
        else{  //В доставку
       ?>
        <input <?= $disable ?> id='btnIsFinish' class="btn btn-primary local-buttons local_btn"  
            style='background: White; border-color:Black; color: Green;'
            type="button" value="Доставлен"  onclick="javascript:setIsFinish();"/>               
     <?php } ?>

    
 
 </td>
 
 <td width='75px' align='right'>
        Фактический вес         
 </td> 

 <td style='padding:10px;'>
 <?php if ($model->viewMode == 'market') echo $model->execWeight; else {?>
		<?= $form->field($model, 'execWeight')->label(false)?>
 <?php } ?>                       
 </td>
</tr>
 

<tr>
 <td width='75px' align='right'>
        Сумма затрат
    </td> 
 <td style='padding:10px;'>		
  <?php if ($model->viewMode == 'market') echo $model->execValue; else {?>
        <?= $form->field($model, 'execValue')->label(false)?>
 <?php } ?>               
 </td>
</tr>

<tr>
 <td width='75px' align='right'>
        Дата фактического выполнения        
 </td> 

 <td style='padding:10px;'>		
 <?php if ($model->viewMode == 'market') echo $model->finishDate; else {?>
        <?= $form->field($model, 'finishDate')->textInput(['class' => 'tcal',])->label(false)?>
 <?php } ?>       
 </td>
</tr>

 
 </table>
 </div>
  
 <div class="col-md-4" >		
  
    <div style='height:250px; overflow: auto;'>
    <?php echo $deliverList; ?> 
    <div style='width:100%; text-align:right;' ><a href='#' 
    onclick='javascript:openWin("store/deliver-zakaz&action=create&requestId= <?= $model->id ?>&schetId= <?= $model->refSchet ?>", "deliverWin");' >
    <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a></div>   
    </div>
 
    <div style='text-align:right;'>   
        <div style="padding:10px;">
        <a class="btn btn-default local-buttons" href="#"  style='padding:2px' onclick="javascript:openWin('store/print-supply-request&id=<?= $supplyRecord->id ?>','printWin');" ><img src='img/printer.png' alt='Печать'></a>
        </div>
        <div style="padding:10px;">
        <a class='btn btn-warning local-buttons' href="#" onclick="javascript: window.close(); "> Выйти без сохранения </a>
        </div>
        <div style="padding:10px;">
        <?= Html::submitButton('Сохранить и выйти', ['class' => 'btn btn-primary local-buttons']) ?>
        </div>
    </div>
 
 </div>
 
</div>    
<?php 
if ($model->viewMode != 'market') { 	  
 ActiveForm::end(); 
}?>

<script>
</script>


