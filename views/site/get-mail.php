<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;

/* @var $this yii\web\View */
	$this->title = 'Электронная почта';

$this->registerJsFile('@web/phone.js');
$this->registerCssFile('@web/phone.css');
    
?>
<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
} 

.leaf {
    height: 70px; /* высота нашего блока */
    width:  100px;  /* ширина нашего блока */
    border: 0px solid #C1C1C1; /* размер и цвет границы блока */
    padding:5px;
    font-weight:bold; 
    box-shadow: 0.4em 0.4em 5px rgba(122,122,122,0.5);
    
}
.leaf:hover {
    box-shadow: 0.4em 0.4em 5px #696969;
}

.leaf-selected {    
    box-shadow: 0.4em 0.4em 5px White;
    border: 1px solid Silver; /* размер и цвет границы блока */
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
  function openLinkContact(id){
   openWin('site/reg-contact-by-mail&noframe=1&id='+id  ,'contactWin');  
  }
  
  
  function switchData(id, paramType){

  //openSwitchWin('site/switch-mail-param&id='+id+'&paramType='+paramType);    
    data = [];
/*    data['id'] =id
    data['paramType'] = paramType;*/
    $.ajax({
        url: 'index.php?r=site/switch-mail-param&id='+id+'&paramType='+paramType,
        //url: 'index.php?r=site/switch-mail-param',
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSwitch(res)
        },
        error: function(){
            alert('Error while retriving data!');
        }
    });	
  
  }

 function    showSwitch(res){
    switch (res['paramType'])
    {
        case 'isDel':
            id="isDel"+res['id'];
            if (res['val'] == 1)  document.getElementById(id).style.background='Crimson';    
                            else  document.getElementById(id).style.background='White';                  
        break;
        case 'isZakaz':
            id="isZakaz"+res['id'];
            if (res['val'] == 1)  document.getElementById(id).style.background='Orange';    
                            else  document.getElementById(id).style.background='White';                  
        break;
        case 'isSupplier':
            id="isSupplier"+res['id'];
            if (res['val'] == 1)  document.getElementById(id).style.background='DarkBlue';    
                            else  document.getElementById(id).style.background='White';                  
        break;
        
        
    }
        console.log(res); 
}
  
  
    
</script>

  


<div class ='row'>
  <div class ='col-md-5'>   
  <h2><?= Html::encode($this->title) ?></h2>
  </div>

 <div class ='col-md-2'>   
  </div>
    
  <div class ='col-md-1'>   
  <?PHP
  $nonLink = $model->getMyNonLinkMail();
  if ($nonLink == 0) {$bg='#dbffbd'; $cl = 'DarkBlue';}
                else {$bg='Crimson'; $cl = 'White';}
  ?>
              <div  class='btn btn-primary leaf ?>' style='background: <?=$bg ?>; color:<?= $cl ?>;'>
                <div class='leaf-txt'> Не обработано <br> почты </div>
                <div class='leaf-val'><?= $nonLink ?></div> 
                <div class='leaf-sub' ></div>
              </div>
  </div>

  <div class ='col-md-1'>   
  </div>

  <div class ='col-md-1'>   
  <?PHP
  $nonLink = $model->getNonRefMail();
  if ($nonLink == 0) {$bg='WhiteSmoke'; $cl = 'DarkBlue';}
                else {$bg='WhiteSmoke'; $cl = 'DarkBlue';}
  ?>
              <div  class='btn btn-primary leaf ?>' style='background: <?=$bg ?>; color:<?= $cl ?>;'>
                <div class='leaf-txt'> Не связанно <br> почты </div>
                <div class='leaf-val'><?= $nonLink ?></div> 
                <div class='leaf-sub' ></div>
              </div>
  </div>
  
  
  <div class ='col-md-1'>   
  </div>
 
  <div class ='col-md-1'>   
    <a href="index.php?r=site/sync-mail"><span class='glyphicon glyphicon-refresh'></span></a>   
  </div>

</div>

<div class ='spacer'>
</div>


