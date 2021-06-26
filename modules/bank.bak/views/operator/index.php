<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper; 

$this->title = 'Банк - выписки';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/phone.js');
$this->registerJsFile('@web/js/modules/bank/operator.js');

 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />


<div class="item-header">Банковские операции:</div> 

 <?php
$typeArray = $model->getTypeArray();
$typeArray[0]='не задан'; 
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $detailProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
    
           
            [
                'attribute' => 'recordDate',
                'label'     => 'Дата',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['recordDate'])+4*3600);
               }
                
            ],            

           [
                'attribute' => 'debetOrgTitle',
                'label'     => 'Плательщик',
                'format' => 'raw',     
            ],            

            [
                'attribute' => 'creditOrgTitle',
                'label'     => 'Получатель',
                'format' => 'raw',     
            ],            
                        
            [
                'attribute' => 'debetSum',
                'label'     => 'Расход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['debetSum'],2,',','&nbsp;');
               }
                
            ],            

            [
                'attribute' => 'creditSum',
                'label'     => 'Приход',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditSum'],2,',','&nbsp;');
               }
                
            ],            
           /****/
           
            [
                'attribute' => 'orgTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    if (empty($model['orgRef'])) return "&nbsp;";
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['orgRef']."\", \"childwin\")' >".$model['orgTitle']."</a>";
                },
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
                              'onchange' => 'saveData('.$model['id'].',"contragentType");'
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
                              'onchange' => 'saveData('.$model['id'].',"operationType");'
                              ]);
                },
            ],    
                 
                      
        ],
    ]
); 
Pjax::end(); 
?>




<div class="item-header">Загруженные выписки:</div> 
<?php
Pjax::begin();

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $extractProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],  
             
           [
                'attribute' => 'uploadTime',
                'label'     => 'Загружена',
                'format' => 'raw', 
                //'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'value' => function ($model, $key, $index, $column) {                    
                    return date("d.m.Y H:i:s", strtotime($model['uploadTime'])+4*3600);
               }
               
            ],         

            [
                'attribute' => 'creationDate',
                'label'     => 'Дата создания',
                'format' => 'raw',     
                'value' => function ($model, $key, $index, $column) {                    
                    return "<a href ='#' onclick=\"openWin('bank/operator/show-extract&id=".$model['id']."','childWin');\" >".date("d.m.Y h:i", strtotime($model['creationDate'])+4*3600)."</a>";
               }
                       
            ],            

            [
                'attribute' => 'creditTurn',
                'label'     => 'Поступления',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['creditTurn'],2,',','&nbsp;');
               }
                
            ],            
            
                        
            [
                'attribute' => 'debetTurn',
                'label'     => 'Расходы',
                'format' => 'raw',            
                'value' => function ($model, $key, $index, $column) {                    
                    return number_format($model['debetTurn'],2,',','&nbsp;');
               }
                
            ],            

                        
            [
                'attribute' => 'userFIO',
                'label'     => 'Оператор',
                'format' => 'raw',            
            ],            

            /****/
        ],
    ]
); 

Pjax::end(); 
?>

<a href='index.php?r=bank/operator/load-bank' class='btn btn-primary'>Загрузить</a>



<?php
$form = ActiveForm::begin(['id' => 'saveDataForm']);
echo $form->field($detailModel, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($detailModel, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($detailModel, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
ActiveForm::end(); 
?>
