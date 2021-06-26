<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 


$this->title = 'Список зарегистрированных документов';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/doc-list.js');

$frm ="";
$to ="";

?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />


<style>
.table-small {
padding: 2px;
font-size:12px;
}
.action_ref {    
    color:Green;
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.orginfo {
  
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
}

.modal-body {
    position: relative;
    overflow-y: auto;
    max-height: 1024px;
    width:  1280px;
    padding: 5px;
}


.leaf {
    height: 60px; /* высота нашего блока */
    width:  80px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}

.leaf:hover {
    /*box-shadow: 0.4em 0.4em 5px #696969;*/
    border: 1px solid Blue; /* размер и цвет границы блока */
    background-color:#eaf2f8;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
}

.leaf-selected:hover {        
    border: 1px solid Blue; /* размер и цвет границы блока */
}



.leaf-txt {    
    font-size:11px;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}


</style>


<script type="text/javascript">

function loadExtaract()
{
 $('#loadFileDialog').modal('show'); 
}
var lnkWin;
function select1C_schet(docid)
{
    $('#recordId').val(docid);   
    url = 'index.php?r=bank/operator/select-schet&noframe=1&docid='+docid;
/*    $('#select1C_schetFrame').attr('src', url);
    //document.getElementById('selectOrgDialogFrame').src
    $('#select1C_schetDialog').modal('show');     
*/
  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();
    
}


var errWin;
function selectError()
{
  $('#selectErrDialog').modal('show');       
  
  /*if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();*/    
}
function setErrFilter (d,month,year){
 $('#selectErrDialog').modal('hide');     
 
 document.location.href="index.php?r=bank/operator/doc-list&detail=1&year="+year+"&month="+month+"&day="+d;
 
}

function selectControl()
{

  document.getElementById('selectControlDialogFrame').src="index.php?r=/bank/operator/doc-control-calendar&noframe=1&month=<?=$model->month?>&year=<?=$model->year?>";    
  $('#selectControlDialog').modal('show');       
  
  /*if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
  lnkWin=window.open(url, 'selectWin','toolbar=no,scrollbars=yes,resizable=no,top=50,left=800,width=1024,height=730');   
  window.lnkWin.focus();*/    
}
function setControlFilter (d,month,year){
 $('#selectControlDialog').modal('hide');      
 document.location.href="index.php?r=bank/operator/doc-list&detail=2&year="+year+"&month="+month+"&day="+d;
 
}



function setRef(ref, type)
{
  if(!(lnkWin == null) && !(lnkWin.closed) )    window.lnkWin.close();
      
  $('#dataType').val(type);
  $('#dataVal').val(ref);      
  
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/bank/operator/save-doc-ref',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            document.location.reload(true);              
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	

 
}


function selectDeal(recordId, refOrg, docArticleRef )
{
    //var orgRef =  document.getElementById('refOrg').value;
    if (refOrg == 0) {alert("Организация не выбрана!"); return;}
    var url =  "index.php?r=/site/org-deal-select&noframe=1&orgId="+refOrg+"&selectedDeal="+docArticleRef;  
   // console.log(url);
    document.getElementById('recordId').value=recordId;
    document.getElementById('selectOrgDealFrame').src= url;
    $('#selectOrgDeal').modal('show');  
}

function closeOrgDeal(selectedDeal)
{       
    $('#selectOrgDeal').modal('hide');      
    document.getElementById('dataVal').value=selectedDeal;
    
    var data = $('#saveDataForm').serialize();
        $.ajax({
            url: 'index.php?r=/bank/operator/set-deal-param',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(res){     
                showDeal(res);
            },
            error: function(){
                alert('Error while saving data!');
            }
        });	    
}

function showDeal(res)
{
    var typeid = "orgType"+res.id;
    document.getElementById(typeid).innerText=res.orgType;
    var dealid = "orgDeal"+res.id;
    document.getElementById(dealid).innerText=res.orgDeal;

    console.log(res);  
    //document.location.reload(true);     
}



function regNewDoc(){ 
  url = 'index.php?r=bank/operator/reg-doc&noframe=1&id=0';
  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  window.wreg.focus();
}

function openDoc(id, docUri){ 
  var url = 'index.php?r=bank/operator/reg-doc&noframe=1&id='+id;
  wreg=window.open(url, 'regWin','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=800,width=520,height=730'); 
  if (docUri != ''){
  wid=window.open(docUri, 'docWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();
  }
  window.wreg.focus();
}


function openScanWindow(){
//Uri='https://drive.google.com/drive/folders/1vYt7wiJn_uO3wph0A27uoZ6HGBB0hRxE?usp=sharing'; //корень
Uri='https://drive.google.com/drive/folders/1gE5sXaAFBVFDWdPK3M5d0e4QWyxyMB-P?usp=sharing';   //upload
  wid=window.open(Uri, 'scanWin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=10,width=720,height=900'); 
  window.wid.focus();

}

function scanGoogleDisk()
{  
       $(document.body).css({'cursor' : 'wait'});
        var request = 'index.php?r=/google/api/scan-disk';
        console.log(request);
        $.ajax({
            url: request,
            type: 'GET',
            dataType: 'json',
           // data: data,
            success: function(res){     
               $(document.body).css({'cursor' : 'default'});        
               console.log(res);
<?php 
    if (!empty($model->orgRef)){
        echo "openWin('bank/operator/doc-list&flt=newOnly','newDoc');";
    }
?>
               document.location.reload(true);      
            },
            error: function(){
                $(document.body).css({'cursor' : 'default'});     
                alert('Error while scan documents!');
            }
        });	    
  
}

function scanYandex(){

    $(document.body).css({'cursor' : 'wait'});
    var data = $('#saveDataForm').serialize();
        $.ajax({
        
            url: 'index.php?r=/yandex/api/get-chk-disk',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(res){     
            $(document.body).css({'cursor' : 'default'});     
                document.location.reload(true);     
            },
            error: function(){
            $(document.body).css({'cursor' : 'default'});     
                //alert('Ошибка сканирования');
                document.location.reload(true);     
            }
        });	    

    
}

</script> 


<table width='100%'><tr>

<td width='90px'>
    <input type='button' class='btn btn-default' value='Сканы' onclick='openScanWindow();'>
</td>
<td width='70px'>
        <?php        
        echo \yii\helpers\Html::tag( 'div', "G", 
                   [
                     'class'   => 'btn btn-default',
                     'title'   => 'Сканировать Google-диск',
                     'onclick' => 'scanGoogleDisk();',
                     'style'   => 'color:DarkBlue;font-size:15px;',                     
                   ]);     
        ?>
</td>         
<td width='70px'>
        <?php        
        echo \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'btn btn-primary',
                     'title'   => 'Добавить документ',
                     'onclick' => 'regNewDoc();',
                     //'style'   => $style,                     
                   ]);     
        ?>
