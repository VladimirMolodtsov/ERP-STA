<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Доступные заявки';
$this->params['breadcrumbs'][] = $this->title;

?>
  <h2><?= Html::encode($this->title) ?></h2>
   
<script>
function openWin(url)
{
  wid=window.open("index.php?r=market/market-zakaz-create&id="+url,'zakazwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}
</script>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],		    
            'title:raw:Организация',
			'userFIO:raw:Менеджер',
            [
                'attribute' => 'contactDate',
				'label'     => 'Дата контакта',
                'format' => ['datetime', 'php:d-m-Y'],
            ],
			

            [
                'attribute' => 'nextContactDate',
				'label'     => 'Дата следущего контакта',
                'format' => ['datetime', 'php:d-m-Y'],
				
            ],
			
            [
                'attribute' => 'Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by id  DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="&nbsp;";				
				if (empty ($resList) ) {return "&nbsp;";}
				for($i=0;$i<count($resList);$i++){					
					if ( ($resList[$i]['contactFIO'] =="-" || $resList[$i]['contactFIO'] =="") && ($resList[$i]['note']=="")) {continue;}
					$ret= date("d.m.Y",strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']." ".$resList[$i]['note']."<br>\n";}
                    return $ret;
                },
            ],		
			
			[
                'attribute' => 'id',
				'label' => 'Взять в работу',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
				  return "<input class='btn btn-primary' style='width: 75px;'  type='button' value='Взять'  onclick='javascript:openWin(\"".$model['id']."\")'/>";
				
                    //return "<a class='btn btn-primary' href='index.php?r=market/market-zakaz-create&id=".$model['id']."'>Взять</a>";
                },
            ],		
			
        ],
    ]
);
?>

<script type="text/javascript">
window.opener.location.reload(false); 
</script>
