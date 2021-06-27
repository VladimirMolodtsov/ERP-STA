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

$record = $model->preparePurchase();
if ($record == false) echo "Запрашиваемая закупка не существует";

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

$this->registerJsFile('@web/tcal.js');
$this->registerCSSFile('@web/tcal.css');

$model->getDeliverData();

?>

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

.waitCell {
    width: 200px;        
    height: 100%;
    display: block;
    font-size: 12px;    
    text-align: left;
    /*background:DarkSlateGrey;*/
}    
.doneCell {
    width: 200px;        
    height: 100%;
    display: block;
    font-size: 12px;    
    text-align: left;
    background:DarkGreen;
    color:White;
    font-weight:bold;
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

.dval {
  float: right; /* блок занимает ширину содержимого, max-width её ограничивает */
  max-width: 8em;
}

.label-local{
   width: 190px;        
}

.executed {
    background: #4169E1;
    color:white;
}

.planned {
    background: #C0C0C0;
    color:white;
}

.table-small {
  font-size:12px;
  padding:1px;
}

.td-small {
  font-size:12px;
  padding:1px;
}


</style>

<script type="text/javascript">

function recalcCostValue(){
 url = 'index.php?r=store/recalc-cost-value&id=<?= $model->id ?>';
      console.log(url);   
//    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
               document.location.reload(true);  
        },
        error: function(){
            alert('Error while saving data!');
        }
    });    

 
}

function recalcControlCostValue(){
 url = 'index.php?r=store/recalc-cost-control-value&id=<?= $model->id ?>';
      console.log(url);   
//    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
               document.location.reload(true);  
        },
        error: function(){
            alert('Error while saving data!');
        }
    });    

 
}

function openDoc(id, docUri){ 
  url = 'index.php?r=bank/operator/reg-doc&noframe=1&id='+id;
//  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  if (docUri != ''){
  wid=window.open(docUri, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();
  }
//  window.wreg.focus();
}

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
function alertPurch(boxId)
{
  alert ("Закупка не согласована!");
}
function alertSchet (boxId)
{
  alert ("Закупка не согласована!");
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


function closeZaprosList(zaprosWareId)
{
  openSwitchWin('store/purchase-zapros-link&id=<?= $model->id ?>&zaprosWareId='+zaprosWareId);  
  window.opener.location.reload(false); 
}

function chngControl(id,tab, fltOrgTitle, fromDate, toDate)
{
  url = 'index.php?r=store/purchase-control-list&noframe=1&fltOrgTitle='+fltOrgTitle+'&lnkId='+id+'&showTab='+tab;
  url =url+'&fromDate='+fromDate+'&toDate='+toDate;
  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();    
}

function addMixControl()
{    
   // $('#recordId').val(docid);   
    url = 'index.php?r=store/purchase-control-list&noframe=1&fltOrgTitle=<?= $model->supplierTitle ?>';
/*    $('#select1C_schetFrame').attr('src', url);
    //document.getElementById('selectOrgDialogFrame').src
    $('#select1C_schetDialog').modal('show');     
*/
  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();    
}

function closeSchetList(docId, docType, lnkType )
{

  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  var recordId =$('#id').val();

  $('#recordId').val(recordId); //запрос       
  $('#dataVal').val(lnkType);    //тип привязки
  $('#dataType').val(docType);     //привязываемый документ
  $('#dataId').val(docId);        //ссылка на документ  
  
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/store/save-purch-data',
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

/*  openSwitchWin('store/purchase-set-schet&id=<?= $model->id ?>&schetId='+schetId+'&schetType='+schetType);  
  window.opener.location.reload(false); */
}

var lnkWin;
function addMixSchet()
{    
   // $('#recordId').val(docid);   
    url = 'index.php?r=store/purchase-schet-list&noframe=1&supplierRef=0';
/*    $('#select1C_schetFrame').attr('src', url);
    //document.getElementById('selectOrgDialogFrame').src
    $('#select1C_schetDialog').modal('show');     
*/
  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();    
}


    
    
/*  document.getElementById('schet_list_frame').src = 'index.php?r=store/purchase-schet-list&noframe=1&supplierRef=0';
  showDialog('#schet_list_form')  */

function addMainSchet()
{
  document.getElementById('schet_list_frame').src = 'index.php?r=store/purchase-schet-list&noframe=1&supplierRef=<?=$model->supplierRef?>';
  showDialog('#schet_list_form')  
}
function unlinkSchet(lnkid)
{
  openSwitchWin('store/purchase-schet-unlink&id='+lnkid);      
}

function unlinkControl(lnkid)
{
 closeSchetList(lnkid, 'unlinkControl', 0 );   
}

function saveField(id, type)
{
    
    idx= id+type;
    
    document.getElementById('dataId').value=id;
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    doSave();
}    

function switchField(id, type)
{
        
    document.getElementById('dataId').value=id;
    document.getElementById('dataType').value=type;
    
    doSave();
}    

function doSave()
{
        
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/store/save-purch-data',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSaved(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });    
}
function    showSaved(res){    
    if (res['reload'] == true) document.location.reload(true); 
    else console.log(res); 
}

</script>
<div class='row'>
<div class="col-md-3" > <h3><?= Html::encode($this->title) ?></h3></div>

<div class="col-md-6" align='left'>
   <?php 
   $action = "openWin('site/org-detail&orgId=".$model->supplierRef."','orgwin')";   
   echo  \yii\helpers\Html::tag( 'div', Html::encode($model->supplierTitle), 
                   [
                     'class'   => 'clickable',
                     'id'      => 'supplierTitle',
                     'onclick' => $action,
                     'style'  => 'font-size:18px;margin-top:22px;'
                   ]);
    ?>

</div>
<div class="col-md-3"   align='right'>
<a href='#' style='margin-top:20px;' class='btn btn-primary' onclick="javascript:openWin('site/reg-contact&id=<?= $model->supplierRef ?>','orgwin')" > Контакты <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> </a></nobr> 
</div>

</div>


<div class='spacer'></div>




<?php  
$roles = $model->getPuchaseRoles();
    
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
                if (count($dataList)==0) return ""; 
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
                'attribute' => 'refZakaz',
                'label'     => 'Удалить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                return "<a href='#' onclick=\"javascript:rmWare('".$model['refPurchaseZakaz']."');\"><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></a>";
                },
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
            'label' => "Исходный состав закупки:  ▼ к-во позиций: ".$model->wareInPurchesCount,
            'content' => $contentWare,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
    
