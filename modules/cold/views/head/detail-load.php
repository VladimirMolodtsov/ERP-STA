<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Холодная база - детализация загрузки';
$this->params['breadcrumbs'][] = $this->title;
?>


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
                'attribute' => 'orgTitle',
                'label'     => 'Клиент',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgRazdel',
                'label'     => 'Раздел',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgSubRazdel',
                'label'     => 'Подраздел',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgRubrica',
                'label'     => 'Рубрика',
                'format' => 'raw',
             ],        
             
             [
                'attribute' => 'orgArea',
                'label'     => 'Район',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgCity',
                'label'     => 'Город',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgDistrict',
                'label'     => 'Район',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgAdress',
                'label'     => 'Адрес',
                'format' => 'raw',
             ],        
             
             [
                'attribute' => 'orgX',
                'label'     => 'X',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgY',
                'label'     => 'Y',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgIndex',
                'label'     => 'Индекс',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgEMail',
                'label'     => 'E-Mail',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgPhoneList',
                'label'     => 'Телефоны',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'orgFAXList',
                'label'     => 'Факс',
                'format' => 'raw',
             ],        
             
             [
                'attribute' => 'orgURL',
                'label'     => 'Сайт',
                'format' => 'raw',
             ],        

             [
                'attribute' => 'isUniqe',
                'label'     => 'Уникален',
                'format' => 'raw',
             ],        
             

        ],
    ]
    );
?>


<!-- Service -->


<?php
Modal::begin([
    'id' =>'simpleContactDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    //'header' => '<h4> Менеджер ответственный за работу с организацией </h4>',
]);?><div style='width:650px'>

	<iframe width='600px' height='620px' frameborder='no'   src='index.php?r=store/purchase-ware-schet&noframe=1' seamless>
        Ваш браузер не поддерживает плавающие фреймы!
      </iframe>	  


</div><?php
Modal::end();
?>
