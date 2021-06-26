<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$curUser=Yii::$app->user->identity;


?>

<script>
function setPhone(phone, phoneContactFIO)
{
  window.parent.setPhone(phone, phoneContactFIO);
}
</script>

<h3><?= $model->contactFIO ?></h3>

<?php Pjax::begin(); ?>  
<?php
echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
          'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],          
        'columns' => [
            [
                'class' => \yii\grid\SerialColumn::class,
            ],
            
             [
                'attribute' => 'phone',
                'label'     => 'Телефон',                
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                
                
                if (!empty ($model['phone'])){
                    return 
                    "<a href='#' onclick='javascript:setPhone(\"".Html::encode($model['phone'])."\",\"".Html::encode(trim($model["phoneContactFIO"]))."\");'>".Html::encode($model['phone'])."</a>";                    
                    }
                return "&nbsp;";
                    }
                    
            ],
            
            [
               'attribute' => 'lastD',
                'label' => 'Дата контакта',
                'format' => ['datetime', 'php:d.m.Y'],
            ],
          

        ],
    ]
);
?>
<?php Pjax::end(); ?>

