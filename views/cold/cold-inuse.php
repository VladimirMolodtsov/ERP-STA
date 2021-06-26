<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;

$this->title = 'Организация в обработке';
//$this->params['breadcrumbs'][] = $this->title;

$info=$model->getUseData($id);

//print_r($info);
$startTimeInWork = $info['startTimeInWork'] + 60*60*7;

?>
  <h2><?= Html::encode($this->title) ?></h2>

<p> Вы выбрали для работы <b> <?php echo $info['Title'];?> </b> </p>  
<p> К сожалению Выбранная вами организация уже находится в обработке оператором <b><?php echo $info['userFIO'];?> </b>. </p>
<p> Время начала его работы <b><?php echo date("d.m.Y H:i:s", $startTimeInWork );?></b> </p>
<p> Оррганизация может бвть освобождена  через 30 минут после взятия в работу <b>(<?php echo date("d.m.Y H:i:s", $startTimeInWork +60*30);?>)</b>. </p>
<p> Если организация не доступна после указанного времени нажмите на кнопку "Обновить" в форме холодные звонки. </p>
<p> Вернитесь к выбору организации и обновите страницу (нажмите F5). </p>
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Вернутся к выбору" onclick="javascript:history.back();"/>

