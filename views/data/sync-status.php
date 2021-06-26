<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Статус синхронизации';

$monthList = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' , 'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );                    

?>
<style>
.table-small {
padding: 2px;
font-size:12px;
}

.m_empty {
 width:100%;
 height:35px;
 /*background-color:Green;*/
 padding:5px;
 text-align:center;
}


.m_norm {
 width:100%;
 height:35px;
 background-color:Green;
 padding:5px;
 text-align:center;
}
.m_warn{
 width:100%;
 height:35px;
 background-color:LightBlue;
 padding:5px;
 text-align:center;
}
.m_err{
 width:100%;
 height:35px;
 background-color:Crimson;
 padding:5px;
 text-align:center;
}

</style>

<script>

function forceSync (box,id)
{
     idx=box+'_img'+id;
    document.getElementById(idx).style.visibility='visible';
    var data = new Array();
    $.ajax({
        url: 'index.php?r=data/force-sync&id='+id+'&box='+box,
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res){     
            showSync(res);    
        },
        error: function(){
            alert('Error while retreving data!');
        }
    });	
}

function showSync(res)
{
  //document.location.reload(true);               
   idx=res['box']+'_img'+res['id'];
   oldbox=res['box']+'_box'+res['id'];
   newbox=res['newbox']+'_box'+res['id'];

   document.getElementById(idx).style.visibility='hidden';
   if (res['newbox'] == 0) $bg='Crimson';
   else if(res['newbox'] < 3) $bg='LightBlue';
   else                       $bg='Green';
   document.getElementById(newbox).style.background=$bg;
   document.getElementById(oldbox).style.background='White';
   
   console.log(res);
   
   
}
</script>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<p><b><?= Html::encode($this->title) ?></b>


<div  class='btn btn-primary leaf' style='background:<?php if ($model->errcnt >0) echo "Brown"; else echo "ForestGreen"; ?>; color:White; height:60px;' 
        href='#' onclick=""><div class='leaf-txt' style='font-size:12px;' >Просрочено: </div>
        <div class='leaf-val' style='font-size:20px;' ><?= $model->errcnt ?></div> </div> 
        

<?php


$columns =[];

$columns[0]= [
                'attribute' => 'title',
				'label'     => 'Название',
                'format' => 'raw',                                
                'contentOptions' => ['width' => '280px'],
                'value' => function ($model, $key, $index, $column) {                                
                return "<div style='width:275px;'>".$model['title']."</div>";
                }   		

            ];

$columns[]=  [
                'attribute' => 'lastSync',
				'label'     => 'Синхронизация',
				'contentOptions' => ['width' => '150px'],
                //'contentOptions' => ['style' => 'padding:0px;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                    return date("d.m.Y H:i", $model['lastSync']);
                }                                
               ];		

               
for ($i=0;$i<=4;$i++ )
{
$dt=strtotime(date('Y-m-d'))-$i*24*3600;
$columns[]=  [
                'attribute' => 'lastSync',
				'label'     => date("d.m", $dt),
				'contentOptions' => ['style' => 'padding:0px;','width' => '150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($dt, $i) {                                
                
                 $n=$i+1;
                 $class="m_norm";
                 $action = 'forceSync('.$n.', '.$model['id'].')';
                 if($i>1)  $class="m_warn";
                 if($i>=5) $class="m_err";
                
                $et=$dt+24*3600;
                if ($model['lastSync'] >= $et) {
                    $class="m_empty";
                    $action = '';   
                }
                if ($model['lastSync'] < $dt) {
                    $class="m_empty";
                    $action = '';   
                }
                
                 
                 
                 $id = $n.'_img'.$model['id'];
                 $boxid = $n.'_box'.$model['id'];

                 $img =  Html::img('@web/img/ajax-loader.gif', 
                    [   
                        'width' => '20px',
                        'height' => '20px',
                        'alt' => 'Загрузка',
                        'id'  => $id,
                        'style' => 'visibility:hidden;'
                    ]);

                 return \yii\helpers\Html::tag( 'div', $img, 
                   [
                     'class'   => 'clickable '.$class,                          
                     'onclick' => $action,
                     'style'  => 'padding:5px;',
                     'id'  => $boxid,
                   ]);
                }                                
               ];		
}               


$columns[]=  [
                'attribute' => 'lastSync',
				'label'     => 'просрочено',
				'contentOptions' => ['style' => 'padding:0px;', 'width' => '150px'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                $dt=time()-5*24*3600;
                $class="m_err";
                $action = 'forceSync(0,'.$model['id'].')';
                if ($model['lastSync'] >= $dt){
                    $class="m_empty";
                    $action = '';   
                }
                
                $id = '0_img'.$model['id'];
                $boxid = '0_box'.$model['id'];

                $img =  Html::img('@web/img/ajax-loader.gif', 
                    [   
                        'width' => '20px',
                        'height' => '20px',
                        'alt' => 'Загрузка',
                        'id'  => $id,
                        'style' => 'visibility:hidden;'
                    ]);

                 return \yii\helpers\Html::tag( 'div', $img, 
                   [
                     'class'   => 'clickable '.$class,                                       
                     'onclick' => $action,
                     'id'  => $boxid,
                   ]);
                }                                
               ];		
               

               
/*$columns[]=  [
                'attribute' => '-',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {                                
                $action ="";
                 return \yii\helpers\Html::tag( 'div', "<span class='glyphicon glyphicon-refresh'></span>", 
                   [
//                     'class'   => 'btn btn-primary btn-smaller',
//                     'id'      => 'today',
                     'onclick' => $action,
                     'class'   => 'clickable',
                   ]);

                }                                
               ];		

*/
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

