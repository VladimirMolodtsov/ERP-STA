<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;

use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Звонки - протоколирование';
 
$this->registerJsFile('@web/phone.js');

$curUser=Yii::$app->user->identity;
?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />

<style>

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
  
<script>
var curRecord = 0;
var curOrg = 0;




function selectContact(id, orgRef)
{
 curRecord = id;
 //var idx = 'orgTitle'+id;
 //document.getElementById('currentSel').innerHTML = document.getElementById(idx).innerHTML;
 document.getElementById('selectContactDialogFrame').src = 'index.php?r=/zadarma/ats/select-contact&noframe=1&orgRef='+orgRef ;
 $('#selectContactDialog').modal('show');  
}

function newContact(orgRef)
{
   openWin('site/reg-contact&id='+orgRef+'&atsRef='+curRecord, 'contactWin');
}


function setContact(selContactRef)
{
  $('#selectOrgDialog').modal('hide');

   var orgTitle = document.getElementById('orgTitle').value;
   $(document.body).css({'cursor' : 'wait'});
   var url = 'index.php?r=zadarma/ats/set-contact&id='+curRecord+'&contactRef='+selContactRef;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){
            console.log(res);
            $(document.body).css({'cursor' : 'default'});
//            setOrg(res.id);
            document.location.reload(true);
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive format detail!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });



   openSwitchWin("zadarma/ats/set-org&id="+curRecord+'&orgRef='+orgRef);
}


function showSetOrgList(id,orgRef)
{
 curRecord = id;
 curOrg = orgRef;
 var idx = 'orgTitle'+id;
 document.getElementById('currentSel').innerHTML = document.getElementById(idx).innerHTML;
 $('#selectOrgDialog').modal('show');  
}

function setOrg(orgRef)
{ 
  $('#selectOrgDialog').modal('hide');  
   openSwitchWin("zadarma/ats/set-org&id="+curRecord+'&orgRef='+orgRef);    
}




function openCurrentOrg()
{
    showOrg(curOrg);
}
function showOrg(orgRef)
{
 openWin('site/org-detail&orgId='+orgRef,'orgWin');
}

function newOrg()    
{ 

   var orgTitle = document.getElementById('orgTitle').value;
   $(document.body).css({'cursor' : 'wait'});   
   var url = 'index.php?r=site/create-org&orgTitle='+orgTitle;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
//        data: data,
        success: function(res){     
            console.log(res);
            $(document.body).css({'cursor' : 'default'});            
            setOrg(res.id);
            //document.location.reload(true); 
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            console.log('Error while retrive format detail!');
            console.log(url);
            //alert('Error while retrive ware groups!');
        }
    });	
    
}

function setCalendaFilter(d,m,y)
{
    var fltDate = y+'-'+m+'-'+d;
//alert(fltDate);
    
    var url = 'index.php?r=/zadarma/ats/show-log&noframe=1&fltDetail=0&fltDate='+fltDate;
    document.location.href=url;
}

function showCalendar(){
    $('#selectCalendarDialog').modal('show');    
    
}

function selectNow()
{
    var url = 'index.php?r=/zadarma/ats/show-log';
    document.location.href=url;
}



function selectError()
{
    var url = 'index.php?r=/zadarma/ats/show-log&noframe=1&fltDetail=2&fltDate=<?= $model->fltDate ?>';
    document.location.href=url;
}


function selectGood()
{
    var url = 'index.php?r=/zadarma/ats/show-log&noframe=1&fltDetail=1&fltDate=<?= $model->fltDate ?>';
    document.location.href=url;
}


function doCallNew(phone)
{      
  window.open("<?php echo $curUser->phoneLink; ?>"+phone,'doCall','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');     
}

</script>

<?php 

$dayStat = $model->getPhoneDayStatistics();

$curDay = date("d.m.Y", strtotime($model->fltDate));
?>

<div class='row'>

    <div class="col-md-2" >
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectNow();'>
        <div class='leaf-txt' > Сегодня </div>
        <div class='leaf-val' ><?= $dayStat['dayNow'] ?></div> 
        <div class='leaf-sub'>за <?= date("d.m.y") ?></div>
    </a>
    </div> 


    <div class="col-md-2" >
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='showCalendar();'>
        <div class='leaf-txt' > Календарь  </div>
        <div class='leaf-val' ><?= $dayStat['dayAll'] ?></div> 
        <div class='leaf-sub'>за <?= $curDay ?></div>
    </a>
    </div> 



    <div class="col-md-2" >
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectError();'>
        <div class='leaf-txt' style='color:Crimson' > Ошибок  </div>
        <div class='leaf-val' style='color:Crimson'><?= $dayStat['dayError'] ?></div> 
        <div class='leaf-sub'>за  <?= $curDay ?></div>
    </a>
    </div> 


   <div class="col-md-2" >
    <a  class='btn btn-primary leaf ' style='background:White ; color:Blue;' href='#' onclick='selectGood();'>
        <div class='leaf-txt' style='color:DarkGreen' > Опознано  </div>
        <div class='leaf-val' style='color:DarkGreen'><?= $dayStat['dayGood'] ?></div> 
        <div class='leaf-sub'>за  <?= $curDay ?></div>
    </a>
    </div> 

    
    <div class="col-md-4" >
    Уникальных номеров за <?= $curDay ?>: <?= $dayStat['phoneNum'] ?>
    </div> 
  



</div>


