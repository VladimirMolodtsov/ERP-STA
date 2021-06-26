<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'договора';
//$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->isGuest == true){ return;}
    
 ?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<script type="text/javascript">

function showEditForm(id)
{
    
  openWin('head/contract-edit&id='+id,'childwin');  
    
}

function printContract(id,format)
{

  openWin('head/print-contract&id='+id+'&format='+format,'childwin');

}

</script> 
 
<style>



</style>

<?php 

  echo  \yii\grid\GridView::widget(
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
                'attribute' => 'clientTitle',
                'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                        
                    if (empty ($model['refOrg']) ) return $model['clientTitle'];                                 
                    $add="";
                    if ($model['clientTitle'] !=$model['orgTitle'] ) $add="<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['refOrg']."\")' >"."<br>(".$model['orgTitle'].")"."</a>";
                    return $model['clientTitle'].$add;
                },
             ],   

            [
                'attribute' => 'orgINN',
                'label' => 'ИНН',
                'format' => 'raw',                
                
            ],    

            [
                'attribute' => 'orgKPP',
                'label' => 'КПП',
                'format' => 'raw',                
                
            ],   

            [
                'attribute' => 'creationTime',
                'label' => 'Регестр',
                'format' => 'raw',                
                
            ],    

/*           [
                'attribute' => 'contactorFull',
                'label' => 'В лице',
                'format' => 'raw',                
                
            ],    
 
           [
                'attribute' => 'contractorReason',
                'label' => 'На основании',
                'format' => 'raw',                
                
            ],    
*/
           [
                'attribute' => 'oplateStart',
                'label' => 'С получения',
                'format' => 'raw',                
                
            ],    

           [
                'attribute' => 'oplatePeriod',
                'label' => 'В течении',
                'format' => 'raw',                
                
            ],    
      
           [
                'attribute' => 'predoplata',
                'label' => 'Предоплата',
                'format' => 'raw',                
                
            ],    
      
             [
                'attribute' => 'dateEnd',
                'label' => 'Действует до',
                'format' => 'raw',                
                
            ],    
    
            [
                'attribute' => 'internalNumber',
                'label' => 'Номер',
                'format' => 'raw',                
                
            ],    
        
            [
                'attribute' => 'refOrg',
                'label' => 'Привязка',
                'format' => 'raw',    
                'filter' => ['1'=>'Да', '2' => 'Нет'],                 
                'value' => function ($model, $key, $index, $column) {
                    
                    if (!empty($model['refOrg']) ){ $isOp = true;}
                    else                          { $isOp = false;}
                    
                    return \yii\helpers\Html::tag('span', $isOp ? 'Yes' : 'No',
                    ['class' => 'label label-' . ($isOp ? 'success' : 'danger'),'title' => 'привязка к организации']);
                },
                
                
            ],

            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',                
                'value' => function ($model, $key, $index, $column) {

                 return "<a href='#' onclick='showEditForm(".$model['id'].")'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span></a>";

                },
                
            ],    
            
            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 return "<a href='#' onclick='printContract(".$model['id'].",\"doc\")'><span class='glyphicon glyphicon-print' aria-hidden='true'></span></a>";

                },

            ],

            [
                'attribute' => '',
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {

                 return "<a href='#' onclick='printContract(".$model['id'].",\"html\")'><span class='glyphicon glyphicon-search' aria-hidden='true'></span></a>";

                },

            ],

        ],
    ]
);