/*******************/
?>

<?php

$providerSchet = $model->getLinkSchetProvider(Yii::$app->request->get());    
$content= \yii\grid\GridView::widget([
        'dataProvider' => $providerSchet,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small',],
        'columns' => [
             [
                'attribute' => 'purchRole',
                'label'     => 'Тип',
                'contentOptions' => [ 'style' => 'padding:2px; width:200px;',],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($roles){   
                 $id = $model['id'].'purchRole'; 
                 return Html::dropDownList( 
                          'shetType', 
                          $model['purchRole'], 
                          $roles,
                          [
                            'class' => 'form-control',
                             'style' => 'width:200px;font-size:12px; padding:1px;', 
                             'id' => $id, 
                             'onchange' => 'saveField('.$model['id'].',"purchRole");'
                           ]);
                                 
                //return $roles[$model['purchRole']];
                }
            ],        

             [
                'attribute' => 'schetNum',
                'label'     => 'Счет №',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($roles){                    
                
                $strSql = "SELECT id,docUri from {{%documents}} where refSupplierSchet=:refSupplierSchet"; 
                $docRef = Yii::$app->db->createCommand($strSql, [':refSupplierSchet' => $model['schetRef'], ])->queryOne();                    
                
                 if (!empty($docRef)) { $class = 'clickable'; $action ="openDoc(".$docRef['id'].",'".$docRef['docUri']."')";}
                                 else { $class = ''; $action ="";}
                 return \yii\helpers\Html::tag( 'div', $model['schetNum'], 
                   [
                     'class'   => $class,
                     'onclick' => $action,
                     'title'   => "Документ",
                     'style'   => "padding:2px;margin:0px;",
                   ]);
                
                }
                
            ],        
             [
                'attribute' => 'schetDate',
                'label'     => 'Дата',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
            ],        

             [
                'attribute' => 'supplierRef1C',
                'label'     => '1с',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
            ],        

            [
                'attribute' => '-',                
                'label'     => 'Сумма счета',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                 $schetSum= Yii::$app->db->createCommand("Select Sum(goodSumm) 
                 from  {{%supplier_schet_content}}
                 where {{%supplier_schet_content}}.schetRef = :schetRef",                  
                 [':schetRef' => $model['schetRef'],])->queryScalar();
                        
                 return number_format($schetSum,2,'.','&nbsp;');
               }                
            ],            

            [
                'attribute' => 'purchSum',                
                'label'     => 'Сумма в заказе',
                'format' => 'raw',                            
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'purchSum';                 
                 $action =  "saveField(".$model['id'].", 'purchSum');"; 
                 return Html::textInput( 
                          $id, 
                          $model['purchSum'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; padding:1px;font-size:12px;margin:0px;height:30px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            

             [
                'attribute' => 'orgTitle',
                'label'     => 'контрагент',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
            ],        
        
             [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    

                $action ="unlinkSchet(".$model['id'].")";
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-remove'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "Убрать",
                     'style'   => "padding:5px;margin:5px;color:Crimson;",
                   ]);
                

                }
                
            ],        

    ]
    ]);
