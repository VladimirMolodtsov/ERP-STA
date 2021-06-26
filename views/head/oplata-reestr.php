<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;


$this->title = 'Оплаты по счетам. Управление';



?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 
<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<style>
.gridcell_ {
    display: block;
    font-size: 12px;	
    text-align: center;
}
.gridcell {
	width: 120px;		
	height: 100%;
    display: block;
    font-size: 12px;	
    text-align: center;
    word-wrap: break-word;
	/*background:DarkSlateGrey;*/
}	
.gridcell:hover{
	background:Silver;
    cursor: pointer;
	color:#FFFFFF;
}
.editcell{
   width: 120px;
   display:none;
   white-space: nowrap;
   background:White;
}

.label-local{
   width: 190px;		
}

.table-local {    
  font-size: 12px;
}

/*Контейнер для листиков*/
.leaf {
    height: 60px; /* высота нашего блока */
    width:  120px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    margin-top: -60px;
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-txt {    
    font-size:15px;
}
.leaf-val {    
    font-size:20px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}




</style>

<script type="text/javascript">
var reestrId =0;

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

function rmOplata(id)
{
    openSwitchWin('fin/rm-from-reestr&id='+id);
}

function removeOplata(id, linkId)
{
    openSwitchWin('fin/detach-from-reestr&id='+id+'&linkId='+linkId);
}
function setFormDate(id)
{
 
 editId = 'edit_formDate'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-formdate&id='+id+'&val='+Val); 
}

function setOplateDate(id)
{
 
 editId = 'edit_oplateDate'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-oplatedate&id='+id+'&val='+Val); 
}

function setNote(id)
{
 
 editId = 'edit_note'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-note&id='+id+'&val='+Val); 
}

function setOplateType(id)
{
 
 editId = 'edit_oplateType'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-oplatetype&id='+id+'&val='+Val); 
}

function setSummOplate(id)
{
 
 editId = 'edit_summOplate'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-summoplate&id='+id+'&val='+Val); 
}

function setSummRequest(id)
{
 
 editId = 'edit_summRequest'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-summrequest&id='+id+'&val='+Val); 
}

function setOrg(id)
{
 
 editId = 'edit_Org'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-org&id='+id+'&val='+Val); 
}



function openMultiSchetList()
{
  showDialog('#multi_schet_list_form'); 
}

function openSchetList(id)
{
 reestrId = id;
 showDialog('#schet_list_form'); 
}    

function openOplateList(id)
{
 reestrId = id;
 
 document.getElementById('oplate_list_frame').src='index.php?r=fin/oplata-list-reestr&noframe=1&reestrId='+reestrId;
 showDialog('#oplate_list_form'); 
}    

function closeOplateList(oplateId)
{
  openSwitchWin('fin/reestr-set-oplata&reestrId='+reestrId+'&oplateId='+oplateId);         
}

function closeSchetList(schetId)
{
  openSwitchWin('fin/reestr-set-schet&id='+reestrId+'&schetId='+schetId);  
}

function closeMultiSchetList(schetIdList)
{
  openSwitchWin('fin/reestr-set-multi-schet&schetListId='+schetIdList);  
}

function setNormative(id)
{
 
 editId = 'edit_plan'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-plan&id='+id+'&val='+Val); 
}


function finitOplata(reestrId)
{
  openSwitchWin('fin/reestr-finit&id='+reestrId);  
}

function openReestrDetail(id)
{
 openWin('head/reestr-detail&noframe=1&id='+id,'reestrWin');  
}    


</script>


<h3><?= Html::encode($this->title) ?></h3>

<table border='0' width='100%'>
<tr>
    <td width='600px'>
 <?php 
    $leafValue   =$model->getLeafValue ();
    $normProvider= $model->getNormProvider(); 
    
    

    
    $content = \yii\grid\GridView::widget(
    [
        'dataProvider' => $normProvider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
                'attribute' => 'normTitle',
				'label'     => 'Название',
                'format' => 'raw',
         /*       'value' => function ($model, $key, $index, $column) {                                 
                $id = "normTitle".$model['id'];    
                $ret ="<div id='viewBox_".$id."' class='gridcell' style='width:75px;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$model['oplateDate']."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value='".$model['normTitle']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setNormTitle('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;
               },*/                                                
            ],		


     	    [
                'attribute' => 'plan',
				'label'     => 'Норматив',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $id = "plan".$model['id'];    
                $ret ="<div id='viewBox_".$id."' class='gridcell' style='text-align:right; width:75px;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".number_format($model['plan'],2,'.','&nbsp;')."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value='".$model['plan']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setNormative('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;
                },                

            ],		
            
     	    [
                'attribute' => 'Начисление',
				'label'     => 'Начисление',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $strSql = "Select Sum(goodSumm) from {{%supplier_schet_header}}, {{%supplier_schet_content}}, {{%reestr_oplat}} 
                 where {{%supplier_schet_header}}.id = {{%reestr_oplat}}.refSchet AND {{%supplier_schet_header}}.id = {{%supplier_schet_content}}.schetRef
                 AND  MONTH({{%reestr_oplat}}.formDate) =:month  AND  YEAR({{%reestr_oplat}}.formDate) =:year AND {{%reestr_oplat}}.oplateType =:oplateType ";                
                $ret = Yii::$app->db->createCommand($strSql,                  
                 [':month' => date('n'),':year' => date('Y'), ':oplateType' => $model['id']])->queryScalar();      
                 
				return  "<div style=' text-align:right;'>".number_format($ret,2,'.','&nbsp;')."</div>";
                },                
                
            ],		

     	    [
                'attribute' => 'В оплату',
				'label'     => 'В оплату',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                
                $ret = Yii::$app->db->createCommand("Select Sum(summOplate) from  {{%reestr_oplat}}  
                 where MONTH({{%reestr_oplat}}.formDate) =:month  AND  YEAR({{%reestr_oplat}}.formDate) =:year AND {{%reestr_oplat}}.oplateType =:oplateType",                  
                 [':month' => date('n'),':year' => date('Y'),':oplateType' => $model['id'],])->queryScalar();      
				return  "<div style=' text-align:right;'>".number_format($ret,2,'.','&nbsp;')."</div>";
                },                
                
            ],		

     	    [
                'attribute' => 'Факт',
				'label'     => 'Факт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                
                 $ret = Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}}, {{%reestr_oplat}}  
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_oplat}}.id = {{%reestr_lnk}}.reestrId  
                 AND  MONTH({{%reestr_oplat}}.formDate) =:month  AND  YEAR({{%reestr_oplat}}.formDate) =:year AND {{%reestr_oplat}}.oplateType =:oplateType",                  
                 [':month' => date('n'),':year' => date('Y'),':oplateType' => $model['id']])->queryScalar();      

				return  "<div style=' text-align:right;'>".number_format($ret,2,'.','&nbsp;')."</div>";
                },                
                
            ],		
            
      ]//columns            
    ]
	);
    
    //<span class=\"glyphicon glyphicon-triangle-bottom\" aria-hidden=\"true\"></span>
     echo Collapse::widget([
    'items' => [
        [
        
        
            'label' => "Нормативы: ▼ ",                        
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
    
    
    ?>    
    
    
    
    
   <?php  
   $sync = $model->getSyncValue();
   $utr = $model->getLastSuplierSchet ();      
   ?>  
    
    </td>
    <td align='center' >
     <a  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:Crimson;' href='#' onclick="openMultiSchetList();" >      
        <div class='leaf-txt'>Новые счета: </div>
        <div class='leaf-val'><?= $leafValue['schetNew'] ?></div>
        <div class='leaf-sub' > </div>
      </a>

     <div  class='btn btn-primary leaf' style='background:WhiteSmoke ; color:DarkBlue;'  >      
        <div class='leaf-txt'>В работе: </div>
        <div class='leaf-val'><?= $leafValue['activeOplate'] ?></div>
        <div class='leaf-sub' > </div>
      </div>
      <br>
    <font size='-1'> Последняя синхронизация счета: <br><?= $sync['supplierSchet']."&nbsp;(".$utr.")" ?></font>
    
    </td>

</tr>
</table>
<?php
$normTitleList = $model->getNormTitle();
 	
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
        /*    [
                'class' => \yii\grid\SerialColumn::class,
            ],*/
 
            [
                'attribute' => 'schDate',
				'label'     => 'Дата <br> счета',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if (empty($model['schNum'])) $val = '&nbsp;';                   
                    else $val = date("d.m.Y", strtotime($model['schDate']));                    
					return  "<div  class='gridcell' style='width:65px;' onclick=\"javascript:openReestrDetail('".$model['id']."');\">&nbsp;".$val."</div>"; 
                },

            ],		

     	    [
                'attribute' => 'schNum',
				'label'     => 'Номер <br> счета',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    if (empty($model['schNum'])) $val = '&nbsp;';
                    else $val = $model['schNum'];                    
					return  "<div  class='gridcell'  style='width:50px;' onclick=\"javascript:openReestrDetail('".$model['id']."');\">&nbsp;".$val."</div>"; 
                },

            ],		

            [
                'attribute' => 'oplateDate',
				'label'     => 'Дата <br> платежа',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                   
                $back = "";
                $oplateTime = strtotime($model['oplateDate']);
                if ($oplateTime > 0) 
                {
                    $oplateDate = date("d.m.Y",$oplateTime);                
                    if($oplateTime <= time()) $back = "	background-color: Gold ;"; 
                }
                else  $oplateDate = " - ";
                
                
                
                 
                $id = "oplateDate".$model['id'];    
                $ret="<div id='viewBox_".$id."' class='gridcell' style='width:75px; font-weight: bold; ".$back."' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$oplateDate."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell'><nobr>";
                $ret.="<input  id='edit_".$id."' class='tcal' style='width:75px;' value='".$model['oplateDate']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setOplateDate('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                
				return  $ret;
               },                                
            ],		
            
     	    [
                'attribute' => 'orgTitle',
				'label'     => 'Компания',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $id = "Org".$model['id'];    
                
                if (!empty($model['schNum'])) $ret = "<div style='width:195px;'>".$model['orgTitle']."</div>";
                else {
                $ret ="<div id='viewBox_".$id."' class='gridcell'  style='width:190px;  text-align:left;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$model['orgTitle']."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell'  style='width:200px;' ><nobr>";
                $ret.="<input  id='edit_".$id."'   style='width:75px;' value='".$model['orgTitle']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setOrg('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                }
				return  $ret;
                },                

            ],		
            
     	    [
                'attribute' => 'summRequest',
				'label'     => 'Сумма <br> счета',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $id = "summRequest".$model['id'];    
                
                if (!empty($model['schNum'])) $ret = "<div style='width:65px; text-align:right;'>".number_format($model['summRequest'],2,'.','&nbsp;')."</div>";
                else {
                    
                $ret ="<div id='viewBox_".$id."' class='gridcell'  style='width:65px; text-align:right;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".number_format($model['summRequest'],0,'.','&nbsp;')."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:90px;' ><nobr>";
                $ret.="<input  id='edit_".$id."'   style='width:75px;' value='".$model['summRequest']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setSummRequest('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
                }

				return  $ret;
                },                
                
            ],		

            
            
     	    [
                'attribute' => 'Оплачено',
				'label'     => 'Оплачено',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $id = "summRequest".$model['id'];    
                                
                $oplSumm = Yii::$app->db->createCommand("Select Sum(lnkOplate) from  {{%reestr_lnk}} 
                 where  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $model['id'],])->queryScalar();      
                 /*$color = "Green";
                 if ($oplSumm < $model['summRequest'])$color = "Crimson";
                 color:".$color.";*/
                $ret= "<div  class='gridcell' style='width:80px;   text-align:right;' onclick=\"javascript:openOplateList('".$model['id']."');\" >".number_format($oplSumm,0,'.','&nbsp;')."&nbsp;&nbsp;&nbsp;<span class='glyphicon glyphicon-plus' aria-hidden='true'></span></div>";
                //$ret.= "<div  class='gridcell'  style='text-align: right;' onclick=\"javascript:openOplateList('".$model['id']."');\"> <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></div>"; 
                
//                $ret= "<div style='color:Brown;'>".number_format(($model['summRequest']-$oplSumm),2,'.','&nbsp;')."</div>";
				return  $ret;
                },                
                
            ],		

     	    [
                'attribute' => 'Остаток',
				'label'     => 'Остаток',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {      

                $oplSum= Yii::$app->db->createCommand("Select Sum(lnkOplate) from  {{%reestr_lnk}} 
                 where {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $model['id'],])->queryScalar();
                 
                 $val = $model['summRequest']-$oplSum;
                 $bg = "";
                 $color = "color:Brown; ";
                 if ($val < 0) $bg = "background-color: Yellow ;";
                 if ($val == 0) $color = "color:DarkGreen; ";
                 $ret= "<div style=' text-align:right;".$color.$bg."'>".number_format($val,2,'.','&nbsp;')."</div>";
                 return   $ret;
               },         
            ],		

     	    [
                'attribute' => '',
				'label'     => 'Сверка/<br> Оплаты',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                 $strSql= "SELECT id FROM  {{%control_sverka_header}}  ORDER BY onDate DESC, id DESC LIMIT 1";
                 $list  =Yii::$app->db->createCommand($strSql)->queryAll();   
                 if (count($list) == 0 ) $headerRef = 0;
                                     else $headerRef=$list[0]['id'];
                 if (empty ($headerRef)) $headerRef = 0;                    
                    
                 $strSql= "SELECT sum(balanceSum) FROM  {{%control_sverka_dolga}} as a, {{%control_sverka_dolga_use}} as b where 
                 a.useRef = b.id AND 
                 headerRef =:headerRef AND b.orgRef = :refOrg";
                 $sverka=Yii::$app->db->createCommand($strSql, [':refOrg' => $model['refOrg'],':headerRef' => $headerRef])->queryScalar();   
                 if ($sverka >= 0) $add="<font color='DarkGreen'>". number_format($sverka,0,'.',"&nbsp")."</font>";
                              else $add="<font color='Crimson'>". number_format($sverka,0,'.',"&nbsp")."</font>";

                 if (empty($model['schNum'])) $opalte= '&nbsp;';                 
                 else
                 {
                 $strSql= "SELECT sum(oplateSumm) FROM  {{%supplier_oplata}} where refOrg = :refOrg AND oplateDate >= :oplateDate";
                 $opalte=Yii::$app->db->createCommand($strSql, [':refOrg' => $model['refOrg'], ':oplateDate' =>$model['schDate']])->queryScalar();   
                 $opalte=number_format($opalte,0,'.',"&nbsp");
                 }

				return  $add."<br>". $opalte;
                },                
                
            ],		

            
     	    [
                'attribute' => 'summOplate',
				'label'     => 'Оплатить',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {      

                $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $model['id'],])->queryScalar();
                 
                 $val = $model['summRequest']-$oplSum;
                
                $id = "summOplate".$model['id'];    
                $ret ="<div id='viewBox_".$id."' style='width:65px; text-align:right;'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;". number_format($model['summOplate'],0,'.','&nbsp;')."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:90px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:65px;' value='".$val."'>";
                $ret.="<a href ='#' onclick=\"javascript:setSummOplate('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;
               },         
            ],		

            [
                'attribute' => 'oplateType',
				'label'     => 'Статья',
                'format' => 'raw',
                'filter' => $normTitleList,
                'value' => function ($model, $key, $index, $column) use ($normTitleList) {                    
                $id = "oplateType".$model['id'];    
                $ret ="<div id='viewBox_".$id."' class='gridcell'  style='width:100px;' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$model['normTitle']."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:120px;' ><nobr>";
                $ret.=Html::dropDownList('cat', 'null', $normTitleList , ['id'=>'edit_'.$id, 'style' => 'width:100px;']);
                //$ret.="<input  id='edit_".$id."'  style='width:75px;'  value='".$model['oplateType']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setOplateType('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;   
                },                        
            ],		

            
     	    [
                'attribute' => 'note',
				'label'     => 'Комментарий',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                    
                $id = "note".$model['id'];    
                $ret ="<div id='viewBox_".$id."' class='gridcell' style='width:140px; ' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;".$model['note']."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:165px;' ><nobr>";
                $ret.="<input  id='edit_".$id."'  style='width:140px;' value='".$model['note']."'>";
                $ret.="<a href ='#' onclick=\"javascript:setNote('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;   
                },                        
            ],		
            

 
     /*	    [
                'attribute' => 'Оплаты',
				'label'     => 'Платежки',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $ret="";   
                 $listOplLnk = Yii::$app->db->createCommand("Select {{%reestr_lnk}}.id, oplateDate, oplateSumm from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $model['id'],])->queryAll();      
                 $ret.="<table width='100%' border='0'>";
                 for ($i=0; $i< count($listOplLnk); $i++)
                 {
                  $ret.="<tr><td><b>".number_format($listOplLnk[$i]['oplateSumm'],'2','.','&nbsp;')."</b></td><td>&nbsp;от:&nbsp;".date('d.m', strtotime($listOplLnk[$i]['oplateDate']))."</td>";                    
                  $ret.="<td style='color:Crimson; padding:3px'><a href='#' style='color:Crimson;' onclick=\"removeOplata('".$model['id']."','".$listOplLnk[$i]['id']."');\"><span class='glyphicon glyphicon-remove' aria-hidden='true'></span></a></td></tr>";                    
                 }
                 $ret.="</table>";
                  $ret.="<div  class='gridcell'  style='text-align: right;' onclick=\"javascript:openOplateList('".$model['id']."');\"> <span class='glyphicon glyphicon-plus' aria-hidden='true'></span></div>"; 
                  return $ret;
                },

            ],	*/	
            
            [
                'attribute' => 'isActive',
				'label'     => 'Завер.',
                'format' => 'raw',
                'filter' => [
                '1'=>'Все',
                '2'=>'Актив.',
                '3'=>'Завер.',
                ],
                'value' => function ($model, $key, $index, $column) {
                if ($model['isActive'] == 0) return "&nbsp;";    
				return "<nobr><a href='#' class='btn btn-primary' style='padding-top: 0px; padding-bottom: 0px; padding-left: 2px; padding-right: 2px; background-color: White ;' onclick=\"javascript:finitOplata('".$model['id']."');\"><font size='-3' color='Green'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span></font></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick=\"javascript:rmOplata('".$model['id']."');\"><font color='Crimson'><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span></font></a></nobr>";
				;
                },
            ],		

            
            
        ],
    ]
	);
