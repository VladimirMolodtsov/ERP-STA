<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\data\SqlDataProvider;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest == true){ return;}
    $curUser=Yii::$app->user->identity;
//if (!($curUser->roleFlg & 0x0020)) {return;}

$this->title = 'Сводная статистика контактов с клиентами по организациям';
//$this->params['breadcrumbs'][] = $this->title;

   $rangeTime = time() - 60*60*24*$model->period;
   $rangeDate = date ('Y-m-d', $rangeTime);

?>
  <h2><?= Html::encode($this->title) ?></h2>

  <link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 


  <style>

.local_btn
{
	padding: 2px;
	font-size: 10pt;
	width: 75px;	
	float:right;
}
		
 
</style>


 <script>

function openSaveFltWin()
{
  wid=window.open("index.php?r=site/save-flt-stat-orgs&fltName="+document.getElementById('fltName').value,'childwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600'); 
//  window.wid.focus();
}

function openDownloadWin()
{
  wid=window.open("index.php?r=site/download-stat-orgs&period=<?= $model->period ?>",'childwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600'); 
  window.wid.focus();
}


function setPeriod()
{
	document.location.href="index.php?r=site/stat-orgs&period="+document.getElementById('period').value; 
}


function loadFlt()
{
	document.location.href="index.php?r=site/stat-orgs&fltId="+document.getElementById('fltNameLst').value;
}