</td>   
<td width='70px'>
       <a href='#' onclick='loadExtaract();'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span></a>
</td>   

<!-- -->


<td>
</td>   
<?php 
    if (empty($model->orgRef) && ($model->flt != 'newOnly')){
?>
<td width='110px'>
    <a href="#" class='btn btn-primary'  onclick="openWin('store/sale-list');">Реализации</a> 
</td>

<td>
</td>   

<td width='150px'>
    <a href="#" class='btn btn-primary'  onclick="openWin('index.php?r=bank/operator/doc-list&flt=all&<?= Yii::$app->request->queryString  ?>&format=csv&noframe=1','childWin');">Выгрузить</a> 
</td>


<td width='90px'>
<?php  if ($model->detail==0 ) $class = 'leaf-selected';
                            else  $class = ''; 
$all=  $model-> getTotalDoc();                            
                            ?>    
    <a  class='btn btn-primary leaf <?=$class?>' style='background:WhiteSmoke ; color:Blue;' 
        href='index.php?r=bank/operator/doc-list&detail=0#detail_list'>
        <div class='leaf-txt'> Всего </div>
        <div class='leaf-val'><?= $all ?></div> 
        <div class='leaf-sub'> </div>
    </a>    
</td>

<td width='90px'>
<?php  if ($model->detail==1 ) $class = 'leaf-selected';
                            else  $class = ''; 
$err=  $model->getTotalErrors();                            
//
/*href='index.php?r=bank/operator/doc-list&from=<?=$frm?>&to=<?=$to?>&detail=1#detail_list'                        */
                            ?>    
    <div  class='btn btn-primary leaf <?=$class?>' style='background:#fce4c2 ; color:Blue;'  onclick='selectError()'>
        <div class='leaf-txt'> Ошибки: </div>
        <div class='leaf-val'><?= $err ?></div> 
        <div class='leaf-sub'> </div>
    </div>
    
</td>

<td width='90px'>
<?php  if ($model->detail==2 ) $class = 'leaf-selected';
                            else  $class = ''; 
//
/*href='index.php?r=bank/operator/doc-list&from=<?=$frm?>&to=<?=$to?>&detail=1#detail_list'                        */
                            ?>    
    <div  class='btn btn-primary leaf <?=$class?>' style='background:#fce4c2 ; color:Blue;'  onclick='selectControl()'>
        <div class='leaf-txt'> Контроль </div>
        <div class='leaf-txt'>заполнения</div> 
        <div class='leaf-sub'> </div>
    </div>
    
</td>

<?php 
  }
