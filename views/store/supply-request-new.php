<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Collapse;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use kartik\grid\GridView;

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
		document.getElementById('btnRejectRequest').value = 'Отменено';
		document.getElementById('btnRejectRequest').style ='background-color: Crimson; color:White';
		btnRejectRequest =1;
		document.getElementById("supplyform-isreject").value=1;
	}else	
	{
		document.getElementById('btnRejectRequest').value = 'Отменить';
		document.getElementById('btnRejectRequest').style ='background-color: White; color:Crimson';
		btnRejectRequest =0;
        document.getElementById("supplyform-isreject").value=0;
	}	
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

function showEditBox(boxId)
{

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
 
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
    
}

function closeEditBox(boxId)
{
if (boxId == "0") {return;}

 showId = 'viewBox_'+boxId;
 editId = 'editBox_'+boxId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    

}

function addScenario()
{
  openWin('store/scenario-editor','childWin');  
}

function chngScenario()
{
 n = document.getElementById("scenario").options.selectedIndex;
 val = document.getElementById("scenario").options[n].value;
    
 openSwitchWin('store/set-scenario&requestId=<?=  $supplyRecord->id ?>&scenId='+val);       
}


function setDone(i)
{
    
 id = "edit_Done"+i;    
 val = document.getElementById(id).value;
    
 openSwitchWin('store/set-supply-status&requestId=<?=  $supplyRecord->id ?>&statusId='+i+'&val='+val);       
}

function unSetDone(i)
{
    
 id = "edit_Done"+i;    
 val = document.getElementById(id).value;
    
 openSwitchWin('store/set-supply-status&requestId=<?=  $supplyRecord->id ?>&statusId='+i+'&val=0');       
}


function openSrcRequest()
{
 openWin('market/market-request-supply&schetId=<?= $supplyRecord->refSchet ?>&noframe=1','requestWin');       
}


function openSchet()
 {
    openWin('market/market-schet&id=<?= $supplyRecord->refSchet ?>','schetWin');
 }


function openDocList()
{   
//Показ диалога
    $(".modal-dialog").width(600);
    $('#docListForm').modal('show');   
}
 
function closeDocList(id, title)
{
    document.getElementById('recordId').value=document.getElementById('id').value;
    document.getElementById('dataId').value=id;
    document.getElementById('dataType').value='lnkDocRequest';
    //document.getElementById('dataVal').value=document.getElementById(idx).value;
    saveData()()
}

function saveField(id, type)
{
    idx= id+type;
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    saveData();
 }
 
function lnkRemove(id)
{
    document.getElementById('recordId').value=id;
    document.getElementById('dataType').value='lnkRemove';
    saveData();
}
 
function saveData()
{   
    $(document.body).css({'cursor' : 'wait'});   
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=store/save-lnk-doc-request',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if (res.isReload) document.location.reload(true); 
            $(document.body).css({'cursor' : 'default'});                        
        },
        error: function(){
            alert('Error while saving data!');
            $(document.body).css({'cursor' : 'default'});            
        }
    });	
} 
</script>
 
 
 
  <h3>Заявка на отгрузку № 
  <?php
/*  if ($curUser->id != $model->extSupplyData['refManager'] )
        echo $model->extSupplyData['id'];
  else */
       echo "<a href='#' onclick='openSrcRequest();'>".$model->extSupplyData['id']."</a>";       
  
  
  ?>
  </h3>
  <p> Клиент <font size="+1"><?= $model->extSupplyData['title'] ?></font></p>  
  <p> Менеджер: <?= $model->extSupplyData['userFIO'] ?> </p>    
  <table border='0' width='80%'>
  <tr>
  <td> Счёт №  <span class='clickable' onclick='openSchet()'><?= $model->extSupplyData['schetNum'] ?>  от 
  <?= date ("d.m.Y", strtotime($model->extSupplyData['schetDate']))?> </span></td> 
 
  <td>Сумма счета:  <?= Html::encode($model->extSupplyData['schetSumm']) ?> </td>
  <td>Оплачено:     <?= Html::encode($model->extSupplyData['summOplata']) ?></td>
  </tr>
  </table>
