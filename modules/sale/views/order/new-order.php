<?php
/* view форма нового заказа */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\grid\GridView;
use yii\bootstrap\Alert;


$this->title = 'Форма заказа';

$model->loadOrder();

?>
<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
</style>
 <script>

function setOrg(res)
{  
  console.log(res);
  if(res.N == 0) return;
  var orgData = res.orgData[0];

  if(orgData.zakazId > 0){
      document.location.href = 'index.php?r=sale/order/new-order&id='+orgData.zakazId;  
      return;
  }
    console.log(orgData);
    //alert (orgData.orgRef);
    $('#orgRef').val(orgData.orgRef);
    $('#orgTitle').val(orgData.orgTitle);
    $('#orgInn').val(orgData.orgInn);
    $('#orgKpp').val(orgData.orgKpp);
    $('#orgPhone').val(orgData.orgPhone);
    $('#contactFIO').val(orgData.contactFIO);
    $('#orgAdress').val(orgData.orgAdress);
    
    document.getElementById('selectGood').style.display='Block';

    
}

function initLoad()
{
    alert("init");
    var email = $('#email').val();
    if (email != "") searchByEmail();
}
/** Поиск организации по почте */
function searchByEmail()
{
  var email = $('#email').val();
  var URL = 'index.php?r=/sale/order/get-org&email='+email;
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
           setOrg(res); 
        },
        error: function(){
            alert('Error while retrieve data!');
        }
    });	
}

/** Поиск организации по id */
function searchByOrgId(orgId)
{
  var URL = 'index.php?r=/sale/order/get-org&orgId='+orgId;
  console.log(URL); 
    $.ajax({
        url: URL,
        type: 'GET',
        dataType: 'json',
        success: function(res){     
           setOrg(res); 
        },
        error: function(){
            alert('Error while retrieve data!');
        }
    });	
}

/* Сохранение параметров */
function saveData()
{
   $(document.body).css({'cursor' : 'wait'});
    var data = $('#Mainform').serialize();
    $.ajax({
        url: 'index.php?r=sale/order/save-order-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            $(document.body).css({'cursor' : 'default'});
            console.log(res);
            if (res.isReload){
                document.location.href = 'index.php?r=sale/order/new-order&id='+res.id;
                }
            if (res.isSwitch){
            showSwitch(res);
            }
        },
        error: function(){
            $(document.body).css({'cursor' : 'default'});
            alert('Error while saving data!');
        }
    });	
}


function switchValue(id, type)
{      
      $('#dataId').val(id);  
      $('#dataType').val('wareInOrder');  
      saveData();
}

function saveField(id, type)
{
   var idx = '#'+type+id;
   var val = $(idx).val();

      $('#dataVal').val(val);
      $('#dataId').val(id);
      $('#dataType').val(type);

    saveData();
}

function showSwitch(res)
{
   var idx = res.dataType+res.dataId;

    if (res.val == 0)
        document.getElementById(idx).style.background='White';
    else
        document.getElementById(idx).style.background='Blue';

}



function finishOrder()
{
    var email = $('#email').val();
    var zakazId = $('#id').val();
    if (zakazId > 0 )
        document.location.href = 'index.php?r=sale/order/get-order&id='+zakazId+'&email='+email;
    else
        alert ("Заказ должен бытьь сформирован!");
}

 </script>  

 <?php $form = ActiveForm::begin(['id' => 'Mainform','action' => 'index.php?r=sale/order/save-order-detail']); ?>

 <table border='0' width='100%'><tr>
 <td><?= $form->field($model, 'email')->textInput([
        'id'=>'email',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Электронная почта',
        'onChange'    => 'searchByEmail()'
        ])->label('Электронная почта')
  ?></td>
  <td width='50px;'>
  <?php
  echo  \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-refresh'></span>",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'refreshInfo',
                     'onclick' => 'searchByEmail()',
                     'title'   => 'Загрузить информацию по электронной почте',
                     'style'   => 'color:White;margin-top:7px; margin-left:5px; width:40px;'
                   ]);

  ?>
  </td>
  </tr></table>
 
 <div onload="initLoad();">
 <p><b>Данные заказчика</b></p>
 <table class='table table-striped'>
     <tr>
         <td> Название </td>
         <td colspan='3'> <?= $form->field($model, 'orgTitle')->textInput([
        'id'=>'orgTitle',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Юридическое наименование клиента (полная или краткая форма))',
        ])->label(false)
         ?></td>
     </tr>
 
     <tr>
         <td> ИНН </td>
         <td> <?= $form->field($model, 'orgInn')->textInput([
        'id'=>'orgInn',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'ИНН',
        ])->label(false)
         ?></td>

         <td> КПП </td>
         <td> <?= $form->field($model, 'orgKpp')->textInput([
        'id'=>'orgKpp',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'КПП',
        ])->label(false)
         ?></td>
               
     </tr>
   
     <tr>

         <td> Телефон </td>
         <td> <?= $form->field($model, 'orgPhone')->textInput([
        'id'=>'orgPhone',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Телефон  для связи',
        ])->label(false)
         ?></td>

         <td> Контактное лицо</td>
         <td> <?= $form->field($model, 'contactFIO')->textInput([
        'id'=>'contactFIO',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Контактное лицо ответственное за заказ',
        ])->label(false)
         ?></td>               
     </tr>
 

     <tr>
         <td> Юридический адрес </td>
         <td colspan='3'> <?= $form->field($model, 'orgAdress')->textInput([
        'id'=>'orgAdress',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Юридический адрес',
        ])->label(false)
         ?></td>
     </tr>
 
 
      
 </table>
 </div>  
