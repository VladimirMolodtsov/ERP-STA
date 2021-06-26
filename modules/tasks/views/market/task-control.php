<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use kartik\date\DatePicker;
use kartik\time\TimePicker;

$this->title = 'Cписок выданных задач';
$now=$model->curTime;

$prev=$now-24*3600;
$next=$now+24*3600;

//echo date("H:i",$curTime);
$this->registerJsFile('@web/phone.js');

?>
<script>
function changeShowDate()
{
  showDate = document.getElementById('show_date').value;
  document.location.href='index.php?r=/tasks/market/task-control&date='+showDate ;
}
</script>


<div class ='row'>
  <div class ='col-md-3'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/task-control&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
      <div class ='col-md-3' style='text-align:center'>
   <?php   
   echo DatePicker::widget([
    'name' => 'show_date',
    'id' => 'show_date',
    'value' => date("d.m.Y",$now),    
    'type' => DatePicker::TYPE_COMPONENT_APPEND,
     'options' => ['onchange' => 'changeShowDate();',],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy'        
    ]
]);
?>      
</div>

   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=tasks/market/task-control&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-1'>   
  </div>  
  <div class ='col-md-1'>   
  </div>
  <div class ='col-md-1'>   
  </div> 
</div>



<?php

echo date("d.m.Y", $model->curTime)."<br>";

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'floatHeader'=>true,
        'responsive'=>true,
        'hover'=>true,
        'showFooter' => true,
        'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'before'=>Html::a('<i class="glyphicon glyphicon-plus"></i> Create Country', ['create'], ['class' => 'btn btn-success']),
        //'after'=>Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),        
         ],        
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'attribute' => 'userFIO',
                'label'     => 'Исполнитель',
                'filter'    => $model->getUserList(),
                'format'    => 'raw',
            ],

            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format'    => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >".$model['orgTitle']."</a>";
                },
                
            ],


            [
                'attribute' => 'calN',
                'label'     => 'План',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'footer'    => $model->sumData['calN'],
                'format'    => 'raw',
            ],
            [
                'attribute' => 'cntN',
                'label'     => 'Контакты',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
                'footer'    => $model->sumData['cntN'],
            ],
 
            
            [
                'attribute' => 'atsN',
                'label'     => 'Набор',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                                
                'format'    => 'raw',
                'footer'    => $model->sumData['atsN'],
            ],
            [
                'attribute' => 'atsD',
                'label'     => 'Звонки',                    
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                                
                'format'    => 'raw',
                'footer'    => $model->sumData['atsD'],
            ],            
            [
                'attribute' => 'mailN',
                'label'     => 'Почта',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
                'footer'    => $model->sumData['mailN'],
            ],

            
           [
                'attribute' => 'chngStatus',
                'label'     => 'Прогресс',
                //'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
                //'footer'    => $model->sumData['chngStatus'],
                'value' => function ($model, $key, $index, $column) use ($now) {
                
                $newSd= Yii::$app->db->createCommand(
                'SELECT count(id) from {{%zakaz}} where refOrg=:refOrg and DATE(formDate) = :date', 
                [':refOrg' => $model['refOrg'], ':date' => date('Y-m-d', $now)])->queryScalar();
                
                $chngStatus = $model['chngStatus']+$newSd; 
                
                if ($chngStatus == 0) return "&nbsp;";
                return  \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'glyphicon glyphicon-ok',
                     /*'id'      => $id,
                     'onclick' => $action,*/
                     'style'   => "color:Green",
                     'title'   => 'Смена статуса',
                   ]);
                }
            ],
                
           [
                'attribute' => '-',
                'label'     => 'План',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) use ($now) {
                    
                $listData= Yii::$app->db->createCommand(
                'SELECT event_date, eventNote  from {{%calendar}} where ref_org=:refOrg and DATE(event_date)>:date order by event_date ASC', 
                [':refOrg' => $model['refOrg'], ':date' => date('Y-m-d', $now)])->queryAll();
                
                
                if (count($listData) ==0) return "&nbsp;";
                 
                 //$r="<div>";                 
                 //$r="<pre>";                 
                /* $r .=Yii::$app->db->createCommand(
                'SELECT event_date, eventNote  from {{%calendar}} where ref_org=:refOrg and DATE(event_date)>:date order by event_date ASC', 
                [':refOrg' => $model['refOrg'], ':date' => date('Y-m-d', $now)])->getRawSql();*/
                 //$r .= print_r($listData, true); 
                 $r =date("d.m.Y", strtotime($listData[0]['event_date']));                 
                 //$r.= "<br>".$listData[0]['eventNote'];
                 //$r.="</pre>";                 
                 //$r.="</div>";
                 return $r;
                }
            ],    
                    
        
        
        
     ]      
         
    ]
);
?>

<pre>
<?php 
/*
ALTER TABLE `rik_contact` ADD COLUMN `docStatus` INTEGER DEFAULT 0;
ALTER TABLE `rik_contact` ADD COLUMN `cashStatus` INTEGER DEFAULT 0;
ALTER TABLE `rik_contact` ADD COLUMN `supplyStatus` INTEGER DEFAULT 0;
update rik_contact as a, rik_calendar as b
set a.docStatus = b.docStatus,
a.`cashStatus` = b.cashState,
a.supplyStatus = b.supplyState
where a.id = b.refExecute;

*/
//print_r($model->debug); 

//print_r($model->sumData);
?>
</pre>
