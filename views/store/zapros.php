<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;
use yii\bootstrap\Collapse;


$curUser=Yii::$app->user->identity;
$this->title = 'Мониторинг цены без проведения закупки - форма коммерческого директора';
if ($model->id > 0)  
{ 
    $zaprosRecord = $model->preparePurchaseZakaz();                      
}
?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


<style>
.button {
	width: 150px;
	font-size: 10pt;	
} 

.btn-local {	
	padding: 2px;	 
	font-size: 10pt;	
} 

 .btn-block{
    padding: 2px;	 
 }
 
.gridcell {
	width: 85px;		
	height: 100%;
    display: block;
	/*background:DarkSlateGrey;*/
}	
.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.gridcell:hover{
	background:Silver;
    cursor: pointer;
	color:#FFFFFF;
}
.editcell{
   width: 85px;		
   display:none;
   white-space: nowrap;
}

.box_shadow {
	box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
}

.middle_lbl
{
 padding-left:15px;	
}
.executed {
    background: #4169E1;
	color:white;
}

.planned {
    background: #C0C0C0;
	color:white;
}

td 
{
 padding:4px;
}


</style>

<script type="text/javascript">

function setWorkInActive ()
{
   $('#frmRealizeValue').modal('show');       
}


function setInWork()
{
    /*Одобрение*/
    //openSwitchWin('store/purchase-zakaz-setinwork&id=<?= $model->id ?>');
    openWin('store/zapros-odobrenie&id=<?= $model->id ?>','childWin');
    
}

function goodListDialog()
{
   $('#frmGoodListDialog').modal('show');          
}

function schetListDialog()
{
   $('#frmSchetListDialog').modal('show');         
   //showDialog("#add_from_schet_form");
}


var curVariantId = 0;
function openSchetDialog(refOrg, variantId )
{           
    //document.getElementById('schet_form_iframe').        
    curVariantId = variantId;
    loc = "index.php?r=store/purchase-schet-list&noframe=1&supplierRef="+refOrg;
    $('#schet_form_iframe').attr('src', loc);    
    //showDialog('#schet_list_form');
    $('#frmActualSchetDialog').modal('show');             
}

function closeSchetList(schetId)
{
  openSwitchWin('store/purchase-zakaz-set-schet&id='+curVariantId+'&schetId='+schetId);  
}

function switchInList(variantId)
{
  openSwitchWin('store/zapros-switch-in-list&variantId='+variantId);  
}

function regRequest(requestId)
{ 
   
 document.forms["RequestForm"]["editVariantId"].value=requestId;
 //document.forms["RequestForm"]["editRequestDate"].value=;
 $('#frmRequstDialog').modal('show');         
}

/****************************************/


function rmVariant(variantId)
{    
    openSwitchWin('store/purchase-zakaz-rmvariant&id=<?= $model->id ?>&variantId='+variantId);
}

function setVariantActive(variantId)
{
    openSwitchWin('store/purchase-zakaz-activate&id=<?= $model->id ?>&variantId='+variantId);
}

function setDenied()
{
    openSwitchWin('store/purchase-zakaz-deny&id=<?= $model->id ?>');
}

function setUnPermited()
{
    openSwitchWin('store/purchase-zakaz-unpermited&id=<?= $model->id ?>');
}


function setPermited()
{
    openSwitchWin('store/purchase-zakaz-permited&id=<?= $model->id ?>');
}

function unSetInWork()
{
    openSwitchWin('store/purchase-zakaz-unsetinwork&id=<?= $model->id ?>');
}

function rmWare(wareId)
{
    openSwitchWin('store/purchase-zakaz-rmware&id=<?= $model->id ?>&wareref='+wareId);
}


function addContact(orgId)
{
    openWin('site/reg-contact&singleWin=1&id='+orgId,'contactWin')
}

function showEditCount(wareId, count)
{

 showId = 'wareCount_'+wareId;
 editId = 'wareCountEdit_'+wareId;   
           
    document.getElementById(showId).style.display = 'none';
    document.getElementById(editId).style.display = 'block';    
}

function closeEditCount(wareId)
{

 showId = 'wareCount_'+wareId;
 editId = 'wareCountEdit_'+wareId;   
           
    document.getElementById(showId).style.display = 'block';
    document.getElementById(editId).style.display = 'none';    
}


function setCount(wareId)
{
   editId = 'wareCountVal_'+wareId;   
   value= document.getElementById(editId).value;
   openSwitchWin('store/purchase-zakaz-setcount&id=<?= $model->id ?>&wareref='+wareId+'&count='+value);
}

