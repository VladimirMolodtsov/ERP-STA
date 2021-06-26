<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\bootstrap\Alert;


$curUser=Yii::$app->user->identity;
$this->title = 'Комментарий к доставке';


$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');

?>
<style>

.btn-small{
margin:2px;
padding:2px;
height:20px;
width:20px;
}
 
.gridcell {
	width: 100%;		
	height: 100%;
	/*background:DarkSlateGrey;*/
}	

.nonActiveCell {
	width: 100%;		
	height: 100%;	
	color:Gray;
	text-decoration: line-through;
}	

.gridcell:hover{
	background:DarkSlateGrey;
	color:#FFFFFF;
}

.grd_menu_btn
{
    padding: 2px;
    font-size: 10pt;
    width: 130px;
}

.table-local
{
    padding: 2px;
    font-size: 10pt;
}
</style>

<script type="text/javascript">
function setNote(id){
     window.parent.setDstNote(id);
}
</script>	

<?php  
echo \yii\grid\GridView::widget(
    [
		        	
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small table-local' ],
        'columns' => [
                    
            [
                'attribute' => 'note',
				'label'     => 'Комментарий',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {    
                /*Нет запроса - это только товар*/
                
                if (mb_strlen($model['note'],'utf-8')> 150)
                    $showTitle = mb_substr($model['note'],0,150,'utf-8')."..."; 
                else
                    $showTitle = $model['note']; 
                
                $action = "setNote(".$model['id'].");" ;                
                $val = \yii\helpers\Html::tag( 'div', $showTitle, 
                   [
                     'onclick' => $action,
                     'class'   => 'clickable',
                     'style'  => 'font-size:11px;',
                     'title'  => $model['note']
                   ]);

                 return $val;
               }
            ],		

             [
                'attribute' => 'isDefault',                
                'label'     => 'Основной',
                'format' => 'raw',                            
                'contentOptions' => ['style' => 'padding:0px;width:20px;text-align:center;'],                
                'value' => function ($model, $key, $index, $column) {                    
                 $style="";                                  
                 $id = $model['id'].'isDostavkaDefault';                 
                 if ($model['isDefault'] == 1) $style ="background-color:Green;";
                 else  $style ="background-color:White;";
                 $val = \yii\helpers\Html::tag( 'div',"", 
                   [
                     'class'   => 'btn btn-primary btn-small',
                     'id'      => $id,
                     'style'   => "font-size:10px;".$style,
                   ]);
                return $val;   
               }                
            ],  


        ],
    ]
	);

 

?>   
<br>   
   
	

