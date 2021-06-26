<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

use kartik\date\DatePicker;
use kartik\time\TimePicker;



$curUser=Yii::$app->user->identity;
$this->title = 'Контакт с контрагентом';
//$this->params['breadcrumbs'][] = $this->title;
$record=$orgModel->loadOrgRecord();
$phoneList=$orgModel->getCompanyPhones();
$adressList=$orgModel->getCompanyAdress();

?>
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style> 
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.child {
  height:28px;  
  width:100%;
  //padding:5px;
 // text-decoration: underline;  
}
.child:hover {
 color:Blue;
 text-decoration: underline;
 background-color: LightGreen ;
 cursor:pointer;
}
.part-header
{
    padding: 2px;	 
	color: Black;
	text-align: right;    
	background-color: LightBlue ;
	font-size: 11pt;
    font-weight: Bold;
}

</style>

<script type="text/javascript">
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function setPhone(phone, phoneContactFIO)
{     

  if (phoneContactFIO != '')document.forms["Mainform"]["orgcontactform-contactfio"].value=phoneContactFIO;
  document.forms["Mainform"]["orgcontactform-contactphone"].value=phone;
  //document.getElementById("cphone").innerHTML =phone; 
  $('#showContactDialog').modal('hide');     
}

function setEmail(email, emailContactFIO)
{     

  if (emailContactFIO != '')document.forms["Mainform"]["orgcontactform-contactfio"].value=emailContactFIO;
  document.forms["Mainform"]["orgcontactform-contactemail"].value=email;
  //document.getElementById("cphone").innerHTML =phone;   
}


function doCall()
{       
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["Mainform"]["orgcontactform-contactphone"].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100');      
}

function doMail()
{       
  window.open("index.php?r=site/mail&orgId=<?= Html::encode($record->id)?>&email="+document.forms["Mainform"]["orgcontactform-contactemail"].value,'email','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=800,height=600');      
}

function checkRejectNote()
{
    var str = document.forms["Mainform"]["orgcontactform-note"].value;
    str.trim();
   if (str=="") 
   {
       document.forms["Mainform"]["orgcontactform-note"].value="Контакт не состоялся";
   }
    
}
function checkOkNote()
{
    var str = document.forms["Mainform"]["orgcontactform-note"].value;
    str.trim();
   if (str=="Контакт не состоялся") 
   {
       document.forms["Mainform"]["orgcontactform-note"].value="";
   }
    
}

/**********/

function showContactPhoneList(contactFio) {
document.getElementById('frameShowContactDialog').src='index.php?r=site/show-phone-contact&noframe=1&refOrg=<?= $orgModel->orgId ?>&contactFIO='+contactFio;
$('#showContactDialog').modal('show');     
}

/**********/

/*Выносим в отдельный блок все что связано с назначением события*/
function showSelectEventTime() {

var d=document.getElementById('nextContactDate').value;
document.getElementById('frameEventTimeDialog').src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date='+d;
$('#selectEventTimeDialog').modal('show');     
}

function setSelectEventTime(eventTime) {
document.getElementById('nextContactTime').value = eventTime;
//document.getElementById('nextContactTimeShow').innerHTML = eventTime;
$('#selectEventTimeDialog').modal('hide');     
}

/*************/
function switchSetTask()
{
  if (document.getElementById('noTask').value =='0'){
  document.getElementById('noTask').value = '1';
  document.getElementById('btnSetTask').style.background ='Green';
  document.getElementById('btnSetTask').style.color ='White';
  
  document.getElementById('nextContactDate').style.visibility ='hidden';
  document.getElementById('nextContactTime').style.visibility ='hidden';  
  
  }else{
  document.getElementById('noTask').value ='0';
  document.getElementById('btnSetTask').style.background ='White';
  document.getElementById('btnSetTask').style.color ='Black';

  document.getElementById('nextContactDate').style.visibility ='visible';
  document.getElementById('nextContactTime').style.visibility ='visible';  
   
  
  }

}

function submitMainForm ()
{
   
 if (document.getElementById('noTask').value =='0')
 {
    if (document.getElementById('nextContactDate').value =='')
    {
        alert ("Дата следующего контакта должны быть заполнены");
        return;
    }
    
    if (document.getElementById('nextContactTime').value =='')
    {
        alert ("Дата и время следующего контакта должны быть заполнены");
        return;
    }

    if (document.getElementById('nextContactTime').value =='-')
    {
        alert ("Дата и время следующего контакта должны быть заполнены");
        return;
    }
  }
    document.getElementById('Mainform').submit();        
}

