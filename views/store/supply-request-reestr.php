<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;

use kartik\date\DatePicker;
use kartik\time\TimePicker;

$this->title = 'Реестр отгрузок';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
    $curUser=Yii::$app->user->identity;
//if (!( ($curUser->roleFlg & 0x0020) || ($curUser->roleFlg & 0x0100) )) {return;}

$leafValue = $model->getLeafValue();

$this->registerJsFile('@web/js/store/supply-request-reestr.js');
$this->registerJsFile('@web/phone.js');
 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />

<script type="text/javascript">

</script> 
 
<style>

.lbl-width {
    width:150px;
}


.gridcell {
	width: 100%;		
	height: 100%;
    display: block;
}	

.switchcell {
	   
}	

.switchcell:hover {
    border-style:outset;
	border-color:WhiteSmoke; /* чернaя */		   
	cursor: pointer;
}	

.numrow{
	width: 100%;		
	height: 80px;
    display: block;
    //background-color:silver;
}

.checked{   
    color: DarkGreen;
}

.mustcheck{
  color:Crimson;
}

.needcheck{
  color:DarkOrange;
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}

.form-control {
 margin:0px;
 padding:1px;
 font-size:12px;
 height: 30px;
}
.container {

width:90%;
}

.warecount {
    font-weight:bold;
    text-align:right;
    //border:solid;   
}