$content.= "<div class='row'>";
    $content.= "<div class='col-md-5'  style='font-size:12px;'></div>";    
    $content.= "<div class='col-md-3' style='font-size:12px;'>";    
        $content.="Затраты на перевозку: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2'  style='font-size:12px;'>";    
        $content.=" ".number_format($model->deliverSum, 2, '.', '&nbsp;');        
    $content.= "</div>";    
    $content.= "
    <div class='col-md-2' align='right'>
        <div class='clickable' title='Добавить счет' onclick='addMixSchet();'><span class='glyphicon glyphicon-plus'></span></div>        
    </div>
</div>";
    

$content.= "<div class='row'>";
    $content.= "<div class='col-md-5'></div>";    
    
    $content.= "<div class='col-md-3'  style='font-size:12px;font-weight:bold;'>";    
        $content.="ИТОГО: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2' style='font-size:12px;font-weight:bold;'>";    
        $content.=" ".number_format($model->getPurchTotalSum(), 2, '.', '&nbsp;');    
    $content.= "</div>";
$content.= "</div>";



 echo Collapse::widget([
    'items' => [
        [
            'label' => "Связанные счета:  ▼ Сумма: ".number_format($model->getPurchTotalSum(), 2, '.', ' ')." руб.",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 


?>





<?php  
$roles = $model->getPuchaseRoles();


$provider = $model->getMainWareInSchetProvider(Yii::$app->request->get());        
$content =  \yii\grid\GridView::widget(
    [
                    
        'dataProvider' => $provider,
//        'filterModel' => $model,
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
            
             [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    
                $action ="switchField(".$model['id'].", 'switchWareAddition')";
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ban-circle'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "В накладные",
                     'style'   => "padding:5px;margin:5px;color:DarkBlue;",
                   ]);
                

                }
                
            ],        
                          
            
        ],               
    ]
    );
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Товары по счету:  ▼ Сумма: ".number_format($model->mainWareSum, 2, '.', ' ')." руб.",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

?>


<hr>
<?php    
        
