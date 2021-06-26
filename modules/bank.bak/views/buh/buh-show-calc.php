<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;

$this->title = 'Детализация расчета';
$this->params['breadcrumbs'][] = $this->title;


?>

 
 
<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<style>

</style>


<script type="text/javascript">

function setDataUse(refSrc,val,opType)
{
    openSwitchWin('bank/buh/set-data-use&refCheck=<?= $model->idx ?>&opType='+opType+'&mult='+val+'&refSrc='+refSrc); 
}

</script> 

<a name='dataUse'> </a>
<?= $model->showCalc() ?>

<hr>

<?php

$idx=$model->idx;
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
        
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
        
            [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
            ],

            [
                'attribute' => 'v5',
                'label' => 'Предыдущее значение',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {     
                    $val = number_format($model['v5'],'2','.','&nbsp;');
                    return "<div align='center'>".$val."</div>";
                },                
            ],
            [
                'attribute' => '-',
                'label' => 'Использовать предыдущее',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($idx)  {
     
                 $list = Yii::$app->db->createCommand("Select id, refCheck, opType, refSrc, mult  FROM {{%buh_check_calc}} 
                 where refCheck=:refCheck AND opType = 1 AND refSrc=:refSrc LIMIT 1",
                 [
                 ':refCheck' => $idx,
                 ':refSrc'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];

                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].",-1,1)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 0,1)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 1,1)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                 
                               
                return  "<div align='center'>".$out."</div>";
                },
                
                
            ],
            
                       

                                   
            [
                'attribute' => 'v6',
                'label' => 'Текущее значение',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {     
                    $val = number_format($model['v6'],'2','.','&nbsp;');
                    return "<div align='center'>".$val."</div>";
                },                
            ],
                       

            [
                'attribute' => '-',
                'label' => 'Использовать текущее ',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($idx) {
     
                 $list = Yii::$app->db->createCommand("Select id, refCheck, opType, refSrc, mult  FROM {{%buh_check_calc}} 
                 where refCheck=:refCheck AND opType = 0 AND refSrc=:refSrc LIMIT 1",
                 [
                 ':refCheck' => $idx,
                 ':refSrc'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].",-1,0)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 0,0)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#dataUse' onclick='setDataUse(".$model['idx'].", 1,0)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                 
                               
                return  "<div align='center'>".$out."</div>";
                },
            ],
            
            
                                   
        ],
    ]
);
?>
<br>
<a name='controlUse'> </a>
<?php

$idx=$model->idx;
echo GridView::widget(
    [
        'dataProvider' => $controlprovider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        
        'responsive'=>true,
        'hover'=>false,
        
        'pjax'=>true,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
        
            [
                'attribute' => 'titleTask',
                'label' => 'Показатель',
                'format' => 'raw',
            ],

                                   
            [
                'attribute' => 'v6',
                'label' => 'Значение',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)use($idx) {     
                    $val = number_format($model['v6'],'2','.','&nbsp;');
                    
                    if  ($model['idx'] ==$idx) return  "<div align='center' style='background-color:LemonChiffon'>".$val."</div>";
                    return $model['idx']."<div align='center'>".$val."</div>";
                },                
            ],
                       

            [
                'attribute' => '-',
                'label' => 'Использовать',
                'contentOptions'   =>   ['style' => 'padding:2px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($idx) {
                 if  ($model['idx'] >=$idx) return "&nbsp;";
                 if  ($model['idx'] ==$idx) return "<div align='center' style='background-color:LemonChiffon'>&nbsp;</div>";
                 
                 $list = Yii::$app->db->createCommand("Select id, refCheck, opType, refSrc, mult  FROM {{%buh_check_calc}} 
                 where refCheck=:refCheck AND opType = 2 AND refSrc=:refSrc LIMIT 1",
                 [
                 ':refCheck' => $idx,
                 ':refSrc'   => $model['idx'],
                 ]                 
                 )->queryAll();                 
                 if(count($list) == 0) $val=0;
                 else $val=$list[0]['mult'];
                
                 $out = "";
                 if ( $val != -1 ) $out .= "<a href='#controlUse' onclick='setDataUse(".$model['idx'].",-1,2)'>-1&nbsp;</a>" ;
                 else              $out .="<span style='color:White;background-color:Crimson'><b>-1</b></span>&nbsp;" ; 
                 if ( $val != 0 ) $out .= "<a href='#controlUse' onclick='setDataUse(".$model['idx'].", 0,2)'>&nbsp;0&nbsp;</a>" ;
                 else              $out .= "<b>&nbsp;0</b>&nbsp;" ; 
                 if ( $val != 1 ) $out .= "<a href='#controlUse' onclick='setDataUse(".$model['idx'].", 1,2)'>&nbsp;+1</a>" ;
                 else              $out .="<span style='color:White;background-color:Green'><b>+1</b></span>&nbsp;" ; 
                 
                 
                               
                return  "<div align='center'>".$out."</div>";
                },
            ],
            
            
                                   
        ],
    ]
);
?>