function closeOrgList(id, title)
{
   orgRef = id;   
   orgTitle= title;
   openSwitchWin('store/purchase-zakaz-addorg&id=<?= $model->id ?>&orgref='+orgRef+'&orgtitle='+orgTitle);
}

function closeWareSchet(supplierWareId)
{
    openSwitchWin('store/purchase-add-from-schet&id=<?= $model->id ?>&supplierWareId='+supplierWareId);      
}

function delPurchase()
{    
    document.location.href='index.php?r=store/purchase-zakaz-del&id=<?= $model->id ?>';      
}

</script>

<h3><?= Html::encode($this->title) ?></h3>



<?php $form = ActiveForm::begin(['id' => 'Mainform' ]); ?>  

<?php 
$disChange = "";
$isChange = $model->showList['change'];
$isReadOnly =0;

if ($model->isActive == 0)$isReadOnly =1;
if ($model->status == 8)$isReadOnly =1;
if ((!($curUser->roleFlg & 0x0010)) && (!($curUser->roleFlg & 0x0020)))
{
$isChange =0;
$isReadOnly = 1;
}

if ($isChange==0) $disChange = " disabled=on ";




echo "<div class='item-header'> Исходный запрос: <b>".$model->goodTitle.".</b> к-во:<b>".$model->goodCount." ".$model->goodEd."</b></div>"

//Существуещий запрос 
?>

<?php  
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $wareInZakazProvider,
//		'filterModel' => $model,
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
                'attribute' => 'В наличие',
				'label'     => 'В наличие',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return  Yii::$app->db->createCommand("Select amount+inTransit from {{%warehouse}} where id =:wareRef ", 
                    [':wareRef' => $model['id'],])->queryScalar();                       
                },                               
                
            ],		

        	[
                'attribute' => 'Зарезерв.',
				'label'     => 'Зарезерв.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                        return  Yii::$app->db->createCommand("Select reserved from {{%warehouse}} where id =:wareRef", 
                        [':wareRef' => $model['id'],])->queryScalar();    
                },                               
                
            ],		

			[
                'attribute' => 'Расход',
				'label'     => 'Ср. Расход',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $l = Yii::$app->db->createCommand("Select ifnull(SUM(supplyCount),0) as N, TIMESTAMPDIFF(DAY, MIN(supplyDate), NOW()) as P 
                       from {{%supply}}  where wareRef =:wareRef ", 
                [':wareRef' => $model['id'],])->queryAll();    
                if ($l[0]['P'] == 0 ) return  0 ;   
                return number_format(30*$l[0]['N']/$l[0]['P'],0,'.','');                          
                },                               

            ],		
            
			[
                'attribute' => 'wareCount',
				'label'     => 'К-во',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    $ret="<div id='wareCountEdit_".$model['id']."' class='editcell'>
                    <nobr>
                    <input  style='width:50px;' id='wareCountVal_".$model['id']."' value='".$model['wareCount']."'>
                    <a href ='#' onclick=\"javascript:setCount('".$model['id']."'); \"><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></a>
                    <a href ='#' onclick=\"javascript:closeEditCount('".$model['id']."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>
                    </nobr>
                    </div>";
                    $ret.="<div id='wareCount_".$model['id']."' class='gridcell' onclick=\"javascript:showEditCount('".$model['id']."','".$model['wareCount']."' );\">".$model['wareCount']."</div>";                                   
                    return $ret;
                },                               
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
                'value' => function ($model, $key, $index, $column) use($isChange) {
                if ($isChange == 0) return "&nbsp;";    
				return "<a href='#' onclick=\"javascript:rmWare('".$model['id']."');\"><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },
            ],		

            
        ],               
    ]
	);
    