$provider = $model->getWareInSchetProvider(Yii::$app->request->get());        
$content =  \yii\grid\GridView::widget(
    [
                    
        'dataProvider' => $provider,
//        'filterModel' => $model,
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

            [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    
                if ($model['purchRole'] > 0) return;
                $action ="switchField(".$model['id'].", 'switchWareAddition')";
                
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-random'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "В основной",
                     'style'   => "padding:5px;margin:5px;color:DarkBlue;",
                   ]);
                

                }
                
            ],        
                          

            
        ],               
    ]
    );
    
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Дополнительные расходы:  ▼ ".number_format(($model->deliverSum+$model->addWareSum), 2, '.', ' ')." руб.",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>   
<div class='spacer'></div>
<?php  
$edList = $model->getEdList();
$defEd= $model->getDefEd();
$provider = $model->getMainWareInSchetProvider(Yii::$app->request->get());    
$content =  \yii\grid\GridView::widget(
    [
              
        'dataProvider' => $provider,
//        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
  
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
                'label'     => 'Сумма',
                'format' => 'raw',
            ],        

            
            [
                'attribute' => 'wareCostCount',
                'label'     => 'Вес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";       
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['goodCount'];
                 
                 $id = $model['id'].'wareCostCount';                 
                 $action =  "saveField(".$model['id'].", 'wareCostCount');"; 
                 return Html::textInput( 
                          $id, 
                          $wareCostCount,                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:90px; padding:1px;font-size:12px;margin:0px;height:30px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                

            ],                    

            
            [
                'attribute' => 'wareEdValueRef',
                'label'     => 'Ед. себ.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($edList, $defEd){   
                
                 $id = $model['id'].'wareEdValueRef'; 
                 if (empty ($model['wareEdValueRef']))  return $edList[$defEd];
                 return $edList[$model['wareEdValueRef']];


                }
                
            ],        
            
              

            [
                'attribute' => '-',
                'label'     => 'Доп сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['goodCount'];

                 $wareCost = number_format( ($model['wareCostAdd']*$wareCostCount) ,3,'.','&nbsp;');                
                 
                 return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                    'title'   => number_format( ($model['wareCostAdd']) ,3,'.','&nbsp;')." р/кг * ".$wareCostCount." кг.",                     
                   ]);
               }                                               
            ],      
            
            [
                'attribute' => 'wareCostAdd',
                'label'     => 'Доп на ед.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['goodCount'];

                 $wareCostSum = $model['wareCostAdd']*$wareCostCount;
                 $wareCost = number_format( ($wareCostSum/$model['goodCount']) ,3,'.','&nbsp;');                
                 $wareCost .= " р/".$model['goodEd'];
                
                  return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                      'title'   => $model['wareCostAdd']." руб./кг",                     
                   ]);
               }                                               
            ],                  
            
            [
                'attribute' => 'wareCostPrice',
                'label'     => 'Себест.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['goodCount'];
                 
                             
                 $wareCostPrice = number_format( ($model['wareCostValue']/$model['goodCount']) ,3,'.','&nbsp;');                
                 $wareCostPrice  .= " р/".$model['goodEd'];
                
                  return \yii\helpers\Html::tag( 'div', $wareCostPrice, 
                   [                    
                    'title'   => $model['wareCostAdd']."руб./кг",                     
                    'style'=> "width:100px;"    
                   ]);
               }                                               
            ],      

            
            [
                'attribute' => 'wareCostValue',
                'label'     => 'Сумма.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['goodCount'];
                 
                 $wareCost = number_format( ($model['wareCostValue']) ,2,'.','&nbsp;');                
                
                  return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                        'title'   => "По счету ".number_format($model['goodSumm'],3,'.','&nbsp;') ." руб.",                     
                   ]);
               }                                               
            ],      
            
            
        ],               
    ]
    );
    
$recalcCostData = $model->getRecalcCostData($model->id);

$content.= "<div class='row'>
    <div class='col-md-9'>";
$content.= " Доставка: <b>".number_format($recalcCostData['deliverSum'],3,'.','&nbsp;')."</b>";
$content.= " Доп Расх: <b>".number_format($recalcCostData['addSumm'],3,'.','&nbsp;')."</b>";
$content.= " Вес общий: <b>".number_format($recalcCostData['wareCount'],3,'.','&nbsp;')."</b>";
$content.= " Доп. на кг: <b>".number_format($recalcCostData['addCost'],3,'.','&nbsp;')."</b>";
    
$content.= "</div>
    <div class='col-md-3'>
        <input  class='btn btn-default'  style='width: 200px;' type='button' value='Рассчитать' onclick='recalcCostValue();' />
    </div>
</div>
";    
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Cебестоимость по счету:  ▼ Сумма: ".number_format($model->getPurchTotalSum(), 2, '.', ' ')." руб. Доп. расходы: ".number_format($model->wareCostAdd, 2, '.', ' ')." руб/кг",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

?>

<div class='spacer'></div>
<hr style='border-color:Crimson;'>
<div class='spacer'></div>

<?php

