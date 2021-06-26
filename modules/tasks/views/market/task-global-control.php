<?php

use kartik\grid\GridView;
use kartik\date\DatePicker;
use kartik\time\TimePicker;

$this->title = 'Контроль за работой менеджеров';
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
  document.location.href='index.php?r=/tasks/market/task-global-control&date='+showDate ;
}
</script>

<h3>Число организаций </h3>

<div class ='row'>
  <div class ='col-md-3'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=tasks/market/task-global-control&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
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
       <a href="index.php?r=tasks/market/task-global-control&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
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
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        
        'responsive'=>true,
        'hover'=>true,
        'showFooter' => false,
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
              //  'filter'    => $model->getUserList(),
                'format'    => 'raw',
                'value' => function ($model, $key, $index, $column) use ($now) {                        
                    return "<a href='index.php?r=tasks/market/task-control&date=".date("Y-m-d", $now)."&userRef=".$model['id']."'>".$model['userFIO']."</a>";
                },
            ],
           
    /*       [
                'attribute' => 'orgN',
                'label'     => 'Контрагентов',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
            ],
 */
 
            [
                'attribute' => 'calN',
                'label'     => 'План',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
            ],
            [
                'attribute' => 'cntN',
                'label'     => 'Контакты',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
            ],
 
            
            [
                'attribute' => 'atsN',
                'label'     => 'Набор',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                                
                'format'    => 'raw',
            ],
            [
                'attribute' => 'atsD',
                'label'     => 'Разговоров',                    
//                'filter'    => ['1'=>'Все','2'=>'7-30','3'=>'30-60','4'=>'>60'],                
                'format'    => 'raw',
            ],            
            [
                'attribute' => 'mailN',
                'label'     => 'Почта',
                'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
            ],

            [
                'attribute' => '-',
                'label'     => 'Погресс',
                //'filter'    => ['1'=>'Все','2'=>'Да','3'=>'Нет'],                
                'format'    => 'raw',
                 'value' => function ($model, $key, $index, $column) use ($now) {
                
                $newSd= Yii::$app->db->createCommand(
                'SELECT count(DISTINCT({{%orglist}}.id)) from {{%zakaz}}, {{%orglist}} where  {{%zakaz}}.refOrg={{%orglist}}.id AND {{%orglist}}.refManager=:refManager and DATE(formDate) = :date', 
                [':refManager' => $model['id'], ':date' => date('Y-m-d', $now)])->queryScalar();
                
                $chngStatus = $model['chngStatus']+$newSd; 
                
                if ($chngStatus == 0) return "&nbsp;";
                return $chngStatus;
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
//print_r($model->debug); ?>
</pre>
