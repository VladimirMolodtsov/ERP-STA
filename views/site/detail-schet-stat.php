<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Статистика работы менеджеров с предприятиями';

?>
<style>
.btn-selected{    
 background-color: WhiteSmoke;   
 color: Black;   
}

</style>

<link rel="stylesheet" type="text/css" href="tcal.css" />
<script type="text/javascript" src="tcal.js"></script> 

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3><?= Html::encode($this->title) ?></h3>

<table border='0' width='160'>
    <tr>
        <td>
        <input class="btn btn-primary <?php if($model->period ==30) echo "btn-selected"; ?>"  style="width:50px;" type="button" value="30" onclick="javascript:document.location.href='index.php?r=site/detail-schet-stat&userId=<?= $model->userId ?>&period=30';"/>
        </td>        
        <td>
        <input class="btn btn-primary <?php if($model->period ==60) echo "btn-selected"; ?>"  style="width:50px" type="button" value="60" onclick="javascript:document.location.href='index.php?r=site/detail-schet-stat&userId=<?= $model->userId ?>&period=60';"/>
        </td>        
        <td>
        <input class="btn btn-primary <?php if($model->period ==90) echo "btn-selected"; ?>"  style="width:50px" type="button" value="90" onclick="javascript:document.location.href='index.php?r=site/detail-schet-stat&userId=<?= $model->userId ?>&period=90';"/>
        </td>                
    </tr>    
    
</table>

<a href="#" onclick="openEditWin('site/detail-org-stat&period=<?= $model->period ?>&format=csv');"> Выгрузить</a> 
<?php
if (!empty($model->userId)) echo "<p><b>".$model->userFIO."</b></p>";
echo "<p>Показаны контрагенты доступные для менеджера (включая доступные для помошников).</p>";
?>

<?php
$period = $model->period;
$userId = $model->userId;
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		//'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
			[
                'attribute' => 'title',
				'label' => 'Контрагент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {	                    
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\", \"childwin\")' >".$model['title']."</a>";
                },
            ],		
                 
     	    [
                'attribute' => 'c1N',
				'label'     => 'Контактов',
                'format' => 'raw',
                
            ],	

     	    [
                'attribute' => 'Контактов менеджера',
				'label'     => 'Контактов менеджера',
                'format' => 'raw',
          		'value' => function ($model, $key, $index, $column) use($period, $userId) {                    
                  
                $strSql= "SELECT COUNT(id) as N FROM {{%contact}} where  TO_DAYS(NOW()) - TO_DAYS({{%contact}}.contactDate) <= ".$period." 
                and ref_user = ".$userId." AND ref_org = ".$model['id'];
                $n =  Yii::$app->db->createCommand($strSql)->queryScalar();                
                    return "<a href='#' onclick='openWin(\"site/contacts-detail&id=".$model['id']."\",\"statwin\");'>".$n."</a>";
                }
            ],		
            

     	    [
                'attribute' => 'schN',
				'label'     => 'Счетов',
                'format' => 'raw',
                
            ],		
            

     	    [
                'attribute' => 'oplata',
				'label'     => 'Оплаты',
                'format' => 'raw',                
            ],		
            
     	    [
                'attribute' => 'supply',
				'label'     => 'Поставки',
                'format' => 'raw',
                
            ],		
            
            
            

            
        ],
    ]
	);
?>

