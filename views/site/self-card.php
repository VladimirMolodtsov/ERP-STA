<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;


$curUser=Yii::$app->user->identity;
$this->title = 'Карточка собственника ';
//$this->params['breadcrumbs'][] = $this->title;

$record=$model->loadOrgRecord();

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/site/org-card.js');

$this->registerCssFile('@web/phone.css');

?>
<style>
 .part-header
{
    padding: 2px;	 
	color: Black;
	text-align: right;    
	background-color: LightBlue ;
	font-size: 11pt;
    font-weight: Bold;
}

 .item-header{
    padding: 10px;     
    color: black;
    text-align: left;    
    font-size: 14pt;
 } 

.edit-form{
    font-size: 10pt;
}
 
input {
    font-size: 10pt;
}
 

.gridcell {
    width: 120px;        
    height: 17px;
    display: block;
    font-size: 12px;    
    text-align: center;
    word-wrap: break-word;
    /*background:DarkSlateGrey;*/
}    
.gridcell:hover{
    background:Silver;
    cursor: pointer;
    color:#FFFFFF;
}
.editcell{
   width: 17px;
   display:none;
   white-space: nowrap;
   background:White;
}
 
 .viewcell{
    width: 100px;        
    height: 17px;
    display: block;
    font-size: 12px;    
    text-align: right;
    word-wrap: break-word;
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
 
 .table-small {
padding: 2px;
font-size:12px;
}

td {
padding-left: 10px;
}

.page-title
{    
  font-size: 14pt;
  font-weight:bold;
}
.panel-heading
{    
 padding:4px;
}

.panel-body
{    
 padding:2px;
}
.summary
{
   display:none; 
}
.collapse-toggle
{
  font-size:12px;  
}


</style>

<script type="text/javascript">

function chngSetNDS()
{
  val =document.getElementById('isSetNDS').checked; 
  if (val == true) document.getElementById('NDS').readOnly = false;
      else document.getElementById('NDS').readOnly=true;
}


/**********/
function saveSelfField( type)
{
    idx= type;
    
    document.getElementById('dataType').value=type;
    document.getElementById('dataVal').value=document.getElementById(idx).value;
    
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=/site/save-self-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSaved(res); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


</script>

<div class='row'>
    <div class='col-md-7' style='margin-top:-10px;'>
        <h2><?= Html::encode($this->title) ?></h2>
    </div>

    <div class='col-md-2'>
  
    </div>
    <div class='col-md-2' align='right;'>
    </div>

    <div class='col-md-1' align='right;'>
    <?php
   /*     $query = preg_replace("/\s+/","+",$model->title);        
        $action= "syncCard(".$model->orgId.")";
        echo \yii\helpers\Html::tag( 'div', "", 
                   [
                     'class'   => 'glyphicon glyphicon-refresh clickable',
                     'onclick' => $action,
                     'style'  => 'margin-top:20px;'
                   ]);
 */        
       ?> 
    </div>
    
</div>



<?php

   $dblGisLabel= "2Гис: ";   
   $dblGisLabel.= "<b>".$model->dblGisLabel."</b>";
   $dblGisContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getDblGisProvider(),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
        
             [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isdblGisDefault';                 
                 $action =  "saveField(".$model['id'].", 'isdblGisDefault');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => 'dblGisLabel',
                'label'     => 'Адрес',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:800px;'],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'dblGisLabel';
                 $action =  "saveField(".$model['id'].", 'dblGisLabel');"; 
                 return Html::textInput( 
                          $id, 
                          $model['dblGisLabel'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:800px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],       

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:40px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'dblGisDel';                 
                 $action =  "saveField(".$model['id'].", 'dblGisDel');";                  
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  
        ],
    ]
    );
    $dblGisContent .= "<div style='text-align:left; padding:0px;'>";
    $dblGisContent .= "<span class='clickable'  onclick='addNewDblGis(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span></div>";

   $okvedLabel= "ОКВЭД: ";   
   $okvedLabel.= "<b>".$model->orgOKVED."</b>";
   $okvedContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getOkvedProvider(),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isOkvedDefault';                 
                 $action =  "saveField(".$model['id'].", 'isOkvedDefault');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  
        
            [
                'attribute' => 'OKVED',
                'label'     => 'Адрес',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:800px;'],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'OKVED';
                 $action =  "saveField(".$model['id'].", 'OKVED');"; 
                 return Html::textInput( 
                          $id, 
                          $model['OKVED'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:800px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],       

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:40px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'okvedDel';                 
                 $action =  "saveField(".$model['id'].", 'okvedDel');";                  
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  
        ],
    ]
    );
    $okvedContent .= "<div style='text-align:left; padding:0px;'>";
    $okvedContent .= "<span class='clickable'  onclick='addNewOkved(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span></div>";


   $emailLabel= "Email: ";   
   $emailLabel.= "<b>".$model->defContactEmail." ".$model->emailContactFIO."</b>";
   $emailContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getEmailListProvider(),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
             [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isDefaultEmail';                 
                 $action =  "saveField(".$model['id'].", 'isDefaultEmail');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Email не верен',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => 'email',
                'label'     => 'Адрес',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:250px;'],
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'email';
                 $action =  "saveField(".$model['id'].", 'email');"; 
                 return Html::textInput( 
                          $id, 
                          $model['email'],
                              [
                              'class' => 'form-control',
                              'style' => 'width:250px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            [
                'attribute' => 'emailContactFIO',
                'label'     => 'Контактное лицо',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:470px;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'emailContactFIO';
                 $action =  "saveField(".$model['id'].", 'emailContactFIO');"; 
                 return Html::textInput( 
                          $id, 
                          $model['emailContactFIO'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:470px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
     
            
            [
                'attribute' => 'status',                
                'label'     => 'Верен',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isBadEmail';                 
                 $action =  "saveField(".$model['id'].", 'isBadEmail');"; 
                 if ($model['status'] == 2) $style ="background-color:Crimson;";
                 else  $style ="background-color:Green;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Email не верен',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  



            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:40px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'emailDel';                 
                 $action =  "saveField(".$model['id'].", 'emailDel');";                  
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  
        ],
    ]
    );
    $emailContent .= "<div style='text-align:left; padding:0px;'>";
    $emailContent .= "<span class='clickable'  onclick='addNewEmail(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span></div>";


   $adressLabel= "Адрес: ";
   $adressLabel.= "<b>".$model->adress."</b>";
   $adressContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $adressProvider,
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            
            [
                'attribute' => 'isOfficial',                
                'label'     => 'Юрид.',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isOfficialAdress';                 
                 $action =  "saveField(".$model['id'].", 'isOfficialAdress');"; 
                 if ($model['isOfficial'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  
            [
                'attribute' => 'adress',
                'label'     => 'Полный адрес',
                'format' => 'raw',  
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'adress';
                 $action =  "saveField(".$model['id'].", 'adress');"; 
                 return Html::textInput( 
                          $id, 
                          $model['adress'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:300px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            
            
            [
                'attribute' => 'area',
                'label'     => 'Область',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'area';
                 $action =  "saveField(".$model['id'].", 'area');"; 
                 return Html::textInput( 
                          $id, 
                          $model['area'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            [
                'attribute' => 'city',
                'label'     => 'Город',
                'format' => 'raw', 
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'city';
                 $action =  "saveField(".$model['id'].", 'city');"; 
                 return Html::textInput( 
                          $id, 
                          $model['city'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            [
                'attribute' => 'district',
                'label'     => 'Район',
                'format' => 'raw',  
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'district';
                 $action =  "saveField(".$model['id'].", 'district');"; 
                 return Html::textInput( 
                          $id, 
                          $model['district'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            [
                'attribute' => 'index',
                'label'     => 'Индекс',
                'format' => 'raw', 
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'index';
                 $action =  "saveField(".$model['id'].", 'index');"; 
                 return Html::textInput( 
                          $id, 
                          $model['index'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:75px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            [
                'attribute' => 'streetAdres',
                'label'     => 'Адрес (ул, дом)',
                'format' => 'raw',  
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'streetAdres';
                 $action =  "saveField(".$model['id'].", 'streetAdres');"; 
                 return Html::textInput( 
                          $id, 
                          $model['streetAdres'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:175px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
            
             [
                'attribute' => 'isBad',                
                'label'     => 'Верен',
                'format' => 'raw',  
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'isBadAdress';                 
                 $action =  "saveField(".$model['id'].", 'isBadAdress');"; 
                 if ($model['isBad'] == 1) $style ="background-color:Crimson;";
                 else  $style ="background-color:Green;";

                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Адрес не верен',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'adressDel';                 
                 $action =  "saveField(".$model['id'].", 'adressDel');"; 
                 
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  
            
            
        ],
    ]
    );

    $adressContent.="<div style='text-align:left; padding:0px;'>";
    $adressContent.="<span class='clickable' onclick='addNewAdress(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span></div>";




   $phoneLabel= "Телефон ";
   $phoneLabel .= "<b>".$model->defContactPhone." ".$model->defContactFIO."</b>";
   $phoneContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getPhoneListProvider(),        
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isDefaultPhone';                 
                 $action =  "saveField(".$model['id'].", 'isDefaultPhone');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  
            
     

            [
                'attribute' => 'phone',                
                'label'     => 'Телефон',
                'format' => 'raw',       
                'contentOptions' => ['style' => 'padding:0px;width:150px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'phone';                 
                 $action =  "saveField(".$model['id'].", 'phone');"; 
                 return Html::textInput( 
                          $id, 
                          $model['phone'],                                
                              [
                               'class' => 'form-control',
                              'style' => 'width:150px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  


            [
                'attribute' => 'phoneContactFIO',                
                'label'     => 'Контакт',
                'format' => 'raw',       
                'contentOptions' => ['style' => 'padding:0px;width:450px;text-align:center;'],                                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'phoneContactFIO';                 
                 $action =  "saveField(".$model['id'].", 'phoneContactFIO');"; 
                 return Html::textInput( 
                          $id, 
                          $model['phoneContactFIO'],                                
                              [
                               'class' => 'form-control',
                              'style' => 'width:450px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  

            
            [
                'attribute' => 'status',                
                'label'     => 'Верен',
                'format' => 'raw', 
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'phoneStatus';                 
                 $action =  "saveField(".$model['id'].", 'phoneStatus');"; 
                 if ($model['status'] == 2) $style ="background-color:Crimson;";
                 else  $style ="background-color:Green;";

                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Использовать этот счет по умолчанию',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw', 
                'contentOptions' => ['style' => 'width:40px;padding:0px;text-align:center;'],                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'phoneDel';                 
                 $action =  "saveField(".$model['id'].", 'phoneDel');"; 
                 
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  

            
        ],
    ]
);
    $phoneContent .= "<div style='text-align:left; padding:0px;'>";
    $phoneContent .= "<span class='clickable' onclick='addNewPhone(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span></div>";
    
    $bankLabel = 'Банк ';
    if(!empty($model->orgAccount)){
    $bankLabel .= '<b>'.$model->orgBank."</b>";     
    $bankLabel .= ', р/с <b>'.$model->orgAccount."</b>"; 
    $bankLabel .= ', к/с <b>'.$model->orgKS."</b>"; 
    
    $bankLabel .= ', БИК: <b>'.$model->orgBIK."</b>"; 
    }
        
    
    $bankContent= \yii\grid\GridView::widget(
    [
        'dataProvider' => $accProvider,        
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'isDefault',                
                'label'     => 'По умолч.',
                'format' => 'raw',     
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'isDefault';                 
                 $action =  "saveField(".$model['id'].", 'isDefault');"; 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";

                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Использовать этот счет по умолчанию',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => 'orgBank',                
                'label'     => 'Банк',
                'format' => 'raw',             
                'contentOptions' => ['style' => 'width:265px;padding:0px;text-align:center;'],                                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'orgBank';                 
                 $action =  "saveField(".$model['id'].", 'orgBank');"; 
                 return Html::textInput( 
                          $id, 
                          $model['orgBank'],                                
                              [
                               'class' => 'form-control',
                              'style' => 'width:265px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]) ;
               }                
            ],  

            [
                'attribute' => 'orgRS',                
                'label'     => 'Р/С',
                'format' => 'raw',          
                'contentOptions' => ['style' => 'width:165px;padding:0px;text-align:center;'],                                                                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'orgRS';                 
                 $action =  "saveField(".$model['id'].", 'orgRS');"; 
                 return Html::textInput( 
                          $id, 
                          $model['orgRS'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:165px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  

            [
                'attribute' => 'orgKS',                
                'label'     => 'К/С',
                'format' => 'raw',     
                'contentOptions' => ['style' => 'width:265px;padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'orgKS';                 
                 $action =  "saveField(".$model['id'].", 'orgKS');"; 
                 return Html::textInput( 
                          $id, 
                          $model['orgKS'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:165px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  

            [
                'attribute' => 'flgKS',                
                'label'     => "<span class='glyphicon glyphicon-ok-circle' 'title' = 'К/С не требуется'></span>",
                'encodeLabel' => false,
                'format' => 'raw',   
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'flgKS';                 
                 $action =  "saveField(".$model['id'].", 'flgKS');"; 
                 if ($model['flgKS'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'К/С не требуется',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => 'orgBIK',                
                'label'     => 'БИК',
                'format' => 'raw',   
                'contentOptions' => ['style' => 'width:100px;padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'orgBIK';                 
                 $action =  "saveField(".$model['id'].", 'orgBIK');"; 
                 return Html::textInput( 
                          $id, 
                          $model['orgBIK'],                                
                              [
                              'class' => 'form-control',
                              'style' => 'width:100px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  


            [
                'attribute' => 'isActive',                
                'label'     => 'Активен',
                'format' => 'raw',     
                'contentOptions' => ['style' => 'padding:0px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'isActive';                 
                 $action =  "saveField(".$model['id'].", 'isActive');"; 
                 if ($model['isActive'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
//                     'title'   => $title,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  


            
            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:40px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'accountDel';                 
                 $action =  "saveField(".$model['id'].", 'accountDel');";                  
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],              
            
        ],
    ]
);
    $bankContent .= "<div style='text-align:left; padding:0px;'>";
    $bankContent .= "<span class='clickable'  onclick='addNewAccount(".$model->orgId.");'><span class='glyphicon glyphicon-plus'></span></span>";    
    $bankContent .= "</div>";
?>

<?php $form = ActiveForm::begin([
   'options' => ['class' => 'edit-form'],
   'id'=> 'edit-form'
]); ?>   
  
<table border=0 width=100% cellpadding="5px" style="padding:5px">
  <tr>
      <td colspan='2'>Краткое наименование</td>
      <td> <?= $form->field($model, 'title')->textInput(['id'=>'title', 'style' => 'width:500px;' ])->label(false)?>  </td> 
  
      <td>Дата регистрации</td>
      <td> 
      
      
            <?= $form->field($model, 'registartionDate')->widget(DatePicker::classname(), [
            'options' => [],
            'removeButton' => false,
            'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'dd.mm.yyyy'
            ]
            ])->label(false);
            ?>

      </td>    
  </tr>     

  <tr>
      <td colspan='2'>Полное юридическое наименование</td>
      <td> <?= $form->field($model, 'orgFullTitle')->textInput(['id'=>'orgFullTitle', 'style' => 'width:500px;' ])->label(false)?>  </td>  

      <td>Действуещее?</td>
      <td> <?=  $form->field($model, 'isOrgActive')->dropDownList([
            '1' => 'Да',    
            '0' => 'Нет',    
            ])->label(false) ?>  
      </td>    
  </tr>

  <tr> <td colspan='5'>
   <table border=0 width='100%'>

    <tr>
      <td>Email служебный</td>
      <td> <?php
      echo  Html::textInput( 
       'EMail-def', 
       $model->cfgParam['EMail-def'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'EMail-def',   
         'placeholder' => 'Служебны адрес электронной почты',       
         'onchange' => "saveSelfField('EMail-def');",
      ]);          
      ?>  </td>  
      <td>Email Продажи</td>
      <td> <?php 
      echo  Html::textInput( 
       'EMail-SALE', 
       $model->cfgParam['EMail-SALE'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'EMail-SALE',          
         'placeholder' => 'Адрес электронной почты отдела продаж',       
         'onchange' => "saveSelfField('EMail-SALE');",
      ]);          
      ?>  </td>  

      <td>Email доставка</td>
      <td> <?php 
      echo  Html::textInput( 
       'EMail-PURCH', 
       $model->cfgParam['EMail-PURCH'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'EMail-PURCH',          
         'placeholder' => 'Адрес электронной почты отдела доставок',       
         'onchange' => "saveSelfField('EMail-PURCH');",
      ]);          
      ?>  </td>  
      

      
  </tr>
  
    <tr>
      <td>Директор</td>
      <td> <?php 
      echo  Html::textInput( 
       'director', 
       $model->cfgParam['director'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'director',   
         'placeholder' => 'Руководитель организации', 
         'onchange' => "saveSelfField('director');",      
      ]);          
      ?>  </td>  
      <td>Гл. Бухгалтер</td>
      <td> <?php 
      echo  Html::textInput( 
       'buhgalter', 
       $model->cfgParam['buhgalter'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'buhgalter',          
         'placeholder' => 'ФИО главного бухгалтера',     
         'onchange' => "saveSelfField('buhgalter');",        
      ]);          
      ?>  </td>  
      
      <td>Email контроль</td>
      <td> <?php 
      echo  Html::textInput( 
       'EMail-Control', 
       $model->cfgParam['EMail-Control'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'EMail-Control',          
         'placeholder' => 'Адрес электронной почты отдела контроля',   
         'onchange' => "saveSelfField('EMail-Control');",            
      ]);          
      ?>  
      </td>        
  </tr>

      <tr>
      <td>Счет действителен</td>
      <td> <?php 
      echo  Html::textInput( 
       'schetDuration', 
       $model->cfgParam['schetDuration'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'schetDuration',   
         'placeholder' => 'Счет действителен', 
         'onchange' => "saveSelfField('schetDuration');",                  
      ]);          
      ?>  </td>  
      <td>Уловия по счету</td>
      <td> <?php 
      echo  Html::textInput( 
       'schetCondition', 
       $model->cfgParam['schetCondition'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'schetCondition',          
         'placeholder' => 'Уловия по счету',  
         'onchange' => "saveSelfField('schetCondition');",                       
      ]);          
      ?>  </td>  

      <td>Хранение</td>
      <td> <?php 
      echo  Html::textInput( 
       'wareCondition', 
       $model->cfgParam['wareCondition'],                                
      [
         'class' => 'form-control',
         'style' => 'font-size:11px;padding:1px;', 
         'id' => 'wareCondition',          
         'placeholder' => 'Хранение',       
         'onchange' => "saveSelfField('wareCondition');",                       
      ]);          
      ?>  </td>  

      
  </tr>
  
    
  </table>
  <br>
  </td></tr>  
  <tr>
      <td>ИНН</td>
      <td> <?= $form->field($model, 'schetINN')->textInput(['id'=>'schetINN', 'style' => 'width:130px;' ])->label(false) ?>  </td>  
      <td> 
      <?php $readonly=false; if ($model->isSetNDS == 0) {$readonly=true;}  ?>
      <div class='row' >
          <div class='col-sm-2' style='padding-left:30px;padding-top:10px;text-align:right'>НДС</div>
          <div class='col-sm-4' style='text-align:right'><?= $form->field($model, 'NDS')->textInput(['id'=>'NDS', 'readonly'=>$readonly ])->label(false)?></div>
          <div class='col-sm-1' style='text-align:left;padding-top:10px;padding-left:0px;'>%</div>
          <div class='col-sm-5'>
            <?= $form->field($model, 'isSetNDS')->checkbox(['id' => 'isSetNDS', 'label' => 'Установлен', 'onchange' =>'chngSetNDS();'])?>
          </div>
      </div>   
      </td>      
      <td>Директор </td>      
      <td> <?= $form->field($model, 'headFIO')->label(false)?>  </td>      
  </tr>

  <tr>
      <td>КПП</td>
      <td> <?= $form->field($model, 'orgKPP')->textInput(['id'=>'schetINN', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,
        'items' => [       
            [
            'label' => $adressLabel ,
            'content' => $adressContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
     </td>        
  </tr>
  
  <tr>
      <td>ОГРН</td>
      <td> <?= $form->field($model, 'orgOGRN')->textInput(['id'=>'orgOGRN', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' => $phoneLabel ,
            'content' => $phoneContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
     </td>  
  </tr>

  <tr>
      <td>ОКАТО</td>
      <td> <?= $form->field($model, 'orgOKATO')->textInput(['id'=>'orgOKATO', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' => $emailLabel ,
            'content' => $emailContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
     </td>  
      
  </tr>

  
  <tr>
      <td>ОКПО</td>
      <td> <?= $form->field($model, 'orgOKPO')->textInput(['id'=>'orgOKPO', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' => $bankLabel ,
            'content' => $bankContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
      </td>  
      
  </tr>
        
  <tr>
      <td>ОКОНХ</td>
      <td> <?= $form->field($model, 'orgOKONH')->textInput(['id'=>'orgOKONH', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' => $dblGisLabel ,
            'content' => $dblGisContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
      </td>  
      
  </tr>
       
  <tr>
      <td>Категория</td>
      <td> <?= $form->field($model, 'razdel')->textInput(['id'=>'razdel', 'style' => 'width:130px;' ])->label(false)?>  </td>  
      
      <td colspan = 3> 
      <?php
       echo Collapse::widget([
        'encodeLabels' => false,       
        'items' => [
            [
            'label' => $okvedLabel ,
            'content' => $okvedContent,
            'contentOptions' => [],
            'options' => []
            ]
        ]
        ]); 
      ?>
      </td>  
      
  </tr>

       
</table>  
   
<table border=0 width=100% cellpadding="5px" style="padding:5px">
<tr>
<td width='60%'>&nbsp;</td>
<td align='right'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?></td>
</tr></table>
 <br>


  

<?php ActiveForm::end(); ?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/site/save-self-detail']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataVal' )->textInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo "<div align='center'><input type='submit' ></div>";
ActiveForm::end(); 

/*echo "<pre>";
print_r($model->debug);
echo "</pre>";*/
?>


<?php
Modal::begin([
    'id' =>'showSyncProgress',
    //'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Поиск в 1С </h4>',
]);?>
<div style='width:100%; text-align:center;'><img src='img/ajax-loader.gif'></div>
<?php
Modal::end();
?>









    <?php
    /*<div class="part-header"> Сайты</div>   
     Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getUrlListProvider(),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [

            [
                'attribute' => 'url',
                'label'     => 'Сайт',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'url';
                 $action =  "saveField(".$model['id'].", 'url');"; 
                 return Html::textInput( 
                          $id, 
                          $model['url'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:450px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
     
            
            [
                'attribute' => 'isBad',                
                'label'     => 'Верен',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'isBadUrl';                 
                 $action =  "saveField(".$model['id'].", 'isBadUrl');"; 
                 if ($model['isBad'] == 1) $style ="background-color:Crimson;";
                 else  $style ="background-color:Green;";

                 
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Email не верен',
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  

            [
                'attribute' => '',                
                'label'     => '',
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'urlDel';                 
                 $action =  "saveField(".$model['id'].", 'urlDel');"; 
                 
                 $val = \yii\helpers\Html::tag( 'div',"<span class='glyphicon glyphicon-trash'></span>", 
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'удалить',
                   ]);
                return $val;   
               }                
            ],  

   
        ],
    ]
);
<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewUrl(<?= $model->orgId ?>);'>Новый адрес</a>
</div>
Pjax::end(); 
*/?>
<div class='spacer'></div>

<?php /*
<div class="part-header"> &nbsp;</div>   
<div class='spacer'></div>
<table border=0 width=100% cellpadding="5px" style="padding:5px">
<tr>
<td width='60%'>&nbsp;</td>
<td align='right'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?></td>
<td align='right'>&nbsp;<input class="btn btn-primary"  style="width: 150px;" type="button" value="Выйти" onclick="javascript:window.opener.location.reload(false); window.close();"/> <td>
</tr></table>
*/?>