function linkToDeal(id)
{
    //document.getElementById('dataVal').value=val;    
    //var data = $('#saveDataForm').serialize();
    var url = 'index.php?r=site/link-contact-zakaz&contactId=<?=$model->contactId ?>&zakazId='+id;
    $(document.body).css({'cursor' : 'wait'});   
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        //data: data,
        success: function(res){     
            console.log(res);
            $(document.body).css({'cursor' : 'default'});            
            document.location.reload(true); 
            
        },
        error: function(){
            alert('Error while sync data!');
            $(document.body).css({'cursor' : 'default'});                        
            console.log(url);
        }
    });	
}

</script>
<h3>
  <?= Html::encode($this->title) ?>:      
  <strong><a href="index.php?r=site/org-detail&orgId=<?= Html::encode($record->id)?>"><?= Html::encode($record->title)?></a></strong>
</h3>

<div class="part-header"> Данные по контакту</div>
<div class="spacer"> </div>
 <?php $form = ActiveForm::begin(['id' => 'Mainform',]); ?>  

<div class ='row'>
   <div class ='col-md-5'>   

 <table border=0 style="border:0px" width=100%>
  <tr>        
     <td colspan='2'>   
     <?= $form->field($model, 'contactFIO')->label('Контактное лицо')?>     
     </td>
  </tr>
  <tr>        
     <td><?= $form->field($model, 'contactPhone')->label(false)?> </td>
     <td width="50px"> <div style="position:relative; top:-7px; left:10px;"> &nbsp;<button class="btn btn-primary" type="button" onclick="javascript:doCall();"><span class="glyphicon glyphicon-phone-alt"></span></button> </div></td>
  </tr>
  
  <tr>
     <td><?= $form->field($model, 'contactEmail')->label(false)?></td>
     <td  width="50px"><div style="position:relative; top:-7px; left:10px;">&nbsp;<button class="btn btn-primary"  type="button"  onclick="javascript:doMail();"><span class="glyphicon glyphicon-envelope"></button></div></td>
  </tr>
</table>      
  
 <table border=0 style="border:0px" width=100%> 
  <tr>        
    <td><?= $form->field($model, 'nextContactDate')->textInput([/*'class' => 'tcal',*/ 'style'=>'width:150px;', 'type' => 'date', 'id' =>'nextContactDate',
        'onchange' => 'showSelectEventTime()'  ])->label(false)?>
    </td>  
    <td><?= $form->field($model, 'nextContactTime')->textInput(['id' => 'nextContactTime', 'type' => 'time'])->label(false) ?></td>
    <td><div type='button' id='btnSetTask' class='btn btn-default' style='position:relative;left:10px;top:-8px;' onClick='switchSetTask()'>Не назначать </div></td>    
  </tr>
 </table>
        <?= $form->field($model, 'note')->textarea(['rows' => 4, 'cols' => 25])->label('Комментарий')?>
<!------ 
 <table border=0 style="border:0px" width=100%> 
  <tr>        
    <td><?= $form->field($model, 'status')->radio(['label' => 'Информация получена', 'value' => 1, 'onclick' => 'checkOkNote();', 'uncheck' => null]) ?>
    </td>  
    <td><?= $form->field($model, 'status')->radio(['label' => 'Звонок не состоялся', 'value' => 2, 'onclick' => 'checkRejectNote();', 'uncheck' => null]) ?>          </td>  
  </tr>
 </table>
  --->          
</div>
   
<div class ='col-md-7'>   
<div style='height:350px;'>
<?php Pjax::begin(); ?>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $phoneProvider,
          'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],          
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
            [
               'attribute' => 'phoneContactFIO',
                'label' => 'Контактное лицо',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                if (!empty ($model['phoneContactFIO'])){
                    return 
                    "<a href='#' onclick='javascript:showContactPhoneList(\"".Html::encode($model['phoneContactFIO'])."\")'>".
                    Html::encode($model['phoneContactFIO'])."</a>";                    
                    }
                return "&nbsp;";
                }                
            ],
          
             [
                'attribute' => 'phone',
                'label'     => 'Телефон',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                if (!empty ($model['phone'])){
                    return 
                    "<a href='#' onclick='javascript:setPhone(\"".Html::encode($model['phone'])."\",\"".Html::encode(trim($model["phoneContactFIO"]))."\");'>".Html::encode($model['phone'])."</a>";                    
                    }
                return "&nbsp;";
                    }
                    
            ],
            
            [
               'attribute' => 'lastD',
                'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.Y'],
            ],
          
           /* [
               'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 return 
                    "<a href='#' onclick='javascript:editPhone(\"".Html::encode($model['id'])."\">"."</a>";                    
                  }
            ],*/
           

        ],
    ]
);
?>
<?php Pjax::end(); ?>
</div>

  <div style='text-align:right; verical-align:bottom; padding-bottom:5px;'>
    <a class='btn btn-primary' href="#" onclick="javascript: submitMainForm();" style ='background-color: ForestGreen;'> Сохранить </a>
  </div>

 </div>