$providerControl = $model->getDocInControlProvider(Yii::$app->request->get());    
$content= \yii\grid\GridView::widget([
        'dataProvider' => $providerControl,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small',],
        'columns' => [
             [
                'attribute' => 'purchRole',
                'label'     => 'Тип',
                'contentOptions' => [ 'style' => 'padding:2px; width:200px;',],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($roles){   
                 $id = $model['id'].'purchControlRole'; 
                 return Html::dropDownList( 
                          'shetType', 
                          $model['purchRole'], 
                          $roles,
                          [
                            'class' => 'form-control',
                             'style' => 'width:200px;font-size:12px; padding:1px;', 
                             'id' => $id, 
                             'onchange' => 'saveField('.$model['id'].',"purchControlRole");'
                           ]);
                                 
                //return $roles[$model['purchRole']];
                }
            ],        

              [
                'attribute' => '-',
                'label'     => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
      
                 if (empty ($model['docOrgTitle'])) return $model['suppOrgTitle'];
                                         else       return $model['docOrgTitle'];
                 
               }                
            ],                    

            [
                'attribute' => 'purchSum',
                'label'     => 'Сумма ',
                'format' => 'raw',
            ],        

             [
                'attribute' => 'purchSum',                
                'label'     => 'Сумма в закупке',
                'format' => 'raw',                            
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'purchControlSum';                 
                 $action =  "saveField(".$model['id'].", 'purchControlSum');"; 
                 return Html::textInput( 
                          $id, 
                          $model['purchSum'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; padding:1px;font-size:12px;margin:0px;height:30px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],            

            [
                'attribute' => 'docIntNum',
                'label'     => 'Документ ERP',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $class ="";
                 $action = "";
                 
                  if (empty ($model['docOrgTitle'])) $orgTitle=$model['suppOrgTitle'];
                                          else       $orgTitle=$model['docOrgTitle'];

                 if(empty($model['docOrigDate']))                         
                 {                                          
                     $fromDate =  date('d.m.Y', time()-10*24*3600);
                     $toDate   =  date('d.m.Y');
                 }                                                                           
                 else
                 {
                     $fromDate =  date('d.m.Y', strtotime($model['docOrigDate'])-5*24*3600);
                     $toDate   =  date('d.m.Y', strtotime($model['docOrigDate'])+5*24*3600);
                 }
                 
                 if (!empty($model['docRef'])){
                    $class = 'clickable'; 
                    $action ="openDoc(".$model['docRef'].",'".$model['docURI']."')";
                    $val = $model['docIntNum'];
                }   else {
                    $class = 'clickable'; 
                    $val ="---";                    
                    $action ="chngControl(".$model['id'].",1,'".$orgTitle."','".$fromDate."','".$toDate."');";
                }
                
                 return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => $class,
                     'onclick' => $action,
                     'title'   => "Документ в ERP",
                     'style'   => "padding:2px;margin:0px;",
                   ]);     
               }                

                
            ],        

            [
                'attribute' => '-',
                'label'     => 'Входящий в ERP',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
      
                 $val =  $model['docOrigNum'];
                 $val .= "<br>".$model['docOrigDate'];
                 return $val;
                 
               }                
            ],                    

             [
                'attribute' => 'ref1C',
                'label'     => 'Документ в 1с',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($roles){                    
                
                 return \yii\helpers\Html::tag( 'div', $model['ref1C'], 
                   [
                     //'class'   => $class,
                     //'onclick' => $action,
                     'title'   => "Документ",
                     'style'   => "padding:2px;margin:0px;",
                   ]);
                
                }
                
            ],        
            

             [
                'attribute' => 'ref1C',
                'label'     => 'Поступление товара',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($roles){                    
                
                 if (empty ($model['docOrgTitle'])) $orgTitle=$model['suppOrgTitle'];
                                         else       $orgTitle=$model['docOrgTitle'];
                
                 if(empty($model['docOrigDate']))                         
                 {                                          
                     $fromDate =  date('d.m.Y', time()-10*24*3600);
                     $toDate   =  date('d.m.Y');
                 }                                                                           
                 else
                 {
                     $fromDate =  date('d.m.Y', strtotime($model['docOrigDate'])-5*24*3600);
                     $toDate   =  date('d.m.Y', strtotime($model['docOrigDate'])+5*24*3600);
                 }
                 

                
                $action ="chngControl(".$model['id'].",2,'".$orgTitle."','".$fromDate."','".$toDate."');";

                $strSql = "Select "

                /*Add linked doc

                  $lnkRecord->purchRef = intval($this->recordId);
                  $lnkRecord->controlRef = intval($this->dataId);




                */

                if (empty($model['ref1C'])) {
                    $class = 'clickable';
                    $val ="---";
                }
                else {                     
                    if (empty($model['inNum'])) $val ='№  ';
                    else $val = $model['inNum']."<br>".$model['inDate'];
                }
                
                 return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "Документ",
                     'style'   => "padding:2px;margin:0px;",
                   ]);
                
                }
                
            ],        



             [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    

                $action ="unlinkControl(".$model['id'].")";
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-remove'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "Убрать",
                     'style'   => "padding:5px;margin:5px;color:Crimson;",
                   ]);
                

                }
                
            ],        

    ]
    ]);
 $content.= "<div class='row'>";
    $content.= "<div class='col-md-5'  style='font-size:12px;'></div>";    
      $content.= "<div class='col-md-3' style='font-size:12px;'>";    
        $content.="Затраты на перевозку: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2'  style='font-size:12px;'>";    
        $content.=" ".number_format($model->deliverSum, 2, '.', '&nbsp;');        
    $content.= "</div>";    
    
    $content.= "
    <div class='col-md-2' align='right'>
        <div class='clickable' title='Добавить счет' onclick='addMixControl();'><span class='glyphicon glyphicon-plus'></span></div>        
    </div>