.orginfo {
    
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
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
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
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

<h3><?= Html::encode($this->title) ?></h3>
<table border ='0' width='100%'><tr>
  <td width='110px;'>
    <div class="col-md-2">
        <div  class='btn btn-primary leaf leaf-selected' style='background:WhiteSmoke ; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1"> 
                <div class='leaf-txt' >Развернутый</div>
                <div class='leaf-val' style='color:Crimson'></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td>   

    <td width='110px;'>
        <div  class='btn btn-primary leaf ' style='background:WhiteSmoke; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1&mode=1';"> 
                <div class='leaf-txt' >Отдел<br>продаж</div>
                <div class='leaf-val' style='color:Crimson'></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td>   
    
  <td width='110px;'>&nbsp;</td>
    
    <td width='110px;'>
       <?php
       if ($model->mode == 2) $class= "leaf-selected";
                         else $class= "";
       ?>
       
        <div  class='btn btn-primary leaf <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1&mode=2';"> 
                <div class='leaf-txt' >Новые</div>
                <div class='leaf-val' style='color:Crimson'><?= $leafValue['requestNew']?></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td>   
   
    <td width='110px;'>
       <?php
       if ($model->mode == 3) $class= "leaf-selected";
                         else $class= "";
       ?>
        <div  class='btn btn-primary leaf <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1&mode=3';"> 
                <div class='leaf-txt' >Активные</div>
                <div class='leaf-val' ><?= $leafValue['requestInExec']+$leafValue['requestNew']+$leafValue['requestFinished'] ?></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td>   

   
    <td width='110px;'>
       <?php
       if ($model->mode == 4) $class= "leaf-selected";
                         else $class= "";
       ?>
    
        <div  class='btn btn-primary leaf <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1&mode=4';"> 
                <div class='leaf-txt' >В процессе:</div>
                <div class='leaf-val' ><?= $leafValue['requestInExec'] ?></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td> 
      
    <td width='110px;'>
       <?php
       if ($model->mode == 5) $class= "leaf-selected";
                         else $class= "";
       ?>
    
        <div  class='btn btn-primary leaf <?= $class ?>' style='background:LightYellow ; color:Blue;'                  
              onclick="javascript:document.location.href='index.php?r=store/supply-request-reestr&noframe=1&mode=5';"> 
                <div class='leaf-txt' >Выполнено:</div>
                <div class='leaf-val' ><?= $leafValue['requestFinished']?></div> 
                <div class='leaf-sub' ></div>
       </div>
   </td>   
         
   <td></td>
      
</tr></table>


<hr>
<?php 
$timeshift = $model->timeshift;
  //echo  \yii\grid\GridView::widget(  
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


 /*      'rowOptions' => function ($model) {
            //add your condition here
            if ($model->id == 1 || $model->id == 2 || $model->id == 3  ) {
                  return ['style' => ' position:fixed;'];
            }      
        ,
*/        
        'columns' => [       
            [
                'attribute' => 'requestDate',
                'label' => 'Номер /<br>Дата время',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px', ] ,               
                'value' => function ($model, $key, $index, $column) use($timeshift) {
                 $val ="<b>".$model['requestId']."</b>"." от <br>".date("d.m.Y <br> H:i",strtotime($model['requestDate'])+$timeshift)."";
                 $url= "store/supply-request-new";
                 
                 if ($model['supplyState'] & 0x00004) {
                 $val = "<s>".$val."</s> ";
                 }  
                 
                return "<div class='numrow'><a href='#' onclick='javascript:openWin(\"".$url."&viewMode=acceptRequest&id=".$model['requestId']."\", \"supplyWin\");'>
                        ".$val."</a></div>";
                }
            ],    

            [
                'attribute' => 'title',
                'label' => 'Клиент',
                
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

                 $id = 'orgTitle'.$model['requestId'];
                 if ($sverka >= 0) $add="<font color='DarkGreen'>". number_format($sverka,0,'.',"&nbsp")."</font>";
                              else $add="<font color='Crimson'>". number_format($sverka,0,'.',"&nbsp")."</font>";
                
                
                 $strSql= "SELECT dateStart, dateEnd, internalNumber, docUrl, oplatePeriod, oplateStart  FROM  {{%contracts}} where refOrg =:refOrg ORDER BY dateEnd Desc";                 
                 $list=Yii::$app->db->createCommand($strSql, [':refOrg' => $model['refOrg']])->queryAll();  
                 $ret="";
                 $N = count($list);
                 if ($N == 0) $dog = "";
                 else {
                 $style ="";
                 $endTime=strtotime($list[0]['dateEnd']);
                 if ($endTime < time()) $style = "color:Crimson;";
                 
                 $dog = "<br>Договор: <a href='".$list[0]['docUrl']."'  style='$style'>№ ".$list[0]['internalNumber']." до ".date("d.m.Y", $endTime)."</a><br> 
                 ".$list[0]['oplatePeriod']."д. с получения ".$list[0]['oplateStart']." ";
                  }
                
                
                     return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' ><div id='".$id."'>".$model['title']."</div></a>Сверка:&nbsp;".$add.$dog;
                },
            ],       
                        
            [
                'attribute' => 'userFIO',
                'label' => 'Менеджер',
                'format' => 'raw',
            ],    
            
            
           [
                'attribute' => '-',
                'label' => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                  
                 $strSql= "SELECT wareTitle, wareCount, wareEd FROM  {{%schetContent}} 
                 where  refSchet=:refSchet ORDER BY (wareCount*warePrice) DESC";
                 
                 $list=Yii::$app->db->createCommand($strSql, [':refSchet' => $model['refSchet']])->queryAll();   
                 
                 $N = count($list);
                 $id = 'goodTitle'.$model['requestId'];
                 if ($N == 0) return "&nbsp;";                 
                 $good= mb_substr($list[0]['wareTitle'],0,70)." <div class='warecount'>".$list[0]['wareCount']." ".$list[0]['wareEd']."</div>";
                 return "<div id='".$id."' style='width:250px;'>".$good."</div>";                              
                },
            ],        

            [
                'attribute' => 'isAccepted',
                'label'     => 'Одобрение<br> РОП',                
                'encodeLabel' => false,                  
                'format' => 'raw',
                'filter' => [
                '0' => 'Все',
                '1' => 'Одобрены',
                '2' => 'Не одобрены',
                '3' => 'Не рассмотренны',                                
                ],
                'value' => function ($model, $key, $index, $column) {
                
               $val ="";
               $style0='background:LightGray; display:none;';           
               if ($model['isAccepted'] == 0) {     
                $style1='display:none;';     
                $style0='background:LightGray; display:inline;';              
               }
               elseif ($model['isAccepted'] == -1) $style1='background:Crimson;';                  
                                             else  $style1='background:DarkGreen;';     

                                             
               $id = "setAccepted".$model['requestId'];                       
               $action = "switchConfirmOP(".$model['requestId'].", 10);";                                   
               $val .= \yii\helpers\Html::tag( 'div', "<span  class='glyphicon glyphicon-ok'></span>", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Одобрить',
                     'style'   => $style0,
                   ]);
               $val .="&nbsp;";
               $id = "notAccepted".$model['requestId'];                                                        
               $action = "switchConfirmOP(".$model['requestId'].", 11);";                                   
               $val .= \yii\helpers\Html::tag( 'div', "<span  class='glyphicon glyphicon-remove'></span>", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Отказать',
                     'style'   => $style0,
                   ]);
              
               $id = "isAccepted".$model['requestId'];                       
               $action = "switchConfirmOP(".$model['requestId'].", 3);";                                  
               $val .= \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Одобрено РОП',
                     'style'   => $style1,
                   ]);
                return $val;
             }   
                
            ],
            
            