</div>


<!--- Контакт старт--->   

<?php
/********** Диалог с выбором времени *****************/
Modal::begin([
    'id' =>'selectEventTimeDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='620px' frameborder='no' id='frameEventTimeDialog'  src='index.php?r=site/select-event-time&noframe=1&userid=<?= $curUser->id ?>&date=<?= $model->nextContactDate ?>' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>       
</div><?php
Modal::end();
/***************************/

/********** Диалог с показом *****************/
Modal::begin([
    'id' =>'showContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',], 
]);?><div style='width:650px'>
    <iframe width='550px' height='520px' frameborder='no' id='frameShowContactDialog'  src='index.php?r=site/show-phone-contact&noframe=1&contactFIO=&refOrg=' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>       
</div><?php
Modal::end();
/***************************/
?>
     
<!--- Контакт финиш--->  
    


<!--- ******************************************* --->  

<div class="part-header"> Список активных сделок</div>   

<div class='row'>

    <div class='col-md-2'>

     <div style='padding:10px;'>
    <div class='btn btn-primary' style='background-color:DarkGreen; width:150px;' onclick="document.location.href='index.php?r=market/market-zakaz-create&noframe=1&id=<?= Html::encode($record->id)?>'" >Новая сделка</div>

    </div>
    </div>
    <div class='col-md-2'></div>
    <div class='col-md-8'>
    <?php Pjax::begin(); ?>
    <?php
    $currentZakaz = $model->zakazId;
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $activityProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
                    [
                'attribute' => '',
                'label'     => 'Связать',
                'contentOptions' =>['style'=>'padding:2px;text-align:center; width:50px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($currentZakaz) {

                 $id = 'zakaz'.$model['zakazId'];
                 $action="linkToDeal('".$model['zakazId']."')";

                   if ($model['zakazId'] == $currentZakaz){
                    $style='background:Green;color:White;';
                   }else
                   {
                    $style='background:White;color:White;';
                   }

                   return \yii\helpers\Html::tag( 'div', '&nbsp;',
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => 'Связанный контакт',
                   ]);
                },
            ],

            [
                'attribute' => 'zakazId',
                'label'     => 'Заказ',
                'format' => 'raw',            
                'contentOptions' =>['style'=>'padding:0px;width:170px; '],
                
                'value' => function ($model, $key, $index, $column) {                    

                $action=" onclick=\"openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['zakazId']."','zakazWin');\"";
                return "<div class='child' ".$action." >".$model['zakazId']." от ". date("d.m.y", strtotime($model['formDate']))."</div>";                                           
                },
            ],            

            [
                'attribute' => 'schetId',
                'label'     => 'Счет',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;width:17  0px; '],
                'value' => function ($model, $key, $index, $column) {                                    
            
                $list = Yii::$app->db->createCommand(
                'SELECT id, schetNum, schetDate from {{%schet}} where refZakaz=:refZakaz ', 
                    [':refZakaz' => $model['zakazId'] ])->queryAll();
            
               $v="";
                for ($i=0;$i<count($list);$i++)
                {
                $action="openWin('market/market-schet&id=".$list[$i]['id']."','schetWin')";
                $v.= \yii\helpers\Html::tag( 'div', $list[$i]['schetNum']." ".$list[$i]['schetDate'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                
                }
            
               return $v;
            /*     
                 $action=" onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');\"";
                 
                 if (empty($model['schetId'])) return "&nbsp;";                 
                 if (empty($model['ref1C'])) $ret= "<div class='child' ".$action." style='background-color:Yellow'>";
                                        else $ret= "<div class='child' ".$action." style='background-color:LightGreen'>";
                 
                 $ret.= $model['schetNum']."&nbsp;от&nbsp;". date("d.m.y", strtotime($model['schetDate']));                                           
                 $ret.="</div>";
                 return $ret;
*/                 
                },
            ],            
    
    
                [
                'attribute' => 'Оплата',
                'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}},{{%schet}}
                 WHERE {{%oplata}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                [':refZakaz' => $model['zakazId'] ])->queryAll();
                 
                 //return $model['schetId'];
                 if (count($listData)==0) return "&nbsp;";                 
                 if($listData[0]['sumOplata'] == 0)return "&nbsp;";                 
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div  style='padding:5px;background-color:LightGreen'>"; 
                                                            else $ret= "<div  style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));                                                              
                  $ret.="<br>&nbsp;</div>";                  
                 return $ret;                  
                },
            ],            
            
            [
                'attribute' => 'lastSupply',
                'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}},{{%schet}} where 
                {{%supply}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                 [':refZakaz' => $model['zakazId'] ])->queryAll();

                if (count($listData)==0) return "&nbsp;";                 
                if($listData[0]['sumSupply'] == 0)return "&nbsp;";                 

                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div style='padding:5px;background-color:LightGreen'>"; 
                                                                 else $ret= "<div style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));                                                              
                  $ret.="<br>&nbsp;</div>";
                 return $ret;                  
                },
            ],            



            /****/
        ],
    ]
    );
    ?>

    <?php Pjax::end(); ?>
    

    
    <?php Pjax::begin(); ?>
    <?php
    $currentPurchRef = $model->purchaseRef;
    echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgModel->getZakupkiForOrgProvider(Yii::$app->request->get()),
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
                    [
                'attribute' => '',
                'label'     => 'Связать',
                'contentOptions' =>['style'=>'padding:2px;text-align:center; width:50px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use ($currentPurchRef) {

                 $id = 'purchaseRef'.$model['purchaseRef'];
                 $action="linkToPurch('".$model['purchaseRef']."')";

                   if ($model['purchaseRef'] == $currentPurchRef){
                    $style='background:Green;color:White;';
                   }else
                   {
                    $style='background:White;color:White;';
                   }

                   return \yii\helpers\Html::tag( 'div', '&nbsp;',
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,
                     'title'   => 'Связанный контакт',
                   ]);
                },
            ],

            [
                'attribute' => 'currentPurchRef',
                'label'     => 'Закупка',
                'format' => 'raw',            
                'contentOptions' =>['style'=>'padding:0px;width:170px; '],
                
                'value' => function ($model, $key, $index, $column) {                    
                 $action="";
               // $action=" onclick=\"openWin('market/market-zakaz&orgId=".$model['refOrg']."&zakazId=".$model['zakazId']."','zakazWin');\"";
                return "<div class='child' ".$action." >".$model['purchaseRef']."  </div>";                                           
                //". date("d.m.y", strtotime($model['purchDate']))."
                },
            ],            

          /*  [
                'attribute' => 'schetId',
                'label'     => 'Счет',
                'format' => 'raw',                            
                'contentOptions' =>['style'=>'padding:0px;width:17  0px; '],
                'value' => function ($model, $key, $index, $column) {                                    
            
                $list = Yii::$app->db->createCommand(
                'SELECT id, schetNum, schetDate from {{%schet}} where refZakaz=:refZakaz ', 
                    [':refZakaz' => $model['zakazId'] ])->queryAll();
            
               $v="";
                for ($i=0;$i<count($list);$i++)
                {
                $action="openWin('market/market-schet&id=".$list[$i]['id']."','schetWin')";
                $v.= \yii\helpers\Html::tag( 'div', $list[$i]['schetNum']." ".$list[$i]['schetDate'],
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                   ]);
                
                }
            
               return $v;*/
            /*     
                 $action=" onclick=\"openWin('market/market-schet&id=".$model['schetId']."','schetWin');\"";
                 
                 if (empty($model['schetId'])) return "&nbsp;";                 
                 if (empty($model['ref1C'])) $ret= "<div class='child' ".$action." style='background-color:Yellow'>";
                                        else $ret= "<div class='child' ".$action." style='background-color:LightGreen'>";
                 
                 $ret.= $model['schetNum']."&nbsp;от&nbsp;". date("d.m.y", strtotime($model['schetDate']));                                           
                 $ret.="</div>";
                 return $ret;
*/                 
          /*      },
            ],            
    
    
                [
                'attribute' => 'Оплата',
                'label'     => 'Оплата',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    
                
                 $listData= Yii::$app->db->createCommand(
                'SELECT sum(oplateSumm) as sumOplata, max(oplateDate) as lastOplate from {{%oplata}},{{%schet}}
                 WHERE {{%oplata}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                [':refZakaz' => $model['zakazId'] ])->queryAll();
                 
                 //return $model['schetId'];
                 if (count($listData)==0) return "&nbsp;";                 
                 if($listData[0]['sumOplata'] == 0)return "&nbsp;";                 
                 if($listData[0]['sumOplata']+10 > $model['schetSumm'])$ret= "<div  style='padding:5px;background-color:LightGreen'>"; 
                                                            else $ret= "<div  style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumOplata'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastOplate']));                                                              
                  $ret.="<br>&nbsp;</div>";                  
                 return $ret;                  
                },
            ],            
            
            [
                'attribute' => 'lastSupply',
                'label'     => 'Поставка',
                'contentOptions' =>['style'=>'padding:0px;'],
                'format' => 'raw',                            
                'value' => function ($model, $key, $index, $column) {                    

                $listData= Yii::$app->db->createCommand(
                'SELECT sum(supplySumm) as sumSupply, max(supplyDate) as lastSupply from {{%supply}},{{%schet}} where 
                {{%supply}}.refSchet = {{%schet}}.id and {{%schet}}.refZakaz=:refZakaz', 
                 [':refZakaz' => $model['zakazId'] ])->queryAll();

                if (count($listData)==0) return "&nbsp;";                 
                if($listData[0]['sumSupply'] == 0)return "&nbsp;";                 

                if($listData[0]['sumSupply']+10 > $model['schetSumm'])$ret= "<div style='padding:5px;background-color:LightGreen'>"; 
                                                                 else $ret= "<div style='padding:5px;background-color:Yellow'>";
                                                            
                  $ret.=number_format($listData[0]['sumSupply'],2,'.','&nbsp;')." от ". date("d.m.Y", strtotime($listData[0]['lastSupply']));                                                              
                  $ret.="<br>&nbsp;</div>";
                 return $ret;                  
                },
            ],            

*/

            /****/
        ],
    ]
    );
    ?>

    <?php Pjax::end(); ?>
        
    </div>