function setEnableWin(id, btn)
{
  document.getElementById(btn).disabled=true; 
  wid=window.open("index.php?r=market/helper-set-enable&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
 //window.wid.focus();
}

function setDisableWin(id, btn)
{
 document.getElementById(btn).disabled=true; 		
  wid=window.open("index.php?r=market/helper-set-disable&id="+id,'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50');   
  //window.wid.focus();
}


 </script>  


<div class='row'>

<div class='col-md-4'> 
	<input  name='period' id='period' value=<?= $model->period ?> >		
	<input class="btn btn-primary"  style="width: 175px;" type="button" value="Изменить период" onclick="javascript:setPeriod();"/>
</div>


<div class='col-md-4'> 
	<input type='text' id='fltName' value=''> 
	<a class="btn btn-primary" href='#' onclick='openSaveFltWin()'>Сохранить фильтр</a>
</div>


<div class='col-md-4'> 
<select name='fltNameLst' id='fltNameLst'>
<option <?php if ($fltId == 0) echo "selected" ?> value='0'> Сбросить фильтр </option>;
	<?php
	  $listFlt=$model->getListFltStatOrg();
	  for($i=0;$i< count($listFlt);$i++)
	  {
		 $sel=""; 
		if ($fltId == $listFlt[$i]['id']){$sel="selected";}  
		echo "<option ".$sel." value='".$listFlt[$i]['id']."'>".$listFlt[$i]['fltName']."</option>";
	  }
	?>
</select>
<input class="btn btn-primary"  style="width: 175px;" type="button" value="Загрузить" onclick="javascript:loadFlt();"/>
</div>

</div>

<div class='row'>
  <div class='col-md-4'> 
    <br>
	<p> Отчетный период <?= Html::encode($model->period) ?> дней </p>
  </div>	
</div>

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
                    return "<a href='#' onclick='openWin(\"site/org-detail&orgId=".$model['id']."\",\"orgList\")' >".$model['title']."</a>";
                },
            ],		

			
   	        [
                'attribute' => 'userFIO',
				'label'     => 'Менеджер',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
					
					return "<a href='#' onclick='openEditWin(\"site/chng-org-manager&orgId=".$model['id']."\")' >".$model['userFIO']."</a>";
			   },

			],	


		    [
                'attribute' => 'isAvailableForHelper',
				'label'     => 'Разреш. помощ.',
                'format' => 'raw',
				'filter'=>array("1"=>"Да","0"=>"Нет"),
                'value' => function ($model, $key, $index, $column) {
					
					if ($model['isAvailableForHelper'] >0 ){ $isFlg = true;}
					else                           { $isFlg = false;}
                    
					
					$ret = \yii\helpers\Html::tag(
                        'span',
                        $isFlg ? 'Yes' : 'No',
                        [
                            'class' => 'label label-' . ($isFlg ?  'success' : 'danger'),
                        ]
						);
						$id='btn_'.$model['id'];
					if ($model['isAvailableForHelper'] == 1) $ret.= "&nbsp;<input class='btn btn-primary local_btn' id='".$id."' type=button value='Запр.' onclick='javascript:setDisableWin(".$model['id'].",\"$id\");'>";
  				                                     else    $ret.= "&nbsp;<input class='btn btn-primary local_btn' id='".$id."' type=button value='Разр.' onclick='javascript:setEnableWin(".$model['id'].",\"$id\");'>";
	
					return "<nobr>".$ret."</nobr>";	
                },
            ],		

			
   	        [
                'attribute' => 'all_contacts',
				'label'     => 'Всего конт.',
                'format' => 'raw',
         
			],	


   	        [
                'attribute' => 'cur_month',
				'label'     => 'Конт. за период',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  use ($rangeDate) {
				$val = "";
				if ($model['cur_month'] == 0) return "&nbsp;";
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%contact}}.id) as cur, userFIO from  {{%contact}},  {{%user}}  
                where    {{%user}}.id = {{%contact}}.ref_user AND ({{%contact}}.contactDate > :rangeDate) AND  {{%contact}}.ref_org = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $model['id'],
				])->queryAll();
                for ($i=0; $i < count ($resList); $i++)
                {
                $val .= "<nobr>".$resList[$i]['userFIO'].": ". $resList[$i]['cur']."</nobr><br>";
                }
                $val .= "<br>Всего: ".$model['cur_month'];
                return $val;
                   }    
			],	
			
   	        [
                'attribute' => 'zakaz',
				'label'     => 'Заказов',
                'format' => 'raw',
               'value' => function ($model, $key, $index, $column)  use ($rangeDate) {
               
				$val = "";
				if ($model['zakaz'] == 0) return "&nbsp;";
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%zakaz}}.id) as cur, userFIO from  {{%zakaz}},  {{%user}}  
                where    {{%user}}.id = {{%zakaz}}.ref_user AND ({{%zakaz}}.formDate  > :rangeDate) AND  {{%zakaz}}.refOrg = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $model['id'],
				])->queryAll();
                for ($i=0; $i < count ($resList); $i++)
                {
                $val .= "<nobr>".$resList[$i]['userFIO'].": ". $resList[$i]['cur']."</nobr><br>";
                }
                $val .= "<br>Всего: ".$model['zakaz'];
                return $val;
                   }    
                
			],	

   	        [
                'attribute' => 'schet',
				'label'     => 'Счетов',
                'format' => 'raw',
                
                'value' => function ($model, $key, $index, $column)  use ($rangeDate) {
               
				$val = "";
				if ($model['schet'] == 0) return "&nbsp;";
                $resList = Yii::$app->db->createCommand("SELECT COUNT({{%schet}}.id) as cur, userFIO from  {{%schet}},  {{%user}}  
                where    {{%user}}.id = {{%schet}}.refManager  AND ({{%schet}}.schetDate   > :rangeDate) AND  {{%schet}}.refOrg = :ref_org group by userFIO", 
				[
				':rangeDate' =>$rangeDate,
				':ref_org' => $model['id'],
				])->queryAll();
                for ($i=0; $i < count ($resList); $i++)
                {
                $val .= "<nobr>".$resList[$i]['userFIO'].": ". $resList[$i]['cur']."</nobr><br>";
                }
                $val .= "<br>Всего: ".$model['schet'];
                return $val;
                   }  
                
			],	

		],
    ]
);
?>

<!--<a class="btn btn-primary" href='#' onclick='openDownloadWin()'>Выгрузить данные</a> -->
