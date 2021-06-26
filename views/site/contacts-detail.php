<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\widgets\Pjax;


$this->title = 'Детализация контактов';
$this->params['breadcrumbs'][] = $this->title;

?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

function getRecord(pbxid)
{
  openWin ("zadarma/api/get-record&pbxCallId="+pbxid, "records");      
}

function openZakaz(id)
{
   openWin('market/market-zakaz&orgId= <?= $model->id ?>&noframe=1&zakazId='+id,'childWin');
}

function openSchet(id)
{
   openWin("market/market-schet&noframe=1&id="+id,'childWin');
}

function removTask(id)
{
   openSwitchWin("market/mark-event-done&id="+id);
}

function showContact(id)
{
   url =  'index.php?r=/site/show-contact&noframe=1&id='+id;
   document.getElementById('showContactDialogFrame').src=url;  
   $('#showContactDialog').modal('show');     
   
}


</script>

  <h2><?php echo Html::encode($this->title); 
        echo "&nbsp;&nbsp;<a href=index.php?r=site/org-detail&orgId=".$model->id."&noframe=1>".$model->getOrgTitle()."</a>";
  ?></h2>


<h3>Список назначенных задач</h3>

<?php
echo GridView::widget(
    [
        'dataProvider' => $tasksProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
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
                'attribute' => 'startDate',
                'label' => 'Начало',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],

            [
                'attribute' => 'planDate',
                'label' => 'План',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
            [
                'attribute' => 'deadline',
                'label' => 'Дедлайн ',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
            
           [
                'attribute' => 'taskPriority',
                'label' => 'Приоритет ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                switch ($model['taskPriority'])                                
                {
                case 0:
                    return "Нормальный";
                break;
                
                case 1:
                    return "Поручение";                
                break;
                
                case 2:
                    return "Приказ";                
                break;
                    
                }
                
                return "&nbsp;";
            }

            ],
            

          'note:raw:Задача',            
             'userFIO:raw:Менеджер',
           

            
        ],
    ]
);
?>


<h3>Список выполняемых задач</h3>


<?php
echo GridView::widget(
    [
        'dataProvider' => $eventProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
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
                'attribute' => 'deadline',
                'label' => 'Дедлайн ',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],

            
            [
                'attribute' => 'event_date',
                'label' => 'Дата ',
                'format' => ['datetime', 'php:d.m.Y'],
            ],

            [
                'attribute' => 'eventTime',
                'label' => 'Время ',
                'format' => 'raw',                
                //'format' => ['datetime', 'php:H:i'],
            ],

           'note:raw:Задача',
           [
                'attribute' => 'eventNote',
                'label'     => 'Ожидаемое событие',                
                'format' => 'raw',                
                
                'value' => function ($model, $key, $index, $column) {
                
                if (!empty ($model['ref_schet'])){
                    return $model['eventNote']."<br><a href='#' onclick='openSchet(".$model['ref_schet'].");'>Счет № ".$model['schetNum']." от ".date("d.m.Y", strtotime($model['schetDate']))."</a>"  ;
                }   
                
                if (!empty ($model['ref_zakaz'])){
                    return $model['eventNote']."<br><a href='#' onclick='openZakaz(".$model['ref_zakaz'].");'>Заказ № ".$model['ref_zakaz']." от ".date("d.m.Y", strtotime($model['zakazDate']))."</a>" ;
                }
                return $model['eventNote'];
                }                
            ],            
          
          [
                'attribute' => 'phone',
                'label'     => 'Телефон/почта',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                                
                if (!empty ($model['phone'])){
                    return $model['contactFIO']." ".$model['phone'];
                }
                
                if (!empty ($model['contactEmail'])){return $model['contactFIO']." ".$model['contactEmail'];}
                return "&nbsp;";
                }                
            ],

            'userFIO:raw:Менеджер',
            
          [
                'attribute' => '',
                'label'     => '',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                            
                if (  $model['refTask'] > 0 && $model['eventStatus'] == 2 ){
                    return "<a href='#' onclick='acceptTaskExec(".$model['refTask'].");'><span class='glyphicon glyphicon-ok'></span></a>";
                }                
                if ( $model['refTask'] == 0 ){
                    return "<a href='#' style='color:Crimson' onclick='removTask(".$model['id'].");'><span class='glyphicon glyphicon-remove'></span></a>";
                }
                 
                 return "&nbsp;";   
                }
                
            ],
            
            
            
        ],
    ]
);
?>