</div>

<?= $form->field($model, 'purchaseRef')->hiddenInput()->label(false)?>
<?= $form->field($model, 'orgId')->hiddenInput()->label(false)?>
<?= $form->field($model, 'zakazId')->hiddenInput(['id'=> 'zakazId' ])->label(false)?>
<?= $form->field($model, 'atsRef')->hiddenInput(['id' => 'atsRef', ])->label(false)?>
<?= $form->field($model, 'noTask')->hiddenInput(['id'=> 'noTask' ])->label(false)?>
<?= $form->field($model, 'contactId')->hiddenInput()->label(false)?>    
   <?php ActiveForm::end(); ?>
  
  
<div class="part-header"> Предыдущие Контакты </div>   
  
<?php Pjax::begin(); ?>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $contactProvider,
          'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],          
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            [
                'attribute' => 'grd_contactDate',
                    'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.y H:i'],
            ],
          
            'grd_contactFIO:raw:Контактное лицо',
            
              [
                'attribute' => 'grd_phone',
                    'label'     => 'Телефон/почта',                
                'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                    
                                        
                                        
                    if (!empty ($model['grd_phone'])){
                    return 
                    "<a href='#' onclick='javascript:setPhone(\"".Html::encode($model['grd_phone'])."\",\"".Html::encode(trim($model["grd_contactFIO"]))."\");'>".Html::encode($model['grd_phone'])."</a>";
                    
                    }
                    if (!empty ($model['contactEmail'])){return 
                    "<a href='#' onclick='javascript:setEmail(\"".Html::encode($model['contactEmail'])."\",\"".Html::encode(trim($model["grd_contactFIO"]))."\");'>".Html::encode($model['contactEmail'])."</a>";
                    }
                    
                return "&nbsp;";
                    }
                    
            ],

               [
                'attribute' => 'note',
                    'label'     => 'Комментарий',                
                'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                         return mb_substr($model['grd_note'],0,260);
                    }
                    
            ],

               'grd_userFIO:raw:Менеджер',
        ],
    ]
);
?>
<?php Pjax::end(); ?>
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Отменить" onclick="javascript:history.back();"/>





