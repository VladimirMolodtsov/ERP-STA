<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;

$record=$model->loadOrgRecord();
?>

<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


<h3>Группировка контрагентов</h3>    
<br>
<font size="+1"> 
 <div class='row'>
    <div class='col-md-5'>
        Наименование контрагента: <?= Html::encode($record->title) ?>
    </div>    

    <div class='col-md-5' style='text-align:right;'>
    <?php if ($record->orgGrpRef == 0) echo "Не входит в групповой контакт.";  
                                 else  echo "Группа компаний: <b>".$model->orgGroupTitle."</b>";        
     ?>        
    </div>    
    
    <div class='col-md-1'>
        <a  class="btn btn-primary"  href='index.php?r=site/org-rm-grp&orgId=<?=  Html::encode($record->id)  ?>'>Сбросить</a>
    </div>    


    <div class='col-md-1'>

    </div>    
 </div>
</font>    
<br>
 
<div class="part-header"> Включить в состав</div>     
<br>
<?php
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
                'attribute' => 'orgGrpTitle',
				'label'     => 'Группа',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($record) {                    
                  return "<a  href='index.php?r=site/org-add-grp&grpId=".$model['id']."&orgId=".$record->id."'>".$model['orgGrpTitle']."</a>";  
				},
			],	
			
			[
                'attribute' => 'orgTitle',
				'label'     => 'Включает организации',
                'format' => 'raw',
				
               'value' => function ($model, $key, $index, $column) {

                $strSql= "select  distinct {{%orglist}}.id,{{%orglist}}.title from {{%orglist}} where 
                          {{%orglist}}.orgGrpRef = :orgGrpRef";
				$res = Yii::$app->db->createCommand ($strSql, [':orgGrpRef' => $model['id'],])->queryAll();
                $ret="";
                for ($i=0; $i< count($res); $i++ )
                {
                   $ret.= "<a href='index.php?r=site/org-detail&orgId=".$res[$i]['id']."' >".$res[$i]['title']."</a>"."<br>";                    
                }                
				return $ret;
                },

			],	

  	        [
                'attribute' => 'action',
				'label'     => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use($record) {                    
                  return "<div style='text-align:center;'><a  href='index.php?r=site/org-grp-del&grpId=".$model['id']."&orgId=".$record->id."'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span></a></div>";  
				},
			],	
            
            
            
    ]        
]);
?>

<div class="part-header"> Добавить новую группу</div>     
<br>
<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'editOrgGroupTitle')->label('Группа компаний:') ?>
    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>

