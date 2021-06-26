<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Платежки';
$curUser=Yii::$app->user->identity;

?>
<style>
.table-local {    
  font-size: 12px;
}

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



</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setOplate(id) {
	window.parent.closeOplateList(id);
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

function setLnkOplate(id)
{
 
 editId = 'edit_lnkOplate'+id;   
 Val = document.getElementById(editId).value;
 openSwitchWin('fin/reestr-set-lnkoplate&id='+id+'&val='+Val); 
 
}


</script >

<p> <b>Включено в текущий реестр </b></p>
<?php

$reestrId = $model->reestrId;
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $providerAttached,
	//	'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
			[
                'attribute' => 'Сделка',
				'label'     => 'Сделка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    //Уберем все не цифровые c начала текста            
                    $schetNum = preg_replace("/^[\D]+/u","",$model['sdelkaNum']);	
                    //И предшествующие нули тоже
                    $schetNum = preg_replace("/^0+/u","", $schetNum);	
                    
                    if ( strtotime($model['sdelkaDate']) > 0 )
                        return "<b>".$schetNum."</b>&nbsp;от&nbsp;".date('m.d.Y', strtotime($model['sdelkaDate']));
                    else 
                        return "<b>".$schetNum."</b>";
                },
                
            ],		            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'oplateDate',
				'label'     => 'Платежка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<nobr>".$model['oplateDate']." на сумму ".$model['oplateSumm']."</nobr>";
                },
                
            ],		

     	   [
                'attribute' => 'lnkOplate',
				'label'     => 'Сумма оплаты',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($reestrId) {
                    
                //Общая сумма в реестре
                $reestrSum= Yii::$app->db->createCommand("Select summRequest from  {{%reestr_oplat}} 
                 where {{%reestr_oplat}}.id =:reestrId",                  
                 [':reestrId' => $reestrId,])->queryScalar();

                $oplSum= Yii::$app->db->createCommand("Select Sum(oplateSumm) from {{%supplier_oplata}}, {{%reestr_lnk}} 
                 where {{%supplier_oplata}}.id = {{%reestr_lnk}}.oplataId AND  {{%reestr_lnk}}.reestrId =:reestrId",                  
                 [':reestrId' => $model['id'],])->queryScalar();
                 
                //уже потрачено по платежке
                $varSum= Yii::$app->db->createCommand("Select Sum(lnkOplate) from  {{%reestr_lnk}} 
                 where {{%reestr_lnk}}.oplataId =:oplataId",                  
                 [':oplataId' => $model['id'],])->queryScalar();
                 
                // Остаток
                $remainSum = max($reestrSum-$oplSum, 0);
                 // Остаток в платежке
                $remainPlat = max($model['oplateSumm']-$varSum, 0);     
     
                $val = min($remainSum, $remainPlat );
                
                $id = "lnkOplate".$model['id'];    
                $ret ="<div id='viewBox_".$id."' style='width:75px; text-align:right;'  class='gridcell' onclick=\"javascript:showEditBox('".$id."');\">&nbsp;". number_format($model['lnkOplate'],2,'.','&nbsp;')."</div>"; 
                $ret.="<div id='editBox_".$id."' class='editcell' style='width:100px;'><nobr>";
                $ret.="<input  id='edit_".$id."' style='width:75px;' value='".$val."'>";
                $ret.="<a href ='#' onclick=\"javascript:setLnkOplate('".$model['id']."'); \"> <span class='glyphicon glyphicon-ok' aria-hidden='true'></span> </a>";
                $ret.="<a href ='#' onclick=\"javascript:closeEditBox('".$id."'); \"><span class='glyphicon glyphicon-remove' style='color:Crimson;' aria-hidden='true'></span></a>";
                $ret.="</nobr></div>";
				return  $ret;
                    
                    
                },
                
            ],		

            
            
   			[
                'attribute' => 'Удал.',
				'label'     => 'Удал.',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($reestrId)  {
					return "<a href='#' style='color:Crimson;' onclick=\"window.parent.removeOplata('".$reestrId."','".$model['id']."');\"><span class='glyphicon glyphicon-remove' area-hidden='true'></span></a>";
                },
                
            ],		
  
            

            
        ],
    ]
	);
?>


<p><b> Доступные платежи </b></p>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
			[
                'attribute' => 'sdelkaNum',
				'label'     => 'Сделка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    //Уберем все не цифровые c начала текста            
                    $schetNum = preg_replace("/^[\D]+/u","",$model['sdelkaNum']);	
                    //И предшествующие нули тоже
                    $schetNum = preg_replace("/^0+/u","", $schetNum);	
                    
                    if ( strtotime($model['sdelkaDate']) > 0 )
                        return "<b>".$schetNum."</b>&nbsp;от&nbsp;".date('m.d.Y', strtotime($model['sdelkaDate']));
                    else 
                        return "<b>".$schetNum."</b>";
                },
                
            ],		            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		

			[
                'attribute' => 'oplateDate',
				'label'     => 'Платеж',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					return "<nobr><a href='#' onclick='javascript:setOplate(\"".$model['id']."\");' > ".$model['oplateDate']." на сумму ".$model['oplateSumm']."</a></nobr>";
                },
                
            ],		

			[
                'attribute' => 'inReestr',
				'label'     => 'В реестр',
                'format' => 'raw',
                'filter' => [
                              '1'=>'Все',
                              '2'=>'Не вкл.',
                              '3'=>'Включен',                
                            ],
                            
                'value' => function ($model, $key, $index, $column) {
                    ; 
                    if ($model['inReestr'] >0  ){ $isOp = true;}
					else                        { $isOp = false;}
                    return "<div style='text-align:center;' >".\yii\helpers\Html::tag(
                        'span',
                        $isOp ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isOp ? 'success' : 'danger'),
                        ]
						)."</div>";					
                },
                
            ],		
            
            
            
        ],
    ]
	);
?>