?>  
<?php
  Pjax::begin(); 
  $content = \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $model->getPurchaseRequestListProvider(),
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small', 'style'=>'font-size:12px;' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'id',
				'label'     => 'Заказ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   return "<a href='#' onclick=\"openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['id']."','childWin' )\" >".
                   $model['id']." от ".date("d.m.Y", strtotime($model['formDate']))."</a>";  
                }

			],
            
			[
                'attribute' => 'requestGood',
				'label'     => 'Товар',
                'format' => 'raw',

			],

			[
                'attribute' => 'requestCount',
				'label'     => 'К-во',
                'format' => 'raw',

			],
			[
                'attribute' => 'orgTitle',
				'label'     => 'Клиент',
                'format' => 'raw',
			],

			[
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
			],

		/*	[
                'attribute' => 'Удалить',
				'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  use($isChange)  {  
                   if ($isChange == 0) return "&nbsp;";                    
                   return "<a href='#' onclick=\"openSwitchWin('store/purchase-rm-link&requestId=".$model['requestId']."')\" ><font color='Crimson'><span class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span></font></a>"; 
                }

			],*/

            
        ],               
    ]
	);

 //$content .="<br> <input ".$disChange." class='btn btn-primary'  style='width: 170px;' type='button' value='Добавить' onclick='showDialog(\"#zakaz_list_form\");' /> ";
  

 echo Collapse::widget([
    'items' => [
        [
            'label' => "Связанные заявки:  ▼ ",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

Pjax::end();
?> 


<?php if ($isReadOnly == 0) {?> 
<div class='row'>
    <div class='col-md-3'>
    </div> 
    <div class='col-md-3'>    
    <?php if ($zaprosRecord->status == 0){ ?>    
        <input  class="btn btn-primary"  style="width: 200px;" type="button" value="Одобрение" onclick='setInWork();' />            
    <?php } ?>
    <?php if ($zaprosRecord->status == 1){ ?>    
        <input  class="btn btn-primary"  style="width: 200px;" type="button" value="Отозвать одобрение" onclick='unSetInWork();' />            
    <?php } ?>    
    </div> 
    <div class='col-md-3'>
    <?php if ($zaprosRecord->status == 1){ ?>    
        <input class="btn btn-primary"  style="width: 170px;" type="button" value="Добавить из счета" onclick='schetListDialog();' />
    <?php } ?>
    </div> 
     <div class='col-md-3'>
     <?php if ($zaprosRecord->status == 1){ 
        if ($zaprosRecord->zaprosWareType ==2) $caption = 'Добавить аналог';
        if ($zaprosRecord->zaprosWareType ==1) $caption = 'Добавить сырье';
     ?>    
     
        <input  class="btn btn-primary"  style="width: 170px;" type="button" value="<?= $caption ?>" onclick='goodListDialog();' />    
     <?php } ?>   
    </div> 
</div> 
<br>
<?php }?>

<br>
<?= $form->field($model, 'zakazNote')->textarea(['id' => 'zakazNote','row' =>5, 'style'=>'margin:0px; padding:0px; left:0px'])->label('Комментарий к запросу')?>
<?= $form->field($model, 'id')->hiddenInput()->label(false)?> 
<br>
<div style='text-align: right;'>
 <?= Html::submitButton('Сохранить и выйти', ['class' => 'btn btn-primary', 'style' => 'background-color: ForestGreen; margin-top:-20px; width: 170px; ', 'name' => 'actMainform']) ?>
</div> 
<?php ActiveForm::end(); ?>


<hr>

<div class='row'>
    <div class='col-md-3'>
    </div>

    <div class='col-md-3'>    
        <a href="#"  onclick='delPurchase();' class="btn btn-primary" style="width: 200px;	background: Crimson;" > <span class="glyphicon glyphicon-trash" aria-hidden="true" style='color:White; font-size:17px; '></span> Удалить </a>
    </div>

    
    <div class='col-md-3'>
      <?php if ($zaprosRecord->status == 2){ ?>    
        <input  class="btn btn-primary"  style="width: 200px; 	background: Orange;" type="button" value="Доработать" onclick='setDenied();' />                
      <?php } ?>            
    </div>

        
    
    <div class='col-md-3'>    
    <?php if ($zaprosRecord->status == 2){ ?>    
    <?php } ?>
    
    </div>
</div>



<hr>
<!--- ******************************************************  --->    
<a name='contentlist'></a>
<h3> Поставщики и предложения </h3>

<?php  
Pjax::begin(); 
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $variantsProvider,
//		'filterModel' => $goodModel,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                  return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['orgTitle']."</a>";                                   
                },               
                
			],

        	[
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                  return "<div style='width:200px'>".$model['wareTitle']."</div>";                                   
                },               

			],
            
            [
                'attribute' => 'isRequestSend',
				'label'     => 'Запрос',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($isChange) {
                    
                if ($model['isRequestSend'] >0 ){ $isFlg = true;}
                else                      { $isFlg = false;}
                    $lbl =   \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                       
                if ($isChange == 0 ) {
                $ret = "<div>".$lbl."</div>";
                if ($isFlg ) $ret.="<br>".$model['requestDate'];
                return $ret;
                }
                
                $ret = "<div class='gridcell' onclick='javascript:regRequest(".$model['id'].")'>".$lbl."</div>";
                if ($isFlg ) $ret.="<br>".$model['requestDate'];
				return $ret;
                },               
			],
            
           

           
			[
                'attribute' => 'lastSchetDate',
				'label'     => 'Актуальность',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ( (time() - strtotime($model['lastSchetDate'])) > 10*24*60*60 )
                        $val = "<font color='Crimson'>". date("d-m-Y", strtotime($model['lastSchetDate']))."</font>";                     
                    else 
                        $val = date("d-m-Y", strtotime($model['lastSchetDate'])); 
                 
                  return "<div class='gridcell' style='width:100px' onclick=\"javascript:openSchetDialog('".$model['refOrg']."','".$model['id']."' );\">".$val."</div>";                                                    
                }
			],

			[
                'attribute' => 'curentValue',
				'label'     => 'Цена',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                    if ( (time() - strtotime($model['lastSchetDate'])) > 10*24*60*60 )
                        $val = "<font color='Crimson'>". number_format($model['curentValue'],2,".","&nbsp;") ."</font>";                     
                    else 
                        $val = number_format($model['curentValue'],2,".","&nbsp;"); 
                   
                   return $val;
                }

                
			],
            

			[
                'attribute' => 'Контакты',
				'label'     => 'Контакты',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                   $strSql = "SELECT note FROM {{%contact}} where ref_org =:refOrg order by id DESC LIMIT 1";                   
                   $lastContact = Yii::$app->db->createCommand($strSql, [':refOrg' => $model['refOrg'],])->queryAll();                    
                   if (count($lastContact) > 0) $note=mb_substr($lastContact[0]['note'],0,60,'UTF-8' );
                   else $note= "-";
                   if (empty ($note))$note= "-";
                  return "<div class='gridcell' style='width:200px' onclick=\"javascript:addContact('".$model['refOrg']."');\">".$note."</div>";                                   
                },               
			],
            
            
			[
                'attribute' => 'isInList',
				'label'     => 'В списке',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($isChange) {
                          
                //if ($model['isRequestSend'] ==0 ) return "Нет запроса";
                if ($model['isInList'] >0 ){ $isFlg = true;}
                else                      { $isFlg = false;}
                    $lbl =   \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                        
                if ($isChange == 0 ) return $lbl;       

				return "<div class='gridcell' onclick=\"javascript:switchInList('".$model['id']."');\">".$lbl."</div>";                   
                }
			],
			