<br>

<?php
echo "<pre>";
//print_r ($model->detailList);
echo "</pre>";
$N = count($model->detailList);

$content = "<table class='table table-strip' width='800px'   style='padding:3px' > ";
$content .= "<thead><tr><th style='padding:3px'>Наименование</th><th style='padding:3px'>К-во </th><th style='padding:3px'>Ед.изм </th>
<th style='padding:3px'>Доставить</th><th style='padding:3px'>В доставках</th>
<th style='padding:3px'>Склад</th><th style='padding:3px'>Закупка</th></tr></thead>";
  for ($i=0; $i<$N;$i++ )
  {
	
    $content .="<tr>\n";
    $content .="<td style=padding:3px'> ".$i."</td>\n"; 		
	$content .="<td style=padding:3px'> ".$model->detailList[$i]['wareTitle']."</td>\n"; 		
	$content .="<td 'style=padding:3px'>".$model->detailList[$i]['wareCount']."</td>\n";
	$content .="<td 'style=padding:3px'>".$model->detailList[$i]['wareEd']."</td>\n";
    if (empty($model->detailList[$i]['sumCnt'])) $sumCnt = 0;
                                            else $sumCnt = floatval($model->detailList[$i]['sumCnt']);
    $remain = floatval($model->detailList[$i]['wareCount']) - $sumCnt;
    $content .="<td 'style=padding:3px'>".$remain."</td>\n";
    $content .="<td 'style=padding:3px'>".$sumCnt."</td>\n";
    $content .="<td 'style=padding:3px'>".$model->detailList[$i]['wareRemain']."</td>\n";
    
    $addNew="<a href='#' onclick='javascript:createPurchase(".$model->detailList[$i]['id'].");'> 
              <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a>";
    
    $openPurchase ="<a href='#' onclick='javascript:openPurchase(".$model->detailList[$i]['purchaseRef'].");'> 
              Зак.№ ".$model->detailList[$i]['purchaseRef']." от ".date("d.m",strtotime($model->detailList[$i]['purchaseCreation']))."</a>\n";    

    $openZapros ="<a href='#' onclick='javascript:openZapros(".$model->detailList[$i]['zaprosRef'].");'> 
              Зап.№ ".$model->detailList[$i]['zaprosRef']." от ".date("d.m",strtotime($model->detailList[$i]['zaprosDate']))."</a>\n";
    
    $purch ="";
    if (!empty($model->detailList[$i]['purchaseRef']))
    {            
            $purch =$openPurchase;        
            
            if ($model->detailList[$i]['purchaseisReject'] == 1) {
            $purch ="<span style='color:Crimson;text-decoration: line-through;'>".$openPurchase."</span>";        
            
            }        
    }
    elseif (!empty($model->detailList[$i]['zaprosRef']))
    {
        if ($model->detailList[$i]['zaprosActive'] == 1) $purch =$openZapros;        
    }        
    else
    {    
    $purch =$addNew;
    }
    
    //$purch =$model->detailList[$i]['purchaseRef'];
    
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

 <?php $form = ActiveForm::begin(); ?>   
 <?= $form->field($model, 'id')->hiddenInput(['id' => 'id'])->label(false)?>	
 <?= $form->field($model, 'viewMode')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isAcceptInWork')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isReject')->hiddenInput(['id' => 'supplyform-isreject'])->label(false)?>	
 <?= $form->field($model, 'isAcceptInDeliver')->hiddenInput()->label(false)?>	
 <?= $form->field($model, 'isFinished')->hiddenInput()->label(false)?>	


  <table width=100% border='0'>
  <tr>
    <td style="padding:5px;" colspan='3'>Комментарий к заявке:<br>     
    <div style='border: 1px LightGray solid; width:100%; height:70px; overflow:auto;'><?= $supplyRecord->requestNote ?>&nbsp;</div>
    </td>
  </tr>

  <tr> 
    <td style="padding:5px;  width:200px"> Доставка: </td>
    <td style="padding:5px"><b> <?= $model->listSupplyType[$supplyRecord->supplyType] ?> </b>        
  </tr>

  <tr>
    <td style="padding:5px;" colspan='3'>Комментарий к доставке:<br>     
    <div style='border: 1px LightGray solid; width:100%; height:70px; overflow:auto;'><?= $supplyRecord->dstNote ?>&nbsp;</div>
    </td>
  </tr>

  
  <tr> 
    <td style="padding:5px"> Ожидаемая дата доставки: </td>
    <td style="padding:5px"> <b><?php if ($model->viewMode == 'market') echo $model->supplyDate; else {?>
    <?= $form->field($model, 'supplyDate')->textInput(['class' => 'tcal',])->label(false)?> 
 <?php } ?>                           </b> </td>
  </tr>

   <tr> 
    <td style="padding:5px"> Адрес: </td>
    <td style="padding:5px" colspan='3'> <b><?= $supplyRecord->adress ?> </b> </td>
  </tr>
 
  <tr> 
    <td style="padding:5px"> Контактное лицо: </td>
    <td style="padding:5px"> <b><?= $supplyRecord->contactFIO ?> </b> </td>    
  </tr>
  
  
  <tr>
    <td style="padding:5px">Контактный телефон: </td> 
    <td style="padding:5px"> <b><?= $supplyRecord->contactPhone ?></b> </b></td>
  </tr>  
  
  
   <tr>
    <td style="padding:5px">Транспортная компания: </td> 
    <td style="padding:5px"> <b><?= $supplyRecord->transportName ?></b> </b></td>
   </tr>  

   <tr>
    <td style="padding:5px">Грузополучатель: </td> 
    <td style="padding:5px"> <b><?= $supplyRecord->consignee ?></b> </b></td>
   </tr>  

   <tr>
    <td style="padding:5px">Плательщик: </td> 
    <td style="padding:5px"> <b><?= $supplyRecord->payer?></b> </b></td>
   </tr>  
  
   <tr>
    <td style="padding:5px">Доставить: </td> 
    <td style="padding:5px"> <b><?php 
    if ( $supplyRecord->isToTerminal == 1)  echo " До терминала"; 
                                      else  echo " По адресу"; 
    ?>
    
    </b> </b></td>
   </tr>  
  
  <tr>
  
  <td colspan='3'> 
    <div class='spacer'></div>
      <?php
      
      echo GridView::widget(
    [
        'dataProvider' => $model->getTransportDocProvider(Yii::$app->request->get()),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],        
      //  'filterModel' => false,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){
                 return $model['orgTitle'];
                }

            ],

            [
                'attribute' => 'lnkSum',
                'label' => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){
                 
                 $id = $model['lnkRef']."lnkSum";
                 $action =  "saveField(".$model['lnkRef'].", 'lnkSum');"; 
                 return Html::textInput( 
                          $id, 
                          $model['lnkSum'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                
                }
            ],


            [
                'attribute' => 'docIntNum',
                'label' => 'Вх.№ в ERP',        
                'format' => 'raw',
            ],

            [
                'attribute' => 'docOrigNum',
                'label' => 'Документ ',        
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){
                return $model['docOrigNum']." от ".$model['docOrigDate'] ;               
                }
            ],
            
            

            
            [
                'attribute' => '#',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column){
                $action = "lnkRemove(".$model['lnkRef'].")";
                 return \yii\helpers\Html::tag( 'div', '' ,
                   [
                     'class'   => 'glyphicon glyphicon-remove clickable',
                     'onclick' => $action,
                     'style'   => 'color:Crimson',
                   ]);
  
                }

            ],
            
        ],
    ]
);

      
 
     $action="openDocList();";
     echo \yii\helpers\Html::tag( 'div', '' ,
                   [
                     'class'   => 'glyphicon glyphicon-plus clickable',
                     'onclick' => $action,
                   ]);
  
      
      ?>    
    </td>
  
  </tr>
  
  
  
  
  </table>

