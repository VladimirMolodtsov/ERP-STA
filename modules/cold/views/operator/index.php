<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Холодная база';
$this->params['breadcrumbs'][] = $this->title;

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>



 
</style>


<script type="text/javascript">

function startPhoneRing(phone, orgRef, actType){
// $('#simpleContactDialog').modal('show');     

  
}

</script> 

<div class='row'>
<div class='col-md-8'>     </div>
<div class='col-md-2'>    <a href='index.php?r=cold/operator/cold-new' class='btn btn-primary'>Создать</a> </div>
<div class='col-md-2'>    <a href='index.php?r=cold/operator/load-by-url' class='btn btn-primary'>Загрузить</a> </div>
</div>
<div class="spacer"> </div>    
<div class="part-header"> Холодная база </div>    
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],

             [
                'attribute' => 'orgTitle',
                'label'     => 'Клиент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                $r="<a href='#' onclick=\"openWin('site/org-card&orgId=".$model['orgRef']."&mode=1','childwin');\" >".$model['orgTitle']."</a>";
                $r.="<br>ИНН: ".$model['orgINN']."</a>";
                   
                   return $r;
                },

             ],        

             [
                'attribute' => 'ЗЧБ',
                'label'     => 'ЗЧБ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                if (empty($model['headFIO'])) return " - ";
                if (empty($model['orgFullTitle'])) return " -";
                if (empty($model['registartionDate'])) return " -";
                                        
                if ($model['isOrgActive'] == 1) return "<div style='color:Green'><span  class='glyphicon glyphicon-ok'></span></div>";
                if ($model['isOrgActive'] == 0) return "<div style='color:Crimson'><span  class='glyphicon glyphicon-minus-sign'></span></div>";

                },

             ],        
             
                         
             [
                'attribute' => 'city',
                'label'     => 'Город, адрес',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $adress = Yii::$app->db->createCommand('SELECT adress from {{%adreslist}} WHERE ref_org =:refOrg and isBad =0', 
                 [':refOrg' => $model['orgRef'], ])->queryOne();                   
                    return  $model['city']."<br>".$adress['adress'];
                },                
            ],        

            
             [
                'attribute' => 'Телефоны',
                'label'     => 'Телефоны',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 $list = Yii::$app->db->createCommand('SELECT phone, status from {{%phones}} WHERE ref_org =:refOrg', 
                 [':refOrg' => $model['orgRef'], ])->queryAll();                   
                 $ret="";
                 
                 $actType=0;
                 if (!empty($model['firstContactRef'])) $actType=1;
                 if (!empty($model['secondContactRef']))$actType=2;
                 
                 
                 
                 for ($i=0; $i<count($list);$i++ )
                 {
                    $phone = $list[$i]['phone']; 
                    if ($list[$i]['status'] == 2) $ret.= "<s>".$phone."</s>";    
                    if ($list[$i]['status'] == 1) $ret.= "<b>".$phone."</b>";
                    if ($list[$i]['status'] == 0) $ret.= "".$phone."";
                    //$ret="<a href=# onclick=\"startPhoneRing('".$phone."','".$model['orgRef']."',".$actType.");\">".$ret."</a>";                    
                    $ret.="<br>"; 
                 }
                    return  $ret;
                },                
                
            ],        
            
             [
                'attribute' => '',
                'label'     => 'Разговор1',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 if ($model['firstContactRef'] == 0)
                 {
                  $action = 'openWin("cold/operator/cold-init&id='.$model['orgRef'].'","childWin");';  
                  return "<div class='gridcell' onclick='".$action."' style='width:150px; background:Silver;'> Нет контакта </div>";
                 }
                 $list = Yii::$app->db->createCommand('SELECT contactDate, phone, note from {{%contact}}
                 LEFT JOIN {{%phones}} on {{%phones}}.id = {{%contact}}.ref_phone                 
                 WHERE {{%contact}}.id =:ContactRef', 
                 [':ContactRef' => $model['firstContactRef'], ])->queryOne();                                    
                 
                  $ret="<i>".date("d.m.Y h:i", strtotime( $list['contactDate']))."</i><br>";            
                  $ret.=mb_substr($list['note'],0,150,'utf-8')."<br>";
                  $ret .=$list['phone'];
                  
                  $check=1;
                  if (empty($model['firstContactPosition']))$check=0;
                  if (empty($model['contactEmail']))$check=0;
                  if (empty($model['supplyManagerFIO']))$check=0;
                  
                  if ($check==0) $bg='background:Silver;';
                  else           $bg ='';
                  if ($check==1) $add = "<font color='Green'><span  class='glyphicon glyphicon-ok'></span></font>";
                  else           $add ='';
                     
                 $action = 'openWin("cold/operator/cold-init&id='.$model['orgRef'].'","childWin");';     
                 return "<div class='gridcell' onclick='".$action."' style='width:150px;".$bg."'>".$ret."&nbsp;".$add."</div>";
                },                
                
            ],        
        

            [
                'attribute' => 'supplyManagerFIO',
                'label'     => 'Снабженец',
                'format' => 'raw',

            ],        


             [
                'attribute' => 'secondContactRef',
                'label'     => 'Разговор2',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                 if ($model['firstContactRef'] == 0)
                 {
                    return "<div style='width:150px;'>&nbsp;</div>";
                 }             
                 
                 if ($model['secondContactRef'] == 0)
                 {
                  $action = 'openWin("cold/operator/cold-need&id='.$model['orgRef'].'","childWin");';  
                  return "<div class='gridcell' onclick='".$action."' style='width:150px; background:Silver;'> Нет контакта </div>";
                 }
                 $list = Yii::$app->db->createCommand('SELECT contactDate, phone, note from {{%contact}}
                 LEFT JOIN {{%phones}} on {{%phones}}.id = {{%contact}}.ref_phone                 
                 WHERE {{%contact}}.id =:ContactRef', 
                 [':ContactRef' => $model['secondContactRef'], ])->queryOne();      
                                               
                  $ret="<i>".date("d.m.Y h:i", strtotime( $list['contactDate']))."</i><br>";            
                  $ret.=mb_substr($list['note'],0,150,'utf-8')."<br>";
                  $ret .=$list['phone'];
                  
                 
                  $check=1;
                  if (empty($model['regularity']))$check=0;
                  if (empty($model['mainWareGroup']))$check=0;
                  
                  if ($check==0) $bg='background:Silver;';
                  else           $bg ='';
                  if ($check==1) $add = "<font color='Green'><span  class='glyphicon glyphicon-ok'></span></font>";
                  else           $add ='';

                 
                    
                    
                                     
                 $action = 'openWin("cold/operator/cold-need&id='.$model['orgRef'].'","childWin");';     
                 return "<div class='gridcell' onclick='".$action."' style='width:150px;".$bg."'>".$ret."&nbsp;".$add."</div>";
                    
                },                
                
            ],        


            [
                'attribute' => 'userFIO',
                'label'     => 'Менеджер',
                'format' => 'raw',

            ],        

        ],
    ]
    );
?>


<!-- Service -->


<?php
Modal::begin([
    'id' =>'simpleContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:650px'>

	<iframe width='600px' height='620px' frameborder='no'   src='index.php?r=store/purchase-ware-schet&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  


</div><?php
Modal::end();
?>
