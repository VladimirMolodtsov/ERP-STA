<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;

$curUser=Yii::$app->user->identity;
$this->title = 'Карточка контрагента - редактирование';
//$this->params['breadcrumbs'][] = $this->title;
$record=$model->loadOrgRecord();
$phoneList=$model->getCompanyPhones();
$adressList=$model->getCompanyAdress();

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/site/org-card.js');

$this->registerCssFile('@web/phone.css');

?>
  <h2><?= Html::encode($this->title) ?></h2>
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
</style>

<script type="text/javascript">

function chngSetNDS()
{
  val =document.getElementById('isSetNDS').checked; 
  if (val == true) document.getElementById('NDS').readOnly = false;
      else document.getElementById('NDS').readOnly=true;
}

</script>

<?php $form = ActiveForm::begin([
   'options' => ['class' => 'edit-form'],
   'id'=> 'edit-form'
]); ?>  


<?php
if (!empty($model->leadId)){ 
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Текст лида:",
            'content' => "<pre>".$model->getLeadText()."<pre/>",
            'contentOptions' => ['class' => 'in'],
            'options' => []
        ]
    ]
]); 
}
?> 



<?php
$query = preg_replace("/\s+/","+",$model->title);

if ($mode==1){$in ='in';}
         else{$in ='';}
$content ="";  
$content .= "<div id='ZCB_check' style='height: 450px; width: 950px; '>
         <iframe id='ZCB_iframe'        height='420px' width='900px' frameborder='no' src='https://zachestnyibiznes.ru/search?query=".$query."' seamless>
         Ваш браузер не поддерживает плавающие фреймы!</iframe><br>     
 </div>";
 
 echo Collapse::widget([
    'items' => [
        [
            'label' => "Проверка в ЗЧБ:",
            'content' => $content,
            'contentOptions' => ['class' => ''.$in],
            'options' => []
        ]
    ]
]); 

?> 
 

 
  
  <table border=0 width=100% cellpadding="5px" style="padding:5px">
  <?php /*<tr>
  <td align='right' style="padding-right: 10px;"> Категория</td> 
  <td align='left'> <?=  $form->field($model, 'orgTypeRef')->dropDownList($model->getOrgTypeList())->label(false)?>  </td>    
  <td style="padding-left: 10px;">  </td>
  <td style="padding-left: 10px;">  </td>  
 </tr> */?>
 
  <tr>
  <td style="padding-left: 10px;"> <?= $form->field($model, 'title')->label('Наименование компании')?>  </td> 
  <td style="padding-left: 10px;"> <?= $form->field($model, 'schetINN')->label('ИНН')?>  </td>
  <td style="padding-left: 10px;"> <?= $form->field($model, 'orgKPP')->label('КПП')?>  </td>
  <td style="padding-left: 10px;"> <?=  $form->field($model, 'isOrgActive')->dropDownList([
    '1' => 'Да',    
    '0' => 'Нет',    
    ])->label('Действуещее?')?>  </td>    
  </tr>
 
  <tr>
  <td style="padding-left: 10px;" colspan='2' > <?= $form->field($model, 'orgFullTitle')->label('Юр. наименование (полное)')?>  </td> 
  <td style="padding-left: 10px;"> <?= $form->field($model, 'registartionDate')->label('Дата регистрации')?>  </td>
  <td style="padding-left: 10px;"> <?= $form->field($model, 'headFIO')->label('Директор')?>  </td>      
  </tr>
  
  <tr>
  <td >
   <?php
    $readonly=false;
    if ($model->isSetNDS == 0) {$readonly=true;} 
   ?>
  <div class='row' >
  <div class='col-sm-2' style='padding-left:30px;padding-top:10px;'>НДС</div>
  <div class='col-sm-4'><?= $form->field($model, 'NDS')->textInput(['id'=>'NDS', 'readonly'=>$readonly ])->label(false)?></div>
  <div class='col-sm-6'>
    <?= $form->field($model, 'isSetNDS')->checkbox(['id' => 'isSetNDS','label' => 'Установлен', 'onchange' =>'chngSetNDS();'])?></div>
  </div>   
   </td> 
  <td> 
  <div class='btn btn-default' onclick="openWin('site/single-org-deals&orgId=<?= $model->orgId ?>','dealWin')">Взаимодействие с предприятием</div>
  </td> 
  </tr>

 </table>  
  
<?php $content = ' 
<div class="item-header">Текущий адрес:</div>     
  <table border=0 width=100% cellpadding="5px" style="padding:5px">
  <tr>
  <td >'.$form->field($model, 'adressArea')->label('Область'). '</td> 
  <td >'. $form->field($model, 'adressCity')->label('Город').'</td> 
  <td >'. $form->field($model, 'adressDistrict')->label('Район').'</td>  
  </tr><tr>
  <td >'.$form->field($model, 'index')->label('Индекс').'</td> 
  <td  colspan="2">'.$form->field($model, 'adress')->label('Адрес').'</td></tr>
  <tr>
  <td >'.$form->field($model, 'contactPhone')->label('Контактный телефон').'</td>   
  <td colspan="3" >'.$form->field($model, 'contactFIO')->label('Контактное лицо').'</td> 
  </tr><tr>
  <td >'.$form->field($model, 'contactEmail')->label('Электронная почта').'</td> 
  <td colspan="3" >'.$form->field($model, 'contactURL')->label('Сайт').'</td></tr>
  </table>'.$form->field($model, 'adressId')->hiddenInput()->label(false);
  
  echo Collapse::widget([
    'items' => [
        [
            'label' => "Контакты по умолчанию:",
            'content' => $content,
            'contentOptions' => ['class' => ''.$in],
            'options' => []
        ]
    ]
]); 
 
 ?>
 
