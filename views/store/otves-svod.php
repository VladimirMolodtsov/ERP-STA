<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;


$this->title = 'Отвесы - сводная';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');


?>

<style>

.btn-small{
margin:2px;
font-size: 10pt;
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



</script>


<div class='spacer'></div>

<table border='0' width='100%' > 
        <tr>        
            <td width='250px'>
                <h3><?= Html::encode($this->title) ?></h3>
            </td>

             

            <td align='right'>

            <!-- <div align='right'><span onclick='syncOtves()' class='clickable glyphicon glyphicon-refresh'></span></div> -->
            
            </td>
            
    </tr>
</table>    

     
<?php

echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],        
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
   //     'type'=>'success',
   //     'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => '',
                'label' => 'Поставщик',
     //           'filterInputOptions' => ['class' => 'filter-small', 'style' => 'width: 150px'],
     //           'filter' => $model->getWareInOtves(),
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                            

                  $val = Yii::$app->db->createCommand('SELECT wareProdTitle FROM  {{%ware_producer}}, {{%warehouse}}
                  WHERE  {{%ware_producer}}.id={{%warehouse}}.producerRef AND {{%warehouse}}.id =:refWarehouse',
                    [
                    ':refWarehouse' =>$model['refWarehouse'],
                    ])->queryScalar();
                 
                   return $val;
                }
            ],
      
            [
                'attribute' => 'wareTitle',
                'label' => 'Товар в наименованих поставщика',
     //           'filter' => $model->getWareInOtves(),
                'format' => 'raw',
            ],
           
             [
                'attribute' => 'amount',
                'label' => 'На складах',
                'format' => 'raw',
//                'contentOptions'=>['style' => 'width:75px;padding-left:0px;padding-right:0px;'],
            ],   
           
            [
                'attribute' => '',
                'label' => 'В отвесах',
                'format' => 'raw',
                'contentOptions'=>['style' => 'width:75px;padding-left:0px;padding-right:0px;'],
                'value' => function ($model, $key, $index, $column) {

                 $val = Yii::$app->db->createCommand('SELECT sum(size) FROM  {{%otves_list}}
                  WHERE refZakaz=0 and refSchet=0 and  refWarehouse=:refWarehouse',
                    [
                    ':refWarehouse' =>$model['id'],
                    ])->queryScalar();
                 if(empty($val))$val="----";
         
                   $action = "openWin('store/otves-create&wareScladRef=".$model['id']."','otvesWin')";
                   $id = 'otves'.$model['id'];
                   return   \yii\helpers\Html::tag( 'div', $val,
                   [
                     'class'   => 'clickable',
                     'id'      => $id,
                     'onclick' => $action,
                     'title'   => 'Отвесы',
                   ]);

                }
            ],    
            
                        
        ],
    ]
);
?>


<?php 
$form = ActiveForm::begin(['id' => 'saveDataForm', 'action' => 'index.php?r=store/save-otves-data']);
echo $form->field($model, 'recordId' )->hiddenInput(['id' => 'recordId' ])->label(false);
echo $form->field($model, 'dataType' )->hiddenInput(['id' => 'dataType' ])->label(false);
echo $form->field($model, 'dataVal' )->hiddenInput(['id' => 'dataVal' ])->label(false);
echo $form->field($model, 'wareNameRef' )->hiddenInput(['id' => 'wareNameRef' ])->label(false);
//echo "<input type='submit'>";
ActiveForm::end(); 
?>