<?php 
 if ($model->viewMode == 'market') { ?>	  
    <p> Комментарий к исполнению: <pre style='background: White;'>	<?= $supplyRecord->supplyNote ?></pre>
 <?php } 
 else {?>
 <hr> 

 <div class='row'  >   
  	<div class="col-md-9" >
	<?= $form->field($model, 'supplyNote')->textarea(['style' => 'width:920px; height:70px;' ])->label('Комментарий к исполнению')?>		
	</div>
   	<div class="col-md-3" style='margin-top:25px'>
    
	</div>
</div>
 <?php } // заполнение комментария ?>
 
<div class='spacer'></div>
 
<div class='row'>      
     <div class="col-md-3">
    <?php  if ($supplyRecord->viewManagerRef > 0) 
           echo "<div class='local_lbl' style='background: Green; border-color:Green; color: White;' >Просмотренно </div> ";
      else echo "<div class='local_lbl' style='background: White; border-color:Black; color: Black;' >Не просмотренно </div> " ;
    ?>
     </div>
     <div class="col-md-1">
     </div>
     <div class="col-md-4">
		 <?php if ($supplyRecord->viewManagerRef > 0) echo date('d.m.Y', strtotime($supplyRecord->execView) )." ".$model->extSupplyData['viewManager']; ?>     
     </div>
          
     <div class="col-md-3">
      <?php        
      if($supplyRecord->supplyState & 0x00004) {$style="background-color: Crimson;"; $val="Отменено";}
                                          else {$style="background-color: White; color:Crimson;"; $val="Отменить";}
      if ($curUser->id == $model->extSupplyData['refManager'] )                                           
      {
         echo "<input  id='btnRejectRequest' class='btn btn-primary local_btn'  style='".$style."' type='button' value='".$val."' 	onclick='javascript:rejectInWork();' />";
       }
      ?>            
     </div>
     <div class="col-md-1">
     </div>