</div>";
    
$content.= "<div class='row'>";
    $content.= "<div class='col-md-5'></div>";    
    
    $content.= "<div class='col-md-3'  style='font-size:12px;'>";    
        $content.="Товары: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2' style='font-size:12px;'>";    
        $content.=" ".number_format($model->getPurchControlWareSum(), 2, '.', '&nbsp;');    
    $content.= "</div>";
$content.= "</div>";

$content.= "<div class='row'>";
    $content.= "<div class='col-md-5'></div>";    
    
    $content.= "<div class='col-md-3'  style='font-size:12px;'>";    
        $content.="Доп расходы: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2' style='font-size:12px;'>";    
        $content.=" ".number_format($model->getPurchControlAddSum(), 2, '.', '&nbsp;');    
    $content.= "</div>";
$content.= "</div>";


$content.= "<div class='row'>";
    $content.= "<div class='col-md-5'></div>";    
    
    $content.= "<div class='col-md-3'  style='font-size:12px;font-weight:bold;'>";    
        $content.="ИТОГО: ";        
    $content.= "</div>";
    $content.= "<div class='col-md-2' style='font-size:12px;font-weight:bold;'>";    
        $content.=" ".number_format($model->getPurchControlSum(), 2, '.', '&nbsp;');    
    $content.= "</div>";
$content.= "</div>";



 echo Collapse::widget([
    'items' => [
        [
            'label' => "Связанные поставки:  ▼ Сумма: ".number_format($model->getPurchControlSum(), 2, '.', ' ')." руб.",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 


?>






<?php  
$roles = $model->getPuchaseRoles();


$provider = $model->getWareInControlProvider(Yii::$app->request->get(), 0);    
$content =  \yii\grid\GridView::widget(
    [
                    
        'dataProvider' => $provider,
//        'filterModel' => $model,
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
                'attribute' => 'wareSumm',
                'label'     => 'На сумму',
                'format' => 'raw',
            ],      
            
             [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    
                $action ="switchField(".$model['id'].", 'switchWareControlAddition')";
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-ban-circle'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "В накладные",
                     'style'   => "padding:5px;margin:5px;color:DarkBlue;",
                   ]);
                

                }
                
            ],        
                          
            
        ],               
    ]
    );
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Товары по документам поставки:  ▼ Сумма: ",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

?>


<hr>
<?php    
        
$provider = $model->getWareInControlProvider(Yii::$app->request->get(), 1);          
$content =  \yii\grid\GridView::widget(
    [
                    
        'dataProvider' => $provider,
//        'filterModel' => $model,
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
                'attribute' => 'wareSumm',
                'label'     => 'На сумму',
                'format' => 'raw',
            ],                    

            [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',
                'contentOptions' => [ 'style' => 'padding:2px',],
                'value' => function ($model, $key, $index, $column) use($roles){                    
                if ($model['purchRole'] > 0) return;
                $action ="switchField(".$model['id'].", 'switchWareAddition')";
                
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-random'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'title'   => "В основной",
                     'style'   => "padding:5px;margin:5px;color:DarkBlue;",
                   ]);
                

                }
                
            ],        
                          

            
        ],               
    ]
    );
    
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Дополнительные расходы по документам поставки:  ▼ ",
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>   




<div class='spacer'></div>

