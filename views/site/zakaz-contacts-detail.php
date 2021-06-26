<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\widgets\Pjax;


$this->title = 'Детализация контактов по заказу № '.$model->refZakaz;
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>

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

function switchData(id,task)
{

  var URL = 'index.php?r=/site/set-contact-status&id='+id+'&task='+task;
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
           window.opener.location.reload(false); 
           document.location.reload(true);            
        },
        error: function(){
            alert('Error while preparing data!');
        }
    });	
}

function showContact(id)
{
   url =  'index.php?r=/site/show-contact&noframe=1&id='+id;
   document.getElementById('showContactDialogFrame').src=url;  
   $('#showContactDialog').modal('show');     
   
}

</script>

  <h2><?php echo Html::encode($this->title);?></h2>

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
                'attribute' => 'contactDate',
                'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.Y H:i:s'],
            ],
        
          'contactFIO:raw:Контактное лицо',           
          [
                'attribute' => 'phone',
                'label'     => 'Телефон/почта',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                                                
                if (!empty ($model['phone'])){

                $atsLogList = Yii::$app->db->createCommand("SELECT *                
                FROM 
                {{%ats_log}} where duration > 10 and external_num=:phone AND DATE(call_start)='".date ("Y-m-d", strtotime($model['contactDate']))."'                
                 Order by call_start ASC",
                [
                 ':phone' => $model['phone'],                                                  
                ]                
                )->queryAll();    
                                
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
                
                }
                    return $model['phone'].$log;//."<br>".$str;
                }
                
                if (!empty ($model['contactEmail'])){return $model['contactEmail'];}
                return "&nbsp;";
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
            [
                'attribute' => 'eventType',
                'label'     => 'Игнорировать',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $id='ignore'.$model['id'];
                   $action = "switchData(".$model['id'].", 'ignore')";
                   $style="";
                   if ($model['eventType']==5) $style = 'background-color:Crimson;';
                   $val = \yii\helpers\Html::tag( 'div',"" , 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                   
                    return $val;
                }
                
            ],
    
            [
                'attribute' => 'eventType',
                'label'     => 'Лид',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $id='lead'.$model['id'];
                   $action = "switchData(".$model['id'].", 'lead')";
                   $style="";
                   if ($model['eventType']==20) $style = 'background-color:Green;';
                   $val = \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'btn btn-default btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                   
                    return $val;
                }
                
            ],

        
        ],
    ]
);
?>

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