<table border=0 width=100% cellpadding="5px" style="padding:5px">
<tr>
<td width='60%'>&nbsp;</td>
<td align='right'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?></td>
<td align='right'>&nbsp;<input class="btn btn-primary"  style="width: 150px;" type="button" value="Выйти" onclick="javascript:window.close();"/> <td>
</tr></table>
 <br>


<div class="part-header"> Список банковских счетов </div>   

<?php
Pjax::begin(['id' => 'rsListPjax']);
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $accProvider,        
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],


            [
                'attribute' => 'orgBank',                
                'label'     => 'Банк',
                'format' => 'raw',                            
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
                'attribute' => 'isDefault',                
                'label'     => 'По умолч.',
                'format' => 'raw',                            
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
            
        ],
    ]
);
?>
<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewAccount(<?= $model->orgId ?>);'>Новый счет</a>
</div>
<div class='spacer'>
</div>
<?php
Pjax::end(); 
?>

  
<div class="part-header"> Список известных телефонов</div>   

<?php
Pjax::begin(['id' => 'phoneListPjax']);
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getPhoneListProvider(),        
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

            [
                'attribute' => 'phone',                
                'label'     => 'Телефон',
                'format' => 'raw',                            
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
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'phoneContactFIO';                 
                 $action =  "saveField(".$model['id'].", 'phoneContactFIO');"; 
                 return Html::textInput( 
                          $id, 
                          $model['phoneContactFIO'],                                
                              [
                               'class' => 'form-control',
                              'style' => 'width:350px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  

     
            
            [
                'attribute' => 'status',                
                'label'     => 'Верен',
                'format' => 'raw',                            
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
?>
<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewPhone(<?= $model->orgId ?>);'>Новый телефон</a>
</div>

<?php
Pjax::end(); 
?>

<div class='spacer'></div>
  
<div class="part-header"> Список известных адресов</div>   
<?php
Pjax::begin();
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $adressProvider,
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [
            
            [
                'attribute' => 'area',
                'label'     => 'Область',
                'format' => 'raw',                            
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
                'attribute' => 'adress',
                'label'     => 'Полный адрес',
                'format' => 'raw',                            
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
            
  /*          [
                'attribute' => 'Координаты',
                'label' => 'Координаты',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    return $model['x']." ".$model['y'];
                },
            ], */       

             [
                'attribute' => 'isBad',                
                'label'     => 'Верен',
                'format' => 'raw',                            
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

?>

<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewAdress(<?= $model->orgId ?>);'>Новый адрес</a>
</div>
<?php
Pjax::end(); 
?>
<div class='spacer'></div>
  

<div class="part-header"> Список E-mail адресов</div>   
    <?php
     Pjax::begin();
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $model->getEmailListProvider(),
        //'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],        
        'columns' => [

                        
            [
                'attribute' => 'email',
                'label'     => 'Адрес',
                'format' => 'raw',                            
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
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                 
                 
                 $id = $model['id'].'emailContactFIO';
                 $action =  "saveField(".$model['id'].", 'emailContactFIO');"; 
                 return Html::textInput( 
                          $id, 
                          $model['emailContactFIO'],
                              [
                               'class' => 'form-control',
                              'style' => 'width:350px; font-size:11px;padding:1px;'.$style, 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
               }                
            ],  
     
            
            [
                'attribute' => 'status',                
                'label'     => 'Верен',
                'format' => 'raw',                            
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
?>
<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewEmail(<?= $model->orgId ?>);'>Новый адрес</a>
</div>
<?php
Pjax::end(); 
?>
<div class='spacer'></div>

<div class="part-header"> Сайты</div>   
    <?php
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
?>
<div style='text-align:right; padding:0px;'>
<a href="#" class='btn btn-primary' onclick='addNewUrl(<?= $model->orgId ?>);'>Новый адрес</a>
</div>
<?php
Pjax::end(); 
?>
<div class='spacer'></div>

<div class="part-header"> &nbsp;</div>   
<div class='spacer'></div>
<table border=0 width=100% cellpadding="5px" style="padding:5px">
<tr>
<td width='60%'>&nbsp;</td>
<td align='right'><?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'style' => 'width:150px; background-color: DarkGreen ;']) ?></td>
<td align='right'>&nbsp;<input class="btn btn-primary"  style="width: 150px;" type="button" value="Выйти" onclick="javascript:window.opener.location.reload(false); window.close();"/> <td>
</tr></table>
<?php ActiveForm::end(); ?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=/site/save-detail']);
echo $form->field($model, 'dataRequestId' )->hiddenInput(['id' => 'dataRequestId' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
//echo "<div align='center'><input type='submit' ></div>";
ActiveForm::end(); 
/*echo "<pre>";
print_r($model->debug);
echo "</pre>";*/
?>

