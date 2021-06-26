<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Холодные звонки';
$this->params['breadcrumbs'][] = $this->title;

?>
<style>
.button {
    background-color: #e7e7e7; color: black;
	box-shadow: 3px 3px;
    border: 1px;
    color: black;
    padding: 5px px;
	width: 150px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;	
} 
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 15px;
}
 .button_menu{
    padding: 15px;	 
 }
 
</style>

 <script>
function openWin(url)
{
  window.open("index.php?r="+url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1050,height=700'); 
  <?php
  /*window.open("index.php?r="+url,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=50,left=500,width=750,height=900'); */
  ?>
}
</script> 



  <h2><?= Html::encode($this->title) ?></h2>
 
<table  width="600px" border=1>  
<tr>
<td>Доступно к работе</td>
<td><?= Html::encode($model->availableCount()) ?></td>
<td width="100px"><input class="btn btn-primary" style="width: 150px;" type="button" value="Просмотреть" onclick="javascript:openWin('cold/cold-view');"/></td>
</tr>
  
<tr>
<td>Не взято в работу</td>
<td><?= Html::encode($model->noContactCount()) ?></td>
<td width="100px"><input class="btn btn-primary" style="width: 150px;" type="button" value="Взять в работу" onclick="javascript:openWin('cold/cold-init-select');"/></td>
</tr>

<tr>
<td>Не завершен первый контат</td>
<td><?= Html::encode($model->haveNoFinishedContactCount()) ?></td>
<td width="100px"><input class="btn btn-primary" style="width: 150px;"  type="button" value="Продолжить" onclick="javascript:openWin('cold/cold-init-continue');"/></td>
</tr>


<tr>
<td>Отказы</td>
<td><?= Html::encode($model->rejectCount()) ?></td>
<td width="100px"><input class="btn btn-primary"  style="width: 150px;" type="button" value="Просмотреть" onclick="javascript:openWin('cold/cold-reject');"/></td>
</tr>

<tr>
<td>Данные снабженца получены</td>
<td><?= Html::encode($model->haveContactCount()) ?></td>
<td width="100px"><input class="btn btn-primary"  style="width: 150px;" type="button" value="Продолжить" onclick="javascript:openWin('cold/cold-need-select');"/></td>
</tr>

<tr>
<td>Потребности известны</td>
<td><?= Html::encode($model->readyCount()) ?></td>
<td width="100px"><input class="btn btn-primary"  style="width: 150px;" type="button" value="Просмотреть" onclick="javascript:openWin('cold/cold-schet-select');"/></td>
</tr>

</table>


 
<div class="button_menu">
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Добавить контакт" onclick="javascript:openWin('cold/cold-new');"/>
<input class="btn btn-primary"  style="width: 150px;" type="button" value="Обновить" onclick="javascript:window.location='index.php?r=cold/refresh';"/>
</div>