<?php
  $style='display:block';
  if (empty($model -> orgRef)) $style='display:none';
?>
 <div id='selectGood' style='<?= $style ?>' >

<?php
$zakazId = $model -> id;
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'showFooter' => false,
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-condensed'
        ],
        
                            
        'columns' => [                        
           [
                'attribute' => 'id',
                'label' => '',
                'format' => 'raw',
                'contentOptions' => ['width' => '50px'],
                'value' => function ($model, $key, $index, $column) use($zakazId) {    
               $id = 'wareInOrder'.$model['id'];
               $action = "switchValue(".$model['id'].", 'wareInOrder');" ;                  
               
               $active = Yii::$app->db->createCommand("Select ifnull(isActive,0) FROM {{%zakazContent}}
               Where refZakaz = :refZakaz AND wareNameRef = :wareNameRef ", 
               [':refZakaz' => $zakazId, ':wareNameRef' => $model['id']])->queryScalar();  
               if (empty($active))  $style='background:White;';    
                              else  $style='background:Blue;';    
               
               return \yii\helpers\Html::tag( 'div', "",
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Добавить в заказ',
                     'style'   => $style,
                   ]);
                                    
                },
            ],        
            [
                'attribute' => 'wareTitle',
                'label' => 'Товар/услуги',
                'format' => 'raw',
            ],        

            [
                'attribute' => '',
                'label'     => 'Количество',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:0px;width:100px;'],
                'value' => function ($model, $key, $index, $column)  use($zakazId) {
                    $id = "wareCount".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareCount');"; 

                    $val = Yii::$app->db->createCommand("Select ifnull(count,0) FROM {{%zakazContent}}
                    Where refZakaz = :refZakaz AND wareNameRef = :wareNameRef ",
                    [':refZakaz' => $zakazId, ':wareNameRef' => $model['id']])->queryScalar();
                    if (empty($val))  $val = "";


                     return Html::textInput( 
                          $id, 
                          $val,
                              [
                              'class' => 'form-control',
                              'style' => 'width:100pxpx; font-size:11px;padding:1px;', 
                              'id' => $id, 
                              'onchange' => $action,
                              ]);
                },
            ],             
                            
            [
                'attribute' => 'wareEd',
                'label'     => 'Ед. изм.',                
            ],

           [
                'attribute' => 'v1',
                'label' => 'Цена без скидки',
                'format' => 'raw',
                'contentOptions' => ['width' => '50px'],
                'value' => function ($model, $key, $index, $column) {                        
                    return number_format($model['v1'],'2','.','&nbsp;');
                },
            ],        
            
                        
        ],
    ]
);
?>

  <?php
  if (!empty($model -> id))
  echo  \yii\helpers\Html::tag( 'div', "Завершить и сформировать коммерческое предложение",
                   [
                     'class'   => 'btn btn-primary',
                     'id'      => 'finishOrderBtn',
                     'onclick' => 'finishOrder()',
                     'title'   => 'Загрузить информацию по электронной почте',
                     'style'   => 'color:White;'
                   ]);

  ?>


</div >

   <?= $form->field($model, 'id')->hiddenInput(['id' => 'id'])->label(false)?>
   <?= $form->field($model, 'orgRef')->hiddenInput(['id' => 'orgRef'])->label(false)?>

<?php   
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>


