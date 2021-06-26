<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Выбор организации для доставки';
$curUser=Yii::$app->user->identity;

?>
<style>

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<script type="text/javascript">
function setOrg(id, inn, title, adress) {
    
   <?php if($act ==1){ ?>     
	window.parent.document.getElementById('deliversform-reforg').value=id;    
	window.parent.document.getElementById('orgINN').innerHTML=inn;    
	window.parent.document.getElementById('orgTitle').innerHTML=title;    
	window.parent.document.getElementById('orgAdress').innerHTML =adress;    
	window.parent.closeOrgList();
   <?php }
   if($act ==2){ ?> 

    window.parent.document.getElementById('deliversform-reffromorg').value=id;    	
	window.parent.document.getElementById('orgFromTitle').innerHTML=title;   
    window.parent.document.getElementById('orgFromAdress').innerHTML =adress;            
	window.parent.closeOrgList();
    
   <?php } ?> 
}
</script >

<h3><?= Html::encode($this->title) ?></h3>

<?php

echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $orgListProvider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
			[
                'attribute' => 'title',
				'label'     => 'Наименование',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
		
		$adressList = Yii::$app->db->createCommand(
            'SELECT id, [[index]], area, city, district, adress, isOfficial from {{%adreslist}} where ref_org =:refOrg and isBad=0 order by isOfficial DESC', 
            [':refOrg' => $model['id']])->queryAll();
		$orgAdress="";
		if (count ($adressList)>0)
		{
			$orgAdress=    "<b>Индекс:</b>".$adressList[0]["index"]." ";		
			$orgAdress .=  "<b>Область:</b>".$adressList[0]["area"]." ";
			$orgAdress .=  "<b>Город:</b>".$adressList[0]["city"]." ";	
			$orgAdress .=  "<b>Адрес:</b>".$adressList[0]["adress"];		
		}
					return "<a href='#' onclick='javascript:setOrg(\"".$model['id']."\",\"".$model['schetINN']."\", \"".$model['title']."\", \"".$orgAdress."\" );' >".$model['title']."</a>";
                },
            ],		
        ],
    ]
	);
?>