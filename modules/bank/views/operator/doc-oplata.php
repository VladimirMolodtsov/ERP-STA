<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 


$this->title = 'Список зарегистрированных документов';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/doc-list.js');



?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />


<style>
.table-small {
padding: 2px;
font-size:12px;
}
.action_ref {    
    color:Green;
}

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.orginfo {
  
}

.orginfo:hover {    
    color:Blue;         
    cursor:pointer;
}

</style>


<script type="text/javascript">



</script> 





<?php 
  switch ($model->flt) {
   
   case 'buh':
       echo "<h3>Документы бухгалтерии</h3>";
   break;

   case 'office':
       echo "<h3>Документы офиса</h3>";
   break;

   case 'ware':
       echo "<h3>Документы производства и закупок</h3>";
   break;
   
   default:
       echo "<h3>Общий список</h3>";
   break;
         

  }
 ?> 



<div class='row'>
<div class="col-md-8">
</div>   

<div class="col-md-2">
    <a href="#" class='btn btn-primary'  onclick="openWin('index.php?r=bank/operator/doc-list&flt=all&<?= Yii::$app->request->queryString  ?>&format=csv&noframe=1','childWin');">Выгрузить</a> 
</div>   
   
   <div class='col-md-2'>
    <input type='button' class='btn btn-primary' value='Загрузить документы' onclick='loadDocFinalize();'>
   </div>
   
</div> 



<hr>
<?php
$typeArray = $model->getTypeArray();
$typeArray[0]='не задан';
 
  echo GridView::widget(
    [
        'dataProvider' => $provider,
        'filterModel' => $model,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
      
        'responsive'=>true,
        'hover'=>false,
        
        /*'panel' => [
        'type'=>'success',
  //      'footer'=>true,
         ], */       
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        'id' => 'reestrGrid'
        ],


        'columns' => [
            [
                'attribute' => 'docIntNum',
                'label'     => '#',
                'format' => 'raw',                            
            ],  
             
            [
                'attribute' => 'regDateTime',
                'label'     => 'Загружен',
                 'filter' => false,
                //'format' => ['datetime', 'php:d.m H:i'],
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                 
                   $regTime = strtotime($model['regDateTime']);
                   if ($regTime > 100) return  date("d.m.y H:i", $regTime);
                   else return  "&nbsp;";
   
               }
               
            ],            
            
            [
                'attribute' => 'orgTitle',
                'label'     => 'Контрагент',
                'format' => 'raw',                            
                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";
                 if ($model['refOrg'] == 0) $style='color:Crimson;'; 
                 $id = 'refOrg'.$model['id'];
                 $action = "selectOrg(".$model['id'].",".$model['orgINN'].")";                 
                    return "<div id='".$id."' class='orginfo' onclick='".$action."' style='".$style."'>".$model['orgTitle']."<br>".$model['orgINN']."</div>";
               }

            ],            
            
            [
                'attribute' => 'docOrigNum',
                //'filter' => false,
                'label'     => 'Документ',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return "<a href ='#' onclick=\"openExtWin('".$model['docURI']."','childWin');\" 
                    >".$model['docTitle']." ".$model['docOrigNum']."</a>";
               }
                       
            ],            


            [
                'attribute' => 'docOrigDate',
                //'filter' => false,
                'label'     => 'Дата',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {    
                
                $docOrigTime = strtotime($model['docOrigDate']);
                if ($docOrigTime  > 100) return date("d.m.y", $docOrigTime);
                else  return "&nbsp;";

               }
                       
            ],            

            
           [
                'attribute' => 'docSum',
                'filter' => false,
                'label'     => 'На сумму',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['docSum'],2,',','&nbsp;');
               }
                
            ],            
            
           [
                'attribute' => 'docOrigStatus',
                'filter' => [0 => 'Все', 1 => 'Оригинал',  2 => 'Копия', 3 => 'Скан', ],
                'label'     => 'Статус',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {     
                    switch ($model['docOrigStatus']){
                        case  1: return "Копия"; break;
                        case  2: return "Скан"; break;
                        default: return "Оригинал"; break;
                   }
               }
            ],            
            
 
            [
                'attribute' => 'docNote',
                'filter' => false,
                'label'     => 'Комментарий',
                'format' => 'raw',                            
            ],            

            [
                'attribute' => 'docGoal',
                'filter' => [0 => 'Все', 1 => 'Бухгалтерия',  2 => 'Офис', 3 => 'Производство', ],
                'label'     => 'Ответств.',
                'format' => 'raw',                            
            ],            
                                     
            [
                'attribute' => 'docOwner',
                'filter' => [0 => 'Все', 1 => 'Бухгалтерия',  2 => 'Офис', 3 => 'Производство', ],
                'label'     => 'Передать',
                'format' => 'raw',                            
            ],            
                                     
