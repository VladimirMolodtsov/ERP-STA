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
        <input class="btn btn-primary <?php if($model->period ==30) echo "btn-selected"; ?>"  style="width:50px;" type="button" value="30" onclick="javascript:document.location.href='index.php?r=site/manager-org-stat&period=30';"/>
        </td>        
        <td>
        <input class="btn btn-primary <?php if($model->period ==60) echo "btn-selected"; ?>"  style="width:50px" type="button" value="60" onclick="javascript:document.location.href='index.php?r=site/manager-org-stat&period=60';"/>
        </td>        
        <td>
        <input class="btn btn-primary <?php if($model->period ==90) echo "btn-selected"; ?>"  style="width:50px" type="button" value="90" onclick="javascript:document.location.href='index.php?r=site/manager-org-stat&period=90';"/>
        </td>                

     </tr>    
    
</table>


<a href="#" onclick="openEditWin('site/manager-org-stat&period=<?= $model->period ?>&format=csv');"> Выгрузить</a> 


<h4>Число контрагентов связанных с менеджером</h4>
<?php
$period = $model->period;
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
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
            ],		

     	    [
                'attribute' => 'Нет контактов',
				'label'     => 'Нет контактов',
                'format' => 'raw',
         		'value' => function ($model, $key, $index, $column) use($period) {                    
                  
                $strSql= "SELECT count({{%orglist}}.`id`) from  {{%orglist}} left join
                  (SELECT COUNT(id) as N, ref_org from  {{%contact}} where  TO_DAYS(NOW()) - TO_DAYS({{%contact}}.contactDate) <= ".$period." 
                  group by ref_org ) as a on a.`ref_org` = {{%orglist}}.id where                    
                  {{%orglist}}.refManager = ".$model['userId']." and  a.N is NULL AND isOrgActive = 1";

                   $n =  Yii::$app->db->createCommand($strSql)->queryScalar();                
                    return "<a href='#' onclick='openWin(\"site/no-contact-org-stat&userId=".$model['userId']."&period=".$period."\",\"statwin\");'>".$n."</a>";
                }
                
            ],		


            
     	    [
                'attribute' => 'c1N',
				'label'     => 'от 1 до 5',
                'format' => 'raw',
         		'value' => function ($model, $key, $index, $column) use($period) {                    
                    return "<a href='#' onclick='openWin(\"site/detail-org-stat&userId=".$model['userId']."&period=".$period."\",\"statwin\");'>".$model['c1N']."</a>";
                }
                
            ],		
            
     	    [
                'attribute' => 'c5N',
				'label'     => '>5 нет счета',
                'format' => 'raw',
         		'value' => function ($model, $key, $index, $column) use($period) {
                   
                    return "<a href='#' onclick='openWin(\"site/no-schet-stat&userId=".$model['userId']."&period=".$period."\",\"statwin\");'>".$model['c5N']."</a>";
                }
                
            ],		

     	    [
                'attribute' => 'schN',
				'label'     => 'Выписан счет',
                'format' => 'raw',
         		'value' => function ($model, $key, $index, $column) use($period) {
                   
                    return "<a href='#' onclick='openWin(\"site/detail-schet-stat&userId=".$model['userId']."&period=".$period."\",\"statwin\");'>".$model['schN']."</a>";
                }
                
            ],		
            
     	    [
                'attribute' => 'sdlN',
				'label'     => 'Движение по сделке',
                'format' => 'raw',
         		'value' => function ($model, $key, $index, $column) use($period) {
                   
                    return "<a href='#' onclick='openWin(\"site/detail-schet-stat&userId=".$model['userId']."&period=".$period."\",\"statwin\");'>".$model['sdlN']."</a>";
                }

            ],		

        ],
    ]
	);
?>

