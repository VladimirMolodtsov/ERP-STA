<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
//use yii\grid\GridView;
use kartik\grid\GridView;

use yii\bootstrap\Collapse;
use kartik\date\DatePicker;

$this->title = 'Активные сделки';
//if (Yii::$app->user->isGuest == true){ return;}
$to=date("Y-m-d", $model->to_time);
$frm=date("Y-m-d", $model->frm_time);


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

?>

<script type="text/javascript">
function setDate()
{
  var frm = document.getElementById('from_date').value;
  var to = document.getElementById('to_date').value;
    document.location.href='index.php?r=head/sdelka-list&noframe=1&detail=<?=$model->detail?>&from='+frm+'&to='+to;
}

</script> 
 
<style>

.leaf {
    height: 80px; /* высота нашего блока */
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



.leaf-txt {    
    font-size:11px;
    width:  80px;  /* ширина нашего блока */
    word-break: break-all;
/*    -ms-hyphens: auto;
    -webkit-hyphens: auto;*/
    hyphens: auto;
    hyphenate-limit-chars: 6 3 3;
    hyphenate-limit-lines: 2;   
    hyphenate-limit-last: always;
    hyphenate-limit-zone: 8%;
}
.leaf-val {    
    font-size:17px;
}
.leaf-sub {    
    font-size:12px;
    text-align: right;
    color:DimGrey;
}
.child {
    height:60px;
    padding:2px;
}

</style>

<div class ='spacer'></div>

<div class ='row'>
   
  <div class ='col-md-3' style='text-align:center'>
    <?php   
    echo DatePicker::widget([
    'name' => 'from_date',
    'id' => 'from_date',
    'value' => date("d.m.Y",$model->frm_time),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
 //    'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
  </div>
  
  <div class ='col-md-3' style='text-align:center'>
    <?php   
    echo DatePicker::widget([
    'name' => 'to_date',
    'id' => 'to_date',
    'value' => date("d.m.Y",$model->to_time),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
//     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
    ]);
    ?>      
  </div>

  <div class ='col-md-2' style='text-align:left'>
      <?php echo  \yii\helpers\Html::tag( 'div', 'Применить', 
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'addfilter',
                     'onclick' => 'setDate();',
                   ]);
    ?>
  </div>

    <div class ='col-md-2' style='text-align:center'>
  </div>

    <div class ='col-md-2' style='text-align:right'>
      <?php echo  \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>",
                   [
                     'class'   => 'btn btn-default',
                     'id'      => 'addfilter',
                     'onclick' => "openWin('market/market-zakaz-create&noframe=1&id=0','childWin');",
                   ]);
    ?>
  </div>

</div>

<div class='spacer'></div>

    <table border='0' width='100%'> 

<?php  if ($model->detail==0 ) $class = 'leaf-selected';
                            else  $class = ''; ?>    
    <td><a  class='btn btn-primary leaf <?=$class?>' style='background:White ; color:Blue;' href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=0#detail_list'>
        <div class='leaf-txt' >Всего <br>сделок: </div>
        <div class='leaf-val'><?= $leafValue['allDeal'] ?></div> 
        <div class='leaf-sub'><?= $leafValue['allDealSumm'] ?></div>
    </a></td> 

<?php  if ($model->detail==1 ) $class = 'leaf-selected';
                            else  $class = ''; ?>        
        <td> <a  class='btn btn-primary leaf <?=$class?>' style='background:MintCream  ; color:Blue;'   href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=1#detail_list'>
        <div class='leaf-txt' >Заявки, <br>новые: </div>
        <div class='leaf-val' ><?= $leafValue['newZakaz'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['newZakazSumm'] ?></div>
        </a></td>                

<?php  if ($model->detail==2 ) $class = 'leaf-selected';
                            else  $class = ''; ?>          
        <td> <a  class='btn btn-primary leaf <?=$class?>' style='background:MintCream ; color:Blue;'         href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=2#detail_list'>
        <div class='leaf-txt'>Заявки, <br>в работе: </div>
        <div class='leaf-val'><?= $leafValue['zakazInWork'] ?></div>
        <div class='leaf-sub' ><?= $leafValue['zakazInWorkSumm'] ?></div>
        </a></td>

<?php  if ($model->detail==3 ) $class = 'leaf-selected';
                            else  $class = ''; ?>           
        <td><a  class='btn btn-primary leaf  <?=$class?>' style='background:#d1f2eb ;  color:Blue;'          href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=3#detail_list'>
        <div class='leaf-txt'>Счета, <br>новые </div>
        <div class='leaf-val'><?= $leafValue['newSchet'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['newSchetSumm'] ?></div>
        </a></td>

<?php  if ($model->detail==9 ) $class = 'leaf-selected';
                            else  $class = ''; ?>           
        <td><a  class='btn btn-primary leaf  <?=$class?>' style='background:Gold ;  color:Brown;'          href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=9#detail_list'>
        <div class='leaf-txt'>Счета, <br>ожидают </div>
        <div class='leaf-val'><?= $leafValue['waitSchet'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['waitSchetSumm'] ?></div>
        </a></td>
        
                
<?php  if ($model->detail==4 ) $class = 'leaf-selected';
                            else  $class = ''; ?>    
        <td><a  class='btn btn-primary leaf  <?=$class?>' style='background:#d1f2eb ; color:Blue;'          href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=4#detail_list'> 
        <div class='leaf-txt'>Счета, <br>в работе: </div>
        <div class='leaf-val'><?= $leafValue['schetInWork'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['schetInWorkSumm'] ?></div>
        </a></td>
        
<?php  if ($model->detail==7 ) $class = 'leaf-selected';
                            else  $class = ''; ?>          
        <td><a  class='btn btn-primary leaf  <?=$class?>' style='background: #eafaf1; color:Blue;'           href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=7#detail_list'>
        <div class='leaf-txt'>В Оплате: </div>
        <div class='leaf-val'><?= $leafValue['cashProc'] ?></div>
        <div class='leaf-sub' ><?= $leafValue['cashProcSumm'] ?></div>
        </a></td>        
        
<?php  if ($model->detail==5 ) $class = 'leaf-selected';
                            else  $class = ''; ?>                          
        <td> <a  class='btn btn-primary leaf  <?=$class?>' style='background: #eafaf1; color:Blue;'         href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=5#detail_list'>
        <div class='leaf-txt'>Отгрузка, <br>ждет: </div>
        <div class='leaf-val' ><?= $leafValue['supplyWait'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['supplyWaitSumm'] ?></div>
        </a></td>

         
<?php  if ($model->detail==6 ) $class = 'leaf-selected';
                            else  $class = ''; ?>                          
        <td> <a  class='btn btn-primary leaf <?=$class?>' style='background:WhiteSmoke ; color:Blue;'         href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=6#detail_list'>
        <div class='leaf-txt' style='margin-top:10px;margin-bottom:5px;'>В отгрузке:</div>      
        <div class='leaf-val'><?= $leafValue['supplyProc'] ?></div>
        <div class='leaf-sub' ><?= $leafValue['supplyProcSumm'] ?></div>
        </a></td>
    
        
<?php  if ($model->detail==8 ) $class = 'leaf-selected';
                            else  $class = ''; ?>                          
        <?php if ($leafValue['finitProc']>0){$color="Blue";} else {$color="ForestGreen";}  ?>        
        <td><a  class='btn btn-primary leaf <?=$class?>' style='background:WhiteSmoke  ; color:<?= $color ?>;'  href='index.php?r=head/sdelka-list&noframe=1&from=<?=$frm?>&to=<?=$to?>&detail=8#detail_list'>
        <div class='leaf-txt' style='margin-top:10px;margin-bottom:5px;'>В завершении:</div>
        <div class='leaf-val'><?= $leafValue['finitProc'] ?></div> 
        <div class='leaf-sub' ><?= $leafValue['finitProcSumm'] ?></div>
        </a></td>
    
             
        </tr>         

        
    </table>

<div class='spacer'></div>


<?php 
   echo GridView::widget(
    [
        'dataProvider' => $provider,
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

        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
           [
                'attribute' => 'orgTitle',
                'label' => 'Клиент',
                'contentOptions' =>['style'=>'font-size:12px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                        
                    if (empty($model['orgTitle'])) $t='--';
                                              else $t = $model['orgTitle'];
                    if (empty($model['orgId'])) return "<font color='Crimson'>Не задан</font>";
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgId']."\", \"childWin\")' >".$t."</a>";
                },
            ],            

           [
                'attribute' => 'userFIO',
                'contentOptions' =>['style'=>'font-size:12px;'],
                'filter' => $model->getUserList(),
                'label' => 'Менеджер',
                'format' => 'raw',            
            ],            
        
            [
                'attribute' => 'zakazId',
                'label' => 'Заказ',
                'format' => 'raw',
                'contentOptions' =>['style'=>'padding:0px;font-size:12px;'],
                'value' => function ($model, $key, $index, $column) {  
                
                    $action=" onclick=\"openWin('market/market-zakaz&orgId=".$model['orgId']."&zakazId=".$model['zakazId']."','zakazWin');\"";            
                    $ret= "<div class='clickable child' ".$action.">";
                    $ret.= "<b>Заказ</b> № ".$model['zakazId']."&nbsp;от&nbsp;".date ('d.m.Y', strtotime($model['formDate']))."<br>";                   
                    $ret.="</div>";
                    return $ret;
                },
            ],        
                      
            [
                'attribute' => 'fltSchet',
                'label' => 'Счет',
                'format' => 'raw',
                'filter' => [1 => 'Счет cформирован в ERP', 2 => 'Счет согласован с клиентом', 3 => 'Счет заведен в 1С', 4=> 'Счет принят клиентом в оплату' ],
                'contentOptions' =>['style'=>'padding:0px;font-size:12px;'],
                'value' => function ($model, $key, $index, $column) {  
                
                if (empty($model['schetId'])) return "";
                
                 $action=" onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');\"";                 
                    $s='';
                 switch ($model['docStatus']) {
                    case 1:
                        $s='';
                    break;
                    
                    case 2:
                        $s='background-color:Gold';                    
                    break;
                    
                    case 3:
                        $s='background-color:LightGreen';                    
                    break;
                    
                    case 4:
                        $s='background-color:LightGreen';                    
                    break;
                    
                    default:
                        $s='';                    
                    break;
                 
                 }
                 
                 $ret= "<div class='clickable child' ".$action." style='".$s."'>";
                                       
				 $ret.= "<b>Счёт</b> № ".$model['schetNum']."&nbsp;от&nbsp;". date("d.m.Y", strtotime($model['schetDate']))."<br> на: ";
                 $ret.=number_format($model['schetSumm'],2,'.','&nbsp;');				                           
                 $ret.="</div>";
                 return $ret;
                },
            ],        
                      
            [
                'attribute' => 'Оплата',
				'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;font-size:12px;'],
                'format' => 'raw',			                
                'value' => function ($model, $key, $index, $column) {					
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();
                 
                 //return $model['schetId'];
                 if (count($listData)==0) return "&nbsp;";                 
                 if($listData[0]['sumOplata'] == 0)return "&nbsp;";                 
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div class='child'  style='background-color:LightGreen'>"; 
                                                            else $ret= "<div class='child'  style='background-color:Yellow'>";
                                                            
				  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));				                                              
                  $ret.="<br>&nbsp;</div>";                  
                 return $ret;                  
                },
            ],			
			
            [
                'attribute' => 'Поставка',
				'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;font-size:12px;'],
                'format' => 'raw',			                
                'value' => function ($model, $key, $index, $column) {					
                if (empty($model['schetId'])) return "&nbsp";    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}} where refSchet=:refSchet  ', 
                [':refSchet' => $model['schetId'],])->queryAll();
                             
                if(count($listData)==0 || $listData[0]['sumSupply'] == 0)               
                { 
                                
                    $list = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet',
                    [':refSchet' => $model['schetId'] ])->queryAll();
                    if (count ($list) == 0 ) return "Нет запроса ";
                    $ret= "<div>Запрос на поставку № ".$list[0]['id']." от ".date ('d.m.Y', strtotime($list[0]['requestDate']))."</div>";                 
                    return $ret;
                }
                
                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div class='child' style='background-color:LightGreen'>"; 
                                                                 else $ret= "<div class='child' style='background-color:Yellow'>";
                                                            
				  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));				                                              
                  $ret.="<br>&nbsp;</div>";
                 return $ret;                  
                },
            ],			

                        
            [
                'attribute' => 'dealStatus',
                'label'     => 'Текущий статус',
                'filter'=>array(
                    "0" => "Все незавершенные",
                    "1" => "На стадии заявки",                
                    "2" => "Оформление документов",                
                    "3" => "Ожидает оплаты ",                
                    "4" => "В ходе поставки",                
                ),
                
                'format' => 'raw',
                'contentOptions' =>['style'=>'font-size:11px;padding:2px;'],
                'value' => function ($model, $key, $index, $column) {
                    
                    if (empty($model['schetId']))
                    {
                        if ($model['isFormed'] == 0) return "Заявка не согласована";
                        if ($model['isFormed'] == 1) return "Заявка согласована <br> счет не выписан";
                    }
                    
                    $lastOp = Yii::$app->db->createCommand(
                    'SELECT max(refOp) as max_refOp from {{%schet_status}}
                      where refSchet=:refSchet', 
                    [':refSchet' => $model['schetId'] ])->queryOne();

                
                $retVal="Ожидается:";
                if ($model['docStatus']>0)
                {
                  if ($model['supplyState']>0)    
                  {
                    $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =3 order BY razdelOrder')->queryAll();        
                    $retVal .="<div>";
                    //$retVal .= "Выполнено: ".$list[$model['supplyState']-1]['Title'];
                    if (count ($list) > $model['supplyState']) { $retVal.= $list[$model['supplyState']]['Title'];}
                    $retVal .="</div>";
                  }                                  
                  if ($model['cashState']>0)    
                  {
                    $retVal .="<div>"; 
                    $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =2 order BY razdelOrder')->queryAll();        
                    //$retVal .= "Выполнено: ".$list[$model['cashState']-1]['Title'];
                    if (count ($list) > $model['cashState']) { $retVal.=$list[$model['cashState']]['Title'];}                    
                    $retVal .="</div>";
                  }                                  
                    $retVal .="<div>";
                    $list = Yii::$app->db->createCommand('SELECT id, Title, razdelOrder FROM {{%schet_status_op}} where razdel =1 order BY razdelOrder')->queryAll();        
                    //$retVal .= "Выполнено: ".$list[$model['docStatus']-1]['Title'];
                    if (count ($list) > $model['docStatus']) { $retVal.=$list[$model['docStatus']]['Title'];}                    
                    $retVal .="</div>";
                    
                   return $retVal;    
                }
                
                },
            ],        
            
              [
                'attribute' => 'Активность',
				'label'     => 'Активность',                
                'format' => 'raw',			                
                'contentOptions' =>['style'=>'font-size:11px;'],
                'value' => function ($model, $key, $index, $column) {					
                

                $listData= Yii::$app->db->createCommand(
                'SELECT count(id) as numContact, max(contactDate) as lastContact from {{%contact}} where refZakaz=:refZakaz  ', 
                [':refZakaz' => $model['zakazId'],])->queryAll();
                             
                if(count($listData)==0 || $listData[0]['numContact'] == 0)               
                { 
                    return "Нет активности";
                }
                
				  $ret="Контактов: ".$listData[0]['numContact']."<br>". date("d.m.Y", strtotime($listData[0]['lastContact']));				                                              
                  
                 return $ret;                  
                },
            ],			

            
         /*  
         [
                'attribute' => 'Исполнение поставки',
                'label'     => 'Исполнение поставки',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {
                $ret = "";
                if (empty($model['schetId'])) return "&nbsp";    
                $list = Yii::$app->db->createCommand('SELECT id, requestDate FROM {{%request_supply}} where refSchet =:refSchet',
                [':refSchet' => $model['schetId'] ])->queryAll();
                if (count ($list) == 0 ) return "Нет запроса ";
                $ret.= "<div>Запрос на поставку № ".$list[0]['id']." от ".date ('d.m.Y', strtotime($list[0]['requestDate']))."</div>";                 
                
                $list = Yii::$app->db->createCommand('SELECT requestNum, creationDate, requestDatePlanned, deliverSum, isFinished  FROM {{%request_deliver}} where refSchet =:refSchet',
                [':refSchet' => $model['schetId'] ])->queryAll();
                if (count ($list) == 0 ) return $ret."<div>Нет отгрузки</div>";
                for ($i=0; $i < count ($list ); $i++ ) 
                {                    
                    $ret.="<div>Задание № ".$list[$i]['requestNum']." от ".date ('d.m.Y', strtotime($list[$i]['creationDate']))."<br> на сумму ".$list[$i]['deliverSum'];
                    if ($list[$i]['isFinished'] == 1 ) $ret.=" <font color='ForestGreen' >Выполнено </font>";                    
                    $ret.="</div>";                     
                }
                return $ret;
                },
            ],        
            */
            
     
     
          ],
       ]
     );

?>

