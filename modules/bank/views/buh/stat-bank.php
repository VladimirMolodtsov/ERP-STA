<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Движение средств по банку';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>
.table-small {
padding: 2px;
font-size:12px;
}
</style>

<script>

function openExtractDetail(month)
{
 openWin ("bank/buh/download-extract&noframe=1&curMonth="+month,'download');  
}
function openBankOpDetail(month){
 openWin ("bank/buh/download-bank-op&noframe=1&curMonth="+month,'download');     
}
function openBankRemainDetail(month){

}

function showRow(row)
{
  for ($i=0;$i<8;$i++)
  {
    idx= $i+'_'+row;
    if (document.getElementById(idx).style.visibility == 'hidden'){        
        document.getElementById(idx).style.visibility='visible'; 
        //document.getElementById(idx).style.background='White'; 
    }
    else
        document.getElementById(idx).style.visibility='hidden'; 
  }      
    
}

</script>

<?php




?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<p><b><?= Html::encode($this->title) ?></b>


<?php
$syncArray = $model->syncArray;

$columns =[];

$columns[0]= [
                'attribute' => 'title',
				'label'     => 'Название',
                'format' => 'raw',                                
                'value' => function ($model, $key, $index, $column) {                                
                return "<div style='width:175px;'>".$model['title']."</div>";
                }   		

            ];

$columns[]=  [
                'attribute' => '-',
				'label'     => 'Всего',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                $val=0;
                for ($i=1;$i<13;$i++) $val+=$model[$i];
                    
                    return number_format($val,2,'.','&nbsp;');
                }                                
               ];		


for ($i=1;$i<13;$i++)     
$columns[]=  [
                'attribute' => $i,
				'label'     => $i,
                'format' => 'raw',
                'header' => '<div align="center" style="width:100%">'.$i.'
                <span class="glyphicon glyphicon-plus clickable" style="align:right;" onclick="showRow('.$i.')"></span> 
                </div>',                
                'contentOptions' => ['style' => 'padding:0px;'],
                'value' => function ($model, $key, $index, $column)use($i, $syncArray) {                                
                
                $id=$model['id'].'_'.$i;
                $action = "";
                    if ($model['id'] != 8){
                        $val= number_format($model[$i],0,'.','&nbsp;');
                        if ($model['id'] <= 3) $action = "openExtractDetail(".$i.")";
                                         else  $action = "openBankOpDetail(".$i.")";
                        $style='visibility:hidden;';                                   
                        $bg ='background-color:AliceBlue;';
                        if ( $model['c_'.$i] != 0)$bg ='background-color:PapayaWhip;'; 
                    }
                    else  {                        
                        $val= number_format($model[$i],0,'.','&nbsp;')."<br>".$syncArray[$i];
                        $action = "openBankRemainDetail(".$i.")";
                        $style='visibility:visible';
                        $bg ='background-color:White;';
                    }

                   $res= \yii\helpers\Html::tag( 'div', $val, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'id'=> $id,
                     'style' => "padding:2px;".$style,                
                     'title' =>  $model['c_'.$i],
                   ]);
                   
                   return \yii\helpers\Html::tag( 'div', $res, 
                   [
                     'class'   => 'clickable',
                     'onclick' => $action,
                     'style' => "width:100%;height:35px;".$bg,                                          
                   ]);
                   
                   
                   
                    
                }                                
               ];		

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],		
        'tableOptions' => [ 'class' => 'table table-striped table-bordered table-condesed table-small' ],
        'columns' => $columns,
	]
    );
?>


<pre>

<?php 

//print_r($model->dataArray);
?>

</pre>