<?php  
$edList = $model->getEdList();
$defEd= $model->getDefEd();
$provider = $model->getWareInControlProvider(Yii::$app->request->get(), 0);    
$content =  \yii\grid\GridView::widget(
    [
              
        'dataProvider' => $provider,
//        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
  

            [
                'attribute' => 'wareTitle',
                'label'     => 'Наименование',
                'format' => 'raw',
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
                'attribute' => 'wareSumm',
                'label'     => 'Сумма',
                'format' => 'raw',
            ],        

            
            [
                'attribute' => 'wareCostCount',
                'label'     => 'Вес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";       
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['wareCount'];
                 
                 $id = $model['id'].'wareCostControlCount';                 
                 $action =  "saveField(".$model['id'].", 'wareCostControlCount');"; 
                 return Html::textInput( 
                          $id, 
                          $wareCostCount,                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:90px; padding:1px;font-size:12px;margin:0px;height:30px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                

            ],                    

            
            [
                'attribute' => 'wareEdValueRef',
                'label'     => 'Ед. себ.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($edList, $defEd){   
                
                 $id = $model['id'].'wareEdValueRef'; 
                 if (empty ($model['wareEdValueRef']))  return $edList[$defEd];
                 return $edList[$model['wareEdValueRef']];


                }
                
            ],        
            
              

            [
                'attribute' => '-',
                'label'     => 'Доп сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['wareCount'];

                 $wareCost = number_format( ($model['wareCostAdd']*$wareCostCount) ,3,'.','&nbsp;');                
                 
                 return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                    'title'   => number_format( ($model['wareCostAdd']) ,3,'.','&nbsp;')." р/кг * ".$wareCostCount." кг.",                     
                   ]);
               }                                               
            ],      
            
            [
                'attribute' => 'wareCostAdd',
                'label'     => 'Доп на ед.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['wareCount'];

                 $wareCostSum = $model['wareCostAdd']*$wareCostCount;
                 $wareCost = number_format( ($wareCostSum/$model['wareCount']) ,3,'.','&nbsp;');                
                 $wareCost .= " р/".$model['wareEd'];
                
                  return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                      'title'   => $model['wareCostAdd']." руб./кг",                     
                   ]);
               }                                               
            ],                  
            
            [
                'attribute' => 'wareCostPrice',
                'label'     => 'Себест.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['wareCount'];
                 
                             
                 $wareCostPrice = number_format( ($model['wareCostValue']/$model['wareCount']) ,3,'.','&nbsp;');                
                 $wareCostPrice  .= " р/".$model['wareEd'];
                
                  return \yii\helpers\Html::tag( 'div', $wareCostPrice, 
                   [                    
                    'title'   => $model['wareCostAdd']."руб./кг",                     
                    'style'=> "width:100px;"    
                   ]);
               }                                               
            ],      

            
            [
                'attribute' => 'wareCostValue',
                'label'     => 'Сумма.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 if ($model['wareCostCount'] > 0.00001 ) $wareCostCount = $model['wareCostCount'];
                                                    else $wareCostCount = $model['wareCount'];
                 
                 $wareCost = number_format( ($model['wareCostValue']) ,2,'.','&nbsp;');                
                
                  return \yii\helpers\Html::tag( 'div', $wareCost, 
                   [                    
                        'title'   => "По счету ".number_format($model['wareSumm'],3,'.','&nbsp;') ." руб.",                     
                   ]);
               }                                               
            ],      
            


            
            
        ],               
    ]
    );
    
$recalcCostData = $model->getRecalcControlCostData($model->id);

$content.= "<div class='row'>
    <div class='col-md-9'>";
$content.= " Доставка: <b>".number_format($recalcCostData['deliverSum'],3,'.','&nbsp;')."</b>";
$content.= " Доп Расх: <b>".number_format($recalcCostData['addSumm'],3,'.','&nbsp;')."</b>";
$content.= " Вес общий: <b>".number_format($recalcCostData['wareCount'],3,'.','&nbsp;')."</b>";
$content.= " Доп. на кг: <b>".number_format($recalcCostData['addCost'],3,'.','&nbsp;')."</b>";
    
$content.= "</div>
    <div class='col-md-3'>
        <input  class='btn btn-default'  style='width: 200px;' type='button' value='Рассчитать' onclick='recalcControlCostValue();' />
    </div>
