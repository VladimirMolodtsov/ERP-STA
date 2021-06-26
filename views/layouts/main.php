<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper; 

AppAsset::register($this);

$request = Yii::$app->request; 
$noframe =  intval($request->get('noframe',0));	

$list = Yii::$app->db->createCommand( 'SELECT id, keyValue from {{%config}}          
          where  id IN(1300,1301,1302)')->queryAll();
$cfgList =  ArrayHelper::map($list, 'id', 'keyValue');           

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>

<style>
.local-nav{
/*    background:DarkGreen; */
    background:<?= $cfgList['1301'] ?>;  
}
.navbar-default .navbar-nav > li > a {
    color: Crimson;
}
.navbar-inverse .navbar-nav > li > a{
    color: White;
}

</style>

<script>

function showSearchDialog(){
    $('#searchDialog').modal('show');       
}    

function startSearch()
{
 $('#searchDialog').modal('hide');       
 var url= 'index.php?r=market/market-search&noframe=1&findString='+document.getElementById('searchInput').value;
 window.open(url, 'childwin','toolbar=no, scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
/* window.wid.focus(); */
}
</script>
<?php $this->beginBody(); ?>


<?php if( $noframe != 1 ) { ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => $cfgList[1300],
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top local-nav',
        ],
    ]);
    
   /*echo " <a  class='btn btn-primary' 
   style='position:relative; padding:10px; left:20px; top:5px; background:ForestGreen; color:White; height: 40px;	width:  150px;' href='#' 
   onclick=\"javascript:wid=window.open('https://docs.google.com/forms/d/e/1FAIpQLSdoUum_gzvTPmxovFGo1Pte9M_csNj1xPNKp_sYg2SFqlymNA/viewform', 'childwin','toolbar=no, scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); window.wid.focus();\">
   <div style='font-size:15px'>Запрос товара</div></a>";
*/
  if (!empty($cfgList['1302'])){
   echo " <a  class='btn btn-primary' 
   style='position:relative; padding:10px; left:50px; top:5px; background:LightBlue; color:Black; height: 40px;	width:  100px;' href='#' 
   onclick=\"javascript:wid=window.open('".$cfgList['1302']."', 'childwin','toolbar=no, scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); window.wid.focus();\">
   <div title='Письмо контрагенту' style='font-size:15px'>Письмо</div></a>";
   }
if (!Yii::$app->user->isGuest)    
   echo  \yii\helpers\Html::tag( 'div', '<span class="glyphicon glyphicon-search"></span>', 
                   [
                     'class'   => 'clickable',
                     'onclick' => 'showSearchDialog();',
                     'style' => "color:White;position:relative;display:inline;font-size:15px;left:75px; top:10px; padding-right:75px;",                
                     'title' =>  'Поиск контрагента',
                   ]);
   
    
   if (!Yii::$app->user->isGuest) 
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'encodeLabels' => false,
        'items' => [    
            ['label' => 'Навигация', 'items' => [            
                ['label' => 'Home','url' => ['/site/index']],                
                ['label' => 'Холодная база','url' => ['/cold/site']],
                ['label' => 'Отдел продаж','url' => ['/market/market-start']],
                ['label' => 'Коммерческий директор','url' => ['/head/market-head']],
                ['label' => 'Управление','url' => ['/site/head-start']],
                ['label' => 'Диспетчер','url' => ['/tasks/dispetcher']],                
                ['label' => 'Отдел снабжения','url' => ['/store/sclad-start']],
                ['label' => 'Работа с персоналом','url' => ['/site/personal-start']],
                ['label' => 'Финансовые документы','url' => ['/fin/fin-start']],
                ['label' => 'Банковская выписка','url' => ['/bank/operator']],      
                ['label' => 'Загруженные выписки','url' => ['/bank/operator/extract-list']],      
                ['label' => 'Документы','url' => ['/bank/operator/doc-list']],                      
                ['label' => 'Документы на оплату','url' => ['/bank/buh/store-pay']],                      
            ]],   

            ['label' => 'Товар', 'items' => [            
                ['label' => 'Отдел снабжения','url' => ['/store/sclad-start']],
                ['label' => 'Документы на оплату','url' => ['/bank/buh/store-pay']],                      
                ['label' => 'Внутренняя номенклатура','url' => ['/store/ware-list']],
                ['label' => 'Товары поставщиков','url' => ['/store/ware-show']],                      
                ['label' => 'Товары на складах','url' => ['/store/ware-sclad']],
                ['label' => 'Товары реализации','url' => ['/store/ware-names']],
                ['label' => 'Отвесы','url' => ['/store/otves-list']],
            ]],   

            
            ['label' => 'Коммуникации', 'items' => [            
                ['label' => 'E-mail','url' => ['/site/get-mail']],                
                ['label' => 'Звонки-список','url' => ['/zadarma/ats/show-log']],
                ['label' => 'Телефонная книга','url' => ['/site/phone-book']],
                ['label' => 'Справочник E-mail','url' => ['/site/email-book']],
                ['label' => 'Взамодействие','url' => ['/site/org-deals']],
                ['label' => 'Реестр контрагентов','url' => ['/site/org-reestr']],
                ['label' => 'Поиск клиентов','url' => ['/head/client-search']],
            ]],   
                        
            ['label' => 'Синхронизация с 1С', 'items' => [            
                ['label' => 'Продажи и клиенты','url' => ['/data/sync-all'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Закупки и поставщики','url' => ['/data/sync-supplier-all'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Склад и товар','url' => ['/data/sync-price'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Состояние склада и отвесы','url' => ['/data/sync-price'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Состояние счета','url' => ['/data/sync-bank'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Сверка долга','url' => ['/data/sync-sverka'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Договора','url' => ['/data/sync-google-contract'], 'linkOptions' => ['target'=>'_blank']],
                ['label' => 'Статус','url' => ['/data/sync-status']],
            ]],   
            
	        ['label' => '<span class="glyphicon glyphicon-cog"></span>', 'items' => [            
	            ['label' => 'Карточка собственника','url' => ['site/self-card']],
	            ['label' => 'Пользователи и роли','url' => ['site/role']],
	            ['label' => 'Контактные адреса','url' => ['site/config']],
                ['label' => 'Сроки исполнения (лиды)','url' => ['/option/lead-config']],
                ['label' => 'Взаимодействие с конрагентом','url' => ['/site/org-deals-cfg']],
                ['label' => 'Классификатор документов','url' => ['/bank/operator/doc-classify']],
                ['label' => 'Склады отгрузки','url' => ['store/deliver-sclad-list']],
                
            ]],   
	        
	        
	        
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>
<?php }?>
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>


<?php 
if( $noframe != 1 ) { ?>
<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; ООО СТА <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>
<?php }?>

<?php
Modal::begin([
    'id' =>'searchDialog',
    'closeButton' => ['tag' => 'button', 'label' => 'x',],
    'header' => '<h4> Поиск контрагента </h4>',
]);?>
<form id='searchForm' onsubmit='startSearch(); return false;'>
<div class='row'>
    <div class='col-sm-10'>
<?php
echo Html::textInput( 
                'searchInput', 
                '',                                
                [
                 'class' => 'form-control',             
                 'id' => 'searchInput',                  
                 'style' => 'width:450px'
                ]);
?>
    </div>
    <div class='col-sm-2'>
<?php                
echo  Html::submitButton('<span class="glyphicon glyphicon-search"></span>', 
    [
        'class' => 'btn btn-default',         
    ]);                 
?>
    </div>
</div>
</form>
<?php Modal::end();
?>



<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>



