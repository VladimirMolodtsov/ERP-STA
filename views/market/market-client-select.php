<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Alert;

$this->title = 'Список клиентов для менеджера';
//$this->params['breadcrumbs'][] = $this->title;
$curUser=Yii::$app->user->identity;
if ($curUser->roleFlg & 0x0080)
{
	
 echo "Помошник менеджера";
}

?>
<p>Менеджер <b><?= Html::encode($curUser->userFIO)?></b></p>
<h3><?= Html::encode($this->title) ?></h3>
<style>
.button {
    background-color: GhostWhite ;
    color: Gray ;
	border-color: Gray;
	text-align:right;
} 
</style>
 <script>
function openWin(url)
{
  wid=window.open("index.php?r="+url,'orgdetail','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}
 </script>  
 
 
 
 
<?php
if ($model->showMyClient==1) $alertText = "Список клиентов <b>назначенных на менеджера</b>";
else $alertText = "Список клиентов <b>доступных </b> менеджеру для работы, включая клиентов не закрепленные за другими менеджерами и клиентов доступных менеджеру как помошнику";

echo Alert::widget([
    'options' => [
        'class' => 'alert-info'
    ],
    'body' => $alertText
]);





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
                    $ret= "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\")' >".$model['title']."</a>";
                    if ($model['isOrgActive'] == 0) $ret = "<del>".$ret."</del>";
                    return $ret;
                },
            ],		
				
            [
	
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',                
				/*'filter'=>function ($model, $key, $index, $column) {
				return array("1"=>"Мои","2"=>"Все");
				},*/
            ],

            [
	
                'attribute' => 'nextContactDate',
				'label'     => 'Назначено',
                'format' => ['datetime', 'php:d.m.Y'],
            ],

            [
                'attribute' => 'contactDate',
				'label' => 'Последний Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by  id DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= date("d.m.Y", strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']."<br>".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

            [
                'attribute' => 'activeZakaz',
				'label' => 'Активные Заявки',
                'format' => 'raw',
                'filter'=>array("1"=>"Есть", "2"=>"Несколько"),
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT id, formDate from {{%zakaz}} where refOrg=:ref_org AND isActive=1 order by id DESC', 
				[':ref_org' => $model['id']])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= "<a href='index.php?r=market/market-zakaz&orgId=".$model['id']."&zakazId=".$resList[$i]['id']."'>№ ".$resList[$i]['id']." от ".date("d-m-Y", strtotime($resList[$i]['formDate']))."</a></br>\n";}
                return $ret."";
                },
            ],		

			 
            [
                'attribute' => 'activeSchet',
				'label' => 'Активные Счета',
                'format' => 'raw',
                'filter'=>array("1"=>"Есть", "2"=>"Несколько"),
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT id, schetNum, schetDate from {{%schet}} where refOrg=:ref_org AND isSchetActive=1 order by id DESC', 
				[':ref_org' => $model['id']])->queryAll();
				$ret="";
				for($i=0;$i<count($resList);$i++){$ret= "<a href='index.php?r=market/market-schet&id=".$resList[$i]['id']."'>№ ".$resList[$i]['schetNum']." от ".date("d-m-Y", strtotime($resList[$i]['schetDate']))."</a></br>\n";}
                return $ret."";
                },
            ],		
			
			[
                'attribute' => 'isOrgActive',
				'label' => 'Активен',
                'format' => 'raw',
                'filter'=>array("1"=>"Активные", "2"=>"Не активные"),
                'value' => function ($model, $key, $index, $column) {					

                if ($model['isOrgActive'] == 0) return "&nbsp;";
                    return "<a class='btn btn-primary button' href='index.php?r=market/market-zakaz-create&id=".$model['id']."'>Заявка</a>";
                },
            ],		
            
            
			
        ],
    ]
);
?>
