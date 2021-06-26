<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use app\models\User;


/* @var $this yii\web\View */
	$this->title = 'Обработка электронной почты';

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
    
  function switchUsage(id){
  openSwitchWin('head/switch-user-rpt-mail&id='+id);  
  
/*    data = [];
    $.ajax({
        url: 'index.php?r=/head/switch-user-rpt-mail&id='+id,
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
  */
  } 
  
 function    showSwitch(res){
    switch (res['switchType'])
    {
        case 'U_RPT_MAIL':
            id="U_RPT_MAIL"+res['id'];
            if (res['val'] == 1)
            {
                document.getElementById(id).style.background='Green';    
            }else{
                document.getElementById(id).style.background='White';    
            }  
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
  $nonLink = $model->getAllNonLinkMail();
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
                'attribute' => 'userFIO',
                'label' => 'Мененджер',
                'filter' => $model->getUserList(),		      
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                  return "<a href='index.php?r=site/get-mail&requestFIO=".$model['id']."'>".$model['userFIO']."</a>";  
                }                
            ],

            [
                'attribute' => '',
                'label' => 'Обработано <br> сегодня',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                 $strSql = 'SELECT COUNT(id) from {{%mail}} where date(msgDate)=CURDATE() and ifnull(refContact,0) > 0 AND refManager=:refManager';   
                 $processed = Yii::$app->db->createCommand($strSql,[':refManager' => $model['id']])->queryScalar();                
  
                 return $processed;
                }                               
            ],

            [
                'attribute' => '',
                'label' => 'Ожидает <br> за сегодня',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                 $strSql = 'SELECT COUNT(id) from {{%mail}} where date(msgDate)=CURDATE() and ifnull(refContact,0) = 0 AND refManager=:refManager';   
                 $processed = Yii::$app->db->createCommand($strSql,[':refManager' => $model['id']])->queryScalar();                
  
                 return $processed;
                }                               
            ],

            [
                'attribute' => '',
                'label' => 'Ожидает <br> всего',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    
                 $strSql = 'SELECT COUNT(id) from {{%mail}} where ifnull(refContact,0) = 0 AND refManager=:refManager' ;  
                 $processed = Yii::$app->db->createCommand($strSql,[':refManager' => $model['id']])->queryScalar();                
  
                 return $processed;
                }                               
            ],


            [
                'attribute' => 'usageFlag',
                'label' => "Контроль",
                'encodeLabel' => false,
                'format' => 'raw',
                'filter' => [0 => 'Все', 1 => 'Нет', 2 => 'Да'], 
                'value' => function ($model, $key, $index, $column) {
                  $id = 'U_RPT_MAIL'.$model['id'];  
                  $action="switchUsage('".$model['id']."')";
                  if (!($model['usageFlag'] & User::U_RPT_MAIL)){
                   $style='background:White;color:Black;';                     
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
                     'title'   => 'Сообщение удалено',
                   ]);                                                            
                }                               
            ],
            
            
            
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
