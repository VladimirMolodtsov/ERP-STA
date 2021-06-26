<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Данные по оплате';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
    $curUser=Yii::$app->user->identity;
if (!( ($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x0100) )) {return;}


 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">
function reSyncRemain(id)
{
    openSwitchWin("store/resync-remain&zakazid="+id);
}
</script> 
 
<style>



</style>

<?php 

  echo  \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
       
            [
                'attribute' => 'requestDate',
                'label' => '№',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $val ="<nobr><b>".$model['requestId']."</b>"." от ".date("d.m",strtotime($model['requestDate']))."</nobr>";
                 $url= "store/supply-request-new";
                return "<a href='#' onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a>";
                }
            ],    

            [
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],    
            
            [
                'attribute' => 'schetNumber',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $url= "market/market-schet&noframe=1&id=".$model['refSchet'];
                 return "<a href='#' onclick='javascript:openWin(\"".$url."\", \"childWin\");'>".$model['schetNum']." от ".date("d.m.y",strtotime($model['schetDate']))."<br><nobr> на сумму: ".number_format($model['schetSumm'], 2, '.', '&nbsp;')."</nobr></a>";
                }
            ],    
            
            
            [
                'attribute' => 'supplyDate',
                'label'     => 'Дата отгрузки',
                'format' => 'raw',
                //'format' => ['datetime', 'php:d.m.y'],
                
                'value' => function ($model, $key, $index, $column) {
                 
                 $ret="";
                 if (!empty($model['supplyDate'])) $ret.= " План: ".date("d.m.Y",strtotime($model['supplyDate']))."<br>";
                 if (!empty($model['finishDate'])) $ret.= " Факт: ".date("d.m.Y",strtotime($model['finishDate']));   
                 return $ret;
                }
                
            ],            

            [
                'attribute' => 'title',
                'label' => 'Организация',
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
                
                     return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a><br>Сверка:&nbsp;".$add;
                },
            ],        


           [
                'attribute' => 'summOplata',
                'label' => 'Оплачено',
                'format' => 'raw',
                /*'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },*/
            ],        

           [
                'attribute' => '-',
                'label' => 'Остаток на складе',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                  
                 $strSql= "SELECT {{%zakazContent}}.wareRemain, {{%zakazContent}}.count, {{%zakazContent}}.wareSyncDate, {{%schet}}.refZakaz FROM  {{%zakazContent}}, {{%schet}}  
                 where {{%zakazContent}}.refZakaz =  {{%schet}}.refZakaz AND {{%schet}}.id =:refSchet";
                 
                 $list=Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet']])->queryAll();   
                 
                 $N = count($list);
                 if ($N == 0) return "No content";
                 
                 $syncDate = $list[0]['wareSyncDate'];
                 if (empty ($syncDate)) {$lbl ="Не определено";}
                 else {
                 $lbl = \yii\helpers\Html::tag( 'span', 'Есть',  ['class' => 'label label-success']);
                 for($i=0; $i<$N; $i++ )
                 {
                  if ($list[0]['wareSyncDate']  < $list[0]['wareSyncDate'] )
                  {
                    $lbl =\yii\helpers\Html::tag( 'span', 'Нет',  ['class' => 'label label-danger']);                
                  }                    
                 }
                 }
                  $add="<div><nobr>".$syncDate."</nobr></div>";
                  return $lbl."&nbsp;<a href='#' onclick='reSyncRemain(".$model['refZakaz'].");'><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></a>".$add;
                },
            ],        

            [
                'attribute' => 'Договор',
                'label'     => 'Договор',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {        
                 $strSql= "SELECT dateStart, dateEnd, internalNumber, docUrl, oplatePeriod, oplateStart  FROM  {{%contracts}} where refOrg =:refOrg ORDER BY dateEnd Desc";                 
                 $list=Yii::$app->db->createCommand($strSql, [':refOrg' => $model['refOrg']])->queryAll();  
                 $ret="";
                 $N = count($list);
                 if ($N == 0) return "&nbsp;";
                 $style ="";
                 $endTime=strtotime($list[0]['dateEnd']);
                 if ($endTime < time()) $style = "color:Crimson;";
                 
                 return "<a href='".$list[0]['docUrl']."'  style='$style'>№ ".$list[0]['internalNumber']." до ".date("d.m.Y", $endTime)."</a><br> 
                 ".$list[0]['oplatePeriod']."д. с получения ".$list[0]['oplateStart']." ";
                
                }   
                
            ],
            
                                    
            [
                'attribute' => 'isAccepted',
                'label'     => 'Одобрено',                
                'format' => 'raw',
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],
                'value' => function ($model, $key, $index, $column) {

                if ($model['supplyState']  & 0x00004 )
                    return \yii\helpers\Html::tag( 'span', 'Отказ',  ['class' => 'label label-danger']);                
                                                                            
                if ($model['isAccepted'] == 1)
                    return \yii\helpers\Html::tag( 'span', 'Да',  ['class' => 'label label-success']);
             }   
                
            ],

            
         
            [
                'attribute' => '',
                'label'     => '',                
                'format' => 'raw',
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],
                'value' => function ($model, $key, $index, $column) {
                
                if ($model['supplyState']  & 0x00004 )
                {
                   $ret =  "<a href='#' title='Одобрить' onclick='openSwitchWin(\"head/supply-request-accept&id=".$model['requestId']."\")' >
                    <span class='glyphicon glyphicon-ok'></span></a>";                    
                   return $ret; 

                }
                
                if ($model['isAccepted'] == 0){
                   $ret =  "<a href='#' title='Одобрить' onclick='openSwitchWin(\"head/supply-request-accept&id=".$model['requestId']."\")' >
                    <span class='glyphicon glyphicon-ok'></span></a>";
                   $ret .=  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' style='color:Crimson;' title='Отказать' onclick='openSwitchWin(\"head/supply-request-reject&id=".$model['requestId']."\")'>
                    <span class='glyphicon glyphicon-remove'></span></a>"; 
                    
                   return $ret; 
                  }  
                  
                if ($model['isAccepted'] == 1){
                    return "<a href='#' title='Отменить одобрение' onclick='openSwitchWin(\"head/supply-request-unaccept&id=".$model['requestId']."\")' >
                    <span class='glyphicon glyphicon-retweet'></span></a>"; 
                }
                
                }
                
            ],

   
        ],
    ]
);


?>