/*			[
                'attribute' => 'id',
				'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($isChange)  {
                if ($isChange == 0 ) return "";
				return "<a href='#' onclick=\"javascript:rmVariant('".$model['id']."');\"><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },
            ],		
*/            
        ],
    ]
	);

 Pjax::end();
?>   
<div style='text-align:center; font-weight:bold; font-size:20px; ' >

<?php 
/*Информационная панель*/
echo "<span class='label label-default' style='' >".$model->informText."</span>";
?>    
</div><br>
<h4>Выбор варианта закупки:</h4>
<?php
    $list = $model->getZaprosVariantInList();
    $dataN = count($list['data']);
    $wareN = count($list['wareTitle']);
    $orgN = count($list['orgTitle']);
    
$idx=array();    
for($i=0; $i<$dataN; $i++)
{
    $refOrg =$list['data'][$i]['refOrg'];
    $refWare=$list['data'][$i]['refWare'];
    $idx[$refOrg][$refWare] = $i;
}
?>

<table class='table table-striped'>
<thead>
<tr>
<th> Контрагент </th>
<?php
for($j=0; $j<$wareN; $j++)
{    
  echo "<th>".$list['wareTitle'][$j]['title']."</th>\n";
}
?>
</tr>
</thead>
<?php
for($i=0; $i<$orgN; $i++)
{
 $refOrg =$list['orgTitle'][$i]['refOrg'];
echo " <tr>\n";
echo "  <td>".$list['orgTitle'][$i]['title']."</td>\n";

for($j=0; $j<$wareN; $j++)
{    
  $refWare=$list['wareTitle'][$j]['refWare'];
  if (isset($idx[$refOrg][$refWare])) 
  {
      $ref = $idx[$refOrg][$refWare];
      echo "<td>".number_format($list['data'][$ref]['curentValue'],2,'.','&nbsp;')."</td>\n";
  }
  else
  {
      echo "<td>&nbsp;</td>\n";      
  }
}

echo " </tr>\n";
}
?>
</table>

<div class='row'>
    <div class='col-md-3'>
    </div>

    <div class='col-md-3'>    
    </div>
    
    <div class='col-md-3'>
    </div>

    
    <div class='col-md-3'>    
        <input  class="btn btn-primary"  style="width: 200px;	background: DarkGreen;" type="button" value="Завершить" onclick='setWorkInActive();' />                
    </div>
</div>


<!--- ******************************************************  --->    


<pre>
<?php
//print_r ($list );

//print_r ($idx );
?>
</pre>