<div class='spacer'></div>

 <?php 
 
     
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,
        //'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'call_start',
                'label'     => 'Дата',
                'format' => 'raw',            
            ],        
                 

            [
                'attribute' => 'external_num',
                'label'     => 'Внешний',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                
                 $action="doCallNew('".$model['external_num']."')";
                 
                  $val = \yii\helpers\Html::tag( 'div', $model['external_num'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                   return $val;
                 }                                
                
            ],        

            [
                'attribute' => 'event',
                'label'     => 'Направление',
                'filter' => [0 => 'Все', 1 => 'Исходящие', 2 => 'Входящие'],
                
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column) {                                
                    if ($model['event']=='NOTIFY_OUT_END') return 'Исходящий';
                    else return 'Входящий';
                }                                
                      
            ],        
                        
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',
                //'filter' => [0 => 'Все', 1 => 'Определен', 2 => 'Не определен'],
                'value' => function ($model, $key, $index, $column) {                                
                    if (empty($model['orgRef']) ) {$title = 'Не определен'; $style='color:Crimson';} 
                                          else    {$title = $model['orgTitle'];$style='';}                     
                                          
                  $id = "orgTitle".$model['id'];                        
                  $action="showSetOrgList(".$model['id'].",".$model['orgRef'].")";
                  $val = \yii\helpers\Html::tag( 'div', $title, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'style'   => $style,
                     'id'      => $id,
                   ]);
                   return $val;                                                               
                  
                }                                
            ],        

           
            [
                'attribute' => 'internal_id',
                'label'     => 'Код АТС',
                'format' => 'raw',
            ],        
                   
            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',
            ],        
                   
            
            
                          
            [
                'attribute' => 'disposition',
                'label'     => 'Статус',
                'format' => 'raw',
                 'filter' => [0 => 'Все', 1 => 'Отвечен', 2 => 'Не отвечен', 3 => 'Занят', 4 => 'Ошибка']
            ],        

            [
                'attribute' => 'duration',
                'label'     => 'Длительность',
                'format' => 'raw',
                 'filter' => [0 => 'Все', 1 => '0 сек', 2 => '0-30 сек', 3 => '>=30 сек']                
             ],        
            
            [
                'attribute' => '-',
                'label'     => 'Контакт',
                'format' => 'raw',                      
                'value' => function ($model, $key, $index, $column) {
                 $val = '---';
                 $action = "selectContact(".$model['id'].",".$model['orgRef'].")";
                 $style = "color:Crimson";
                if (!empty($model['refContact']) )       
                {
                 /* Лид */
                 if ($model['eventType'] >= 10 && $model['eventType'] < 100)
                 {
                      $action = "openWin('site/lead-process&contactId=".$model['refContact']."', 'contactWin')";
                      $val = "Лид № ".$model['refContact'];
                      $style = "";
                 } else
                 {
                      $action = "openWin('site/reg-contact&id=".$model['orgRef']."&contactId=".$model['refContact']."', 'contactWin')";
                      $val = "Контакт № ".$model['refContact'];
                      $style = "";
                 }
                }
                else 
                {
                if (empty($model['orgRef']) ){
                      $action = "openWin('site/new-lead&atsRef=".$model['id']."', 'contactWin')";
                      $val = "Создать лид";
                }
                 
               }  
                 
                 return \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     //'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                 
                 
                     return;
                 }
                   
             ],        
            
            
                        
            [
                'attribute' => 'internal_num',
                'label'     => 'Внутренний',
                'format' => 'raw',
            ],        

            
      ]//columns            
    ]
    );
?>


<?php
Modal::begin([
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Выберите организацию</h4>',
]);?>
<div style='width:550px'>
<div class='row'>
    <div class='col-md-4' align=center> Текущая: </div>  
    <div class='col-md-8' align=center> 
<?php
                  $action="openCurrentOrg()";             
                  echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'id'      => 'currentSel', 
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'style'   => 'font-size:15px;padding:5px;'
                   ]);

?>
    </div>
</div>
<hr>
<div class='row'>
    <div class='col-md-6'>    
<?php

      echo Html::textInput( 
                          'orgTitle', 
                          '',                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:250px; font-size:11px;padding:1px;', 
                              'id' => 'orgTitle', 
                              'placeholder' => 'Название новой организции'     
                              ]);    
?>
    </div>
    <div class='col-md-2'>
        <div class='btn btn-primary' onclick='newOrg();'><span class='glyphicon glyphicon-plus'></span></div>
    </div>    
</div>
<?php    
    Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgProvider,
		'filterModel' => $model,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed small'],
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'selOrgTitle',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                $action="showOrg(".$model['selOrgRef'].");";
                return \yii\helpers\Html::tag( 'div', $model['selOrgTitle'], 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);

                },
            ],		

            
			[
                'attribute' => '',
				'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                
                $action="setOrg(".$model['selOrgRef'].",\"".$model['selOrgTitle']."\" );";
                return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-plus'></span>", 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                
                },
            ],		
            
            
            
            [
                'attribute' => 'selOrgINN',
				'label'     => 'ИНН',                
            ],
            [
                'attribute' => 'selOrgKPP',
				'label'     => 'КПП',                
            ],
			
			

        ],
    ]
);
Pjax::end();
?>
    
</div>

<?php Modal::end();?>


<?php
Modal::begin([
    'id' =>'selectCalendarDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px;'>
    <iframe id='selectCalendarDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/zadarma/ats/ats-calendar&noframe=1&month=<?=date('m',strtotime($model->fltDate))?>&year=<?=date('Y',strtotime($model->fltDate))?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
Modal::begin([
    'id' =>'selectContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Связать с контактом</h4>',
]);?>
<div style='width:550px'>

    <iframe id='selectContactDialogFrame' width='570px' height='470px' frameborder='no'   
    src='index.php?r=/zadarma/ats/select-contact&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
   
</div>

<?php Modal::end();?>






<?php 
//echo "<pre>";
//print_r ($model->debug);
//    print_r($data);
//echo "</pre>";
 ?>