/*            [
                'attribute' => 'marketNote',
                'label'     => 'Комментарий <br> отдела прод.',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '200px', ] ,               
                'value' => function ($model, $key, $index, $column) {                 
                 $id = "marketNote".$model['requestId'];    
                 
                 
                 //$comment = preg_replace("/\n/","<br>",mb_substr($model['marketNote'],-150,150));
                 $comment_array = explode( "\n", mb_substr($model['marketNote'],-150,150));
                 
                 $cN = count($comment_array);
                 $n = max($cN-5, 0); 
                 $comment = "";
                 for ($i=$n; $i<$cN; $i++){
                    $comment .= " ";
                    $comment .= $comment_array[$i];
                    $comment .= "<br>";
                 }
                 
                 return "<div style='width:250px;' id='".$id."' class='gridcell' onclick='editMarketNote(".$model['requestId'].")' title='".$model['marketNote']."'>".$comment."&nbsp;</div>";
                }
                
            ],            
*/

            [
                'attribute' => 'discusNote',
                'label' => 'Обсуждение <br>исполнения',
                'format' => 'raw',
                'encodeLabel' => false,                
                'value' => function ($model, $key, $index, $column) {      
                 $id = "discusNote".$model['requestId'];    
                 $note= mb_substr($model['dstNote']."\n".$model['supplyNote'], 0, 75, 'utf-8'); 
                 //$comment = preg_replace("/\n/","<br>",mb_substr($model['discusNote'],-150,150));
                 $comment_array = explode( "\n", mb_substr($model['discusNote'],-150,150));
                 
                 $cN = count($comment_array);
                 $n = max($cN-5, 0); 
                 $comment = "";
                 for ($i=$n; $i<$cN; $i++){
                    $comment .= " ";
                    $comment .= $comment_array[$i];
                    $comment .= "<br>";
                 }
                
                 $title = $note." ".$model['discusNote'] ;  
                 $note = "<b>".$note."</b>";
                 
                 return $note."<div style='width:250px;' id='".$id."' class='gridcell' onclick='editDiscusNote(".$model['requestId'].")' title='".$title."'>".$comment."&nbsp;</div>";
                }
            ],               


            
            [
                'attribute' => 'marketNeedAcpt',
                'label'     => 'Подтв.<br> ОП.',    
                'encodeLabel' => false,                                           
                'format' => 'raw',
                'filter' => [
                '0' => 'Все',
                '1' => 'Не нужно',
                '2' => 'Нужно, нет',
                '3' => 'Нужно, есть',                                
                ],
                'value' => function ($model, $key, $index, $column) {
                
                $val=""; 
                
                $id1="marketNeedAcpt".$model['requestId'];
                $id2="marketIsAccept".$model['requestId'];

                
                $style1="";    
                $style2="";
            
                if ($model['marketNeedAcpt'] == 0 )
                {
                    $style1='background:Silver;';    
                    $style2='display:none;';
                }                    
                else {
                    $style1='background:DarkOrange;';     
                    if ($model['marketIsAccept'] == 1)
                        $style1.='background:DarkGreen;';
                
                    $style2='display:block;';
                }     
                   
                if ($model['marketIsAccept'] == 1)
                        $style2.='background:DarkGreen;';
                    else    
                        $style2.='background:Crimson;';                        
                                                                           
                   $action = "switchConfirmOP(".$model['requestId'].", 1);";                    
                   $val .= \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id1,
                     'onclick' => $action,
                     'title'   => 'Необходимость согласования',
                     'style'   => $style1,
                   ]);
                    $val.="&nbsp;<br>";                   
                   $action = "switchConfirmOP(".$model['requestId'].", 2);"; 
                   $val .= \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id2,
                     'onclick' => $action,
                     'title'   => 'Согласовано?',
                     'style'   => $style2,
                   ]);
                return $val;
             }   
                
            ],
                        
            
            [
                'attribute' => 'requestDate',
                'label' => 'Просм.',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px'] ,               
                'value' => function ($model, $key, $index, $column)  use($timeshift){
                $val = "";
                if ($model['viewManagerRef'] != 0) 
                 $val.=date("H:i",strtotime($model['execView'])+$timeshift);                 
                else 
                 $val.= "-";
                
                return $val;
                }
            ],               

            [
                'attribute' => 'supplyIsAccept',
                'label' => 'Взято',
                'format' => 'raw',
                'encodeLabel' => false,  
                'filter' => [
                '0' => 'Все',
                '1' => 'Да',
                '2' => 'Нет',                
                ],
              
                'contentOptions'   =>   ['width' => '100px'] ,               
                'value' => function ($model, $key, $index, $column) {
                               
                $id = "supplyIsAccept".$model['requestId'];
                $style="";    
                           
                if ($model['supplyIsAccept'] == 0) $style='background:LightGray;';                  
                                              else  $style='background:DarkGreen;';     
                     
               $action = "switchConfirmOP(".$model['requestId'].", 4);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Взято в обработку отделом доставки',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               


            [
                'attribute' => 'discussIsFinish',
                'label' => 'Обсужд. <br> заверш.',
                'format' => 'raw',
                'filter' => [
                '0' => 'Все',
                '1' => 'Да',
                '2' => 'Нет',                
                ],                  
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px'] ,               
                'value' => function ($model, $key, $index, $column) {

                if (empty($model['discusNote'])) return "&nbsp;";  
                
                $id = "discussIsFinish".$model['requestId'];
                $style="";    
                           
                if ($model['discussIsFinish'] == 0) $style='background:Crimson;';                  
                                              else  $style='background:DarkGreen;';     
                     
               $action = "switchConfirmOP(".$model['requestId'].", 6);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Обсуждение завершено',
                     'style'   => $style,
                   ]);
                return $val;
                
                
                }
            ],               
            

            [
                'attribute' => 'productIsAccept',
                'label' => 'В плане<br> произв.',
                'format' => 'raw',
                'encodeLabel' => false,  
                'filter' => [
                '0' => 'Все',
                '1' => 'Да',
                '2' => 'Нет',                
                ],          
                              
                'contentOptions'   =>   ['width' => '100px'] ,               
                'value' => function ($model, $key, $index, $column) {

                
               $id = "productIsAccept".$model['requestId'];
                $style="";    
                           
                if ($model['productIsAccept'] == 0) $style='background:LightGray;';                  
                                              else  $style='background:DarkGreen;';     
                     
               $action = "switchConfirmOP(".$model['requestId'].", 5);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Включено в плян производства',
                     'style'   => $style,
                   ]);
                return $val;
                                               
                }
            ],               
            
            [
                'attribute' => 'productStart',
                'label'     => 'Начало <br> произв.',
                'encodeLabel' => false,                                
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {
                 
                $ret="";
                $val="";
                
                $style = '';
                if (!empty($model['productStart'])){
                 $val = date("d.m.Y",strtotime($model['productStart']));
                 $style = 'background:LightGreen;font-weight:bold;';                 
                 }
                $idFact = 'productStart'.$model['requestId'];
                $action = "saveData(".$model['requestId'].", 'productStart');";
                 $ret.= DatePicker::widget([
                    'name' => 'productStartN'.$model['requestId'], 
                    'id'   => $idFact, 
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'Дата начала...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    'value' => $val,
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);
                                 
                 return $ret;
                }
                
            ],            
            
            [
                'attribute' => 'readyPlan',
                'label'     => 'Готовность<br> план /факт',
                'encodeLabel' => false,                                
                'format' => 'raw',
                'filter' => [
                '0' => 'Все',
                '1' => 'Выполнены',
                '2' => 'Не выполнены',                
                ],

                //'format' => ['datetime', 'php:d.m.y'],
                
                'value' => function ($model, $key, $index, $column) {
                 
                 $ret="";
                 $val="";
                 $timePlan =0;
                 $style="";
                 if (!empty($model['readyPlan'])) {
                 $val = date("d.m.Y",strtotime($model['readyPlan']));
                 $style = 'font-weight:bold;';                                  
                 }else
                 {                  
                  $statusList   = Yii::$app->db->createCommand("Select * FROM {{%supply_status}}   where refSupply =".$model['requestId'] )->queryAll();
                  if ($model['refScenario'] == 0 ) $refScenario = 1;
                  else $refScenario=$model['refScenario'];                                                      
                  $scenarioList = Yii::$app->db->createCommand("Select * FROM {{%supply_scenario}} where id =".$refScenario )->queryAll();
                  /*Ищем последнюю выполненную операцию*/
                  if (count($statusList)==0)$startTime=0; //выполнение не начиналось - отказать
                  else $startTime= strtotime($statusList[0]['st1']);                  
                  if($startTime > 100)
                  {
                    $dayShift=0; //сколько осталось
                    for ($i=2; $i<=11; $i++ )
                    {     
                     $fld="st".$i;
                     $fld_time=$fld."_time";                      
                     $curTime= strtotime($statusList[0][$fld]);
                     if ($curTime>0){$dayShift=0; $startTime=$curTime;}//с последнего выполненного сдвиг 0
                     else  $dayShift+=$scenarioList[0][$fld_time]*$scenarioList[0][$fld];
                     }
                    $val = date("d.m.Y",$startTime + $dayShift*24*3600); //добавим ко времени начала интервал по сценарию
                   } 
                  }                  
                 
                 $idPlan='readyPlan'.$model['requestId'];
                 $action = "saveData(".$model['requestId'].", 'readyPlan');";
                 $ret.= DatePicker::widget([
                    'name' => 'readyPlanN'.$model['requestId'], 
                    'id'   => $idPlan, 
                    'type' => DatePicker::TYPE_INPUT,
                    'value' => $val,
                    'options' => ['placeholder' => 'План...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);
                
                $val="";
                $style="";
                if (!empty($model['readyFact']) && strtotime($model['readyFact']) > 100) {
                 $val = date("d.m.Y",strtotime($model['readyFact']));
                 $style = 'background:LightGreen;font-weight:bold;';                 
                 }
                $idFact = 'readyFact'.$model['requestId'];
                $action = "saveData(".$model['requestId'].", 'readyFact');";
                $ret.= DatePicker::widget([
                    'name' => 'readyFactN'.$model['requestId'], 
                    'id'   => $idFact, 
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'Факт...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    'value' => $val,
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);
                                 
                 return $ret;
                }
                
            ],            
            
            [
                'attribute' => 'docDate',
                'label'     => 'Документы <br> отгрузки',
                'encodeLabel' => false,                                
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {
                 
                $ret="";                 
                $val="";
                $style="";
                if (!empty($model['docDate'])){
                 $val = date("d.m.Y",strtotime($model['docDate']));
                 $style = 'background:LightGreen;font-weight:bold;';                 
                 }
                $idFact = 'docDate'.$model['requestId'];
                $action = "saveData(".$model['requestId'].", 'docDate');";
                 $ret.= DatePicker::widget([
                    'name' => 'docDateN'.$model['requestId'], 
                    'id'   => $idFact, 
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'Оформлены...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    'value' => $val,
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);
                                 
                 return $ret;
           
                }
                
            ],            
            
            [
                'attribute' => 'supplyDate',
                'label'     => 'Дата отгрузки <br> план /факт',
                'encodeLabel' => false,   
                'format' => 'raw',
                'filter' => [
                '0' => 'Все',
                '1' => 'Выполнены',
                '2' => 'Не выполнены',                
                ],

                'value' => function ($model, $key, $index, $column) {
                 
                $ret="";
                $val="";
                $style = '';
                if (!empty($model['supplyDate']) && strtotime($model['supplyDate']) > 100) {
                    $val = date("d.m.Y",strtotime($model['supplyDate']));
                    $style = 'font-weight:bold;';
                }
                
                $idFact = 'supplyDate'.$model['requestId'];
                $action = "saveData(".$model['requestId'].", 'supplyDate');";
                 $ret.= DatePicker::widget([
                    'name' => 'supplyDateN'.$model['requestId'], 
                    'id'   => $idFact, 
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'План...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    'value' => $val,
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);

                $val="";
                $style = '';
                if (!empty($model['st17']) && strtotime($model['st17']) > 100) {
                    $val = date("d.m.Y",strtotime($model['st17']));
                    $style = 'background:LightGreen;font-weight:bold;';
                }

                $idFact = 'finishDate'.$model['requestId'];
                $action = "saveData(".$model['requestId'].", 'finishDate');";
                 $ret.= DatePicker::widget([
                    'name' => 'finishDateN'.$model['requestId'], 
                    'id'   => $idFact, 
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'Факт...', 
                                  'style' => 'width:100px;'.$style,
                                  'onchange' => $action,
                                  ],
                    'value' => $val,
                    'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                   ]
                ]);
                 return $ret;
                }
                
            ],            

            [
                'attribute' => 'isHaveOriginal',
                'label' => 'Оригинал <br>получен',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px'] ,     
                'filter' => [
                '0' => 'Все',
                '1' => 'Да',
                '2' => 'Нет',                
                ],          
                'value' => function ($model, $key, $index, $column) {

                
                $id = "isHaveOriginal".$model['requestId'];
                $style="";    
                           
                if ($model['isHaveOriginal'] == 0) $style='background:LightGray;';                  
                                              else  $style='background:DarkGreen;';     
                     
               $action = "switchConfirmOP(".$model['requestId'].", 7);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Получены оригиналы документов',
                     'style'   => $style,
                   ]);
                return $val;
                
                                                
               }
            ],               


            [
                'attribute' => 'isFinished',
                'label' => 'ОП <br>принял',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px'] ,    
                'filter' => [
                '0' => 'Все',
                '1' => 'Да',
                '2' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isFinished".$model['requestId'];
                $style="";    
                           
                if ($model['isFinished'] == 0) $style='background:LightGray;';                  
                                         else  $style='background:DarkGreen;';     
                     
               $action = "switchConfirmOP(".$model['requestId'].", 8);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Отдел продаж подтвердил окончание доставки',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               
            
               
            [
                'attribute' => 'isActive',
                'label' => 'Сделка в<br>работе',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '100px'] ,    
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isSchetActive".$model['requestId'];
                $style="";    
                           
                if ($model['isSchetActive'] == 0) $style='background:White;';                  
                                            else  $style='background:Green;';     
                     
               $action =  "switchConfirmOP(".$model['requestId'].", 9);";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Активность сделки',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               
   
   
        ],
    ]
);


/*echo "<pre>";
print_r($model->debug);
echo "</pre>";
*/

?>



<?php
Modal::begin([
    'id' =>'noteEditDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<b> Текст комментария </b>',
]);
echo "<div id='orgTitle' style='width:570px; overflow: auto;'> </div>";
echo "<div id='goodTitle' style='width:570px; overflow: auto;'> </div>";
$form = ActiveForm::begin(['id' => 'noteEditForm']);
echo $form->field($model, 'requestId' )->hiddenInput(['id' => 'requestId' ])->label(false);
echo $form->field($model, 'noteType' )->hiddenInput(['id' => 'noteType' ])->label(false);
echo "<pre id='noteText' style='width:570px; height:200px; overflow: auto;'> </pre>";
echo $form->field($model, 'requestNote')->textArea(['id' => 'requestNote', 'rows' => 7, 'cols' => 45])->label(false);
echo "<input type='button' class='btn btn-primary' onClick='acceptNoteEdit();' value='Сохранить'>";

ActiveForm::end(); 

Modal::end();



$form = ActiveForm::begin(['id' => 'switchEditForm']);
echo $form->field($model, 'requestId' )->hiddenInput(['id' => 'switchRequestId' ])->label(false);
echo $form->field($model, 'switchType' )->hiddenInput(['id' => 'switchType' ])->label(false);
ActiveForm::end(); 

$form = ActiveForm::begin(['id' => 'saveDataForm']);
echo $form->field($model, 'requestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
ActiveForm::end(); 

?>