<?php
Modal::begin([
    'id' =>'frmGoodListDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Выбор закупаемого товара </h4>',
]);?>
<?php  
   Pjax::begin(); 

echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $goodListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small', 'style'=>'font-size:12px;' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

			[
                'attribute' => 'grpGoodList',
				'label'     => 'Товарная группа',
                'format' => 'raw',
                'filter' => $model->getGrpGroup(),
                'value' => function ($model, $key, $index, $column) {
				return $model['grpGood'];
                },
			],
			
			[
                'attribute' => 'wareTitle',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				return "<a href='#' onclick=\"javascript:setGood('".$model['ed']."','".$model['id']."');\"><div id='good_".$model['id']."'>".$model['wareTitle']."</div></a>";
                },
            ],		
            
            [
                'attribute' => 'Поставщики',
				'label'     => 'Поставщики',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
				
                $strSql= "select  distinct {{%orglist}}.id,{{%orglist}}.title from {{%supplier_schet_content}},{{%supplier_schet_header}}, {{%orglist}} where 
                          {{%supplier_schet_header}}.refOrg = {{%orglist}}.id and {{%supplier_schet_content}}.wareRef = :wareRef
                          and {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef
                ORDER BY {{%supplier_schet_header}}.schetDate DESC";
				$res = Yii::$app->db->createCommand($strSql, [':wareRef' => $model['id'],])->queryAll();
                $ret="";
                for ($i=0; $i< count($res); $i++ )
                {   
                   $ret.="<nobr>";
                   if ($i==0) $ret.="<b>";
                   $ret.= mb_substr($res[$i]['title'],0,20,'UTF-8');                    
                   if ($i==0) $ret.="</b>";
                   if (mb_strlen($res[$i]['title'],'UTF-8') >20) $ret.="...";
                   $ret.="</nobr><br>";
                   if ($i>=2) break;                   
                }
                if ($i < count($res))$ret .= "...";                
				return $ret;
                
                
                },
            ],		
            
            
        ],               
    ]
	);

 Pjax::end();
?>   
<?php
Modal::end();
?>

<?php
Modal::begin([
    'id' =>'frmSchetListDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Выбор товара из счета </h4>',
]);?>
<!--- Форма добавить товар из счета ----->	
  <div style='height: 650px; width: 600px;'>
	<iframe width='550px' height='620px' frameborder='no'   src='index.php?r=store/purchase-ware-schet&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
   <br>   
  </div> 

<?php
Modal::end();
?>

<?php
Modal::begin([
    'id' =>'frmActualSchetDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Выбор актуального счета </h4>',
]);?> 	
<!--- Форма выбрать актуальный счет ----->	
  <div style='height: 650px; width: 600px;'>	
	<iframe id='schet_form_iframe' width='570px' height='620px' frameborder='no'   src='index.php?r=store/purchase-schet-list&noframe=1&supplierRef=<?=$model->supplierRef?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>
<?php
Modal::end();
?>

<?php
Modal::begin([
    'id' =>'frmRequstDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Выбор актуального счета </h4>',
]);?> 	   
<!--- Форма регистрация запроса цены ----->	
  <div style='height: 600px; width: 600px;'>	
  <form name='RequestForm' id='RequestForm' action='index.php' method='GET'>
  <input type='hidden' name='r' value='store/purchase-add-request'>
  <input type='hidden' name='id' value='<?= $model->id ?>'>
  <input type='hidden' name='editVariantId' value=0>
  <p>Дата запроса: <input name='editRequestDate' id='editRequestDate' class='tcal' value='<?= date('d.m.Y') ?>'></p>
   Текст письма:
  <div style='margin-top:20px; padding:10px; width:550px;height:450px;'>  
  <textarea name='editRequestNote' id='editRequestNote'  cols='95' rows='15' class='form-control'> 

  </textarea> 
  </div>
       <input class="btn btn-primary"  style="width: 175px;" type="Submit" value="Сохранить"  />
  </form>
  </div>
<?php
Modal::end();
?>
  
    

<?php
Modal::begin([
    'id' =>'frmRealizeValue',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h2> Цена реализации </h2>',
]);?>

<form name='oplateForm' method='get' action='index.php'>
<input type='hidden' name='r' value='store/purchase-zapros-finit'>
<input name='id'  type='hidden'  value='<?= $model->id ?>'> 

<div class='row'>
<div class='col-md-4'>
 Введите цену реализации
</div>
<div class='col-md-4'><input class='form-control' name='relizeVal' id='relizeVal'  value='' > </div>
<div class='col-md-4'><input type='submit' class='btn btn-primary' value='Сохранить'></div>
</div>


<?php
Modal::end();
?>
      
    
    