<br>
<div class='row'>
 <div class='col-md-2'>
 
 </div>

 <div class='col-md-2'>
    <a  class="btn btn-primary"  href='#' onclick="openWin('site/org-deal-reestr&orgId=<?=  Html::encode($model->id)  ?>','orgCard');">Реестр сделок</a>
 </div>

<div class='col-md-2'> 
 <a  class="btn btn-primary"  href='#' onclick="openWin('market/market-zakaz-create&id=<?=  Html::encode($model->id)  ?>','childWin');">Регистрация заказа</a>   
</div>

<div class='col-md-2'> 
   <a  class="btn btn-primary"  href='#' onclick="openWin('site/reg-contact&id=<?=  Html::encode($model->id)  ?>','childWin');">Регистрация контакта</a>
</div>

<div class='col-md-2'> 
   
</div>

<div class='col-md-2'> 
   
</div>

 
</div>  



<h3>Список состоявшихся контактов</h3>

<?php
echo GridView::widget(
    [
        'dataProvider' => $contactsProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>true,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
  /*      'beforeGrid'=>'My fancy content before.',
        'afterGrid'=>'My fancy content after.',*/
        ],
/*        'exportConfig' => [
            GridView::CSV => ['label' => 'Save as CSV'],
            GridView::HTML => ['label' => 'Save as HTML'],
            //GridView::PDF => ['label' => 'Save as PDF'],
        ],*/
/*        'toolbar'=>[
          '{export}',
          '{toggleData}'
        ],        */
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            [
                'attribute' => 'contactDate',
                'label' => 'Дата контакта',
             //   'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {

                 $id = 'contactDate'.$model['id'];
                 $action="openWin('site/reg-contact&singleWin=1&contactId=".$model['id']."', 'contactRegWin')";

                   return \yii\helpers\Html::tag( 'div', date("d.m.Y H:i:s",strtotime($model['contactDate'])),
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
                },
                
            ],
        
          [
                'attribute' => 'event_date',
                'label'     => 'Ожидаемое событие',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                                
                if (empty ($model['event_date'])){return "&nbsp;";}
                
                
                 $val= date ("d.m.Y h:i:s", strtotime($model['event_date']));
                if (!empty ($model['eventNote']))
                {
                    $val .= "<br>".$model['eventNote'];
                } 
                                        
                if ($model['eventStatus'] == 1 && strtotime($model['event_date']) < time()){$val = "<font color='Crimson'>".$val."</font>";}
                return $val;
                }
                
            ],            
          'contactFIO:raw:Контактное лицо',           
          [
                'attribute' => 'phone',
                'label'     => 'Телефон/почта',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                                
                if (!empty ($model['phone'])){

                //and ABS(TIME_TO_SEC(TIMEDIFF(TIME(call_start), '".date ("H:i:s", strtotime($model['contactDate']))."'))) <5400
                
                $atsLogList = Yii::$app->db->createCommand("SELECT *                
                FROM 
                {{%ats_log}} where duration > 10 and external_num=:phone AND DATE(call_start)='".date ("Y-m-d", strtotime($model['contactDate']))."'                
                 Order by call_start ASC",
                [
                 ':phone' => $model['phone'],                                                  
                ]                
                )->queryAll();    
                
               /* $str=Yii::$app->db->createCommand("SELECT * FROM 
                {{%ats_log}} where duration > 10 and external_num=:phone AND DATE(call_start)='".date ("Y-m-d", strtotime($model['contactDate']))."'",
                [
                 ':phone' => $model['phone'],                                                  
                ]                
                )->getRawSql();*/
                
                $N= count ($atsLogList);
                $log="";
                $diff = time();
                if ($N > 0) $log = "<br>".date("H:i:s", strtotime($atsLogList[0]['call_start'])-3600)."&nbsp;".$atsLogList[0]['duration']."сек"."&nbsp;";//.$atsLogList[$i]['a']."сек";      
                for ($i=0;$i<$N; $i++ )
                {
                  $dt= strtotime($model['contactDate']) - (strtotime($atsLogList[$i]['call_start'])-4*3600);
                  if ($dt<$diff && $dt > 0)
                  {
                   $diff = $dt;                     
                   $log = "<br>".date("H:i:s", strtotime($atsLogList[$i]['call_start'])-3600)."&nbsp;".$atsLogList[$i]['duration']."сек"."&nbsp;";//.$atsLogList[$i]['a']."сек";      
                   if ($atsLogList[$i]['is_recorded'] == 1) $log .="<a href='#' onclick='getRecord(\"".$atsLogList[$i]['pbx_call_id']."\")'><span class='glyphicon glyphicon-volume-up'></span></a>";  
                  }
                  //https://my.zadarma.com/mypbx/stat/download/?id=5d71d6c213a8575d6380cf0b&sn=sounds&name=23713-1567741581.7342-73832581849-2019-09-06-104621.mp3
                  //break;
                }
                    return $model['phone'].$log;//."<br>".$str;
                }
                
                if (!empty ($model['contactEmail'])){return $model['contactEmail'];}
                return "&nbsp;";
                }
                
            ],

            [
                'attribute' => 'refZakaz',
                'label'     => 'Продажа',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                if (empty($model['refZakaz'])) return; 
                $zakazList = Yii::$app->db->createCommand("SELECT formDate FROM {{%zakaz}} where id=:refZakaz ",
                [':refZakaz' => $model['refZakaz'], ] )->queryOne();    
                $id = 'refZakaz'.$model['id']; 
                $action="openWin('market/market-zakaz&zakazId=".$model['refZakaz']."','sdelkaWin')";
                $v="Заказ ".$model['refZakaz']." от ".$zakazList['formDate'];
                return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
  
                }
            ],
            
            [
                'attribute' => 'refPurchase',
                'label'     => 'Закупка',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                if (empty($model['refPurchase'])) return; 
                /*$zakazList = Yii::$app->db->createCommand("SELECT formDate FROM {{%zakaz}} where id=:refZakaz ",
                [':phone' => $model['refZakaz'], ] )->queryOne();    */
                $id = 'refPurchase'.$model['id']; 
                $action="openWin('store/head-purchase-zakaz&id=".$model['refPurchase']."','purchaseWin')";
                $v="№ ".$model['refPurchase'];
                return \yii\helpers\Html::tag( 'div',$v, 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                   ]);
  
                }
            ],
            
            [
                'attribute' => 'note',
                'label'     => 'Комментарий',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  
                  $id = 'showContact'.$model['id']; 
                  $action = "showContact(".$model['id'].");"; 
                   
                 return \yii\helpers\Html::tag( 'div',mb_substr($model['note'],0,260), 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => "показать контакт",
                     'style'   => "color:Black;",
                   ]);
  
                    
                   
                }
                
            ],

            'userFIO:raw:Менеджер',
            
            
            
        ],
    ]
);
?>

<input class="btn btn-primary"  style="width: 150px;" type="button" value="Вернутся" onclick="javascript:history.back();"/>


<?php
Modal::begin([
    'id' =>'showContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='showContactDialogFrame' width='570px' height='720px' frameborder='no'   src='index.php?r=/site/show-contact&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>

