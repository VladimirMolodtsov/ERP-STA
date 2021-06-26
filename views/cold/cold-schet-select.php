<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Холодные звонки - получить первичную заявку на счет';
//$this->params['breadcrumbs'][] = $this->title;
// [js]<script type="text/javascript">window.opener.location.reload(false); </script>[/js] 
 
mb_internal_encoding("UTF-8");
?>
  <h2><?= Html::encode($this->title) ?></h2>
 
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
                'attribute' => 'title',
				'label' => 'Организация',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {					
                    return "<a href='index.php?r=site/org-detail&orgId=".$model['id']."'>".$model['title']."</a>";
                },
            ],					
			
//            'have_phone:raw:Известно телефонов',
			'area:raw:Область',
			'city:raw:Город',
			'razdel:raw:Разделы',			

		    /*[
                'attribute' => 'isSchetReject',
				'label'     => 'Отказ',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isSchetReject'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    return \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ?  'danger' : 'success'),
                        ]
						);
                },
            ],		

            [
                'attribute' => 'SchetRejectDate',
				'label'     => 'Дата отказа',
                'format' => ['datetime', 'php:d-m-Y'],
            ],*/			
            [
                'attribute' => 'contactDate',
				'label'     => 'Дата контакта',
                'format' => ['datetime', 'php:d-m-Y'],
            ],			
            [
                'attribute' => 'Контакт',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT note, contactFIO, contactDate from {{%contact}} where ref_org=:ref_org order by id  DESC LIMIT 1 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$ret="&nbsp;";				
				if (empty ($resList) ) {return "$nbsp;";}
				for($i=0;$i<count($resList);$i++){					
					if ( ($resList[$i]['contactFIO'] =="-" || $resList[$i]['contactFIO'] =="") && ($resList[$i]['note']=="")) {continue;}
					$ret= date("d.m.Y",strtotime($resList[$i]['contactDate']))." ".$resList[$i]['contactFIO']." ".$resList[$i]['note']."<br>\n";}
                    return "$ret";
                },
            ],		

            [
                'attribute' => 'Интересы',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
				$resList = Yii::$app->db->createCommand('SELECT need_size, Title, title_size from {{%need}}, {{%need_title}}, {{%need_size}} 
				                                         where {{%need}}.need_title_id={{%need_title}}.id AND {{%need}}.need_size={{%need_size}}.id
				                                         AND ref_org=:ref_org order by need_size DESC, {{%need_title}}.id  LIMIT 3 ', 
				[':ref_org' => $model['id'],])->queryAll();
				$res="";
				for($i=0;$i<count($resList);$i++){					
					$res .= "<nobr>".mb_substr($resList[$i]['Title'], 0, 20)."...</nobr><br>";				
				}
				return $res;
				}
				
            ],		
			
			[
                'attribute' => 'Взять в работу',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    return "<a class='btn btn-primary' href='index.php?r=cold/cold-schet&id=".$model['id']."'>Взять</a>";
                },
            ],		
			
        ],
    ]
);
?>