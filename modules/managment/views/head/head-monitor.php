<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use kartik\grid\GridView;
//use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Collapse;
use yii\bootstrap\Modal;


$this->title = 'Монитор собственника';
$curUser=Yii::$app->user->identity;



?>
<h3><?= Html::encode($this->title) ?></h3>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<style>
.headRow {
 font-size:15pt;      
}
.headRowVal {
 font-size:15pt;      
}


.marked {
  font-weight:bold;
  font-size:13pt;      
}

.simple {
//  font-weight:bold;
  font-size:13pt;      
}


</style>
  
<script>
  function cfgRow  (rowType)
  {
    openWin("/managment/head/monitor-row-cfg&stDate=<?= $model->stDate ?>&enDate=<?= $model->enDate ?>&rowType="+rowType,"childWin");
  } 
  
  function openPeriod()
  {
    stDate = document.getElementById('stDate').value;
    enDate = document.getElementById('enDate').value;
    document.location.href='index.php?r=managment/head/head-monitor&stDate='+stDate+"&enDate="+enDate;
  } 
  
  
  
</script>
<?php
?>

<div class ='row'>

   
   <div class ='col-md-2'>   
    <input class="form-control" type='date' name='stDate' id='stDate' value='<?= $model->stDate ?>'>   
   </div> 
    <div class ='col-md-2'> 
    <input class="form-control" type='date' name='enDate' id='enDate' value='<?= $model->enDate ?>'>
    </div> 
    <div class ='col-md-1'>
       <a href='#' onclick='openPeriod();' ><span class='glyphicon glyphicon-ok'></span></a>
   </div>
   
   
 
  <div class ='col-md-2' style='text-align:center'><?php // $model->syncDateTime ?></div>
  <div class='col-md-1' style='text-align:right;'><?php //<a href='#' onClick='syncBuhSchet();'><span class='glyphicon glyphicon-refresh'></span></a>?></div>  

  <div class ='col-md-4'>
       
  </div>
  
</div>

<div class='spacer'></div>
<table class='table' style="width:700px">

<?php $detailRow = $model->getMonitorDetailRow(1);
$label = "
<div class='row'>
  <div class='col-md-7 headRow'>   Изменение активов: </div>
  <div class='col-md-5 headRowVal'>".number_format($model->rowSum ,2,'.','&nbsp;')."</div>
 </div>
";
?>
<tr>
<td colspan=2>
<?php

$content = "<table class='table table-stripped'>";
$content .= $detailRow; 
$content .= "<tr>
<td colspan=4>
    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class ='col-md-1'><a href='#' onclick='cfgRow(1);'><span class='glyphicon glyphicon-cog'></span></a></div>
    </div>
</td>
</tr>
        
        </tbody>
    </table>
";

 echo Collapse::widget([
    'encodeLabels' => false,    
    'items' => [
        [
            'label' => $label,                                    
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>
</td>
</tr>
<!------------------>
<?php $detailRow = $model->getMonitorDetailRow(2);
$label="
<div class='row'>
  <div class='col-md-7 headRow'>  Изменение склада: </div>
  <div class='col-md-5 headRowVal'>".number_format($model->rowSum ,2,'.','&nbsp;')."</div>
 </div>
"; 
?>
<tr>
<td colspan=2>
<?php
$content = "<table class='table table-stripped'>";
$content .= $detailRow; 
$content .= "
<tr>
<td colspan=4>
    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class ='col-md-1'><a href='#' onclick='cfgRow(2);'><span class='glyphicon glyphicon-cog'></span></a></div>
    </div>
</td>
</tr>
        
        </tbody>
    </table>
";

 echo Collapse::widget([
   'encodeLabels' => false,
    'items' => [
        [
            'label' => $label,                                    
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>
</td>
</tr>
<!------------------>
<?php $detailRow = $model->getMonitorDetailRow(3);

$label="
<div class='row'>
  <div class='col-md-7 headRow'>  Изменение денежной массы: </div>
  <div class='col-md-5 headRowVal'>".number_format($model->rowSum ,2,'.','&nbsp;')."</div>
 </div>
";
?>
<tr>
<td colspan=2>
<?php
$content = "<table class='table table-stripped'>";
$content .= $detailRow; 
$content .= "
<tr>    
<td colspan=4>
    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class ='col-md-1'><a href='#' onclick='cfgRow(3);'><span class='glyphicon glyphicon-cog'></span></a></div>
    </div>
</td>
</tr>
        
        </tbody>
    </table>
";

 echo Collapse::widget([
    'encodeLabels' => false, 
    'items' => [       
        [
            'label' => $label,                        
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>
</td>
</tr>

<!------------------>
<?php $detailRow = $model->getMonitorDetailRow(4);
$label="
<div class='row'>
  <div class='col-md-7 headRow'>  Изменение кредита: </div>
  <div class='col-md-5 headRowVal'>".number_format($model->rowSum ,2,'.','&nbsp;')."</div>
 </div>
";
?>
<tr>
<td colspan=2>
<?php
$content = "<table class='table table-stripped'>";
$content .= $detailRow; 
$content .= "
<tr>
<td colspan=4>
    <div class ='row'>
        <div class ='col-md-10'></div>
        <div class ='col-md-1'><a href='#' onclick='cfgRow(4);'><span class='glyphicon glyphicon-cog'></span></a></div>
    </div>
</td>
</tr>
        
        </tbody>
    </table>
";

 echo Collapse::widget([
    'encodeLabels' => false, 
    'items' => [
        [
            'label' => $label,                        
            'content' => $content,
            'contentOptions' => ['class' => ''],
            'options' => []
        ]
    ]
]); 
?>
</td>
</tr>

</table>


<?php 

/*echo "<pre>\n";
  print_r ($model->debug);
echo "</pre>\n";
*/
 ?>