</div>

<br>


<?php
    $statusList = $model->getStatus();	  
?>
<div class='row'  >   
 <div class="col-md-5">
    <?= $form->field($model, 'scenario')->dropDownList($model->getScenarioVariant(), 
    [
    'id' => 'scenario', 
    'onchange' => 'chngScenario();',
    'value' => $model->scenario,
    ])->label('Сценарий'); ?>    
</div>

 <div class="col-md-2" style='padding:22px;'>
    <input type='button' class="btn btn-primary" onclick='addScenario();' value='Редактировать сценарии'>
 </div>

  <div class="col-md-2" style='padding:22px;'>

 </div>

</div>
<br>     
<a name='status'> </a>
<div class='row'  >   
    <div class="col-md-9">
    <table class='table table-bordered'>
    <tr>
    <td width='50px;'>#</td>
    <td>Стадия</td>
    <td width='75px;' >Дата <br> выполнения</td>
    <td></td>
    <td width='' >Норм<br>(д.)</td>
    <td width='50px;' >Ожидаемая<br> Дата</td>
    </tr>

    
<?php
    /*перебираем статусы*/ 
/*
  $statusList[$i]['title'] = $statusTitles[1];
    $statusList[$i]['value'] = $statusList[0][$fld];
    $statusList[$i]['inUse'] = $scenarioList[0][$fld];
 $statusList[$i]['wait'] = 0;
*/    
    
  $n = count($statusList);  
  $dayToEnd = 0;
  $lastDay=time();
  $lastStat=0;
  for ($i=1; $i<=$n; $i++)
  {
    if (!(empty($statusList[$i]['value']) || $statusList[$i]['value'] == '0000-00-00'))   
        $lastStat = $i;
  }
  
  for ($i=1; $i<=$n; $i++)
  {
  $style = "color:Black;";    
  if ($statusList[$i]['inUse'] == 0) continue;    
  /*if ($statusList[$i]['inUse'] == 0) $style = "text-decoration:line-through; color:Grey;";    
                               else  $style = "color:Black;";*/
                               
  if (empty($statusList[$i]['value']) || $statusList[$i]['value'] == '0000-00-00') 
  {
      $value = "&nbsp;";
      $dayToEnd+=$statusList[$i]['wait'];
  }
   else 
  {  
     $lastDay = strtotime($statusList[$i]['value']);                                   
     $value = date("d.m.Y", $lastDay );
     $dayToEnd = 0;
     
  }
  
  $id = "Done".$i;                                   
  $ret ="<div id='viewBox_".$id."' class='gridcell'  style='width:100px;  text-align:left;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$value."</div>"; 
  $ret.="<div id='editBox_".$id."' class='editcell'  style='width:100px;' ><nobr>";
  $ret.="<input  id='edit_".$id."' class='tcal'   style='width:75px;' value='".$value."'>";
  $ret.="<a href ='#status' onclick=\"javascript:setDone('".$i."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
  $ret.="<a href ='#status' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
  $ret.="</nobr></div>";
 
      
 if ($i> $lastStat) $w=date("d.m.Y",3600*24*$dayToEnd+$lastDay);
                else $w="&nbsp;";
 $wstyle="color:Grey;";
 if($i==11) {
  $planTime=strtotime($supplyRecord->readyPlan);
  if($planTime > 100) { $w = date("d.m.Y",$planTime); $wstyle="color:black;background:LightGreen;";}
  }
                                                 
  echo"    
    <tr>
    <td><div style='$style' >$i</div></td>
    <td><div style='width:300px;$style'>".$statusList[$i]['title']."</div></td>
    <td>".$ret."</td>
    <td><a href='#status' onclick=\"unSetDone('".$i."');\"><span class='glyphicon glyphicon-trash' aria-hidden='true'></span> </a></td>
    <td><div  style='$style'>".$statusList[$i]['wait']."</div></td>
    <td><div  style='".$wstyle."'>".$w."</div></td>
    </tr>
  ";  
  }         
