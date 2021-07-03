<?php
/* view форма нового заказа */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\grid\GridView;
use yii\bootstrap\Alert;


$this->title = 'Форма заказа';

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
    console.log(orgData);
    $('#orgRef').val(orgData.orgRef);
    $('#orgTitle').val(orgData.orgTitle);
    $('#orgInn').val(orgData.orgInn);
    $('#orgKpp').val(orgData.orgKpp);
    $('#orgPhone').val(orgData.orgPhone);
    $('#contactFIO').val(orgData.contactFIO);
    $('#orgAdress').val(orgData.orgAdress);

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
    var data = $('#saveDataForm').serialize();
    $.ajax({
        url: 'index.php?r=sale/order/save-order-detail',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res){     
            console.log(res);
            if(res.isReload==true)document.location.reload(true); 
        },
        error: function(){
            alert('Error while saving data!');
        }
    });	
}


function switchValue(id, type)                  
{


}
 </script>  

 <?php $form = ActiveForm::begin(['id' => 'Mainform',]); ?>


 <?= $form->field($model, 'email')->textInput([
        'id'=>'email',
        //'style'=>'width:100px; margin:0px; font-size:12px;padding:2px;',
        'placeHolder' => 'Электронная почта',
        'onChange'    => 'searchByEmail()'
        ])->label('Электронная почта')
  ?>
 
 <div > 
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
                'value' => function ($model, $key, $index, $column) {    
                
               $action = "switchValue(".$model['id'].", 'wareInOrder');" ;                  
               $style='background:White;';    
               $id = $model['id'].'wareInOrder';
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
                'value' => function ($model, $key, $index, $column) {
                    $id = "wareCount".$model['id'];
                    $action =  "saveField(".$model['id'].", 'wareCount');"; 
                     return Html::textInput( 
                          $id, 
                          '',                                
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


   <?= $form->field($model, 'id')->hiddenInput()->label(false)?>
   <?= $form->field($model, 'orgRef')->hiddenInput()->label(false)?>  

<?php   
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataId' )->hiddenInput(['id' => 'dataId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo "<input type='submit'>";
ActiveForm::end(); 
?>
   
