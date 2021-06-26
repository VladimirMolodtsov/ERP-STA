<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор счета';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
idList=new Array();
function setSchet() {
    
 var strRequest = '';
 for (i=0; i<idList.length; i++)
 {
   if (document.getElementById(idList[i]).checked)  strRequest = strRequest +idList[i]+',';
  }
 
	window.parent.closeMultiSchetList(strRequest);
}

</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
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
                'attribute' => 'schetNum',
				'label'     => 'Счет №',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   
                   $strSql = "SELECT SUM(goodSumm) FROM {{%supplier_schet_content}} where schetRef = :schetRef";                   
                   $sum =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['id'],])->queryScalar();                                       
				   return $model['schetNum']."<br>на&nbsp;сумму&nbsp;".number_format($sum,2,".","&nbsp;");
                },

            ],		
			[
                'attribute' => 'schetDate',
				'label'     => 'Дата',
                'format' => 'raw',
            ],		
            
			[
                'attribute' => 'orgTitle',
				'label'     => 'Поставщик',
                'format' => 'raw',
            ],		
			[
                'attribute' => 'Товар',
				'label'     => 'Товар',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                   $strSql = "SELECT goodTitle FROM {{%supplier_schet_content}} where schetRef = :schetRef LIMIT 4";                   
                   $wareList =  Yii::$app->db->createCommand($strSql, [':schetRef' => $model['id'],])->queryAll();                    
                   $ret="";
                    for ($i=0; $i<count($wareList); $i++ )
                    {
                     $ret.=mb_substr($wareList[$i]['goodTitle'],0,50,'utf-8');
                     if ($i>= 1) break;
                     $ret.="<br>";
                    }
                    if ($i<count($wareList)) $ret.="<br>...";                    
					return $ret;
                },
            ],		
            
            [	
                'attribute' => 'Выбрать',
				'label'     => 'Выбрать',
				'format' => 'raw',
				'value' => function ($model, $key, $index, $column) {
                  
                    $id = $model['id'];
                    $script="<script>idList.push('".$id."');</script>";	
                 return	"<input type=checkbox id='".$id."'>".$script;
					
				}				
            ],

            
        ],
    ]
    
	);
?>

<div class="row">  
    <div class="col-md-9">
   </div>   

    <div class="col-md-3">
		<input class="btn btn-primary"  style="width:220px;" type="button" value="Создать"  onclick="javascript:setSchet();" />
   </div>   

   
</div>      
