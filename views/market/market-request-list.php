<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper; 

$this->title = 'Заявки на доставку';
$curUser=Yii::$app->user->identity;

?>
<h3><?= Html::encode($this->title) ?></h3>
<style>

.local_btn {
	font-size: 12px;
	margin:4px;
	padding:4px;
	width:100px;
} 

</style>
 
<script type="text/javascript" src="phone.js"></script>  

<?php
$listArray = $model->getSupplyRequestStatusArray();
echo \yii\grid\GridView::widget(
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
                'attribute' => 'userFIO',
                'label' => 'Заявка от',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $val ="<nobr><b>".$model['requestId']."</b>"." от ".date("d.m",strtotime($model['requestDate']))."</nobr>";
                 return $val." <br>".$model['userFIO'] ;
                }
            ],    
            
            [
                'attribute' => 'schetNum',
                'label' => 'Счет',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 return $model['schetNum']." от ".date("d.m.y",strtotime($model['schetDate']))."<br><nobr> на сумму: ".number_format($model['schetSumm'], 2, '.', '&nbsp;')."</nobr>";
                }
            ],    
            
            
            [
                'attribute' => 'supplyDate',
                'label'     => 'Дата отгрузки',
                'format' => 'raw',
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
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['title']."</a>";
                },
            ],        

            [
                'attribute' => 'fltView',
                'label'     => 'Просмотрено',                
                'format' => 'raw',
                'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),
                'value' => function ($model, $key, $index, $column) {
                    $val ="";
            
                    if ($model['viewManagerRef'] == 0) 
                    {
                        return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' >
                        ";
                    }                                                                  
                return date("d.m.y h:i", 7*60*60+ strtotime($model['execView']) );
                }
                
            ],

            [
                'attribute' => 'fltStatus',
                'label'     => 'Статус',                
                'format' => 'raw',
                'filter'=>$listArray,

                'value' => function ($model, $key, $index, $column) {
                    $val ="";
                   if ($model['requestId'] < 1453) {
                       $url= "store/supply-request";                                      
                    if ($model['supplyState'] == 0) 
                        return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' 
                       >";
                    if ($model['supplyState'] & 0x00004) 
                        return "<input class='btn btn-danger local_btn'  type=button value='Отказ' 
                       >";                    
                    if ($model['supplyState'] & 0x00001)                     
                       return "<input class='btn btn-success local_btn'  style='background:ForestGreen;' type=button value='Принято' 
                       >";
                    }                      
                 
                 $listStatus = Yii::$app->db->createCommand('Select id, statusTitle from {{%supply_status_title}}')                    
                    ->queryAll();                
                 $listArray= ArrayHelper::map($listStatus, 'id', 'statusTitle');                   
                 $url= "store/supply-request-new";                                               
                 for ($i=17; $i>0; $i--)
                 {
                  $fld="st".$i;     
                  if(strtotime($model[$fld])> 1)         
                  {
                    return $listArray[$i];   
                    break;                   
                  }
                 }
                                                                
                return "<input class='btn  local_btn' style='border-color:Black;' type=button value='Ожидает' >";
                }
                
            ],
            
            [
                'attribute' => 'fltDeliver',
                'label'     => 'Доставки',                
                'format' => 'raw',
                 'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),

                'value' => function ($model, $key, $index, $column) {
                    $val ="";
                    
                    
                    if ($model['supplyType'] == 0 ) $val .= "Самовывоз"."<br>";
                    
                    $inDeliverList = Yii::$app->db->createCommand(
                    'Select sum(requestGoodValue*requestCount) as sumDeliver,
                    {{%request_deliver}}.id as deliverId
                    from {{%request_deliver}}, {{%request_deliver_content}} 
                    where {{%request_deliver}}.id = {{%request_deliver_content}}.requestDeliverRef and refSchet = :refSchet
                    GROUP BY {{%request_deliver}}.id
                    ')
                    ->bindValue(':refSchet', $model['refSchet'])                                        
                    ->queryAll();

                    if (empty ($inDeliverList) ) $val= "<nobr>Нет доставок</nobr>";
                    
                    $cnt = count($inDeliverList); 
                    $sum =0;                    
                    for ($i=0; $i<$cnt; $i++ )
                    {
                        $val .="<nobr>На сумму:".number_format($inDeliverList[$i]['sumDeliver'], 2, '.', '&nbsp;')."</nobr><br>";                            
                        $sum+=$inDeliverList[$i]['sumDeliver'];
                    }
                    
                    if($cnt > 0)
                    {
                    if ($sum == $model['schetSumm'])     {$color ="ForestGreen";}
                    elseif ($sum > $model['schetSumm'])  {$color ="Orange";}
                                                    else {$color ="Crimson";}
                      
                    $val .="<div style='text-align:right;font-weight:bold; color:".$color."'>ВСЕГО: ".number_format($sum, 2, '.', '&nbsp;')."</div> ";
                    }
                    
                return $val;
                }
                
            ],



            [
                'attribute' => 'fltFinish',
                'label'     => 'Доставлено',                
                'format' => 'raw',
                'filter'=>array("1"=>"Все","2"=>"Да","3"=>"Нет",),
                'value' => function ($model, $key, $index, $column) {
                    $val ="";

                    if ($model['supplyState'] & 0x00008) 
                    {
                        return "<input class='btn btn-success local_btn' style='background-color: ForestGreen;' type=button value='Доставлен' >
                        ";
                    }  
                    
                return "";
                }
                
            ],


            
            
            
        ],
    ]
);
  
?>