<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

$this->title = 'Лиды';
$curUser=Yii::$app->user->identity;


$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');

$fltTime ='&fltOverdue='.$model->fltOverdue.'&fltToday='.$model->fltToday.'&fltTomorrow='.$model->fltTomorrow;

?>
<style>
.leaf {
    height: 60px; /* высота нашего блока */
    width:  90px;  /* ширина нашего блока */
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

.selected {
color:white;
background-color:DarkBlue;
}
.selected:hover {
color:white;
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

.block-clickable {
  border: 1px solid Silver; /* размер и цвет границы блока */
}

.block-clickable:hover {
  border: 1px solid Blue; /* размер и цвет границы блока */
  cursor: pointer; 
}

.btn-smaller{
margin:0px;
margin-top:-2px;
padding:1px;
height:15px;
width:15px;
}


</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 


<script type="text/javascript">
function setOrg(id, title) {
   
    //window.parent.document.getElementById('contactOrgTitle').value=title;    
    //window.parent.document.getElementById('orgId').value=id;    
    window.parent.closeOrgList(id, title);
}

function switchTime(fltOverdue,fltToday,fltTomorrow) {        
    url = 'index.php?r=site/head-leads-list&noframe=1&fltStatus=<?=$model->fltStatus?>';
    url = url + '&fltOverdue='+fltOverdue+'&fltToday='+fltToday+'&fltTomorrow='+fltTomorrow;
    document.location.href = url;
}    

function openCalendar(){
    $('#selectCalendarDialog').modal('show');    
    
}
function setCalendaFilter (d,month,year){
  var toDate = year+'-'+month+'-'+d;
  var url = 'index.php?r=site/head-leads-list&noframe=1&toDate='+toDate;   
  document.location.href = url;  
}
</script >

    
<div class='spacer' style='height:20px;'></div>

<table width='1100' border='0'><tr>
<td valign='top' ><h3 style=' margin-top:5px;' ><?= Html::encode($this->title) ?></h3> 
<a  class='btn' style='background:Green; color:White; margin-top:5px;height:30px;width:35px;padding:6px' 
        href='#' onclick="openWin('site/new-lead','newLeadWin');"> <span class='glyphicon glyphicon-plus-sign'> </span></a> </td>

<td width='150px' align='right'>

    <table border=0 width=100%>
    <tr>    
    <td style='width:25px;'> <?php   

                   if ($model->fltOverdue == 1) 
                      {$action="switchTime(0,".$model->fltToday.", ".$model->fltTomorrow.");";
                      $style = 'background:DarkBlue';}
                   else 
                      {$action="switchTime(1,".$model->fltToday.", ".$model->fltTomorrow.");";
                       $style = 'background:White';}
                  
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'overdue',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
                   
        
         ?>     
         
    </td>   
    <td>Просрочено  </td>
    <td><?= $model->leadLeafStatus['overdue'] ?> </td>
    
    </tr>
    
    <tr>    
    <td style='width:25px;'> <?php    

                    if ($model->fltToday == 1) 
                      {$action="switchTime(".$model->fltOverdue.",0, ".$model->fltTomorrow.");";
                      $style = 'background:DarkBlue';}
                   else 
                      {$action="switchTime(".$model->fltOverdue.",1, ".$model->fltTomorrow.");";
                       $style = 'background:White';}

                       echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'yesterday',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
         ?>          
    </td>   
    <td>Сегодня </td>
    <td><?= $model->leadLeafStatus['today'] ?> </td>
    </tr>

    
    <tr>    
    <td style='width:25px;'> <?php    

                    if ($model->fltTomorrow == 1) 
                      {$action="switchTime(".$model->fltOverdue.",".$model->fltToday.", 0);";
                      $style = 'background:DarkBlue';}
                   else 
                      {$action="switchTime(".$model->fltOverdue.",".$model->fltToday.", 1);";
                       $style = 'background:White';}
            
                   echo \yii\helpers\Html::tag( 'div', "&nbsp;", 
                   [
                     'class'   => 'btn btn-primary btn-smaller',
                     'id'      => 'today',
                     'onclick' => $action,
                     'style'   => $style,
                   ]);
         ?>         
    </td>   
    <td>Завтра  </td>    
    <td><?= $model->leadLeafStatus['tomorrow'] ?> </td>    
    </tr>

    <tr>
    <td colspan=3>
   <?php 
            echo \yii\helpers\Html::tag( 'div', "Календарь", 
                   [
                     'class'   => 'clickable',
                     'id'      => 'calendar',
                     'onclick' => "openCalendar();",
                   ]);
    ?>
    </td>  
    </tr>
    </table>
    


</td>       


<td width='110px' align='right'>
<?php  if ($model->fltStatus==15 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a title='добавлено лидов - распознать'  class='btn btn-primary leaf <?=$class?>' style='background:White; color:DarkBlue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=15<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >за <?= $model->leadDuration ?> </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['actual']?></div> </a>         
</td>


<td width='110px' align='right'>
<?php  if ($model->fltStatus==1 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a title='добавлено лидов - распознать'  class='btn btn-primary leaf <?=$class?>' style='background:White; color:DarkBlue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=1<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >Новые </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['noScan']?></div> </a>         
</td>

<td width='110px' align='right'>
<?php  if ($model->fltStatus==2 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a  title=' распознать, задать контрагента' class='btn btn-primary leaf <?=$class?>' style='background:White ; color:Blue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=2<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >Контакты </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['inProgress']?></div> </a> 
        
  
</td>

<td width='110px' align='right'>
<?php  if ($model->fltStatus==6 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a  title='квалифицировать по товару' class='btn btn-primary leaf <?=$class?>' style='background:White ; color:Blue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=6<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >Клиенты </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['client']?></div> </a> 
</td>


<td width='110px' align='right'>
<?php  if ($model->fltStatus==5 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a   title='создать заявку или отложить' class='btn btn-primary leaf <?=$class?>' style='background:White ; color:Blue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=5<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >Товар </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['ware']?></div> </a> 
        
</td>

<td width='110px' align='right'>
<?php  if ($model->fltStatus==11 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a  title='создать заявку' class='btn btn-primary leaf <?=$class?>' style='background:White; color:DarkBlue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=11<?=$fltTime?>'><div class='leaf-txt' style='font-size:12px; ' >В ЗАЯВКУ </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['sdelkaPrepared']?></div> </a> 
        
</td>

<!--
<td width='110px' align='right'>
<?php  if ($model->fltStatus==8 ) $class = 'leaf-selected';
                            else  $class = ''; ?>
 <a  title='в сделке' class='btn btn-primary leaf <?=$class?>' style='background:White ; color:Blue;' 
        href='index.php?r=site/head-leads-list&noframe=1&fltStatus=8'><div class='leaf-txt' style='font-size:12px; ' >Сделка </div>
        <div class='leaf-val' style='font-size:20px;' ><?=$model->leadLeafStatus['zakaz']?></div> </a> 
        
</td>
-->


<td width='50px' align='right'>
</td>


<td  width='170px' valign='top'>

<ul>
<?php $class=""; if($model->fltStatus==0) {$class="class='selected'"; $color='White';} ?>
    <li <?=$class?> ><b><?=$model->leadLeafStatus['all']?> <a <?=$class?>  href='index.php?r=site/head-leads-list&noframe=1&fltStatus=0' >Всего</a></b></li>
    
<?php $class=""; $color='Green'; if($model->fltStatus==4) {$class="class='selected'"; $color='White';} ?>    
    <li <?=$class?> ><b><?=$model->leadLeafStatus['Finished']?> <a <?=$class?> href='index.php?r=site/head-leads-list&noframe=1&fltStatus=4' style='color:<?= $color?>'>Рассмотрено</a></b></li>
    
<?php $class=""; $color="Black"; if($model->fltStatus==7) {$class="class='selected'"; $color='White';} ?>    
    <li <?=$class?> ><b><?=($model->leadLeafStatus['all']-$model->leadLeafStatus['Finished'])?> <a <?=$class?> href='index.php?r=site/head-leads-list&noframe=1&fltStatus=7' style='color:<?= $color?>'>В работе</a></b></li>    

<?php $class=""; $color="Black"; if($model->fltStatus==8) {$class="class='selected'"; $color='White';} ?>    
    <li <?=$class?> ><b><?=($model->leadLeafStatus['zakaz'])?> <a <?=$class?> href='index.php?r=site/head-leads-list&noframe=1&fltStatus=8' style='color:<?= $color?>'>В сделке</a></b></li>        
    
<?php $class=""; $color='Gray'; if($model->fltStatus==9) {$class="class='selected'"; $color='White';} ?>    
    <li <?=$class?> ><b><?=($model->leadLeafStatus['ignore'])?> <a <?=$class?> href='index.php?r=site/head-leads-list&noframe=1&fltStatus=9' style='color:<?= $color?>'>Игнорировать</a></b></li>    
    
<?php $class=""; $color='Gray'; if($model->fltStatus==10) {$class="class='selected'"; $color='White';} ?>    
    <li <?=$class?> ><b><?=($model->leadLeafStatus['wait'])?> <a <?=$class?> href='index.php?r=site/head-leads-list&noframe=1&fltStatus=10' style='color:<?= $color?>'>Отложить</a></b></li>    
    
</ul>    

</td>


</tr></table>

<div class='spacer'></div>

<div style='width:1100px;'  >
<?php

echo GridView::widget(
    [
        'dataProvider' => $leadListProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
   
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'filterModel' => $model,
        
       'panel' => [
        'type'=>'success',   
        ],

        
        'responsive'=>true,
        'hover'=>true,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

             [
                'attribute' => 'contactDate',
                'label'     => 'Дата',
                'filter'   => false,
                'contentOptions' => ['width' => '75px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                    return date("d.m.Y", strtotime($model['contactDate']));
                },                
            ], 
       
             [
                'attribute' => 'lastChngDate',
                'label'     => 'Изменено',
                'contentOptions' => ['width' => '75px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                    return date("d.m.Y", strtotime($model['lastChngDate']));
                },                
            ], 
             [
                'attribute' => 'overDueDate',
                'label'     => 'Проср.',
                'contentOptions' => ['width' => '75px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                              
                    return date("d.m.Y", strtotime($model['overDueDate']));
                },                
            ], 
       
       
             [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'filter'    => $model->getManagerLeadList(),
                'contentOptions' => ['width' => '100px'],
                'format' => 'raw',
            ], 
             [
                'attribute' => 'note',
                'label'     => 'Текст лида',
                'contentOptions' => ['style' => 'padding:0px;'],
                'filter' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

               if (empty( $model['ref_org'] ))
                    $action =  "openWin('site/new-lead&noframe=1&contactId=".$model['id']."', 'leadWin')"; 
                else    
                    $action =  "openWin('site/lead-process&noframe=1&contactId=".$model['id']."', 'leadWin')"; 


                 $parse = preg_split("/\n/",$model['note']);
                 $N = count($parse);
                 $st = min(4,$N);
                 $st = $N-$st;

                 
                 $val = "<div style='font-size:11px;border:0px;'>";
                 /*for($i=$st;$i<$N;$i++){
                 $parse[$i] = preg_replace("/\s+/"," ", $parse[$i]);
                 $val.=mb_substr($parse[$i],0,40,'utf-8')."\n";
                 }*/
                 $str = trim($model['note']);
                 $l = mb_strlen($str, 'utf-8');
                 $l = $l-240;
                 if ($l<0) $l =0;
                 $val.=mb_substr($str,$l,240,'utf-8')."\n";
                 
                 $val.="</div>";
                  //$ret= mb_substr($model['note'],0, 250)." ...";
                   //$val = "<pre>".$ret."</pre>";
                    return  \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'block-clickable',
                     'id'      => 'note',
                     'title'   => $model['note'],
                     'style'   => 'font-size:12px; width:300px;  height:95px;',
                     'onclick' => $action,
                     //overflow: auto;
                   ]);
                    
                    
                   
                },

            ],        
            
            [
                'attribute' => 'phone',
                'label'     => 'Телефон',
                'contentOptions' => ['width' => '100px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {      
                
                   if (!empty($model['ref_org'])) {
                   
                   $list = Yii::$app->db->createCommand(
                    'SELECT contactFIO, contactPhone, contactEmail from {{%orglist}} 
                    where id=:ref_org', 
                    [':ref_org' => $model['ref_org'],])->queryOne();                   
                    return $list['contactPhone']."<br>".$list['contactFIO'];   
                }
                
                if (empty($model['phone'])) $phone=$model['contactPhoneText'];
                                       else $phone=$model['phone'];
                                                                              
                    return $phone."<br>".$model['contactFIO'];
                },
                
            ],        

            [
                'attribute' => 'contactEmail',
                'label'     => 'E-mail',
                'filter'    => false,
                'contentOptions' => ['width' => '100px'],
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {      
                if (!empty($model['ref_org'])) {
                   
                   $list = Yii::$app->db->createCommand(
                    'SELECT contactFIO, contactPhone, contactEmail from {{%orglist}} 
                    where id=:ref_org', 
                    [':ref_org' => $model['ref_org'],])->queryOne();                   
                    return $list['contactEmail'];   
                }
                                                                                              
                 return $model['contactEmail'];
                },
                
            ],        
                        
            [
                'attribute' => 'contactOrgTitle',
                'label'     => 'Клиент',
                 'contentOptions' => ['width' => '150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   
                   if (!empty($model['ref_org'])) {
                    $action="openWin(\"site/org-detail&orgId=".$model['ref_org']."\", \"orgwin\")";
                    $orgTitle =  $model['contactOrgTitle'];
                    $s="";
                   } else {
                    $action =  "openWin('site/new-lead&noframe=1&contactId=".$model['id']."', 'leadWin')"; 
                    $orgTitle = 'Не задан';
                    $s="color:Crimson;";
                   }
                   
                   return \yii\helpers\Html::tag( 'div',$orgTitle, 
                   [
                     //'id'      => $id, 
                     'onclick' => $action,
                     'class'   => 'clickable',                                        
                     //'title'   => $title,
                     'style'   => $s
                   ]);
                
                },
            ],        

            
            [
                'attribute' => '-',
                'label'     => 'Товар',
                'contentOptions' => ['width' => '150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   
                   //if (empty($model['refZakaz'])) return "&nbsp;";
                   
                    $leadWareName = Yii::$app->db->createCommand(
                    'SELECT leadWareName, leadWareCount, leadWareEd from {{%lead_detail}} 
                    where refContact=:refContact', 
                    [':refContact' => $model['id'],])->queryOne();
                   
                 if (empty($leadWareName)) return "";  
                 return $leadWareName['leadWareName']."<br>".$leadWareName['leadWareCount']." ".$leadWareName['leadWareEd'];   
                
                },
            ],          
            
            

            [
                'attribute' => 'refZakaz',
                'label'     => 'Сделка',
                'contentOptions' => ['width' => '150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   
                   if (empty($model['refZakaz'])) return "&nbsp;";
                   
                    $zakazInfoList = Yii::$app->db->createCommand(
                    'SELECT {{%zakaz}}.id as refZakaz, formDate, ifnull({{%schet}}.id,0) as refSchet, schetNum, schetDate from {{%zakaz}} 
                    left join {{%schet}} on {{%zakaz}}.id={{%schet}}.refZakaz where {{%zakaz}}.id=:zakazId', 
                    [':zakazId' => $model['refZakaz'],])->queryOne();
                   $res = "";
                   if (!empty($zakazInfoList['refZakaz']) )
                   {
                       $id = 'zakaz_'.$model['id'];
                       
                       $action="openWin('market/market-zakaz&orgId=".$model['ref_org']."&zakazId=".$zakazInfoList['refZakaz']."', 'sdelkaWin');";
                       $val = "Заказ № ".$zakazInfoList['refZakaz'];
                       $val .= " от ".date("d.m.Y", strtotime($zakazInfoList['formDate']));
                       $res .=\yii\helpers\Html::tag( 'div',$val, 
                       [
                         'id'      => $id, 
                         'onclick' => $action,
                         'class'   => 'clickable', 
                         'style'   => 'font-size:12px;',                                       
                       ]);
                   }
                   
                   if (!empty($zakazInfoList['refSchet']) )
                   {
                       $action="openWin('market/market-schet&id=".$zakazInfoList['refSchet']."', 'sdelkaWin');";
                       $id = 'schet_'.$model['id'];
                       $val = "Счет № ".$zakazInfoList['schetNum'];
                       $val .= " от ".date("d.m.Y", strtotime($zakazInfoList['schetDate']));
                       $res .=\yii\helpers\Html::tag( 'div',$val, 
                       [
                         'id'      => $id, 
                         'onclick' => $action,
                         'class'   => 'clickable',                                        
                         'style'   => 'font-size:12px;',
                       ]);
                   }
                   
                   
                   
                 return $res;   
                
                },
            ],        
            
/*10 - новый
  11 - отложить
  
  12 - пойдет в сделку
  //13 - резервный код
  14 - квалификация товара начата  
  15 - в заявку / квалификация товара закончена заявки нет 

  16- начата обработка, контрагент не определен.
  17- контрагент определен, нужно определение товара 
  
  
  20 - работа завершена - привязана заявка 
  21 - игнорировать            */
            [
                'attribute' => 'fltStatus',
                'label'     => 'Статус',
                'filter'   => [
                1 => 'Новые',
                2 => "Контакты",                    
                6 => "Клиенты",
                5 => "Товар",
                11 => "В заявку",
                8 => "В сделке",
                9 => "Игнорировать",
                10 => "Отложить",
                ],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {

                if ($model['eventType'] == 11) return "Отложить";
                if ($model['eventType'] ==20 ) return "<b>Сделка</b>";                
                if ($model['eventType'] == 21) return "<strike>Игнорировать</strike>";
                
                if ($model['eventType'] == 15) return "В заявку";
                if ($model['eventType'] == 14) return "Товар";
                if ($model['eventType'] == 16) return "Контакты";
                if ($model['eventType'] == 17) return "Клиенты";
                },
                
            ],  
            
            
/*             [
                'attribute' => '-',
                'label'     => '',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                
                if (empty( $model['ref_org'] ))
                    $action =  "openWin('site/new-lead&noframe=1&contactId=".$model['id']."', 'leadWin')"; 
                else    
                    $action =  "openWin('site/lead-process&noframe=1&contactId=".$model['id']."', 'leadWin')"; 
            
                $val = \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-pencil'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => 'check',
                     'onclick' => $action,
                     'title'   => 'Расмотреть',
                     'style'   => 'font-size:14px;text-align:center',
                   ]);
                return $val;
                },

            ],        
 */

        ],
    ]
    );
?>
</div>


<?php
Modal::begin([
    'id' =>'selectCalendarDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectCalendarDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/site/lead-calendar&noframe=1&month=<?=date('m',strtotime($model->toDate))?>&year=<?=date('Y',strtotime($model->toDate))?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>


<?php

if(!empty($model->debug))
{
 echo "<pre>";
print_r($model->debug);
echo "</pre>";
}

//Регестрим скрипты
$js = <<<JS

window.opener.location.reload(false); 

JS;

$this->registerJs($js);
?>  