</div>
";    
       
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Cебестоимость по документам поставки:  ▼ Сумма: ".number_format($model->getPurchControlSum(), 2, '.', ' ')." руб. Доп. расходы на кг: ".number_format($recalcCostData['addCost'], 2, '.', ' ')." руб/кг",
          //  'labelOptions' => ['style' => 'background-color:blue;'],
            'content' => $content,            
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 

?>



<hr>

<?php $form = ActiveForm::begin(['id' => 'Mainform' ]); ?>
<?= $form->field($model, 'id')->hiddenInput(['id'=>'id'])->label(false)?>
<?= $form->field($model, 'zakazNote')->textarea(['id' => 'zakazNote','row' =>10, 'style'=>'margin:0px; padding:0px; left:0px'])->label('Комментарий к закупке')?>
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
    <td><?=$model->printEditBox('s1', 3)?></td>
    <td><?=$model->printEditBox('s2', 1)?></td>
    <td><?=$model->printEditBox('s3', 1)?></td>
    <td><?=$model->printEditBox('s4', 1)?></td>
</tr>

<tr>
    <td>2</td>
    
    <td><?=$model->printEditBox('s1', 4)?></td>
    <td><?=$model->printEditBox('s2', 2)?></td>
    <td><?=$model->printEditBox('s3', 2)?></td>
    <td><?=$model->printEditBox('s4', 2)?></td>
</tr>

<tr>
    <td>3</td>
    <td><?=$model->printEditBox('s1', 1)?></td>    
    <td><?=$model->printEditBox('s2', 3)?></td>
    <td><?=$model->printEditBox('s3', 3)?></td>
    <td><?=$model->printEditBox('s4', 3)?></td>
</tr>

<tr>
    <td>4</td>
    <td><?=$model->printEditBox('s1', 2)?></td>
    <td><?=$model->printEditBox('s2', 4)?></td>
    <td><?=$model->printEditBox('s3', 4)?></td>
    <td></td>
</tr>

<tr>
    <td>5</td>
    <td></td>
     <td></td>
    <td><?=$model->printEditBox('s3', 5)?></td>
    <td></td>
</tr>

<tr>
    <td>6</td>
    <td></td>
    <td></td>
    <td> <?= $model->printEditBox('s3', 6)?></td>
    <td></td>
</tr>

<tr>
    <td>7</td>
    <td></td>
    <td></td>
    <td><?=$model->printEditBox('s3', 7)?></td>
    <td></td>
</tr>

<tr>
    <td>8</td>
    <td></td>
    <td></td>
    <td><?= $model->printEditBox('s3', 8) ?></td>
    <td></td>
</tr>

<tr>
    <td>&nbsp;</td>
    <td></td>
    <td><div style='color:DarkGreen;text-align:right;font-size:13px;' ><?= $model->printOplateSum() ?></div></td>
    <td><div style='color:DarkGreen;text-align:left;font-size:13px;' ><?php $model->printDeliverList() ?></div></td>
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

<!--- Форма список счетов 
  <div id="schet_list_form" class='popup_form' style='height: 650px; width: 620px; margin-left: -300px; margin-top: -400px;'>
    <span id="schet_list_close"  class='popup_close' onclick='closeDialog("#schet_list_form")' >X</span>    
    <iframe id='schet_list_frame' width='600px' height='620px' frameborder='no'   src='index.php?r=store/purchase-schet-list&noframe=1&supplierRef=<?=$model->supplierRef?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>      
   <br>   
  </div>
----->    

<!--- Форма список запрочов ----->    
  <div id="zapros_list_form" class='popup_form' style='height: 750px; width: 730px; margin-left: -300px; margin-top: -400px;'>
    <span id="zapros_list_close"  class='popup_close' onclick='closeDialog("#zapros_list_form")' >X</span>    
    <iframe width='700px' height='720px' frameborder='no'   src='index.php?r=store/purchase-zapros-list&zaprosId=<?= $model->id ?>&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>      
   <br>   
  </div>


  
<!--- ******************************************************  --->  
<div id="overlay" class='overlay'></div>
  
<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action'=>'index.php?r=/store/save-purch-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);

echo "<input type='submit'>";
ActiveForm::end(); 
?>
  
  
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

  
  
  
<?php

if (!empty($model->debug)){
 echo "<pre>";   
 print_r($model->debug);
 echo "</pre>"; 
}    
?>

  
