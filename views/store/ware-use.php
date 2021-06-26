<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;


$this->title = 'Организации и склады';
$curUser=Yii::$app->user->identity;


$now =strtotime($model->strDate);
$prev=$now-24*3600;
$next=$now+24*3600;

?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.otves {
    background-color: Green ;
    //width: 50px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

.small_btn {
    //background-color: Green ;
    //width: 50px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

.inuse {
    background-color:  Brown;
    //width: 30px;
    font-size: 10px;
    margin:4px;
    padding:4px;
} 

</style>
  
<script>
function switchActive(id)
{  
     openSwitchWin('store/switch-ware-use&strDate=<?= date("Y-m-d", $now) ?>&id='+id);
}

function switchFilter(org, val)
{
     openSwitchWin('store/switch-ware-filtered&org='+org+'&filterVal='+val);
}


function switchInSum(id)
{  
     openSwitchWin('store/switch-ware-insum&id='+id);
}

</script>


<div class ='row'>
   <div class ='col-md-1'>   
       <a href="index.php?r=store/ware-use&noframe=1&strDate=<?= date('Y-m-d',$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-6' style='text-align:center'><h4><?= date("d.F.Y", $now) ?></h4></div>
   <div class ='col-md-1' style='text-align:right'>
       <a href="index.php?r=store/ware-use&noframe=1&strDate=<?= date('Y-m-d',$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>
   </div>
 
  <div class ='col-md-3' style='text-align:center'><?= $model->syncDateTime ?></div>
  <div class='col-md-1' style='text-align:right;'><a href='index.php?r=data/sync-sclad&syncTime=<?= $now ?>&noframe=1'><span class='glyphicon glyphicon-refresh'></span></a></div>  
</div>




<div class ='row'>
   <div class ='col-md-8'>   
<?php
//number_format($model->sumValue,2,'.','&nbsp;');
echo GridView::widget(
    [
        'dataProvider' => $orgProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,        
  /*  'panel' => [
        'type'=>'success',
        'footer'=>true,
    ],        */
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

            [
                'attribute' => 'orgTitleUse',
                'label' => 'Организация',
                'format' => 'raw',
            ],

            [
                'attribute' => 'isFiltered',
                'label' => 'Учитывать',
                'filter' => ['1' => 'Все', '2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['isFiltered'] == 1 ){ $isUse = true;  $val=0;}
                   else                           { $isUse = false; $val=1;}
                    return "<a href='#' onclick='switchFilter(".$model['useRef'].",".$val.");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                         ['class' => 'label label-' . ($isUse ? 'success' : 'default'),])."</a>";
                }                
                
            ],

        ],
    ]
);
?>
    </div>
    <div class ='col-md-4' >   
    <p>
    Сумма по себестоимости:  <b><?=  number_format($model->sumValue,2,'.','&nbsp;'); ?></b>
    <br>  <a href="index.php?r=store/ware-content&noframe=1&strDate=<?= date('Y-m-d',$now) ?>">детально ...</a>    
    </p>
    </div>
</div>



<hr>

<?php
//number_format($model->sumValue,2,'.','&nbsp;');
echo GridView::widget(
    [
        'dataProvider' => $provider,
        'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'filterModel' => $model,        
        'responsive'=>true,
        'hover'=>false,
        
    'panel' => [
        'type'=>'success',
  //      'footer'=>true,
    ],        
        
        'pjax'=>false,
        'pjaxSettings'=>[
        'neverTimeout'=>true,
        ],

        'columns' => [

        /*    [
                'attribute' => 'orgTitle',
                'label' => 'Организация',
                'format' => 'raw',
            ],*/
       
            [
                'attribute' => 'scladTitle',
                'label' => 'Склад',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($now) {                
                return "<a href='index.php?r=store/ware-content&noframe=1&strDate=".date('Y-m-d',$now)."&fltOrgTitle=".$model['orgTitle']."&scladTitle=".$model['scladTitle']."'>".$model['scladTitle']."</a>";
                }                
               
            ],
                                    
            [
                'attribute' => 'initSum',
                'label' => 'Сумма ',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                    return number_format($model['initSum'],2,'.','&nbsp;');
                }                
                
            ],

            [
                'attribute' => 'errNum',
                'label' => 'Ошибки ',
                'format' => 'raw',               
                'value' => function ($model, $key, $index, $column) use($now) {                
                return "<a href='index.php?r=store/ware-content&noframe=1&errOnly=1&strDate=".date('Y-m-d',$now)."&fltOrgTitle=".$model['orgTitle']."&scladTitle=".$model['scladTitle']."'>".$model['errNum']." (".number_format($model['errSum'],2,'.','&nbsp;').")"."</a>";
                }                
             
            ],
            
            
            [
                'attribute' => 'isInUse',
                'label' => 'Склад <br> активен',
                'filter' => ['1' => 'Все','2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'encodeLabel'  => false,
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['isInUse'] == 1 ){ $isUse = true;}
                   else                        { $isUse = false;}
                    return "<a href='#' onclick='switchActive(".$model['id'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                        ['class' => 'label label-' . ($isUse ? 'success' : 'danger'),])."</a>";
                }                
                
            ],

            
          [
                'attribute' => 'useInSum',
                'label' => 'Учитывать в <br>сумме на складе',
                'encodeLabel'  => false,
                'filter' => ['1' => 'Все', '2' => 'Да', '3' => 'Нет'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                
                   if ($model['useInSum'] == 1 ){ $isUse = true;}
                   else                           { $isUse = false; }
                    return "<a href='#' onclick='switchInSum(".$model['id'].");'>".\yii\helpers\Html::tag('span',$isUse ? 'Yes' : 'No',
                         ['class' => 'label label-' . ($isUse ? 'success' : 'default'),])."</a>";
                }                
                
            ],                     
        ],
    ]
);
?>

