<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Статистика звонков';
$this->params['breadcrumbs'][] = $this->title;

$now=strtotime($date);

$prev=$now-24*3600;
$next=$now+24*3600;


 ?>


<link rel="stylesheet" type="text/css" href="phone.css" />
<script type="text/javascript" src="phone.js"></script> 

<h3> Статистика звонков </h3>

<div class ='row'>
  <div class ='col-md-4'>   
  </div>
   <div class ='col-md-1'>   
       <a href="index.php?r=zadarma/api/get-stat&date=<?= date("Y-m-d",$prev) ?>" ><span class='glyphicon glyphicon-backward'></span></a>   
   </div>
   <div class ='col-md-2' style='text-align:center'><b><?= date("d.m.Y",$now)  ?></b></div>
   <div class ='col-md-1' style='text-align:right'>    
       <a href="index.php?r=zadarma/api/get-stat&date=<?= date("Y-m-d",$next) ?>" ><span class='glyphicon glyphicon-forward'></span></a>   
   </div>
  <div class ='col-md-4'>   
  </div>
   
</div>


<table class='table table-striped'>
<?PHP 
$listResult= $model->getStatistics($date);
    $keys=$listResult['keys'];
    $fio= $listResult['fio'];
    $data=$listResult['data'];
    $N=count($keys);

echo "<tr>\n";
 echo "<th></th>\n";
 for($i=0;$i<$N;$i++)
 {
  $k=$keys[$i];   
   echo "<th>".$fio[$k]."</th>\n";  
 }
echo "</tr>\n";

for($h=8;$h<20;$h++)
{
  echo "<tr>\n";  
  echo " <td>$h</td>\n";  
   for($i=0;$i<$N;$i++)
    {
        $k=$keys[$i];   
        if (empty ($data[$k][$h]))$data[$k][$h]="&nbsp;";
        echo "  <td>".$data[$k][$h]."</td>\n";  
    }
    
    
  echo "</tr>\n";   
}


?>
</table>
<pre>
<?PHP 


//print_r($listResult);

?>

</pre>