?>

<div class="row">  
    <div class="col-md-8">
   </div>   

   
    <div class="col-md-3">
		<input class="btn btn-primary"  style="width:220px;" type="button" value="Создать новый"  onclick="javascript:document.location.href='index.php?r=fin/add-in-reestr';"/>
   </div>   

    <div class="col-md-1">
    <a href="#" onclick="openWin('index.php?r=head/oplata-reestr&<?= Yii::$app->request->queryString  ?>&format=print&noframe=1','printWin');"><span class="glyphicon glyphicon-print"></span></a> 
   </div>   
   
   
</div>      




<!-------------->

<!--- Форма список счетов ----->	
  <div id="schet_list_form" class='popup_form' style='height: 660px; width: 820px; margin-left: -300px; margin-top: -300px;'>
	<span id="schet_list_close"  class='popup_close' onclick='closeDialog("#schet_list_form")' >X</span>	
	<iframe width='800px' height='620px' frameborder='no'   src='index.php?r=fin/schet-list-reestr&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>

<!--- Форма список счетов для создания ----->	
  <div id="multi_schet_list_form" class='popup_form' style='height: 650px; width: 920px; margin-left: -300px; margin-top: -300px;'>
	<span id="multi_schet_list_close"  class='popup_close' onclick='closeDialog("#multi_schet_list_form")' >X</span>	
	<iframe width='900px' height='600px' frameborder='no'   src='index.php?r=fin/multi-schet-list-reestr&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  
   <br>   
  </div>
  
<!--- Форма список оплат ----->	
  <div id="oplate_list_form" class='popup_form' style='height: 650px; width: 820px; margin-left: -300px; margin-top: -300px;'>
	<span id="oplate_list_close"  class='popup_close' onclick='closeDialog("#oplate_list_form")' >X</span>	
	<iframe width='800px' height='620px' frameborder='no' id="oplate_list_frame"  src='index.php?r=fin/oplata-list-reestr&noframe=1' seamless>
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
		$('#schet_list_form', '#oplate_list_form', '#multi_schet_list_form')
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
  

