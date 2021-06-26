<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
//use kartik\tabs\TabsX;
use yii\bootstrap\Modal;


$this->title = 'Готовая продукция';
$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
?>
<h3><?= Html::encode($this->title) ?></h3>
<link rel="stylesheet" type="text/css" href="phone.css" />

<style>
.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
.minus {
  color:Crimson;  
}

.plus {
  color:Green;  
}

.suspicious {
  color:Crimson;  
}  


.notsuspicious {
  color:DarkGreen;  
}  
</style>
  
<script>

</script>

</script>





<?php
echo  GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
/*    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
  */      
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [       

/*            [
                'attribute' => 'productionNum',
                'label' => 'Наряд',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],*/
            [
                'attribute' => 'schetNum',
                'label' => 'Cчёт',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],
          
            [
                'attribute' => '',
                'label' => 'Сделка',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;',  'width' =>'150px'],    
                'value' => function ($model, $key, $index, $column) {                    
                
                 $strSql = "SELECT {{%schet}}.id, {{%schet}}.schetDate, refOrg, title as orgTitle FROM {{%schet}}, {{%orglist}}
                 WHERE {{%schet}}.refOrg= {{%orglist}}.id and {{%schet}}.schetNum =:schetNum and DATEDIFF(NOW(), {{%schet}}.schetDate ) < 180";
                 $list= Yii::$app->db->createCommand( $strSql, [':schetNum' => $model['schetNum']])->queryAll();
                 $N=count($list);
                 $val="";
                 
                 for ($i=0;$i<$N;$i++)
                 {
                 $info = $model['schetNum']." от ".$list[$i]['schetDate']." ".$list[$i]['orgTitle'];
                 $action = "openWin('market/market-schet&id=".$list[$i]['id']."&noframe=1', 'schetWin')";
                     $val.= \yii\helpers\Html::tag( 'div', $info, 
                   [
                     'class'   => 'clickable',
                     'onClick' => $action,
                   ]);   
                 }
                return $val;
               }
                
            ],

            
      /*      [
                'attribute' => 'wareType',
                'label' => 'вид продукции',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],*/


            [
                'attribute' => 'wareTitle',
                'label' => 'Товар',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],

            [
                'attribute' => 'wareCount',
                'label' => 'кол-во',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],

            [
                'attribute' => 'wareEd',
                'label' => 'ед.изм',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],

            [
                'attribute' => 'note',
                'label' => 'Комментарии, дополнения',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],

            [
                'attribute' => 'finishDate',
                'label' => 'выполнено',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],

      /*      [
                'attribute' => 'status',
                'label' => 'Статус',
                'format' => 'raw',
                'contentOptions' => ['style' => 'padding:2px; font-size:11px;'],    
            ],*/

            
        ],
    ]
);

?>
<pre>
<?php //print_r($model->debug); ?>
</pre>