?>    
 </table>
<br>
 <p>Дней до окончания: <b><?= $dayToEnd ?></b> Ожидаемая дата выполнения <b><?= date("d.m.Y", $lastDay+3600*24*$dayToEnd ) ?></b>
 
 </p>

 </div>

    
    
 <div class="col-md-3" >		
  
    <div style='height:200px; overflow: auto;'>
    <?php echo $deliverList; ?> 
    <div style='width:100%; text-align:right;' ><a href='#' 
    onclick='javascript:openWin("store/deliver-zakaz&action=create&requestId= <?= $model->id ?>&schetId= <?= $model->refSchet ?>", "deliverWin");' >
    <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></a></div>   
    </div>
 
 <?php
  $n = count($model->inDeliverList);  
  $factWeight = 0;
  $factValue  = 0;
  for ($i=0; $i<$n; $i++)  
  {
    $factWeight+= $model->inDeliverList[$i]['factWeight'];
    $factValue+= $model->inDeliverList[$i]['factWeight']+$model->inDeliverList[$i]['request_exp_value'];
  }
      
 ?>
 <table class='table' border=0 width='75%' >
 <tr>
    <td>Фактический вес</td> 
    <td><?= $factWeight ?></td> 
 </tr>

 <tr>
    <td>Сумма затрат</td> 
    <td><?= $factValue ?></td> 
 </tr>

  <tr>
    <td>Дата фактического<br> выполнения:</td> 
    <td> <?php if ($model->viewMode == 'market') echo $model->finishDate; else {?>
        <?= $form->field($model, 'finishDate')->textInput(['class' => 'tcal',])->label(false)?>
        <?php } ?></td> 
 </tr>

 
 </table>
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
 <!--- Принято в работу --->
    
 
<?php 
if ($model->viewMode != 'market') { 	  
 ActiveForm::end(); 
}?>




<?php
Modal::begin([
    'id' =>'docListForm',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='docListFormFrame' width='570px' height='620px' frameborder='no'   src='index.php?r=site/lead-doc-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r($model->debug);
    echo "</pre>";
}
?>




<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-lnk-doc-request']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
