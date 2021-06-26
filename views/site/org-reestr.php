<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Реестр клиентов';
//if (Yii::$app->user->isGuest == true){ return;}

$lastSync = $model->getLastSync();
$err = $model->getOrgReestrErr();
$noActive= $model->getOrgInactive();
$all = $model->getAllActiveOrg();
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">
function selectAll()
{    
  document.location.href="index.php?r=site/org-reestr&detail=0";   
}

function selectError()
{    
  document.location.href="index.php?r=site/org-reestr&detail=1";   
}
function selectInactive()
{    
  document.location.href="index.php?r=site/org-reestr&detail=2";   
}



function createOrg()
	{ 
	
	title = "Создать автоматически";
    var url = 'index.php?r=site/create-org&orgTitle=Новая организация';
    console.log(url);
 $.ajax({
 url: url,
 type: 'GET',
 dataType: 'json',
 //data: data,
 success: function(res){
   openWin('site/org-card&orgId='+res.id, 'orgWin');
 },
 error: function(){
   //console.log(res);
  alert('Error while create contragent!');
 }
 });	
	
}



</script> 
 
<style>
.grd_date_val
{
    padding: 2px;
    font-size: 12px;
    width: 80px;
}
.grd_cell
{
    padding: 2px;
    font-size: 12px;
}


.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
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

<table width=100% border=0><tr>

<td>    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectAll();'>
        <div class='leaf-txt' style='color:Crimson' > Всего  </div>
        <div class='leaf-val' style='color:Crimson'><?= $all ?></div> 
        <div class='leaf-sub'></div>
    </a>
</td>

<td>    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectError();'>
        <div class='leaf-txt' style='color:Crimson' > Ошибок  </div>
        <div class='leaf-val' style='color:Crimson'><?= $err ?></div> 
        <div class='leaf-sub'></div>
    </a>
</td>
  
<td>
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectInactive();'>
        <div class='leaf-txt' > Не Акутивны  </div>
        <div class='leaf-val' ><?= $noActive ?></div> 
        <div class='leaf-sub'></div>
    </a>
</td>

<td>
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='createOrg();'>
        <div class='leaf-txt'>&nbsp;</div>
        <div class='leaf-val' > <span class='glyphicon glyphicon-plus'></span> </div> 
        <div class='leaf-sub'></div>
    </a>
</td>

  
<td>
<?php  
if (strtotime($lastSync) < time()-8*60*60) {$style='color:Crimson;'; $text="Обновить";}
                                  else     {$style=''; $text="";}
 
 echo "<div style='text-align:right;".$style."'> Данные по сделкам актуальны на: <b>". date("d.m.Y h:m", strtotime($lastSync)) ."
<a href='#' onclick='document.location.href=\"index.php?r=site/update-reestr-client\"'> 
 <span class='glyphicon glyphicon-refresh' aria-hidden='true'></span>&nbsp;".$text."</a></b></div>";
?>
</td>
</tr></table>
<div class='spacer'></div>