<?php

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>true,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [            

            [
                'attribute' => 'msgFolder',
                'label' => 'Папка',
                'filter' => [0 =>'Все', 1 => 'INBOX', 2 => 'SENT'], 
            ],


            [
                'attribute' => 'msgTime',
                'label' => 'Дата сообщения',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return date("d.m.y",$model['msgTime'] );
                }                
               
            ],

            [
                'attribute' => 'msgSubject',
                'label' => 'Тема',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                return "<div title='".mb_substr($model['msgBody'],0, 150,'utf-8')."'>".$model['msgSubject']."</div>";
                
                }
            ],

            [
                'attribute' => 'email',
                'label' => 'Адрес',
            ],

            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
            ],

            [
                'attribute' => 'userFIO',
                'label' => 'Мененджер',
                'filter' => $model->getUserList(),		      
            ],

            [
                'attribute' => 'refContact',
                'label' => 'Контакт',
                'filter' => [0 => 'Все', 1 => 'Нет', 2 => 'Да'],                 
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  $id = 'refContact'.$model['id'];  
                  if (empty($model['refOrg'])){
                   $action="";
                   $style='background:Silver;color:White;';
                  }
                  else{
                   $action="openLinkContact('".$model['id']."')";
                   if (empty($model['refContact'])){
                    $style='background:Crimson;color:White;';                     
                   }else
                   {
                    $style='background:Green;color:White;';                      
                   }                      
                  } 
                   return \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Связанный контакт',
                   ]);                                                            
                }                               
            ],

            [
                'attribute' => 'refZakaz',
                'label' => 'Сделка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  $id = 'refContact'.$model['id'];  
                  $action="openLinkContact('".$model['id']."')";
                  if (empty($model['refZakaz'])){
                   $style='background:White;color:White;';                     
                  }else
                  {
                   $style='background:Green;color:White;';                      
                  }                      
                   return \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Связанная сделка',
                   ]);                                                            
                }                               
            ],

            [
                'attribute' => 'isZakaz',
                'label' => "Заказ",
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => [1 => 'Все', 2 => 'Нет', 3 => 'Да'], 
                'value' => function ($model, $key, $index, $column) {
                  $id = 'isZakaz'.$model['id'];  
                  $action="switchData('".$model['id']."', 'isZakaz')";
                  if (empty($model['isZakaz'])){
                   $style='background:White;color:Black;';                     
                  }else
                  {
                   $style='background:Orange;color:White;';                      
                  }                      
                   return \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Заказ в письме (лид)',
                   ]);                                                            
                }                               
            ],

            [
                'attribute' => 'isSupplier',
                'label' => "Поста.",
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => [1 => 'Все', 2 => 'Нет', 3 => 'Да'], 
                'value' => function ($model, $key, $index, $column) {
                  $id = 'isSupplier'.$model['id'];  
                  $action="switchData('".$model['id']."', 'isSupplier')";
                  if (empty($model['isSupplier'])){
                   $style='background:White;color:Black;';                     
                  }else
                  {
                   $style='background:DarkBlue;color:White;';                      
                  }                      
                   return \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Поставщик',
                   ]);                                                            
                }                               
            ],

            [
                'attribute' => 'isDel',
                'label' => "<div style='text-align:center; width: 70px;'><span class='glyphicon glyphicon-trash' title = 'Удален'></span></div> ",
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => [1 => 'Все', 2 => 'Нет', 3 => 'Да'], 
                'value' => function ($model, $key, $index, $column) {
                  $id = 'isDel'.$model['id'];  
                  $action="switchData('".$model['id']."', 'isDel')";
                  if (empty($model['isDel'])){
                   $style='background:White;color:Black;';                     
                  }else
                  {
                   $style='background:Crimson;color:White;';                      
                  }             
                   return \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'style'   => $style,                     
                     'title'   => 'Сообщение удалено',
                   ]);                                                            
                }                               
            ],
            
            
         /**/   
        ],
    ]
);
?>


<pre>
<?php
//phpinfo();
// print_r($model->debug); ?>
</pre>








</p>