/*           [
                'attribute' => 'isFinance',
                'filter' => [0 => 'Все', 1 => 'Да',  2 => 'Нет'],
                'label'     => 'Бухгалтерские',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['isFinance'] == 1) return "Да";
               }
                
            ],            
*/            
           [
                'attribute' => 'isOplate',
                'filter' => [0 => 'Все', 1 => 'Да',  2 => 'Нет'],
                'label'     => 'Оплата',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    if ($model['isOplate'] == 1) return "Да";
               }                
            ],            
                                     
            [
                'attribute' => 'ref1C_input',
                'filter' => false,
                'label'     => 'Вх. № в 1C',
                'format' => 'raw',                            
            ],            

            [
                'attribute' => 'ref1C_schet',
                'filter' => false,
                'label'     => 'Cч. № в 1C',
                'format' => 'raw',                            
            ],            
            
            
            [
                'attribute' => 'contragentType',
                'label' => 'Статус',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '120px'] ,  
                'value' => function ($model, $key, $index, $column) use($typeArray) {                        
                  
                $c = "";
                if ($model['contragentType'] == 0) $c = 'color:Crimson;'; 
                  $id = "contragentType".$model['id'];
                if (empty($model['contragentType'])) $contragentType = '0';
                                               else $contragentType = $model['contragentType'];                  
                  return Html::dropDownList( 
                          $id, 
                          $contragentType, 
                              $typeArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveField('.$model['id'].',"contragentType");'
                              ]);
                },
            ],    
           
            [
                'attribute' => 'operationType',
                'label' => 'Операция',
                'format' => 'raw',
                'contentOptions'   =>   ['width' => '130px'] ,  
                'value' => function ($model, $key, $index, $column)  {                        

                 if (empty($model['contragentType'])) $contragentType = 0;
                                                 else $contragentType = $model['contragentType'];
                 
                $strSql = "SELECT id, operationTitle from {{%doc_operation}} where refDocType = ".$contragentType." ORDER BY id"; 
                $list = Yii::$app->db->createCommand($strSql)->queryAll();                    
                $operationArray =  ArrayHelper::map($list,'id','operationTitle');       
                $operationArray[0]='не задан';
                 $c = "";
                 if ($model['contragentType'] == 0) $c = 'color:Crimson;';                  
                  $id = "operationType".$model['id'];
                  return Html::dropDownList( 
                          $id, 
                          $model['operationType'], 
                              $operationArray,
                              [
                              'class' => 'form-control',
                              'style' => 'width:70px;font-size:12px; padding:1px;'.$c, 
                              'id' => $id, 
                              'onchange' => 'saveField('.$model['id'].',"operationType");'
                              ]);
                },
            ],    
           

            [
                'attribute' => 'isTTN',
                'label' => 'ТТН/Акт',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '50px'] ,    
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isTTN".$model['id'];
                $style="";    
                           
                if ($model['isTTN'] == 0) $style='background:White;';                  
                                    else  $style='background:Green;';     
                     
               $action =  "switchData(".$model['id'].", 'isTTN');";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'ТТН/Акт',
                     'style'   => $style,
                   ]);
                return $val;
                
                }
            ],               
            

            [
                'attribute' => 'isUTR',
                'label' => 'УТР',
                'format' => 'raw',
                'encodeLabel' => false,                
                'contentOptions'   =>   ['width' => '50px'] ,    
                'filter' => [
                '1' => 'Все',
                '2' => 'Да',
                '3' => 'Нет',                
                ],           
                'value' => function ($model, $key, $index, $column) {

                $id = "isUTR".$model['id'];
                $style="";    
                           
                if ($model['isUTR'] == 0) $style='background:White;';                  
                                    else  $style='background:Green;';     
                     
               $action =  "switchData(".$model['id'].", 'isUTR');";                    
               $val = \yii\helpers\Html::tag( 'div', '&nbsp;', 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'УТР',
                     'style'   => $style,
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
    'id' =>'selectOrgDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:600px'>
    <iframe id='selectOrgDialogFrame' width='570px' height='420px' frameborder='no'   src='index.php?r=/bank/operator/doc-org-list&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
    </iframe>	  
</div>
<?php Modal::end();?>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
ActiveForm::end(); 
?>