<?php  
   
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [		        
			[
                'attribute' => 'orgTitle',
				'label' => 'Клиент',
                'contentOptions' => ['class' => 'grd_cell'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                 if(empty($model['orgTitle'])) $orgTitle = '---';
                 else $orgTitle =$model['orgTitle'];
                 $c='';
                 if ($model['isReject'] == 1) $c='color=Gray';
                 if ($model['isOrgActive'] == 0) $c='color=Crimson; text-decoration:line-through;';
                 
                    return "<a href='#' style='".$c."' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\", \"orgWin\")' >".$orgTitle."</a>";
                },
            ],		
    
    	   [
	            'attribute' => 'orgINN',
				'label'     => 'ИНН/КПП', 
                'encodeLabel' => false,                
                'format' => 'raw',
                'contentOptions' => ['class' => 'grd_cell'],
                'value' => function ($model, $key, $index, $column)  {
                    return  "<div style='font-size:12px;width:75px;' ><b>".$model['orgINN']."</b><br>".$model['orgKPP']."</div>"; 
                }                    
           ],
 
 
    	   [
	            'attribute' => 'fltOrgDeal',
				'label'     => 'Взаимодействие', 
                'encodeLabel' => false,                
                'format' => 'raw',
                'filter' => $model->getFltOrgDeal(),
                'contentOptions' => ['class' => 'grd_cell'],
                'value' => function ($model, $key, $index, $column)  {
                   
                    $strSql  = "SELECT DISTINCT grpTitle,isAllArticles, article , {{%bank_op_grp}}.id as grpRef 
                        from {{%org_deals}}, {{%bank_op_article}},{{%bank_op_grp}} ";
                    $strSql .= "where {{%org_deals}}.state = 1 
                    AND {{%org_deals}}.articleRef = {{%bank_op_article}}.id
                    AND {{%bank_op_article}}.grpRef = {{%bank_op_grp}}.id  
                    AND {{%org_deals}}.refOrg = :ref_org ORDER BY {{%bank_op_grp}}.id,  {{%bank_op_article}}.article ";
                                 
                    $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();                    
                        
                 //   return  Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->getRawSql();                    
                    
                    $N = count($resList);
                    if ($N==0) return "";
                    $grpRef=0;
                    $list="<ul>\n";
                    for ($i=0;$i<$N; $i++)
                    {
                      if ($resList[$i]['isAllArticles'] ==1   )  
                      {
                          if ($resList[$i]['grpRef'] != $grpRef) {
                            $list.="<li>".$resList[$i]['grpTitle']."</li>\n";
                            $grpRef = $resList[$i]['grpRef'];
                          }
                      }else
                      {
                         $list.="<li>".$resList[$i]['grpTitle'].": ".$resList[$i]['article']."</li>\n";                           
                      }                        
                    }
                    $list.="</ul>\n";                                        
                    return  $list; 
                }                    
           ],
 
    	   [
	            'attribute' => 'contactPhone',
				'label'     => 'Телефон', 
                'contentOptions' => ['class' => 'grd_cell'],
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                   
                   
                    $strSql  = "SELECT DISTINCT phone from {{%phones}}";
                    $strSql .= "where status<2 AND ref_org = :ref_org ORDER BY phone";                                 
                    $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();                                        
                    $N = count($resList);
                    $grpRef=0;
                    $title="";
                    for ($i=0;$i<$N; $i++)
                    {
                      $title.="".$resList[$i]['phone']."\n";                           
                    }                    
                    $contactPhone="";
                    if(!empty($model['contactPhone'])) $contactPhone=$model['contactPhone'];
                    elseif($N > 0)$contactPhone=$resList[0]['phone'];
                    
                    if($N > 0)$N--;
                    return  "<div title='".$title."'>".$contactPhone." +".($N)."</div>"; 
                }                    
           ],
 

    	   [
	            'attribute' => 'contactEmail',
				'label'     => 'E-mail', 
                'contentOptions' => ['class' => 'grd_cell'],
                'encodeLabel' => false,                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                   
                   
                    $strSql  = "SELECT DISTINCT email from {{%emaillist}}";
                    $strSql .= "where ref_org = :ref_org ORDER BY isDefault DESC";                                 
                    $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();                                        
                    $N = count($resList);
                    //if ($N==0) return "";
                    $grpRef=0;
                    $title="";
                    for ($i=0;$i<$N; $i++)
                    {
                      $title.="".$resList[$i]['email']."\n";                           
                    }                    
                    $contactEmail=trim($model['contactEmail']);
                    if(empty($contactEmail) && $N > 0)
                        $contactEmail=$resList[0]['email'];                    
                        
                    if($N > 0)$N--;
                    return  "<div title='".$title."'>".$title." +".($N)."</div>"; 
                }                    
           ],
 
    	   [
	            'attribute' => 'contactFIO',
				'label'     => 'Контакт ФИО', 
                'contentOptions' => ['class' => 'grd_cell'],
                'encodeLabel' => false,                
                'format' => 'raw',                    
           ],

 
 
    	   [
	            'attribute' => '-',
				'label'     => 'Банк', 
                'encodeLabel' => false,   
                'contentOptions' => ['class' => 'grd_cell'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                   
                   
                    $strSql  = "SELECT DISTINCT orgRS, orgBIK, orgBank, orgKS  from {{%org_accounts}}";
                    $strSql .= "where isActive=1 AND refOrg = :ref_org ORDER BY isDefault DESC";                                 
                    $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();                                        
                    $N = count($resList);
                    if ($N == 0) return "";
                    $title="";
                    $title.="Р/C".$resList[0]['orgRS']."\n";                           
                    $title.="К/C".$resList[0]['orgKS']."\n";                           
                    $title.="БАНК:".$resList[0]['orgBank']."\n";                           
                    $title.="БИК:".$resList[0]['orgBIK']."\n";                           
                    $title.="+ ".($N-1);
                                        
                    $val = mb_substr($resList[0]['orgRS'],0,3,'utf-8');
                    $val .= "...";
                    $l = mb_strlen($resList[0]['orgRS'],'utf-8');
                    $val .= mb_substr($resList[0]['orgRS'], $l-3,3,'utf-8');                    
                    return  "<div title='".$title."'>".$val."</div>"; 
                }                    
           ],
 
    	   [
	            'attribute' => '-',
				'label'     => 'адрес', 
                'encodeLabel' => false,       
                'contentOptions' => ['class' => 'grd_cell', 'style' =>'width:170px;'],                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {
                   
                   
                    $strSql  = "SELECT DISTINCT adress from {{%adreslist}}";
                    $strSql .= "where isBad=0 AND ref_org = :ref_org ORDER BY isOfficial DESC";                                 
                    $resList = Yii::$app->db->createCommand($strSql, [':ref_org' => $model['refOrg'],])->queryAll();                                        
                    $N = count($resList);
                    if ($N == 0) return "";
                    $title="";
                    $title.=$resList[0]['adress']."\n";                           
                    $title.="+ ".($N-1);
                                        
                    $val = mb_substr($resList[0]['adress'],0,50,'utf-8');
                    $val .= "...";                    
                    return  "<div title='".$title."'>".$val."</div>"; 
                }                    
           ],
 
    		[	
                'attribute' => 'lastContact',
				'label'     => 'Контакт',
                'contentOptions' => ['class' => 'grd_cell', 'style' =>'width:90px;'],
                'filter'=>array(
				"1" => "Все",
				"2" => "Контакт",				
				"3" => "Заказ",				
				),
          
              'encodeLabel' => false,
              'format' => 'raw',            				
              'value' => function ($model, $key, $index, $column) {
                    
                    if ( empty($model['period']) ) $period = 14;
                                              else $period = $model['period'];
                     $period = $period*60*60*24;//в секунды
                     
                   if (empty($model['lastContact'])) $cont ="Нет конт.<br>";  
                   else 
                   {    
                    $contTime =strtotime($model['lastContact']);
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    if ($contTime > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $cont = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $contTime)."</div>";                                           
                   }

                 return $cont;
                 
				}
            ],

            
			[	
                'attribute' => 'op',
				'label'     => 'Операция',
                'contentOptions' => ['class' => 'grd_cell', 'style' =>'width:90px;'],
                'filter'=>array(
				"1" => "Все",
				"2" => "Отгрузка",				
				"3" => "Оплата",				
                "4" => "Счет",				
				),

                'encodeLabel' => false,
                'format' => 'raw',            				
                 'value' => function ($model, $key, $index, $column) {
                     
                    if ( empty($model['period']) ) $period = 14;
                                              else $period = $model['period'];
                    $period = $period*60*60*24;//в секунды
   
                   $lastDt=0;  
                   if (empty($model['lastSupply'])) $sup ="Нет отгруз.<br>";                     
                   else
                   {
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    $supTime =  strtotime($model['lastSupply']);
                    $lastDt = $supTime;
                    if ($supTime  > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $sup = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $supTime)."</div>";                                                                
                   }
                   
                   
                   if (empty($model['lastOplate'])) $op ="Нет оплаты<br>";  
                   else 
                   {    
                    $bg = "";
                    $cl="color:DarkGray;"; 
                    $opTime =  strtotime($model['lastOplate']);
                    $lastDt = max( $lastDt, $opTime);
                    if ($opTime  > time() -  $period) 
                    {
                    /*В периоде*/                    
                      $cl="color:White;"; 
                      $bg="background-color:DarkGreen;"; 
                    }                     
                     $op = "<div class='grd_date_val' style='".$cl.$bg."'>".date('d.m.Y', $opTime)."</div>";                                                                               
                   }
                   
                   
                   if (empty ($model['lastSchet'])) $sch = "Нет счета<br>";
                   else 
                   {    
                    $bg = "";
                    $cl="color:DarkGray;"; 

                   $schTime =  strtotime($model['lastSchet']);
                   if (!empty($model['lastActiveSchet']))
                   {
                       /*Есть активные заказы*/
                       $schTime =  strtotime($model['lastActiveSchet']);
                       $lastDt = max( $lastDt, $schTime );
                       if ($schTime > time() -  $period) 
                        {
                            /*В периоде*/                    
                            $cl="color:White;"; 
                            $bg="background-color:DarkGreen;"; 
                        }                               
                   }
                   $wt = "";
                   //if ($schTime <> strtotime($model['lastSdelka'])) $wt="font-weight:bold";                   
                   $sch = "<div class='grd_date_val' style='".$cl.$bg.$wt."'>".date('d.m.Y', $schTime)."</div>";

               /*
                        $sch = date('d.m.Y', strtotime($model['lastSchet']));                 
                        if ($model['lastSchet'] <> $model['lastSdelka'])
                        $sch = "<font color='Green'>".$sch."</font>";*/
                   }
                   
                 if ($lastDt == 0) return "N/A";  
                 return date("d.m.Y",$lastDt);
                 
				}
            ],
           
			
			
        ],
    ]
); 


?>

