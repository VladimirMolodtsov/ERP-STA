<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Collapse;

$this->title = 'Выберите счет 1С';
$curUser=Yii::$app->user->identity;

$this->registerJsFile('@web/phone.js');
$this->registerCSSFile('@web/phone.css');
$model ->initData();
?>
<style>
.page-title
{    
  font-size: 14pt;
  font-weight:bold;
}
.panel-heading
{    
 padding:2px;
}

.panel-body
{    
 padding:2px;
}
.summary
{
   display:none; 
}
.collapse-toggle
{
  font-size:12px;  
}
</style>


<script type="text/javascript">
function setRef(id) {
    //window.parent.closeClientSchetList(id);
    window.opener.closeClientSchetList(id);
    document.location.reload(true); 
}

function switchFltOrg(fltOrg)
{
  document.location.href='index.php?r=market/client-schet-select&noframe=1&refSchet=<?= $model->refSchet ?>&refOrg=<?= $model->refOrg ?>&fltOrg='+fltOrg;      
}

function openOrg()
{
     var url = 'site/org-detail&orgId=<?= $model->refOrg ?>';
    openWin(url,'orgWin')
}

</script >

<div class='btn btn-default' onclick='switchFltOrg(0)' >Все организации</div>
<div class='btn btn-default' onclick='switchFltOrg(1)' >По счету</div>

<h3><?= Html::encode($this->title) ?></h3>

<p> Текущий контрагент:<b>
<?php
 if (empty($model->refOrg)) echo " Контрагент не задан";
                      else  echo " <span class='clickable' onclick='openOrg();' id='orgTitle'>".$model->showOrgTitle."</span>";
?>
</b></p>
<p> Текущий привязанный счет (при перепривязке будет заменен):
<?php
 if (empty($model->refClientData)) echo "счет не задан";
                      else  {                      
 echo "<b> № ".$model->refClientData['schetRef1C']." от ".date("d.m.Y", strtotime($model->refClientData['schetDate']))." на сумму ".number_format($model->refClientData['sum'],2,'.','&nbsp;')."</b>";                                            
 
 echo "<table class='table table-striped table-bordered'>";
 echo "<tr>";
     echo "<td>";
     echo "Товар";
     echo "</td>";
     echo "<td>";
     echo "Количество";
     echo "</td>";
     echo "<td>";
     echo "Ед.изм.";
     echo "</td>";
     echo "<td>";
     echo "Цена";
     echo "</td>";
     echo "<td>";
     echo "Сумма";
     echo "</td>";
     
 echo "</tr>";
 
 
 for ($i=0;$i<count($model->refClientDetail); $i++)
 { 
 echo "<tr>";
     echo "<td>";
     echo $model->refClientDetail[$i]['wareTitle'];
     echo "</td>";
     echo "<td>";
     echo $model->refClientDetail[$i]['wareCount'];
     echo "</td>";
     echo "<td>";
     echo $model->refClientDetail[$i]['wareEd'];
     echo "</td>";
     echo "<td>";
     echo number_format($model->refClientDetail[$i]['wareSum']/$model->refClientDetail[$i]['wareCount'],2,'.',"&nbsp;")." р.";
     echo "</td>";
     echo "<td>";
     echo number_format($model->refClientDetail[$i]['wareSum'],2,'.',"&nbsp;")." р.";
     echo "</td>";
     
 echo "</tr>";
 }
 echo "</table >";
}                      

?>


<hr>
<?php


echo \yii\grid\GridView::widget(
    [
        'dataProvider' => $provider,
		'filterModel' => $model,
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '&nbsp;'],
		'tableOptions' => [ 'class' => 'table table-striped table-bordered table-small' ],
        'columns' => [
   			[
                'attribute' => 'schetDate',
				'label'     => 'Дата',
                'format' => 'raw',         
                'value' => function ($model, $key, $index, $column) {
                  return date("d.m.Y", strtotime($model['schetDate']));  
                }
            ],		

   			[
                'attribute' => 'orgTitle',
				'label'     => 'Контрагент',
                'format' => 'raw',         
            ],		
            
			[
                'attribute' => 'schetRef1C',
				'label'     => 'Ссылка',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)  {               
                
              $refClientData =  Yii::$app->db->createCommand( 'SELECT sum(wareSum) as sum, schetRef1C,  schetDate  
                from {{%client_schet_header}} left join {{%client_schet_content}} on {{%client_schet_header}}.id={{%client_schet_content}}.refHeader
                where {{%client_schet_header}}.id =:refClientSchet', 
                [':refClientSchet' => $model['id'] ])->queryOne();
      
              $refClientDetail =  Yii::$app->db->createCommand( 'SELECT wareTitle, wareCount, wareEd, wareSum  
                from {{%client_schet_content}} where refHeader =:refClientSchet', 
                [':refClientSchet' => $model['id'] ])->queryAll();
                
                $v =$model['schetRef1C'];
                if (!empty($refClientData)){
                $v = " № ".$refClientData['schetRef1C']." от ".date("d.m.Y", strtotime($refClientData['schetDate']));
                $v .=" На сумму ". number_format($refClientData['sum'],2,'.',' ');  
                }
                $contentWare ="";
                if (!empty($refClientDetail)) {
                    $contentWare ="<table class='table table-striped table-bordered'>";
                     for ($i=0;$i<count($refClientDetail); $i++)
                     { 
                         $contentWare .="<tr>";
                         $contentWare .="<td>";
                         $contentWare .=$refClientDetail[$i]['wareTitle'];
                         $contentWare .="</td>";
                         $contentWare .="<td>";
                         $contentWare .=$refClientDetail[$i]['wareCount'];
                         $contentWare .="</td>";
                         $contentWare .="<td>";
                         $contentWare .=$refClientDetail[$i]['wareEd'];
                         $contentWare .="</td>";
                         $contentWare .="<td>";
                         $contentWare .=number_format($refClientDetail[$i]['wareSum']/$refClientDetail[$i]['wareCount'],2,'.',"&nbsp;")." р.";
                         $contentWare .="</td>";

                         $contentWare .="<td>";
                         $contentWare .=number_format($refClientDetail[$i]['wareSum'],2,'.',"&nbsp;")." р.";
                         $contentWare .="</td>";
                         $contentWare .="</tr>";
                     }
                     $contentWare .="</table >";
                }
                return Collapse::widget([
                'items' => [
                        [
                            'label' => $v,
                            'content' => $contentWare,
                            'contentOptions' => ['class' => ''],
                            'options' => []
                        ]
                    ]
                ]); 
                
               // return "<a href='#' onclick='javascript:setRef(".$model['id'].");' >".$v."</a>";
                },
            ],		

            
                        
			[
                'attribute' => 'schetRef1C',
				'label'     => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {               
                return "<a href='#' onclick='javascript:setRef(".$model['id'].");' >
                <span class='glyphicon glyphicon-plus'></span>
                </a>";
                },
            ],		
        ],
    ]
	);
?>

<?php
if(!empty($model->debug)){
    echo "<pre>";
    print_r ($model->debug);
    echo "</pre>";
}
?>
