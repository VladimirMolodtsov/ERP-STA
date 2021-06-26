<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Управление доступностью клиентов помошника менеджера';
//$this->params['breadcrumbs'][] = $this->title;
$curUser=Yii::$app->user->identity;

$fromTime = time() - 60*60*24*30;
$period = (time()-$fromTime)/(60*60*24);

?>
<h3><?= Html::encode($this->title) ?></h3>
<style>
.button {
    background-color: DarkRed;
    color: white  ;
	border-color: DarkRed ;
	text-align:right;
} 
</style>
<script>
function setEnableWin(id, btn)
{
  document.getElementById(btn).disabled=true; 
  wid=window.open("index.php?r=market/helper-set-enable&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
 //window.wid.focus();
}

function setDisableWin(id, btn)
{
 document.getElementById(btn).disabled=true; 		
  wid=window.open("index.php?r=market/helper-set-disable&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50');   
  //window.wid.focus();
}

</script>

<p> Период анализируемой активности <?=$period?> дней c <?php echo date("d-m-Y", $fromTime); ?>. </p>  
  
<?php
//echo "<pre>";
//print_r ($provider);
//echo "</pre>";

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
		                
			[
                'attribute' => 'title',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['id']."'>".$model['title']."</a>";
                },
            ],						
            [	
                'attribute' => 'userFIO',				
				'label'     => 'Менеджер',                
            ],

            [
                'attribute' => 'LastContact',
				'label' => 'Контакт в базе',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","2"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					$fromTime = time() - 60*60*24*30;
					if ( strtotime($model['LastContact']) > $fromTime){ $isFlg = true;}
					else                        { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		

            [
                'attribute' => 'last1CDate',
				'label' => 'Контакт в 1С',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","2"=>"Нет"),
				 //'format' => ['datetime', 'php:d-m-Y'],
                'value' => function ($model, $key, $index, $column) {
					$fromTime = time() - 60*60*24*30;
					if ( strtotime($model['last1CDate']) > $fromTime){ $isFlg = true;}
					else                        { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ? 'success' : 'danger'),
                        ]
						);
                },
            ],		
			
			[
                'attribute' => 'isAvailableForHelper',
				'label' => 'Доступ помошника',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","2"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {			
                 $id = "btn_".$model['id'];				
				 //onclick='javascript:setDisableWin(".$model['id'].",\"$id\");'
				 //onclick='javascript:setEnableWin(".$model['id'].",\"$id\");'
				if ($model['isAvailableForHelper'] == 1) return "<input class='btn btn-primary button' id='".$id."' type=button value='Запретить' onclick='javascript:setDisableWin(".$model['id'].",\"$id\");'>";
												 else    return "<input class='btn btn-primary' id='".$id."' type=button value='Разрешить' onclick='javascript:setEnableWin(".$model['id'].",\"$id\");'>";
                },
            ],		
			
        ],
    ]
);
?>