?>

<td>
</td>   
</tr></table> 



<hr>
<?php
$typeArray = $model->getTypeArray();
$typeArray[0]='не задан';
 
  echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
      
        'responsive'=>true,
        'hover'=>false,
        
        /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
         ], */       
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'id' => 'reestrGrid'
        ],


        'columns' => [
            [
                'attribute' => 'docIntNum',
                'label'     => '#',
                'format' => 'raw', 
                'value' => function ($model, $key, $index, $column) {                    
                 
                 $id = 'docIntNum'.$model['id'];
                 $style="";                 
                 $docUri = preg_replace("/\"/","",$model['docURI']);
                 if(empty($docUri)) $style='color:Crimson';
                 $action = "openDoc(".intval($model['id']).",\"".$docUri ."\")";                 
                 $title = "Оператор ".$model['userFIO'];
                   
                  return  \yii\helpers\Html::tag( 'div', $model['docIntNum'], 
                   [
                     'class'   => 'clickable',
                     'title'   => $title,
                     'onclick' => $action,
                     'style'   => $style,
                     'id'      => $id
                   ]);                    
               }
                
            ],  
             
            [
                'attribute' => 'regDateTime',
                'label'     => 'Загружен',
                 //'filter' => false,
                //'format' => ['datetime', 'php:d.m H:i'],
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                 
                   $regTime = strtotime($model['regDateTime']);
                   if ($regTime > 100) return  date("d.m.y H:i", $regTime);
                   else return  "&nbsp;";
   
               }
               
            ],            
            
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',                            
                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";
                 if ($model['refOrg'] == 0) $style='color:Crimson;'; 
                 $id = 'refOrg'.$model['id'];
                 $action = "selectOrg(".$model['id'].",".$model['orgINN'].")";                 
                    return "<div id='".$id."' class='orginfo' onclick='".$action."' style='".$style."'>".$model['orgTitle']."<br>".$model['orgINN']."</div>";
               }

            ],            
            
            [
                'attribute' => '-',
                //'filter' => false,
                'label'     => 'Документ',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {    
                
                $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','docType');       
                $operationArray[0]='Не задан';
                $v=$operationArray[$model['docClassifyRef']];
                if (empty($model['docClassifyRef'])) $v =  $model['docTitle'];
                    return "<a href ='#' onclick=\"openExtWin('".$model['docURI']."','childWin');\" 
                    >".$v."</a>";
               }
                       
            ],            

            [
                'attribute' => 'docOrigNum',
                //'filter' => false,
                'label'     => '№',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                $strSql = "SELECT {{%doc_classify}}.id, docType from {{%doc_classify}}";
                $list = Yii::$app->db->createCommand($strSql)->queryAll();
                $operationArray =  ArrayHelper::map($list,'id','docType');
                $operationArray[0]='Не задан';
                if (empty($model['docClassifyRef'])) $v =  $model['docTitle'];
                    return "<a href ='#' onclick=\"openExtWin('".$model['docURI']."','childWin');\"
                    >"." ".$model['docOrigNum']."</a>";
               }

            ],

            [
                'attribute' => 'docOrigDate',
                //'filter' => false,
                'label'     => 'Дата',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {    
                
                $docOrigTime = strtotime($model['docOrigDate']);
                if ($docOrigTime  > 100) return date("d.m.y", $docOrigTime);
                else  return "&nbsp;";

               }
                       
            ],            

            
           [
                'attribute' => 'docSum',
                'filter' => false,
                'label'     => 'На сумму',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['docSum'],2,',','&nbsp;');
               }
                
            ],            
            
           [
                'attribute' => 'docOrigStatus',
                'filter' => [0 => 'Все', 1 => 'Оригинал',  2 => 'Копия', 3 => 'Скан', ],
                'label'     => 'Статус',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {     
                    switch ($model['docOrigStatus']){
                        case  1: return "Копия"; break;
                        case  2: return "Скан"; break;
                        default: return "Оригинал"; break;
                   }
               }
            ],            
            
 
            [
                'attribute' => 'docNote',
                'filter' => false,
                'label'     => 'Комментарий',
                'format' => 'raw',                            
            ],            


               [
                'attribute' => 'docArticleRef',
                'label' => 'Тип',
                'filter' => false,
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                             
                $strSql = "SELECT {{%bank_op_article}}.id, grpTitle from {{%bank_op_article}},{{%bank_op_grp}}
                where {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id"; 
                        
                
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','grpTitle');       
                $operationArray[0]='не задан';

                 $c = "";
                 if ($model['docArticleRef'] == 0) $c = 'color:Crimson;';                  
                 $id = "orgType".$model['id'];
                                /*, ".$model['orgDeal']."*/
                 $action = "selectDeal(".$model['id'].",".$model['refOrg'].", ".$model['docArticleRef']." )";                 
                 $v=$operationArray[$model['docArticleRef']];
                 return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  =>  $c,
                   ]);
                   
                
                },
            ],    
                                        
                     
            [
                'attribute' => 'docArticleRef',
                'label' => 'Статья',
                'filter' => false,
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                    
                $strSql = "SELECT {{%bank_op_article}}.id, article from {{%bank_op_article}}"; 
                    
                
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','article');       
                $operationArray[0]='не задан';

                 $c = "";
                 if ($model['docArticleRef'] == 0) $c = 'color:Crimson;';                  
                 $id = "orgDeal".$model['id'];
                                
                 $action = "selectDeal(".$model['id'].",".$model['refOrg'].", ".$model['docArticleRef']." )";                 
                 $v=$operationArray[$model['docArticleRef']];
                 return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  =>  $c,
                   ]);
                   
                
                },
            ],       

                                     
           [
                'attribute' => 'isOplate',
                'filter' => [0 => 'Все', 1 => 'Да',  2 => 'Нет'],
                'label'     => 'Оплата',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['isOplate'] == 1) return "Да";
               }                
            ],            
                                     
            [
                'attribute' => 'ref1C_input',
                'filter' => false,
                'label'     => 'Документ в 1C',
                'format' => 'raw',                            
               'value' => function ($model, $key, $index, $column){                 
                if (empty($model['ref1C_input'])){  $val ="-----";}
                                           else {  $val =$model['ref1C_input'];}

                $style='color:Crimson';                           
                if (!empty($model['refClientSchet'])) $style='color:Green';
                if (!empty($model['refPurch'])) $style='color:Green';                          
                if (!empty($model['refSupply'])) $style='color:Green';                          
                if (!empty($model['refSupplierSchet'])) $style='color:Green';                                                    
                                                                                      
                    $id = 'ref1C_input'.$model['id'];
                    $action ="select1C_schet(".$model['id'].")";
                    
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'title'   => 'Зарегистрирован в 1С как',
                     'onclick' => $action,
                     'style'   => $style,
                     'id'      => $id
                   ]); 
                }
                
            ],            

            [
                'attribute' => 'ref1C_schet',
                'filter' => false,
                'label'     => 'Cч. в 1C',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column){                
                  /*if (empty($model['ref1C_schet'])){ $style='color:Crimson'; $val ="-----";}
                                            else { $style='color:Green'; $val =$model['ref1C_schet'];}*/
                    $val =$model['ref1C_schet'];                        
                    $id = 'ref1C_schet'.$model['id'];
                    //$action ="select1C_schet(".$model['id'].")";
                    
                return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'title'   => 'Связан со счетом/документом в 1с',
                    // 'onclick' => $action,
                     //'style'   => $style,
                     'id'      => $id
                   ]); 
                
                }
            ],            
            
            
            [
                'attribute' => 'contragentType',
                'label' => 'Статус',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '120px'] ,  
                'value' => function ($model, $key, $index, $column) use($typeArray) {                        
                  
                $c = "";
                if ($model['contragentType'] == 0) $c = 'color:Crimson;'; 
                  $id = "contragentType".$model['id'];
                if (empty($model['contragentType'])) $contragentType = '0';
                                               else $contragentType = $model['contragentType'];                  
                  return Html::dropDownList( 
                          $id, 
                          $contragentType, 
                              $typeArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveField('.$model['id'].',"contragentType");'
                              ]);
                },
            ],    
           
            [
                'attribute' => 'operationType',
                'label' => 'Операция',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                 if (empty($model['contragentType'])) $contragentType = 0;
                                                 else $contragentType = $model['contragentType'];
                 
                $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$contragentType." ORDER BY id"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','operationTitle');       
                $operationArray[0]='не задан';
                 $c = "";
                 if ($model['contragentType'] == 0) $c = 'color:Crimson;';                  
                  $id = "operationType".$model['id'];
                  return Html::dropDownList( 
                          $id, 
                          $model['operationType'], 
                              $operationArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveField('.$model['id'].',"operationType");'
                              ]);
                },
            ],    
           

            [
                'attribute' => 'isTTN',
                'label' => 'ТТН/Акт',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '50px'] ,    
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isTTN".$model['id'];
                $style="";    
                           
                if ($model['isTTN'] == 0) $style='background:White;';                  
                                    else  $style='background:Green;';     
                     
               $action =  "switchData(".$model['id'].", 'isTTN');";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'ТТН/Акт',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               
            

            [
                'attribute' => 'isUTR',
                'label' => 'УТР',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '50px'] ,    
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isUTR".$model['id'];
                $style="";    
                           
                if ($model['isUTR'] == 0) $style='background:White;';                  
                                    else  $style='background:Green;';     
                     
               $action =  "switchData(".$model['id'].", 'isUTR');";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'УТР',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               


            [
                'attribute' => 'docGoal',
                'filter' => [0 => 'Все', 1 => 'Бухгалтерия',  2 => 'Офис', 3 => 'Производство', ],
                'label'     => 'Ответств.',
                'format' => 'raw',                            
            ],            
                                     
            [
                'attribute' => 'docOwner',
                'filter' => [0 => 'Все', 1 => 'Бухгалтерия',  2 => 'Офис', 3 => 'Производство', ],
                'label'     => 'Передать',
                'format' => 'raw',                            
            ],            

          /***/                                                                                  
        ],
    ]
); 

