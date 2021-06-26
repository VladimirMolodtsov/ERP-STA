<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Закупки';
$this->params['breadcrumbs'][] = $this->title;
 ?>
 
<h2><?= Html::encode($this->title) ?></h2>

<p>Закупка товаров и услуг проходит в три стадии: </p>
<ol>
 <li> Заявка на закупку.
 <li> Запрос цены.
 <li> Закупка.
</ol>

<h4>Заявка на закупку</h4>
<p>  В ходе формирования заявки запрашивается предполагаемая номенклатура 
закупаемого товара и его количество. Если заявка на закупку формируется 
менеджером продаж, то автоматически осуществляется связь с заказом/счетом клиента.
В строке с такой заявкой в колонке "Запрос цены" отображаются символы 
<font color='green'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></font>  и 
<font color='Crimson'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span></font>.
Щелчок по символу <font color='green'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></font>  
создаст запрос на закупку а щелчок по 
<font color='Crimson'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span></font>
удалит запрос.
<div>
<img src='img/purchase-request.png'>
</div>                    
<h4>Запрос цены</h4>
<p>  В ходе работы с запросом цены уточняется закупаемая номенклатура и определяется количество и цена закупаемого товара.
Форма работы с запросом цены открывается по щелчку на номер запрос в соответствующем столбце.
Может объединять несколько  заявок на однотипный товар.
</p>

<h4>Закупка</h4>
<p>  Позволяет отслеживать прохождение этапов закупки у выбранного поставщика. 
Закупка связана со счетом, совпадает с ним по номенклатуре и может обьединять несколько запросов.
</p>