?>

<?php
Modal::begin([
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectOrgDialogFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/doc-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectErrDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectErrDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/bank/operator/doc-error-calendar&noframe=1&month=<?=$model->month?>&year=<?=$model->year?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

<?php
Modal::begin([
    'id' =>'selectControlDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectControlDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/bank/operator/doc-control-calendar&noframe=1&month=<?=$model->month?>&year=<?=$model->year?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>




<?php
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' =>'index.php?r=/bank/operator/set-deal-param']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>

<?php
Modal::begin([
    'id' =>'loadFileDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],        
]);?><div style='width:650px'>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
  <?php
    echo $form->field($model, 'loadFile')->fileInput()->label('Выберите файл в формате 1CClientBankExchange');
  ?>
  <?= Html::submitButton('Загрузить документ', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>


</div><?php
Modal::end();
?>


<?php
Modal::begin([
    'id' =>'select1C_schetDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',
    'style' => 'width:1024px;'
    ],    
]);?><div style='width:1024px'>

    <iframe id='select1C_schetFrame' width='670px' height='520px' frameborder='no'   src='index.php?r=bank/operator/select-schet&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  

</div><?php
Modal::end();
?>





<?php
Modal::begin([
    'id' =>'selectOrgDeal',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectOrgDealFrame' width='420px' height='420px' frameborder='no'   src='index.php?r=/site/org-deal-select&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

